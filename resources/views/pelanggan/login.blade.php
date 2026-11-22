@extends('shop.layout')
@section('title', 'Masuk | Dava Davi')
@section('content')
<section class="content" style="max-width:560px">
    <p class="kicker">Akun pembeli</p>
    <h1>Masuk</h1>
    <p style="color:var(--muted)">Masuk agar pesanan tercatat di riwayat akunmu. Tanpa akun pun kamu tetap bisa memesan sebagai tamu.</p>

    <form class="form-card" method="post" action="{{ route('pelanggan.login.attempt') }}" style="margin-top:22px">
        @csrf
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
            @error('email')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <div class="field">
            <label for="password">Kata sandi</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            @error('password')<div class="field-error">{{ $message }}</div>@enderror
        </div>
        <label style="display:flex;align-items:center;gap:9px;min-height:44px;font-size:14px"><input type="checkbox" name="ingat" value="1" style="width:20px;min-height:20px"> Ingat saya di perangkat ini</label>
        <button class="button" type="submit">Masuk</button>
    </form>

    <p style="margin-top:18px;color:var(--muted)">Belum punya akun? <a class="text-link" href="{{ route('pelanggan.daftar') }}" style="margin-top:0">Daftar di sini</a>.</p>
</section>
@endsection
