@extends('shop.layout')
@section('title', 'Keranjang Belanja | Dava Davi')
@section('content')
<section class="content"><p class="kicker">Isi keranjang</p><h1>Keranjang belanja</h1>
@if ($lines->isEmpty())
    <div class="empty"><h2>Keranjangmu masih kosong</h2><p>Yuk pilih dulu variannya, nanti balik ke sini.</p><a class="button" href="{{ route('shop.home') }}#produk">Lihat varian</a></div>
@else
    <div class="cart-layout"><div class="cart-list">
        @foreach ($lines as $line)
            <div class="cart-row">
                <div>
                    <h3>{{ $line['product']->nama_produk }}</h3>
                    <p>Rp{{ number_format($line['harga_satuan'], 0, ',', '.') }} per bungkus{{ $line['grosir'] ? ' (harga grosir)' : '' }}</p>
                    <p>Jumlah: {{ $line['jumlah'] }} bungkus</p>
                </div>
                <strong>Rp{{ number_format($line['subtotal'], 0, ',', '.') }}</strong>
                <form method="post" action="{{ route('cart.remove', $line['product']->id) }}">@csrf @method('DELETE')<button class="remove" type="submit">Hapus</button></form>
            </div>
        @endforeach
    </div>
    <aside class="summary"><h2>Ringkasan</h2>
        <div class="sumline"><span>Subtotal produk</span><strong>Rp{{ number_format($total, 0, ',', '.') }}</strong></div>
        <div class="sumline"><span>Ongkos kirim</span><span>Dikonfirmasi terpisah</span></div>
        <div class="sumline total"><span>Total sementara</span><span>Rp{{ number_format($total, 0, ',', '.') }}</span></div>
        <p style="font-size:13px;color:var(--muted)">Total belum termasuk ongkir. Harga grosir kepakai otomatis kalau jumlahnya cukup.</p>
        <a class="button" href="{{ route('checkout.show') }}">Lanjut isi data</a>
        <a class="button secondary" style="width:100%;margin-top:10px" href="{{ route('shop.home') }}#produk">Tambah lagi</a>
    </aside></div>
@endif
</section>
@endsection
