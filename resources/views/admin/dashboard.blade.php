@extends('admin.layout')
@section('title', 'Ringkasan')
@section('content')
<h1>Ringkasan toko</h1>
<p class="lede">Angka di bawah dihitung langsung dari basis data, bukan perkiraan.</p>

<dl class="stat-row">
    <div class="stat">
        <dt>Pesanan baru</dt>
        <dd>{{ $jumlahBaru }}<small>status baru, belum diproses</small></dd>
    </div>
    <div class="stat">
        <dt>Belum dibayar</dt>
        <dd>{{ $jumlahBelumBayar }}<small>menunggu konfirmasi pembayaran</small></dd>
    </div>
    <div class="stat">
        <dt>Produk</dt>
        <dd>{{ $jumlahProduk }}<small>seluruh produk, aktif dan nonaktif</small></dd>
    </div>
    <div class="stat">
        <dt>Stok habis</dt>
        <dd>{{ $produkHabis }}<small>produk dengan stok 0</small></dd>
    </div>
</dl>

<h2>Pesanan terbaru</h2>
@if ($pesananTerbaru->isEmpty())
    <div class="empty"><strong>Belum ada pesanan</strong>Pesanan dari pembeli akan muncul di sini setelah checkout di halaman toko.</div>
@else
    <div class="table-wrap">
        <table>
            <caption>Delapan pesanan terakhir</caption>
            <thead>
            <tr><th scope="col">Kode</th><th scope="col">Tanggal</th><th scope="col">Pemesan</th><th scope="col">Status</th><th scope="col" class="num">Total</th><th scope="col"><span class="sr-only">Aksi</span></th></tr>
            </thead>
            <tbody>
            @foreach ($pesananTerbaru as $order)
                <tr>
                    <td data-label="Kode">{{ $order->kode_pesanan }}</td>
                    <td data-label="Tanggal">{{ $order->tanggal->format('d-m-Y') }}</td>
                    <td data-label="Pemesan">{{ $order->customer->nama }}</td>
                    <td data-label="Status"><span class="chip {{ $order->status }}">{{ $order->labelStatus() }}</span></td>
                    <td class="num" data-label="Total">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                    <td data-label="Aksi"><a class="table-link" href="{{ route('admin.pesanan.show', $order) }}">Buka</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
