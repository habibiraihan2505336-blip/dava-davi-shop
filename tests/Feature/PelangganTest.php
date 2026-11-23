<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class PelangganTest extends TestCase
{
    use DatabaseTransactions;

    private function produk(array $ubah = []): Product
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => 'kategori-uji-otomatis'],
            ['nama_kategori' => 'Kategori Uji Otomatis']
        );

        return Product::create(array_merge([
            'category_id' => $category->id,
            'nama_produk' => 'Keripik Tempe Uji',
            'slug' => 'uji-'.Str::lower(Str::random(8)),
            'harga' => 18000,
            'deskripsi' => 'Data uji otomatis.',
            'stok' => 20,
            'status_aktif' => true,
        ], $ubah));
    }

    private function akun(array $ubah = []): Customer
    {
        return Customer::create(array_merge([
            'nama' => 'Pembeli Uji',
            'email' => 'uji-'.Str::lower(Str::random(6)).'@contoh.id',
            'no_hp' => '0812'.random_int(1000000, 9999999),
            'alamat' => 'Jalan Uji 1, Malang',
            'password' => 'sandi-uji-123',
        ], $ubah));
    }

    public function test_pembeli_dapat_mendaftar_dan_langsung_masuk(): void
    {
        $this->post(route('pelanggan.daftar.attempt'), [
            'nama' => 'Pembeli Baru',
            'email' => 'pembeli-'.Str::lower(Str::random(6)).'@contoh.id',
            'no_hp' => '081234567890',
            'alamat' => 'Jalan Uji 1, Malang',
            'password' => 'sandi-uji-123',
            'password_confirmation' => 'sandi-uji-123',
        ])->assertRedirect(route('akun.index'));

        $this->get(route('akun.index'))->assertOk()->assertSee('Riwayat pesanan');
    }

    public function test_email_ganda_ditolak_saat_daftar(): void
    {
        $ada = $this->akun();

        $this->post(route('pelanggan.daftar.attempt'), [
            'nama' => 'Pembeli Lain',
            'email' => $ada->email,
            'no_hp' => '081234567891',
            'password' => 'sandi-uji-123',
            'password_confirmation' => 'sandi-uji-123',
        ])->assertSessionHasErrors('email');
    }

    public function test_kata_sandi_kurang_dari_delapan_karakter_ditolak(): void
    {
        $this->post(route('pelanggan.daftar.attempt'), [
            'nama' => 'Pembeli Baru',
            'email' => 'pendek-'.Str::lower(Str::random(6)).'@contoh.id',
            'no_hp' => '081234567890',
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
        ])->assertSessionHasErrors('password');
    }

    public function test_pembeli_dapat_masuk_dan_melihat_riwayat_pesanan(): void
    {
        $akun = $this->akun();
        $produk = $this->produk();

        $order = Order::create([
            'customer_id' => $akun->id,
            'total' => 36000,
            'status' => 'baru',
            'payment_status' => 'belum_bayar',
        ]);
        $order->items()->create(['product_id' => $produk->id, 'jumlah' => 2, 'harga_satuan' => 18000]);

        $this->post(route('pelanggan.login.attempt'), [
            'email' => $akun->email,
            'password' => 'sandi-uji-123',
        ])->assertRedirect(route('akun.index'));

        $this->get(route('akun.index'))
            ->assertOk()
            ->assertSee($order->kode_pesanan)
            ->assertSee($produk->nama_produk);
    }

    public function test_pembeli_dapat_masuk_dengan_opsi_ingat_saya(): void
    {
        $akun = $this->akun();

        $this->post(route('pelanggan.login.attempt'), [
            'email' => $akun->email,
            'password' => 'sandi-uji-123',
            'ingat' => '1',
        ])->assertRedirect(route('akun.index'));

        $this->assertNotNull(Customer::query()->where('email', $akun->email)->value('remember_token'));
    }

    public function test_akun_tidak_bisa_dibuka_tanpa_masuk(): void
    {
        $this->get(route('akun.index'))->assertRedirect(route('pelanggan.login'));
    }

    public function test_pesan_ulang_mengisi_keranjang_dari_pesanan_lama(): void
    {
        $akun = $this->akun();
        $produk = $this->produk(['stok' => 20]);

        $order = Order::create([
            'customer_id' => $akun->id,
            'total' => 36000,
            'status' => 'baru',
            'payment_status' => 'belum_bayar',
        ]);
        $order->items()->create(['product_id' => $produk->id, 'jumlah' => 2, 'harga_satuan' => 18000]);

        $this->actingAs($akun, 'pelanggan')
            ->post(route('akun.pesan-ulang', $order))
            ->assertRedirect(route('cart.index'));

        $this->assertSame(2, session('cart')[$produk->id]);
    }

    public function test_pesan_ulang_pesanan_orang_lain_ditolak(): void
    {
        $akun = $this->akun();
        $lain = $this->akun();
        $produk = $this->produk();

        $order = Order::create([
            'customer_id' => $lain->id,
            'total' => 18000,
            'status' => 'baru',
            'payment_status' => 'belum_bayar',
        ]);
        $order->items()->create(['product_id' => $produk->id, 'jumlah' => 1, 'harga_satuan' => 18000]);

        $this->actingAs($akun, 'pelanggan')
            ->post(route('akun.pesan-ulang', $order))
            ->assertNotFound();
    }

    public function test_checkout_saat_masuk_memakai_baris_pelanggan_yang_sama(): void
    {
        $akun = $this->akun(['alamat' => 'Alamat lama']);
        $produk = $this->produk(['stok' => 10]);

        $this->actingAs($akun, 'pelanggan')
            ->withSession(['cart' => [$produk->id => 1]])
            ->post(route('checkout.place'), [
                'nama' => 'Nama Diperbarui',
                'no_hp' => $akun->no_hp,
                'alamat' => 'Alamat baru dari checkout',
            ])->assertRedirect();

        $akun->refresh();
        $this->assertSame('Nama Diperbarui', $akun->nama);
        $this->assertSame('Alamat baru dari checkout', $akun->alamat);
        $this->assertSame(1, $akun->orders()->count());
    }

    public function test_sandi_pelanggan_disimpan_ter_hash(): void
    {
        $akun = $this->akun();

        $this->assertNotSame('sandi-uji-123', $akun->password);
        $this->assertTrue(Hash::check('sandi-uji-123', $akun->password));
    }
}
