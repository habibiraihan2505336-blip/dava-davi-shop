<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): Admin
    {
        return Admin::create([
            'nama' => 'Pengelola Uji',
            'email' => 'uji-'.Str::lower(Str::random(6)).'@davadavi.test',
            'password' => 'sandi-uji-123',
            'role' => 'admin',
        ]);
    }

    private function pesanan(): Order
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => 'kategori-uji-otomatis'],
            ['nama_kategori' => 'Kategori Uji Otomatis']
        );

        $product = Product::create([
            'category_id' => $category->id,
            'nama_produk' => 'Produk Pesanan Uji',
            'slug' => 'pesanan-uji-'.Str::lower(Str::random(6)),
            'harga' => 18000,
            'deskripsi' => 'Data uji otomatis.',
            'stok' => 10,
            'status_aktif' => true,
        ]);

        $customer = Customer::create([
            'nama' => 'Pembeli Uji',
            'no_hp' => '0812'.random_int(1000000, 9999999),
            'alamat' => 'Jalan Uji 1, Malang',
        ]);

        $order = Order::create([
            'customer_id' => $customer->id,
            'total' => 36000,
            'status' => 'baru',
            'payment_status' => 'belum_bayar',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'jumlah' => 2,
            'harga_satuan' => 18000,
        ]);

        return $order->refresh();
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));
        $this->get('/admin/pesanan')->assertRedirect(route('admin.login'));
        $this->get('/admin/produk')->assertRedirect(route('admin.login'));
    }

    public function test_halaman_masuk_dapat_dibuka(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Masuk pengelola')
            ->assertSee('name="email"', false);
    }

    public function test_sandi_salah_ditolak(): void
    {
        $admin = $this->admin();

        $this->post(route('admin.login.attempt'), [
            'email' => $admin->email,
            'password' => 'sandi-keliru',
        ])->assertSessionHasErrors('email');

        $this->assertGuest('admin');
    }

    public function test_admin_dapat_masuk_dan_melihat_ringkasan(): void
    {
        $admin = $this->admin();

        $this->post(route('admin.login.attempt'), [
            'email' => $admin->email,
            'password' => 'sandi-uji-123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Ringkasan toko')
            ->assertSee($admin->nama)
            ->assertSee('Pesanan terbaru');
    }

    public function test_admin_dapat_mengubah_status_pesanan_dan_waktu_bayar_tercatat(): void
    {
        $admin = $this->admin();
        $order = $this->pesanan();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.pesanan.update', $order), [
                'status' => 'diproses',
                'payment_status' => 'dibayar',
                'ongkir' => 0,
            ])->assertRedirect(route('admin.pesanan.show', $order));

        $order->refresh();
        $this->assertSame('diproses', $order->status);
        $this->assertSame('dibayar', $order->payment_status);
        $this->assertNotNull($order->paid_at);
    }

    public function test_status_pesanan_di_luar_daftar_ditolak(): void
    {
        $admin = $this->admin();
        $order = $this->pesanan();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.pesanan.update', $order), [
                'status' => 'entah',
                'payment_status' => 'belum_bayar',
                'ongkir' => 0,
            ])->assertSessionHasErrors('status');
    }

    public function test_admin_dapat_mengubah_harga_dan_stok_produk(): void
    {
        $admin = $this->admin();
        $order = $this->pesanan();
        $product = $order->items->first()->product;

        $this->actingAs($admin, 'admin')
            ->put(route('admin.produk.update', $product), [
                'category_id' => $product->category_id,
                'nama_produk' => $product->nama_produk,
                'harga' => 21000,
                'stok' => 7,
                'deskripsi' => 'Keterangan diperbarui oleh tes.',
                'status_aktif' => '1',
            ])->assertRedirect(route('admin.produk.edit', $product));

        $product->refresh();
        $this->assertSame(21000, $product->harga);
        $this->assertSame(7, $product->stok);
        $this->assertTrue($product->status_aktif);
    }

    public function test_daftar_pesanan_dapat_dicari_berdasarkan_kode(): void
    {
        $admin = $this->admin();
        $order = $this->pesanan();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.pesanan.index', ['cari' => $order->kode_pesanan]))
            ->assertOk()
            ->assertSee($order->kode_pesanan);
    }

    public function test_admin_dapat_mengisi_ongkir_dan_total_menyesuaikan(): void
    {
        $admin = $this->admin();
        $order = $this->pesanan();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.pesanan.update', $order), [
                'status' => 'baru',
                'payment_status' => 'belum_bayar',
                'ongkir' => 12000,
            ])->assertRedirect(route('admin.pesanan.show', $order));

        $order->refresh();
        $this->assertSame(12000, $order->ongkir);
        $this->assertSame(36000, $order->subtotalItem());
        $this->assertSame(48000, $order->total);
    }

    public function test_ongkir_negatif_ditolak(): void
    {
        $admin = $this->admin();
        $order = $this->pesanan();

        $this->actingAs($admin, 'admin')
            ->put(route('admin.pesanan.update', $order), [
                'status' => 'baru',
                'payment_status' => 'belum_bayar',
                'ongkir' => -1,
            ])->assertSessionHasErrors('ongkir');
    }

    public function test_admin_dapat_menambah_dan_mengubah_kategori(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.kategori.store'), ['nama_kategori' => 'Kategori Uji Baru'])
            ->assertRedirect(route('admin.kategori.index'));

        $kategori = Category::query()->where('nama_kategori', 'Kategori Uji Baru')->firstOrFail();
        $this->assertSame('kategori-uji-baru', $kategori->slug);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.kategori.update', $kategori), ['nama_kategori' => 'Kategori Uji Diubah'])
            ->assertRedirect(route('admin.kategori.index'));

        $this->assertSame('Kategori Uji Diubah', $kategori->fresh()->nama_kategori);
    }

    public function test_kategori_yang_dipakai_produk_tidak_bisa_dihapus(): void
    {
        $admin = $this->admin();
        $order = $this->pesanan();
        $kategori = $order->items->first()->product->category;

        $this->actingAs($admin, 'admin')
            ->delete(route('admin.kategori.destroy', $kategori))
            ->assertRedirect(route('admin.kategori.index'))
            ->assertSessionHas('error');

        $this->assertNotNull($kategori->fresh());
    }

    public function test_admin_dapat_menambah_harga_grosir(): void
    {
        $admin = $this->admin();
        $order = $this->pesanan();
        $produk = $order->items->first()->product;

        $this->actingAs($admin, 'admin')
            ->post(route('admin.produk.grosir.store', $produk), [
                'min_qty' => 5,
                'harga_satuan' => 15000,
            ])->assertRedirect(route('admin.produk.edit', $produk));

        $this->assertSame(1, $produk->priceTiers()->count());
        $this->assertSame(15000, $produk->hargaUntuk(5));
        $this->assertSame(18000, $produk->fresh()->hargaUntuk(4));
    }

    public function test_harga_grosir_harus_lebih_rendah_dari_harga_satuan(): void
    {
        $admin = $this->admin();
        $order = $this->pesanan();
        $produk = $order->items->first()->product;

        $this->actingAs($admin, 'admin')
            ->post(route('admin.produk.grosir.store', $produk), [
                'min_qty' => 5,
                'harga_satuan' => 20000,
            ])->assertSessionHasErrors('harga_satuan');

        $this->assertSame(0, $produk->priceTiers()->count());
    }

    public function test_percobaan_masuk_admin_dibatasi(): void
    {
        $admin = $this->admin();

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('admin.login.attempt'), [
                'email' => $admin->email,
                'password' => 'sandi-keliru',
            ])->assertSessionHasErrors('email');
        }

        $respons = $this->post(route('admin.login.attempt'), [
            'email' => $admin->email,
            'password' => 'sandi-keliru',
        ]);

        $respons->assertSessionHasErrors('email');
        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('email'));
        $this->assertGuest('admin');
    }
}
