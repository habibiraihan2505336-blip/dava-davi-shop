<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class MidtransTest extends TestCase
{
    use DatabaseTransactions;

    private const KUNCI = 'SB-Mid-server-KUNCIILAPAN';

    private function pesanan(int $jumlah = 2): Order
    {
        $kategori = Category::query()->firstOrCreate(
            ['slug' => 'kategori-midtrans-uji'],
            ['nama_kategori' => 'Kategori Midtrans Uji']
        );

        $produk = Product::create([
            'category_id' => $kategori->id,
            'nama_produk' => 'Keripik Uji Midtrans',
            'slug' => 'midtrans-uji-'.Str::lower(Str::random(6)),
            'harga' => 16000,
            'deskripsi' => 'Data uji pembayaran.',
            'stok' => 50,
            'status_aktif' => true,
        ]);

        $pelanggan = Customer::create([
            'nama' => 'Pembeli Midtrans',
            'no_hp' => '0812'.random_int(1000000, 9999999),
            'alamat' => 'Jalan Uji Bayar 1, Malang',
        ]);

        $order = Order::create([
            'customer_id' => $pelanggan->id,
            'total' => 16000 * $jumlah + 10000,
            'ongkir' => 10000,
            'status' => 'baru',
            'payment_status' => 'belum_bayar',
        ]);

        $order->items()->create([
            'product_id' => $produk->id,
            'jumlah' => $jumlah,
            'harga_satuan' => 16000,
        ]);

        return $order->refresh();
    }

    private function kirimNotifikasi(Order $order, array $tambahan = [], ?string $kunci = null): \Illuminate\Testing\TestResponse
    {
        $kunci ??= self::KUNCI;

        $payload = array_merge([
            'order_id' => $order->kode_pesanan,
            'status_code' => '200',
            'gross_amount' => (string) $order->total.'.00',
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'fraud_status' => 'accept',
        ], $tambahan);

        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$kunci
        );

        return $this->postJson(route('pembayaran.notifikasi'), $payload);
    }

    public function test_notifikasi_dengan_signature_palsu_ditolak(): void
    {
        config(['services.midtrans.server_key' => self::KUNCI]);
        $order = $this->pesanan();

        $payload = [
            'order_id' => $order->kode_pesanan,
            'status_code' => '200',
            'gross_amount' => (string) $order->total.'.00',
            'transaction_status' => 'settlement',
            'signature_key' => str_repeat('0', 128),
        ];

        $this->postJson(route('pembayaran.notifikasi'), $payload)->assertStatus(403);

        $this->assertSame('belum_bayar', $order->fresh()->payment_status);
    }

    public function test_notifikasi_tanpa_signature_ditolak(): void
    {
        config(['services.midtrans.server_key' => self::KUNCI]);
        $order = $this->pesanan();

        $this->postJson(route('pembayaran.notifikasi'), [
            'order_id' => $order->kode_pesanan,
            'status_code' => '200',
            'gross_amount' => '32000.00',
        ])->assertStatus(422);
    }

    public function test_settlement_menandai_pesanan_dibayar_dan_diproses(): void
    {
        config(['services.midtrans.server_key' => self::KUNCI]);
        $order = $this->pesanan();

        $this->kirimNotifikasi($order)->assertOk();

        $order->refresh();
        $this->assertSame('dibayar', $order->payment_status);
        $this->assertSame('diproses', $order->status);
        $this->assertSame('qris', $order->payment_method);
        $this->assertNotNull($order->paid_at);
    }

    public function test_expire_menandai_pembayaran_kedaluwarsa(): void
    {
        config(['services.midtrans.server_key' => self::KUNCI]);
        $order = $this->pesanan();

        $this->kirimNotifikasi($order, ['transaction_status' => 'expire'])->assertOk();

        $this->assertSame('kedaluwarsa', $order->fresh()->payment_status);
        $this->assertSame('baru', $order->fresh()->status);
    }

    public function test_status_tidak_dikenal_tidak_mengubah_apa_pun(): void
    {
        config(['services.midtrans.server_key' => self::KUNCI]);
        $order = $this->pesanan();

        $this->kirimNotifikasi($order, ['transaction_status' => 'kode_aneh'])->assertOk();

        $this->assertSame('belum_bayar', $order->fresh()->payment_status);
    }

    public function test_pesanan_tidak_dikenal_dijawab_404(): void
    {
        config(['services.midtrans.server_key' => self::KUNCI]);
        $order = $this->pesanan();
        $payload = [
            'order_id' => 'DD-000000-0000',
            'status_code' => '200',
            'gross_amount' => '1000.00',
            'transaction_status' => 'settlement',
        ];
        $payload['signature_key'] = hash('sha512', 'DD-000000-0000'.'200'.'1000.00'.self::KUNCI);

        $this->postJson(route('pembayaran.notifikasi'), $payload)->assertStatus(404);
        $this->assertSame('belum_bayar', $order->fresh()->payment_status);
    }

    public function test_tombol_bayar_tidak_muncul_tanpa_kunci(): void
    {
        config(['services.midtrans.server_key' => '']);
        $order = $this->pesanan();

        $this->get(route('orders.show', $order->kode_pesanan))
            ->assertOk()
            ->assertSee('Kabari penjual buat konfirmasi pembayaran')
            ->assertDontSee('Bayar sekarang');
    }

    public function test_tombol_bayar_muncul_dan_membuat_transaksi_snap(): void
    {
        config(['services.midtrans.server_key' => self::KUNCI]);

        Http::fake([
            'app.sandbox.midtrans.com/*' => Http::response([
                'token' => 'TOKEN-UJI-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/TOKEN-UJI-123',
            ], 201),
        ]);

        $order = $this->pesanan();

        $this->get(route('orders.show', $order->kode_pesanan))
            ->assertOk()
            ->assertSee('Bayar sekarang');

        $this->post(route('pesanan.bayar', $order->kode_pesanan))
            ->assertRedirect('https://app.sandbox.midtrans.com/snap/v4/redirection/TOKEN-UJI-123');

        $order->refresh();
        $this->assertSame('TOKEN-UJI-123', $order->snap_token);
        $this->assertSame('menunggu', $order->payment_status);

        Http::assertSent(function ($request) use ($order) {
            $data = $request->data();

            return $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions'
                && $data['transaction_details']['order_id'] === $order->kode_pesanan
                && $data['transaction_details']['gross_amount'] === $order->total
                && array_sum(array_map(fn ($i) => $i['price'] * $i['quantity'], $data['item_details'])) === $order->total;
        });
    }

    public function test_pesanan_batal_tidak_bisa_dibayar(): void
    {
        config(['services.midtrans.server_key' => self::KUNCI]);
        Http::fake();

        $order = $this->pesanan();
        $order->update(['status' => 'batal']);

        $this->post(route('pesanan.bayar', $order->kode_pesanan))
            ->assertSessionHas('error');

        Http::assertNothingSent();
    }

    public function test_kunjungan_ulang_memakai_tautan_pembayaran_yang_sama(): void
    {
        config(['services.midtrans.server_key' => self::KUNCI]);
        Http::fake();

        $order = $this->pesanan();
        $order->update([
            'payment_status' => 'menunggu',
            'payment_url' => 'https://app.sandbox.midtrans.com/snap/v4/redirection/TOKEN-LAMA',
        ]);

        $this->post(route('pesanan.bayar', $order->kode_pesanan))
            ->assertRedirect('https://app.sandbox.midtrans.com/snap/v4/redirection/TOKEN-LAMA');

        Http::assertNothingSent();
    }

    public function test_cek_status_memperbarui_dari_api_midtrans(): void
    {
        config(['services.midtrans.server_key' => self::KUNCI]);

        $order = $this->pesanan();

        Http::fake([
            'api.sandbox.midtrans.com/*' => Http::response([
                'transaction_status' => 'settlement',
                'payment_type' => 'bank_transfer',
                'status_code' => '200',
                'gross_amount' => (string) $order->total.'.00',
            ], 200),
        ]);

        $this->get(route('pesanan.cek-bayar', $order->kode_pesanan))
            ->assertRedirect(route('orders.show', $order->kode_pesanan));

        $order->refresh();
        $this->assertSame('dibayar', $order->payment_status);
        $this->assertSame('bank_transfer', $order->payment_method);
    }
}
