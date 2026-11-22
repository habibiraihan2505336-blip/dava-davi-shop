<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk Pengelola | Dava Davi</title>
    <style>
        :root{--cream:#fbf6e9;--paper:#fffdf7;--ink:#33251d;--muted:#594b40;--brown:#6f321f;--brown-dark:#4b261c;--gold:#d99a32;--line:#ddcbb0;--green:#3f5a2d;--serif:Georgia,'Times New Roman',serif;--sans:Arial,Helvetica,sans-serif}
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:28px 16px;background:var(--cream);color:var(--ink);font:16px/1.55 var(--sans)}
        a{color:inherit}button,input{font:inherit}button{cursor:pointer}:focus-visible{outline:3px solid #176c62;outline-offset:3px}
        .card{width:min(420px,100%);padding:26px;border:1px solid #d8c5a6;border-top:3px solid var(--gold);background:var(--paper)}
        .brand{display:inline-flex;min-height:44px;flex-direction:column;justify-content:center;margin-bottom:14px;color:var(--brown);font:700 22px/1.1 var(--serif)}
        .brand small{display:block;margin-top:5px;color:var(--muted);font:11px var(--sans);letter-spacing:1px}
        h1{margin:0 0 6px;color:var(--brown-dark);font:500 30px/1.15 var(--serif)}
        p.lede{margin:0 0 20px;color:var(--muted);font-size:14px}
        .field{display:grid;gap:6px;margin-bottom:14px}
        label{font-size:13px;font-weight:700}
        input{width:100%;min-height:46px;padding:10px 12px;border:1px solid #806b55;border-radius:3px;background:#fffdf7;color:var(--ink)}
        .field-error{margin:0;color:#692e1d;font-size:13px}
        .check{display:flex;align-items:center;gap:9px;min-height:44px;margin:4px 0 18px;font-size:14px}
        .check input{width:20px;min-height:20px}
        .button{width:100%;min-height:46px;border:1px solid var(--brown);border-radius:4px;background:var(--brown);color:#fffdf7;font-weight:700}
        .button:hover{background:var(--brown-dark)}
        .notice{margin:0 0 16px;padding:11px 13px;border:1px solid #6c7d47;background:#eff0df;color:#28391d;font-size:14px}
        .notice.error{border-color:#954d38;background:#f7e6dc;color:#542719}
        .back{display:inline-flex;min-height:44px;align-items:center;margin-top:16px;color:var(--brown);font-size:14px;font-weight:700;text-decoration:underline;text-underline-offset:3px}
    </style>
</head>
<body>
<div class="card">
    <header><a class="brand" href="{{ route('shop.home') }}">Dava Davi<small>KERIPIK TEMPE</small></a></header>
    <main>
    <h1>Masuk pengelola</h1>
    <p class="lede">Halaman ini untuk pemilik dan pengelola toko. Pembeli tidak perlu masuk untuk memesan.</p>
    @if (session('success'))<div class="notice" role="status">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="notice error" role="alert">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('admin.login.attempt') }}">
        @csrf
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        </div>
        <div class="field">
            <label for="password">Kata sandi</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
        </div>
        <label class="check"><input type="checkbox" name="ingat" value="1"> Ingat saya di perangkat ini</label>
        <button class="button" type="submit">Masuk</button>
    </form>
    </main>
    <footer><a class="back" href="{{ route('shop.home') }}">Kembali ke halaman toko</a></footer>
</div>
</body>
</html>
