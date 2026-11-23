<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class LaporanController extends Controller
{
    public function index(): View
    {
        $perBulan = $this->perBulan();
        $pesananDihitung = $perBulan->sum(fn (array $baris): int => $baris['jumlah'] - $baris['batal']);
        $omzet = $perBulan->sum('omzet');

        return view('admin.laporan.index', [
            'perBulan' => $perBulan,
            'omzet' => $omzet,
            'jumlahPesanan' => $perBulan->sum('jumlah'),
            'jumlahBatal' => $perBulan->sum('batal'),
            'rataRata' => $pesananDihitung > 0 ? (int) round($omzet / $pesananDihitung) : 0,
            'terlaris' => $this->terlaris(),
        ]);
    }

    /**
     * Ringkasan per bulan. Pesanan berstatus batal tetap dihitung jumlahnya
     * tetapi tidak masuk omzet.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function perBulan(): Collection
    {
        return Order::query()
            ->selectRaw("to_char(tanggal, 'YYYY-MM') as bulan")
            ->selectRaw('count(*) as jumlah')
            ->selectRaw("count(*) filter (where status = 'selesai') as selesai")
            ->selectRaw("count(*) filter (where status = 'batal') as batal")
            ->selectRaw("coalesce(sum(total) filter (where status <> 'batal'), 0) as omzet")
            ->groupByRaw("to_char(tanggal, 'YYYY-MM')")
            ->orderByRaw("to_char(tanggal, 'YYYY-MM') desc")
            ->get()
            ->map(fn ($baris): array => [
                'bulan' => (string) $baris->bulan,
                'label' => Carbon::parse($baris->bulan.'-01')->locale('id')->isoFormat('MMMM YYYY'),
                'jumlah' => (int) $baris->jumlah,
                'selesai' => (int) $baris->selesai,
                'batal' => (int) $baris->batal,
                'omzet' => (int) $baris->omzet,
            ]);
    }

    /**
     * Lima produk dengan jumlah terjual terbanyak, tanpa pesanan batal.
     *
     * @return Collection<int, array<string, mixed>>
     */
    private function terlaris(): Collection
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.status', '<>', 'batal')
            ->selectRaw('products.nama_produk as nama_produk')
            ->selectRaw('sum(order_items.jumlah) as terjual')
            ->selectRaw('sum(order_items.subtotal) as nilai')
            ->groupBy('products.id', 'products.nama_produk')
            ->orderByDesc('terjual')
            ->limit(5)
            ->get()
            ->map(fn ($baris): array => [
                'nama' => (string) $baris->nama_produk,
                'terjual' => (int) $baris->terjual,
                'nilai' => (int) $baris->nilai,
            ]);
    }
}
