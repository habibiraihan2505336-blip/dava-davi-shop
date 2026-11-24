@extends('shop.layout')
@section('title', 'Pesanan '.$order->kode_pesanan.' | Dava Davi')
@section('content')
<section class="content"><p class="kicker">Pesanan masuk</p><h1>Terima kasih, {{ $order->customer->nama }}.</h1>
<div class="order-card" style="max-width:760px;padding:22px;border:1px solid #d8c5a6;border-radius:4px;background:var(--paper)">
    <p>Kode pesanan: <strong>{{ $order->kode_pesanan }}</strong></p>
    <p>Tanggal: {{ $order->tanggal->format('d-m-Y') }}</p>
    <p>Status pesanan: <strong>{{ $order->labelStatus() }}</strong></p>
    <p>Status pembayaran: <strong>{{ $order->labelPembayaran() }}</strong></p>
    @if ($order->payment_status === 'belum_bayar')
        @if (filled(config('services.midtrans.server_key')))
            <p>Pesanan ini belum dibayar. Kamu bisa bayar sekarang lewat Midtrans, atau kabari penjual kalau mau bayar manual.</p>
            <form method="post" action="{{ route('pesanan.bayar', $order->kode_pesanan) }}" style="margin:0 0 14px">
                @csrf
                <button class="button" type="submit">Bayar sekarang</button>
            </form>
        @else
            <p>Pesanan ini belum dibayar. Kabari penjual buat konfirmasi pembayaran, stok, ongkir, dan pengiriman.</p>
        @endif
    @elseif ($order->payment_status === 'menunggu')
        <p>Pembayaran sedang menunggu penyelesaian. Kalau halaman pembayaran tertutup, buka lagi lewat tombol di bawah.</p>
        @if ($order->payment_url)
            <a class="button" href="{{ $order->payment_url }}">Lanjutkan pembayaran</a>
        @endif
        <a class="button secondary" href="{{ route('pesanan.cek-bayar', $order->kode_pesanan) }}">Cek status pembayaran</a>
    @elseif ($order->payment_status === 'dibayar')
        <p>Pembayaran diterima{{ $order->paid_at ? ' pada '.$order->paid_at->format('d-m-Y H:i') : '' }}. Pesanan akan diproses penjual.</p>
    @elseif ($order->payment_status === 'gagal')
        <p>Pembayaran terakhir gagal atau dibatalkan. Coba bayar lagi, atau kabari penjual.</p>
    @elseif ($order->payment_status === 'kedaluwarsa')
        <p>Batas waktu pembayaran sudah lewat. Kabari penjual kalau pesanan masih ingin dilanjutkan.</p>
    @endif
    <h2>Rincian</h2>
    @foreach ($order->items as $item)
        <div class="sumline"><span>{{ $item->product->nama_produk }} × {{ $item->jumlah }} (Rp{{ number_format($item->harga_satuan, 0, ',', '.') }}/bungkus)</span><strong>Rp{{ number_format($item->subtotal, 0, ',', '.') }}</strong></div>
    @endforeach
    <div class="sumline"><span>Subtotal item</span><span>Rp{{ number_format($order->subtotalItem(), 0, ',', '.') }}</span></div>
    <div class="sumline"><span>Ongkos kirim</span><span>{{ $order->ongkir > 0 ? 'Rp'.number_format($order->ongkir, 0, ',', '.') : 'Belum ditetapkan penjual' }}</span></div>
    <div class="sumline total"><span>Total</span><span>Rp{{ number_format($order->total, 0, ',', '.') }}</span></div>
    <p>Alamat penerima: {{ $order->customer->alamat }}</p>
    <p>Nomor HP: {{ $order->customer->no_hp }}</p>
    @if ($order->catatan)
        <p>Catatan: {{ $order->catatan }}</p>
    @endif
    <a class="button" href="{{ route('shop.home') }}">Balik ke katalog</a>
</div>
<p style="margin-top:16px;color:var(--muted);font-size:13px">Simpan kode ini buat nanya status pesanan. Halaman ini bisa dibuka lagi lewat /pesanan/{{ $order->kode_pesanan }}.</p>
</section>
@endsection
