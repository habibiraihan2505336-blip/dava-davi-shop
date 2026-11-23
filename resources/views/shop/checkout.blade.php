@extends('shop.layout')
@section('title', 'Checkout | Dava Davi')
@section('content')
<section class="content"><p class="kicker">Tinggal satu langkah</p><h1>Data penerima</h1>
<p style="color:var(--muted);max-width:700px">Isi data penerima. Ongkir dikabari penjual setelah pesanan masuk, soalnya tergantung tujuan dan kurir.</p>
@guest('pelanggan')
    <p class="placeholder-note" style="margin:14px 0 0">Punya akun? <a class="text-link" href="{{ route('pelanggan.login') }}" style="margin-top:0">Masuk</a> biar pesananmu kesimpan di riwayat. Nggak punya akun juga bisa pesan.</p>
@endguest
<div class="cart-layout" style="margin-top:25px"><form class="form-card" method="post" action="{{ route('checkout.place') }}">@csrf
    <div class="field"><label for="nama">Nama penerima</label><input id="nama" name="nama" value="{{ old('nama', $pelanggan->nama ?? '') }}" autocomplete="name" required>@error('nama')<div class="field-error">{{ $message }}</div>@enderror</div>
    <div class="field"><label for="no_hp">Nomor HP</label><input id="no_hp" name="no_hp" type="tel" value="{{ old('no_hp', $pelanggan->no_hp ?? '') }}" autocomplete="tel" inputmode="tel" placeholder="08xxxxxxxxxx" required>@error('no_hp')<div class="field-error">{{ $message }}</div>@enderror</div>
    <div class="field"><label for="email">Email (opsional)</label><input id="email" name="email" type="email" value="{{ old('email', $pelanggan->email ?? '') }}" autocomplete="email">@error('email')<div class="field-error">{{ $message }}</div>@enderror</div>
    <div class="field"><label for="alamat">Alamat penerima</label><textarea id="alamat" name="alamat" autocomplete="street-address" required>{{ old('alamat', $pelanggan->alamat ?? '') }}</textarea>@error('alamat')<div class="field-error">{{ $message }}</div>@enderror</div>
    <div class="field"><label for="catatan">Catatan untuk penjual (opsional)</label><textarea id="catatan" name="catatan" maxlength="500" style="min-height:70px">{{ old('catatan') }}</textarea>@error('catatan')<div class="field-error">{{ $message }}</div>@enderror</div>
    <button class="button" type="submit">Buat pesanan</button>
</form><aside class="summary"><h2>Pesanan</h2>@foreach ($lines as $line)<div class="sumline"><span>{{ $line['product']->nama_produk }} × {{ $line['jumlah'] }}</span><strong>Rp{{ number_format($line['subtotal'], 0, ',', '.') }}</strong></div>@endforeach<div class="sumline"><span>Ongkos kirim</span><span>Dikonfirmasi penjual</span></div><div class="sumline total"><span>Subtotal</span><span>Rp{{ number_format($total, 0, ',', '.') }}</span></div><p style="font-size:13px;color:var(--muted)">Kode pesanan dibuat otomatis. Total akhir nyesuaiin ongkir dari penjual.</p></aside></div>
</section>
@endsection
