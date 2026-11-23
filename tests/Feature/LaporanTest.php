<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class LaporanTest extends TestCase
{
    use DatabaseTransactions;

    private Admin $admin;

    private Category $category;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Semua dijalankan di dalam transaksi, jadi data asli kembali setelah tes.
        OrderItem::query()->delete();
        Order::query()->delete();

        $this->admin = Admin::create([
            'nama' => 'Pengelola Laporan',
            'email' => 'laporan-'.Str::lower(Str::random(6)).'@davadavi.test',
            'password' => 'sandi-uji-123',
            'role' => 'admin',
        ]);

        $this->category = Category::query()->firstOrCreate(
            ['slug' => 'kategori-laporan-uji'],
            ['nama_kategori' => 'Kategori Laporan Uji']
        );

        $this->customer = Customer::create([
            'nama' => 'Pembeli Laporan',
            'no_hp' => '0813'.random_int(1000000, 9999999),
            'alamat' => 'Jalan Laporan 2, Malang',
        ]);
    }

    private function produk(string $nama, int $harga): Product
    {
        return Product::create([
            'category_id' => $this->category->id,
            'nama_produk' => $nama,
            'slug' => Str::slug($nama).'-'.Str::lower(Str::random(5)),
            'harga' => $harga,
            'deskripsi' => 'Data uji laporan.',
            'stok' => 50,
            'status_aktif' => true,
        ]);
    }

    private function pesanan(Product $produk, int $jumlah, string $tanggal, string $status): Order
    {
        $order = Order::create([
            'customer_id' => $this->customer->id,
            'total' => $produk->harga * $jumlah,
            'status' => $status,
            'payment_status' => 'belum_bayar',
            'tanggal' => $tanggal,
        ]);

        $order->items()->create([
            'product_id' => $produk->id,
            'jumlah' => $jumlah,
            'harga_satuan' => $produk->harga,
        ]);

        return $order->refresh();
    }

    public function test_laporan_menghitung_omzet_per_bulan_tanpa_pesanan_batal(): void
    {
        $sagu = $this->produk('Keripik Sagu Laporan', 18000);
        $menjes = $this->produk('Keripik Menjes Laporan', 16000);

        $this->pesanan($sagu, 2, '2026-10-05', 'selesai');
        $this->pesanan($menjes, 2, '2026-11-07', 'baru');
        $this->pesanan($sagu, 1, '2026-11-09', 'batal');

        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.laporan.index'))
            ->assertOk()
            ->assertSee('Laporan penjualan')
            ->assertSee('Rp68.000')
            ->assertSee('Rp34.000')
            ->assertSee('Oktober 2026')
            ->assertSee('November 2026')
            ->assertSee('Rp36.000')
            ->assertSee('Rp32.000');
    }

    public function test_produk_terlaris_mengabaikan_pesanan_batal(): void
    {
        $sagu = $this->produk('Keripik Sagu Terlaris', 18000);
        $menjes = $this->produk('Keripik Menjes Terlaris', 16000);

        $this->pesanan($sagu, 2, '2026-10-05', 'selesai');
        $this->pesanan($menjes, 3, '2026-10-08', 'selesai');
        $this->pesanan($sagu, 9, '2026-10-09', 'batal');

        $respons = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.laporan.index'))
            ->assertOk();

        $isi = $respons->getContent();

        $this->assertStringContainsString('Keripik Menjes Terlaris', $isi);
        $this->assertStringContainsString('Keripik Sagu Terlaris', $isi);
        // Pesanan batal berisi 9 bungkus sagu, tidak boleh ikut terhitung.
        $this->assertStringNotContainsString('>11<', $isi);
    }

    public function test_laporan_hanya_bisa_dibuka_admin(): void
    {
        $this->get(route('admin.laporan.index'))->assertRedirect(route('admin.login'));
    }
}
