<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pembayaran daring lewat Midtrans Snap.
 *
 * Tanpa kunci di .env, halaman pesanan tetap bisa dipakai: tombol bayar
 * menampilkan penjelasan, bukan error. Alur pesanan manual tidak terganggu.
 */
class PembayaranController extends Controller
{
    public function bayar(string $kode): RedirectResponse
    {
        $order = $this->cari($kode);

        if (! $this->aktif()) {
            return back()->with('error', 'Pembayaran daring belum aktif karena kunci Midtrans belum diisi. Pesanan tetap bisa dikonfirmasi manual oleh penjual.');
        }

        if ($order->status === 'batal') {
            return back()->with('error', 'Pesanan ini sudah dibatalkan, jadi tidak bisa dibayar.');
        }

        if ($order->payment_status === 'dibayar') {
            return back()->with('success', 'Pesanan ini sudah dibayar.');
        }

        // Token Snap berlaku sampai kedaluwarsa, jadi kunjungan berikutnya
        // diarahkan ke halaman yang sama tanpa membuat transaksi baru.
        if ($order->payment_url && $order->payment_status === 'menunggu') {
            return redirect()->away($order->payment_url);
        }

        try {
            $response = Http::withBasicAuth($this->serverKey(), '')
                ->acceptJson()
                ->timeout(20)
                ->post($this->snapUrl(), $this->payload($order));
        } catch (ConnectionException $exception) {
            report($exception);

            return back()->with('error', 'Tidak bisa menghubungi Midtrans. Periksa koneksi lalu coba lagi.');
        }

        if ($response->failed() || blank($response['redirect_url'])) {
            Log::warning('Midtrans menolak pembuatan transaksi', [
                'kode_pesanan' => $order->kode_pesanan,
                'status' => $response->status(),
                'balasan' => $response->json(),
            ]);

            return back()->with('error', 'Pembayaran gagal dibuat. Coba lagi, atau kabari penjual lewat WhatsApp.');
        }

        $order->update([
            'snap_token' => $response['token'] ?? null,
            'payment_url' => $response['redirect_url'],
            'payment_status' => 'menunggu',
        ]);

        return redirect()->away($response['redirect_url']);
    }

    public function cek(string $kode): RedirectResponse
    {
        $order = $this->cari($kode);

        if (! $this->aktif()) {
            return redirect()->route('orders.show', $order->kode_pesanan);
        }

        try {
            $response = Http::withBasicAuth($this->serverKey(), '')
                ->acceptJson()
                ->timeout(20)
                ->get($this->apiUrl().'/'.$order->kode_pesanan.'/status');
        } catch (ConnectionException $exception) {
            report($exception);

            return back()->with('error', 'Status pembayaran belum bisa dicek. Coba lagi sebentar.');
        }

        if ($response->successful()) {
            $this->terapkanStatus($order, $response->json());
        }

        return redirect()
            ->route('orders.show', $order->kode_pesanan)
            ->with('success', 'Status pembayaran diperbarui.');
    }

    public function notifikasi(Request $request): JsonResponse
    {
        $data = $request->all();

        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key'] as $wajib) {
            if (! isset($data[$wajib])) {
                return response()->json(['message' => 'Payload tidak lengkap'], 422);
            }
        }

        $kunci = $this->serverKey();
        $harapan = hash('sha512', $data['order_id'].$data['status_code'].$data['gross_amount'].$kunci);

        if ($kunci === '' || ! hash_equals($harapan, (string) $data['signature_key'])) {
            return response()->json(['message' => 'Signature tidak valid'], 403);
        }

        $order = Order::query()->where('kode_pesanan', $data['order_id'])->first();

        if (! $order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan'], 404);
        }

        $this->terapkanStatus($order, $data);

        return response()->json(['message' => 'Diterima']);
    }

    /**
     * Menerjemahkan status Midtrans menjadi status pembayaran internal.
     *
     * @param  array<string, mixed>  $data
     */
    private function terapkanStatus(Order $order, array $data): void
    {
        $status = (string) ($data['transaction_status'] ?? '');
        $fraud = $data['fraud_status'] ?? null;

        $baru = match (true) {
            $status === 'capture' && $fraud === 'challenge' => 'menunggu',
            $status === 'capture' => 'dibayar',
            $status === 'settlement' => 'dibayar',
            $status === 'pending' => 'menunggu',
            in_array($status, ['deny', 'cancel'], true) => 'gagal',
            $status === 'expire' => 'kedaluwarsa',
            in_array($status, ['refund', 'partial_refund'], true) => 'dikembalikan',
            default => null,
        };

        if ($baru === null) {
            return;
        }

        $perubahan = ['payment_status' => $baru];

        if ($baru === 'dibayar') {
            $perubahan['paid_at'] = $order->paid_at ?? now();

            if (! empty($data['payment_type'])) {
                $perubahan['payment_method'] = mb_substr((string) $data['payment_type'], 0, 50);
            }

            // Pesanan yang sudah dibayar langsung masuk antrean pemrosesan,
            // kecuali sudah selesai atau dibatalkan.
            if ($order->status === 'baru') {
                $perubahan['status'] = 'diproses';
            }
        }

        $order->update($perubahan);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Order $order): array
    {
        $order->loadMissing('items.product', 'customer');

        $items = $order->items->map(fn ($item): array => [
            'id' => (string) $item->product_id,
            'price' => (int) $item->harga_satuan,
            'quantity' => (int) $item->jumlah,
            'name' => mb_substr((string) ($item->product->nama_produk ?? 'Produk'), 0, 50),
        ])->values()->all();

        // gross_amount harus sama dengan jumlah price x quantity, jadi ongkir
        // ikut dikirim sebagai baris tersendiri.
        if ($order->ongkir > 0) {
            $items[] = [
                'id' => 'ongkir',
                'price' => (int) $order->ongkir,
                'quantity' => 1,
                'name' => 'Ongkos kirim',
            ];
        }

        return [
            'transaction_details' => [
                'order_id' => $order->kode_pesanan,
                'gross_amount' => (int) $order->total,
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => mb_substr((string) $order->customer->nama, 0, 50),
                'phone' => $order->customer->no_hp,
            ],
            'callbacks' => [
                'finish' => route('orders.show', $order->kode_pesanan),
            ],
        ];
    }

    private function cari(string $kode): Order
    {
        return Order::query()->where('kode_pesanan', $kode)->firstOrFail();
    }

    private function aktif(): bool
    {
        return $this->serverKey() !== '';
    }

    private function serverKey(): string
    {
        return (string) config('services.midtrans.server_key');
    }

    private function snapUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    }

    private function apiUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }
}
