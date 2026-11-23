@extends('shop.layout')
@section('title', 'Akun Saya | Dava Davi')
@section('content')
<section class="content">
    <p class="kicker">Akun pembeli</p>
    <h1>Akun saya</h1>

    <div class="akun-grid" style="margin-top:22px">
        <div>
            <h2 style="font:500 26px var(--serif);color:var(--brown-dark);margin:0 0 14px">Riwayat pesanan</h2>
            @if ($pesanan->isEmpty())
                <div class="empty"><h2>Belum ada pesanan nih</h2><p>Pesanan yang kamu buat pakai akun ini bakal muncul di sini.</p><a class="button" href="{{ route('shop.home') }}#produk">Lihat varian</a></div>
            @else
                @foreach ($pesanan as $order)
                    <article class="pesanan-card">
                        <div class="pesanan-head">
                            <h2>{{ $order->kode_pesanan }}</h2>
                            <span class="status-pil">{{ $order->labelStatus() }} · {{ $order->labelPembayaran() }}</span>
                        </div>
                        <p style="margin:0 0 8px;color:var(--muted);font-size:14px">Tanggal {{ $order->tanggal->format('d-m-Y') }}</p>
                        @foreach ($order->items as $item)
                            <div class="sumline"><span>{{ $item->product->nama_produk }} × {{ $item->jumlah }}</span><strong>Rp{{ number_format($item->subtotal, 0, ',', '.') }}</strong></div>
                        @endforeach
                        <div class="sumline"><span>Ongkir</span><span>{{ $order->ongkir > 0 ? 'Rp'.number_format($order->ongkir, 0, ',', '.') : 'Dikonfirmasi penjual' }}</span></div>
                        <div class="sumline total"><span>Total</span><span>Rp{{ number_format($order->total, 0, ',', '.') }}</span></div>
                        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:12px">
                            <a class="button secondary" href="{{ route('orders.show', $order->kode_pesanan) }}">Lihat rincian</a>
                            <form method="post" action="{{ route('akun.pesan-ulang', $order) }}">@csrf<button class="button" type="submit">Pesan ulang</button></form>
                        </div>
                    </article>
                @endforeach
                @include('admin.partials.pager', ['paginator' => $pesanan])
            @endif
        </div>

        <aside class="summary">
            <h2>Data akun</h2>
            <div class="sumline"><span>Nama</span><strong>{{ $customer->nama }}</strong></div>
            <div class="sumline"><span>Email</span><strong>{{ $customer->email }}</strong></div>
            <div class="sumline"><span>Nomor HP</span><strong>{{ $customer->no_hp }}</strong></div>
            <div class="sumline"><span>Alamat</span><strong>{{ $customer->alamat ?: 'Belum diisi' }}</strong></div>
            <form method="post" action="{{ route('pelanggan.logout') }}">@csrf<button class="button secondary" type="submit">Keluar dari akun</button></form>
        </aside>
    </div>
</section>
@endsection
