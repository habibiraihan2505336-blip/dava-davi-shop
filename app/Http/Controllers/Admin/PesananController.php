<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PesananController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $cari = trim((string) $request->query('cari'));

        $pesanan = Order::query()
            ->with('customer')
            ->withCount('items')
            ->when(array_key_exists((string) $status, Order::STATUS), fn ($query) => $query->where('status', $status))
            ->when($cari !== '', function ($query) use ($cari) {
                $query->where(function ($q) use ($cari) {
                    $pola = '%'.mb_strtolower($cari).'%';
                    $q->whereRaw('lower(kode_pesanan) like ?', [$pola])
                        ->orWhereHas('customer', fn ($c) => $c
                            ->whereRaw('lower(nama) like ?', [$pola])
                            ->orWhere('no_hp', 'like', '%'.$cari.'%'));
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pesanan.index', [
            'pesanan' => $pesanan,
            'statusAktif' => $status,
            'cari' => $cari,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items.product', 'customer']);

        return view('admin.pesanan.show', ['order' => $order]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Order::STATUS))],
            'payment_status' => ['required', Rule::in(array_keys(Order::PEMBAYARAN))],
            'ongkir' => ['required', 'integer', 'min:0', 'max:10000000'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ], [
            'ongkir.required' => 'Ongkir wajib diisi, tulis 0 bila gratis.',
            'ongkir.integer' => 'Ongkir harus angka bulat rupiah.',
            'ongkir.max' => 'Ongkir maksimal Rp10.000.000.',
            'catatan.max' => 'Catatan maksimal 500 karakter.',
        ]);

        if ($data['payment_status'] === 'dibayar' && $order->paid_at === null) {
            $data['paid_at'] = now();
        }

        if ($data['payment_status'] !== 'dibayar') {
            $data['paid_at'] = null;
        }

        $statusLama = $order->status;

        try {
            DB::transaction(function () use ($order, $data, $statusLama): void {
                $order->update($data);
                $order->refresh()->load('items');

                // Stok dikurangi saat checkout, jadi pembatalan harus mengembalikannya.
                // Mengaktifkan kembali pesanan yang batal berarti mengurangi stok lagi.
                if ($statusLama !== 'batal' && $order->status === 'batal') {
                    foreach ($order->items as $item) {
                        Product::query()->whereKey($item->product_id)->increment('stok', $item->jumlah);
                    }
                } elseif ($statusLama === 'batal' && $order->status !== 'batal') {
                    foreach ($order->items as $item) {
                        $produk = Product::query()->lockForUpdate()->find($item->product_id);

                        if ($produk === null || $produk->stok < $item->jumlah) {
                            throw new \DomainException(
                                'Stok '.($item->product?->nama_produk ?? 'produk').' tidak cukup untuk mengaktifkan kembali pesanan ini.'
                            );
                        }
                    }

                    foreach ($order->items as $item) {
                        Product::query()->whereKey($item->product_id)->decrement('stok', $item->jumlah);
                    }
                }

                // Total selalu subtotal item ditambah ongkir, tidak pernah diketik manual.
                $order->update(['total' => max(1, $order->totalSeharusnya())]);
            });
        } catch (\DomainException $exception) {
            return redirect()
                ->route('admin.pesanan.show', $order)
                ->with('error', $exception->getMessage());
        }

        $pesan = 'Pesanan '.$order->kode_pesanan.' diperbarui.';

        if ($statusLama !== 'batal' && $order->status === 'batal') {
            $pesan .= ' Stok produk sudah dikembalikan.';
        }

        if ($statusLama === 'batal' && $order->status !== 'batal') {
            $pesan .= ' Stok produk dikurangi lagi.';
        }

        return redirect()
            ->route('admin.pesanan.show', $order)
            ->with('success', $pesan);
    }
}
