<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPriceTier;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class ShopFlowTest extends TestCase
{
    use DatabaseTransactions;

    private function kategori(): Category
    {
        return Category::query()->firstOrCreate(
            ['slug' => 'kategori-uji-otomatis'],
            ['nama_kategori' => 'Kategori Uji Otomatis']
        );
    }

    /**
     * @param  array<string, mixed>  $ubah
     */
    private function produk(array $ubah = []): Product
    {
        return Product::create(array_merge([
            'category_id' => $this->kategori()->id,
            'nama_produk' => 'Keripik Tempe Uji',
            'slug' => 'uji-'.Str::lower(Str::random(8)),
            'harga' => 18000,
            'deskripsi' => 'Data uji otomatis, dibuat dan dibatalkan oleh tes.',
            'stok' => 20,
            'status_aktif' => true,
        ], $ubah));
    }

    public function test_katalog_menampilkan_produk_aktif_beserta_kategorinya(): void
    {
        $product = $this->produk();

        $this->get('/')
            ->assertOk()
            ->assertSee('Keripik Tempe Dava Davi')
            ->assertSee($product->nama_produk)
            ->assertSee('Kategori Uji Otomatis')
            ->assertSee('Data uji');
    }

    public function test_produk_nonaktif_tidak_tampil_di_katalog(): void
    {
        $product = $this->produk(['status_aktif' => false, 'nama_produk' => 'Produk Nonaktif Uji']);

        $this->get('/')
            ->assertOk()
            ->assertDontSee($product->nama_produk);
    }

    public function test_pembeli_dapat_menambahkan_produk_ke_keranjang(): void
    {
        $product = $this->produk();

        $this->post('/keranjang', ['product_id' => $product->id, 'quantity' => 2])
            ->assertRedirect('/keranjang');

        $this->get('/keranjang')
            ->assertOk()
            ->assertSee($product->nama_produk)
            ->assertSee('36.000');
    }

    public function test_permintaan_ajax_mengembalikan_jumlah_keranjang(): void
    {
        $product = $this->produk();

        $this->withHeader('Accept', 'application/json')
            ->post('/keranjang', ['product_id' => $product->id, 'quantity' => 3])
            ->assertOk()
            ->assertJsonPath('cart_count', 3)
            ->assertJsonPath('message', 'Produk ditambahkan ke keranjang.');
    }

    public function test_jumlah_di_atas_stok_ditolak(): void
    {
        $product = $this->produk(['stok' => 1]);

        $this->post('/keranjang', ['product_id' => $product->id, 'quantity' => 5])
            ->assertSessionHasErrors('quantity');

        $this->assertSame([], session('cart', []));
    }

    public function test_checkout_menyimpan_pesanan_item_dan_mengurangi_stok(): void
    {
        $product = $this->produk(['stok' => 20]);

        $this->withSession(['cart' => [$product->id => 2]])
            ->post('/checkout', [
                'nama' => 'Pembeli Uji',
                'no_hp' => '081234567890',
                'alamat' => 'Jalan Uji 1, Malang',
            ])
            ->assertRedirect();

        $order = $product->orderItems()->first()->order;
        $this->assertSame(36000, $order->total);
        $this->assertSame('baru', $order->status);
        $this->assertSame('belum_bayar', $order->payment_status);
        $this->assertMatchesRegularExpression('/^DD-\d{6}-\d{4}$/', $order->kode_pesanan);
        $this->assertSame(2, $order->items->first()->jumlah);
        $this->assertSame(36000, $order->items->first()->subtotal);
        $this->assertSame(18, $product->fresh()->stok);
    }

    public function test_harga_grosir_dipakai_saat_jumlah_memenuhi_ambang(): void
    {
        $product = $this->produk(['harga' => 18000, 'stok' => 30]);
        ProductPriceTier::create(['product_id' => $product->id, 'min_qty' => 5, 'harga_satuan' => 15000]);

        $this->withSession(['cart' => [$product->id => 5]])
            ->post('/checkout', [
                'nama' => 'Pembeli Grosir Uji',
                'no_hp' => '081234567891',
                'alamat' => 'Jalan Uji 2, Malang',
            ])
            ->assertRedirect();

        $order = $product->orderItems()->first()->order;
        $this->assertSame(75000, $order->total);
        $this->assertSame(15000, $order->items->first()->harga_satuan);
    }

    public function test_checkout_menolak_keranjang_kedaluwarsa_saat_stok_berubah(): void
    {
        $product = $this->produk(['stok' => 1]);

        $this->withSession(['cart' => [$product->id => 2]])
            ->post('/checkout', [
                'nama' => 'Pembeli Uji',
                'no_hp' => '081234567890',
                'alamat' => 'Jalan Uji 1, Malang',
            ])
            ->assertRedirect('/keranjang')
            ->assertSessionHas('error', 'Stok produk berubah. Periksa keranjang dan coba lagi.');

        $this->assertSame(0, $product->orderItems()->count());
        $this->assertSame(1, $product->fresh()->stok);
    }

    public function test_checkout_mewajibkan_data_pemesan_dan_format_nomor_hp(): void
    {
        $this->post('/checkout', [])
            ->assertSessionHasErrors(['nama', 'no_hp', 'alamat']);

        $this->post('/checkout', [
            'nama' => 'Pembeli Uji',
            'no_hp' => 'abc',
            'alamat' => 'Jalan Uji 1',
        ])->assertSessionHasErrors('no_hp');
    }

    public function test_struk_hanya_bisa_dibuka_dengan_kode_pesanan(): void
    {
        $product = $this->produk();

        $this->withSession(['cart' => [$product->id => 1]])
            ->post('/checkout', [
                'nama' => 'Pembeli Uji',
                'no_hp' => '081234567890',
                'alamat' => 'Jalan Uji 1, Malang',
            ]);

        $order = $product->orderItems()->first()->order;

        $this->get('/pesanan/'.$order->kode_pesanan)
            ->assertOk()
            ->assertSee($order->kode_pesanan)
            ->assertSee('Belum dibayar');

        $this->get('/pesanan/'.$order->id)->assertNotFound();
    }
}
