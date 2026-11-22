@extends('shop.layout')
@section('title', 'Daftar Akun | Dava Davi')
@section('content')
<section class="content" style="max-width:620px">
    <p class="kicker">Akun pembeli</p>
    <h1>Daftar akun</h1>
    <p style="color:var(--muted)">Akun dipakai untuk menyimpan riwayat pesanan dan mengulang pesanan lama. Data hanya dipakai untuk keperluan pesanan.</p>

    <form class="form-card" method="post" action="{{ route('pelanggan.daftar.attempt') }}" style="margin-top:22px">
        @csrf
        <div class="field">
            <label for="nama">Nama</label>
            <input id="nama" name="nama" value="{{ old('nama') }}" autocomplete="name" required>
            @error('nama')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
            @error('email')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="no_hp">Nomor HP</label>
            <input id="no_hp" name="no_hp" type="tel" inputmode="tel" value="{{ old('no_hp') }}" placeholder="08xxxxxxxxxx" autocomplete="tel" required>
            @error('no_hp')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="alamat">Alamat (opsional)</label>
            <textarea id="alamat" name="alamat" autocomplete="street-address" style="min-height:80px">{{ old('alamat') }}</textarea>
            @error('alamat')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="password">Kata sandi (minimal 8 karakter)</label>
            <input id="password" name="password" type="password" autocomplete="new-password" required>
            @error('password')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="password_confirmation">Ulangi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
        </div>
        <button class="button" type="submit">Buat akun</button>
    </form>

    <p style="margin-top:18px;color:var(--muted)">Sudah punya akun? <a class="text-link" href="{{ route('pelanggan.login') }}" style="margin-top:0">Masuk</a>.</p>
</section>
@endsection
