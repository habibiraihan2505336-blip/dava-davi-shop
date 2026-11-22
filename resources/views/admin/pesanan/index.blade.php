@extends('admin.layout')
@section('title', 'Pesanan')
@section('content')
<h1>Pesanan</h1>
<p class="lede">Cari berdasarkan kode pesanan, nama pemesan, atau nomor HP. Status diubah di halaman rincian tiap pesanan.</p>

<div class="toolbar">
    <div class="field" style="min-width:auto">
        <span class="hint">Status</span>
        <div class="filter-group" style="display:flex;flex-wrap:wrap;gap:8px">
            <a class="button secondary" href="{{ route('admin.pesanan.index') }}" @if (! $statusAktif) aria-current="page" @endif>Semua</a>
            @foreach (\App\Models\Order::STATUS as $kunci => $label)
                <a class="button secondary" href="{{ route('admin.pesanan.index', ['status' => $kunci]) }}" @if ($statusAktif === $kunci) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </div>
    </div>
    <form class="field" method="get" action="{{ route('admin.pesanan.index') }}" style="min-width:min(100%,320px)">
        <label for="cari">Cari</label>
        <input id="cari" name="cari" type="search" value="{{ $cari }}" placeholder="Kode, nama, atau nomor HP">
        <button class="button" type="submit">Cari</button>
    </form>
</div>

@if ($pesanan->isEmpty())
    <div class="empty"><strong>Tidak ada pesanan yang cocok</strong>Ubah kata pencarian atau pilih status lain. Pesanan baru muncul setelah pembeli menyelesaikan checkout.</div>
@else
    <div class="table-wrap">
        <table>
            <caption>{{ $pesanan->total() }} pesanan ditemukan</caption>
            <thead>
            <tr>
                <th scope="col">Kode</th><th scope="col">Tanggal</th><th scope="col">Pemesan</th><th scope="col">No. HP</th>
                <th scope="col" class="num">Item</th><th scope="col">Status</th><th scope="col">Bayar</th>
                <th scope="col" class="num">Total</th><th scope="col"><span class="sr-only">Aksi</span></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($pesanan as $order)
                <tr>
                    <td data-label="Kode">{{ $order->kode_pesanan }}</td>
                    <td data-label="Tanggal">{{ $order->tanggal->format('d-m-Y') }}</td>
                    <td data-label="Pemesan">{{ $order->customer->nama }}</td>
                    <td data-label="No. HP">{{ $order->customer->no_hp }}</td>
                    <td class="num" data-label="Item">{{ $order->items_count }}</td>
                    <td data-label="Status"><span class="chip {{ $order->status }}">{{ $order->labelStatus() }}</span></td>
                    <td data-label="Bayar"><span class="chip {{ $order->payment_status === 'dibayar' ? 'bayar' : 'belum' }}">{{ $order->labelPembayaran() }}</span></td>
                    <td class="num" data-label="Total">Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                    <td data-label="Aksi"><a class="table-link" href="{{ route('admin.pesanan.show', $order) }}">Buka</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @include('admin.partials.pager', ['paginator' => $pesanan])
@endif
@endsection
