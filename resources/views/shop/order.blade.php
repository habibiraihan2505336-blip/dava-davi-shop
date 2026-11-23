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
        <p>Pesanan ini belum dibayar. Kabari penjual buat konfirmasi pembayaran, stok, ongkir, dan pengiriman.</p>
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
