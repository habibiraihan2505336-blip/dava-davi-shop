<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Panel Admin') | Dava Davi</title>
    <style>
        :root{--cream:#fbf6e9;--paper:#fffdf7;--ink:#33251d;--muted:#594b40;--brown:#6f321f;--brown-dark:#4b261c;--gold:#d99a32;--line:#ddcbb0;--green:#3f5a2d;--serif:Georgia,'Times New Roman',serif;--sans:Arial,Helvetica,sans-serif}
        *{box-sizing:border-box}body{margin:0;background:var(--cream);color:var(--ink);font:16px/1.55 var(--sans)}a{color:inherit;text-decoration:none}button,input,select,textarea{font:inherit}button,a,select{cursor:pointer}label{cursor:pointer}button:disabled{cursor:not-allowed;opacity:.6}:focus-visible{outline:3px solid #176c62;outline-offset:3px}
        .shell{width:min(1180px,calc(100% - 40px));margin-inline:auto}
        .sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
        .button.secondary[aria-current="page"]{background:var(--brown-dark);border-color:var(--brown-dark);color:#fff8e9}
        .topbar{background:var(--brown-dark);color:#fff8e9}.topbar-inner{min-height:64px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:14px;padding-block:10px}
        .brand{display:inline-flex;min-height:44px;flex-direction:column;justify-content:center;font:700 21px/1.1 var(--serif)}.brand small{display:block;margin-top:4px;color:#ead8b5;font:11px var(--sans);letter-spacing:1px}
        .topnav{display:flex;flex-wrap:wrap;align-items:center;gap:6px}.topnav a{min-height:44px;display:inline-flex;align-items:center;padding:8px 14px;border-radius:4px;font-size:14px;font-weight:700}
        .topnav a:hover{background:rgba(255,248,233,.14)}.topnav a[aria-current="page"]{background:#fff8e9;color:var(--brown-dark)}
        .who{display:flex;align-items:center;gap:12px;font-size:13px;color:#f4e9da}.who form{margin:0}
        .link-button{min-height:40px;padding:6px 12px;border:1px solid #d8c5a6;border-radius:4px;background:transparent;color:#fff8e9;font-size:13px;font-weight:700}
        .link-button:hover{background:rgba(255,248,233,.14)}
        main{padding-block:28px 60px}
        h1{margin:0 0 6px;color:var(--brown-dark);font:500 clamp(30px,3.4vw,42px)/1.1 var(--serif);letter-spacing:-.6px}
        h2{margin:0 0 14px;color:var(--brown-dark);font:500 24px/1.2 var(--serif)}
        .lede{margin:0 0 22px;color:var(--muted);max-width:70ch}
        .notice{margin:0 0 18px;padding:12px 15px;border:1px solid #6c7d47;background:#eff0df;color:#28391d}
        .notice.error{border-color:#954d38;background:#f7e6dc;color:#542719}
        .panel{padding:20px;border:1px solid #d8c5a6;border-radius:4px;background:var(--paper)}
        .stat-row{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:24px}
        .stat{min-width:0;padding:16px 18px;border:1px solid #d8c5a6;border-left:3px solid var(--gold);background:var(--paper)}
        .stat dt{margin:0 0 6px;color:var(--muted);font-size:12px;font-weight:700;letter-spacing:.4px;text-transform:uppercase}
        .stat dd{margin:0;color:var(--brown-dark);font:500 32px/1 var(--serif)}
        .stat dd small{display:block;margin-top:6px;color:var(--muted);font:12px var(--sans)}
        .table-wrap{overflow-x:auto;border:1px solid #d8c5a6;background:var(--paper)}
        table{width:100%;border-collapse:collapse;font-size:14px}
        caption{padding:14px 16px;text-align:left;color:var(--muted);font-size:13px;border-bottom:1px solid var(--line)}
        th,td{padding:12px 14px;text-align:left;border-bottom:1px solid var(--line);vertical-align:top}
        th{background:#f4e9d6;color:var(--brown-dark);font-size:12px;letter-spacing:.4px;text-transform:uppercase;white-space:nowrap}
        tbody tr:last-child td{border-bottom:0}tbody tr:hover{background:#fdf7ea}
        .num{text-align:right;font-variant-numeric:tabular-nums;white-space:nowrap}
        .chip{display:inline-flex;align-items:center;gap:6px;padding:3px 9px;border:1px solid #a88e6f;border-radius:20px;font-size:12px;font-weight:700;white-space:nowrap}
        .chip.baru{border-color:#b06a2c;color:#7d4310}.chip.diproses{border-color:#4d6b8a;color:#2f4a63}.chip.selesai{border-color:#4f6b3a;color:#33481f}.chip.batal{border-color:#9b4a35;color:#6d2c1b}
        .chip.bayar{border-color:#4f6b3a;color:#33481f}.chip.belum{border-color:#b06a2c;color:#7d4310}
        .table-link{display:inline-flex;min-height:40px;align-items:center;color:var(--brown);font-weight:700;text-decoration:underline;text-underline-offset:3px}
        .toolbar{display:flex;flex-wrap:wrap;align-items:end;gap:12px;margin-bottom:18px}
        .toolbar .field{min-width:min(100%,230px)}
        .field{display:grid;gap:6px}.field label{font-size:13px;font-weight:700}
        .field input,.field select,.field textarea{width:100%;min-height:44px;padding:9px 11px;border:1px solid #806b55;border-radius:3px;background:#fffdf7;color:var(--ink)}
        .field textarea{min-height:90px;resize:vertical}.field-error{color:#692e1d;font-size:13px}.hint{color:var(--muted);font-size:12px}
        .button{display:inline-flex;min-height:44px;align-items:center;justify-content:center;padding:9px 18px;border:1px solid var(--brown);border-radius:4px;background:var(--brown);color:#fffdf7;font-weight:700}
        .button:hover{background:var(--brown-dark)}.button.secondary{background:transparent;color:var(--brown)}.button.secondary:hover{background:#f1e7d6}
        .empty{padding:26px;border:1px dashed #a88e6f;background:#fffaf0;color:var(--muted)}
        .empty strong{display:block;margin-bottom:6px;color:var(--brown-dark);font:500 20px var(--serif)}
        .pager{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:18px;font-size:14px}
        .pager a,.pager span{min-height:40px;display:inline-flex;align-items:center;padding:6px 12px;border:1px solid #d8c5a6;background:var(--paper);color:var(--brown-dark)}
        .pager .current{background:var(--brown-dark);border-color:var(--brown-dark);color:#fff8e9}
        .pager .disabled{color:#9a8b7c}
        .grid-two{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(0,1fr);gap:20px;align-items:start}
        .detail-list{margin:0}.detail-list div{display:flex;flex-wrap:wrap;justify-content:space-between;gap:10px;padding:9px 0;border-bottom:1px solid var(--line);font-size:14px}
        .detail-list div:last-child{border-bottom:0}.detail-list dt{color:var(--muted)}.detail-list dd{margin:0;font-weight:700}
        .stack{display:grid;gap:16px}
        .footer-note{margin-top:26px;color:var(--muted);font-size:12px}
        @media(max-width:900px){.stat-row{grid-template-columns:repeat(2,minmax(0,1fr))}.grid-two{grid-template-columns:1fr}.grid-two>*,.stack>*{min-width:0}}
        @media(max-width:900px){
            .table-wrap{overflow:visible;border:0;background:transparent}
            table{width:100%;font-size:14px}
            caption{display:block;padding:0 0 10px;border:0}
            thead{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
            tbody tr{display:block;margin-bottom:12px;padding:6px 14px;border:1px solid #d8c5a6;background:var(--paper)}
            tbody tr:hover{background:var(--paper)}
            tbody th,tbody td{display:grid;grid-template-columns:minmax(0,45%) minmax(0,1fr);gap:12px;align-items:start;border:0;border-bottom:1px solid var(--line);padding:9px 0;text-align:left;white-space:normal;overflow-wrap:anywhere}
            tbody tr td:last-child,tbody tr th:last-child{border-bottom:0}
            tbody td:before{content:attr(data-label);color:var(--muted);font-size:12px;font-weight:700;letter-spacing:.3px;text-transform:uppercase}
            tbody td:empty:before{content:''}
            .num{text-align:left}
            tbody tr.total-row{border-top:2px solid var(--brown)}
            tbody tr.total-row th{display:block;border-bottom:0;padding-bottom:0}
            tbody tr.total-row td{grid-template-columns:minmax(0,45%) minmax(0,1fr);border-bottom:0}
        }
        @media(max-width:640px){.shell{width:min(100% - 28px,540px)}.topbar-inner{align-items:flex-start}.who{width:100%;justify-content:space-between}.stat dd{font-size:27px}}
        @media(prefers-reduced-motion:reduce){*{transition:none!important;animation:none!important}}
    </style>
</head>
<body>
<header class="topbar">
    <div class="shell topbar-inner">
        <a class="brand" href="{{ route('admin.dashboard') }}">Dava Davi<small>PANEL PENGELOLA</small></a>
        <nav class="topnav" aria-label="Navigasi panel">
            <a href="{{ route('admin.dashboard') }}" @if (request()->routeIs('admin.dashboard')) aria-current="page" @endif>Ringkasan</a>
            <a href="{{ route('admin.pesanan.index') }}" @if (request()->routeIs('admin.pesanan.*')) aria-current="page" @endif>Pesanan</a>
            <a href="{{ route('admin.produk.index') }}" @if (request()->routeIs('admin.produk.*')) aria-current="page" @endif>Produk</a>
            <a href="{{ route('admin.kategori.index') }}" @if (request()->routeIs('admin.kategori.*')) aria-current="page" @endif>Kategori</a>
            <a href="{{ route('shop.home') }}">Lihat toko</a>
        </nav>
        <div class="who">
            <span>{{ auth('admin')->user()->nama }}</span>
            <form method="post" action="{{ route('admin.logout') }}">@csrf<button class="link-button" type="submit">Keluar</button></form>
        </div>
    </div>
</header>
<main class="shell">
    @if (session('success'))<div class="notice" role="status">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="notice error" role="alert">{{ session('error') }}</div>@endif
    @if ($errors->any())<div class="notice error" role="alert">Periksa kembali isian. {{ $errors->first() }}</div>@endif
    @yield('content')
    <p class="footer-note">Data pada panel ini berasal dari basis data Supabase proyek Dava Davi. Harga dan stok masih data uji sampai pemilik mengonfirmasi.</p>
</main>
@yield('scripts')
</body>
</html>
