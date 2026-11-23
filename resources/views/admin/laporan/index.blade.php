@extends('admin.layout')
@section('title', 'Laporan penjualan')
@section('content')
<h1>Laporan penjualan</h1>
<p class="lede">Rekap per bulan dari pesanan yang tercatat. Pesanan berstatus dibatalkan dihitung jumlahnya tetapi tidak masuk omzet.</p>

<dl class="stat-row">
    <div class="stat">
        <dt>Omzet</dt>
        <dd>Rp{{ number_format($omzet, 0, ',', '.') }}<small>tanpa pesanan batal</small></dd>
    </div>
    <div class="stat">
        <dt>Pesanan</dt>
        <dd>{{ $jumlahPesanan }}<small>{{ $jumlahBatal }} dibatalkan</small></dd>
    </div>
    <div class="stat">
        <dt>Rata-rata</dt>
        <dd>Rp{{ number_format($rataRata, 0, ',', '.') }}<small>per pesanan yang dihitung</small></dd>
    </div>
    <div class="stat">
        <dt>Bulan tercatat</dt>
        <dd>{{ $perBulan->count() }}<small>bulan dengan pesanan</small></dd>
    </div>
</dl>

<section class="panel" style="margin-bottom:22px">
    <h2>Per bulan</h2>
    @if ($perBulan->isEmpty())
        <div class="empty"><strong>Belum ada pesanan</strong>Rekap muncul setelah ada pesanan masuk dari halaman toko.</div>
    @else
        <div class="table-wrap">
            <table>
                <caption>{{ $perBulan->count() }} bulan</caption>
                <thead>
                <tr>
                    <th scope="col">Bulan</th>
                    <th scope="col" class="num">Pesanan</th>
                    <th scope="col" class="num">Selesai</th>
                    <th scope="col" class="num">Batal</th>
                    <th scope="col" class="num">Omzet</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($perBulan as $baris)
                    <tr>
                        <td data-label="Bulan">{{ $baris['label'] }}</td>
                        <td class="num" data-label="Pesanan">{{ $baris['jumlah'] }}</td>
                        <td class="num" data-label="Selesai">{{ $baris['selesai'] }}</td>
                        <td class="num" data-label="Batal">{{ $baris['batal'] }}</td>
                        <td class="num" data-label="Omzet">Rp{{ number_format($baris['omzet'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <th scope="row">Jumlah</th>
                    <td class="num" data-label="Pesanan"><strong>{{ $jumlahPesanan }}</strong></td>
                    <td class="num" data-label="Selesai"><strong>{{ $perBulan->sum('selesai') }}</strong></td>
                    <td class="num" data-label="Batal"><strong>{{ $jumlahBatal }}</strong></td>
                    <td class="num" data-label="Omzet"><strong>Rp{{ number_format($omzet, 0, ',', '.') }}</strong></td>
                </tr>
                </tbody>
            </table>
        </div>
    @endif
</section>

<section class="panel">
    <h2>Produk terlaris</h2>
    @if ($terlaris->isEmpty())
        <div class="empty"><strong>Belum ada produk terjual</strong>Daftar ini terisi setelah ada pesanan yang tidak dibatalkan.</div>
    @else
        <div class="table-wrap">
            <table>
                <caption>Lima produk dengan jumlah terjual terbanyak</caption>
                <thead>
                <tr><th scope="col">Produk</th><th scope="col" class="num">Terjual (bungkus)</th><th scope="col" class="num">Nilai</th></tr>
                </thead>
                <tbody>
                @foreach ($terlaris as $baris)
                    <tr>
                        <td data-label="Produk">{{ $baris['nama'] }}</td>
                        <td class="num" data-label="Terjual (bungkus)">{{ $baris['terjual'] }}</td>
                        <td class="num" data-label="Nilai">Rp{{ number_format($baris['nilai'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>
@endsection
