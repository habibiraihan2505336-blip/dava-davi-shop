@extends('shop.layout')
@section('title', 'Keripik Tempe Dava Davi | Toko')
@section('content')
<section class="hero" aria-labelledby="hero-title">
    <div class="hero-copy">
        <p class="kicker">Oleh-oleh khas Malang</p>
        <h1 id="hero-title">Keripik tempe yang bikin susah move on.</h1>
        <p>Tiga varian dari Dava Davi: ada yang dari sagu, ada yang menjes, ada yang tempe biasa. Tinggal pilih yang paling cocok buat mood kamu.</p>
        <a class="button" href="#produk">Cek variannya</a>
        <span class="hero-note">Dava Davi · Malang</span>
    </div>
    <div class="hero-art" role="img" aria-label="Ilustrasi kemasan keripik tempe, bukan foto produk asli">
        <span class="orbit orbit-one" aria-hidden="true"></span><span class="orbit orbit-two" aria-hidden="true"></span>
        <div class="hero-pack"><span class="pack-top">DARI DAPUR DAVA DAVI</span><span class="pack-window" aria-hidden="true"><i></i><i></i><i></i></span><span class="pack-name">Keripik<br>Tempe</span><span class="pack-flavor">ILUSTRASI KEMASAN</span></div>
        <div class="art-caption">Ilustrasi kemasan. Foto aslinya ada di katalog bawah.</div>
    </div>
</section>
<section class="section catalog" id="produk" aria-labelledby="products-heading">
    <div class="section-head catalog-head"><div><p class="kicker">Pilih-pilih dulu</p><h2 id="products-heading">Mau coba yang mana?</h2><p>Harga sudah nempel per bungkus. Pilih jumlahnya, langsung masuk keranjang.</p></div>
        <div class="catalog-tools">
            <label class="search-wrap"><span class="sr-only">Cari produk</span><input type="search" placeholder="Cari produk" autocomplete="off" data-product-search aria-label="Cari produk"><span aria-hidden="true">⌕</span></label>
            <div class="filter-group" role="group" aria-label="Filter kategori">
                <a class="filter-chip {{ $kategoriAktif ? '' : 'is-active' }}" href="{{ route('shop.home') }}#produk">Semua</a>
                @foreach ($categories as $category)
                    <a class="filter-chip {{ $kategoriAktif === $category->slug ? 'is-active' : '' }}" href="{{ route('shop.home', ['kategori' => $category->slug]) }}#produk">{{ $category->nama_kategori }}</a>
                @endforeach
            </div>
        </div>
    </div>
    <p class="catalog-count" aria-live="polite" data-product-count></p>
    <div class="placeholder-note" style="margin-bottom:20px">Nama, komposisi, dan harga ngikutin label kemasan. Stok masih angka sementara ya.</div>
    @if ($products->isEmpty())
        <div class="empty"><h2>Belum ada produk di sini</h2><p>Coba kategori lain, atau balik ke semua produk.</p><a class="button secondary" href="{{ route('shop.home') }}#produk">Lihat semua produk</a></div>
    @else
        <div class="products">
            @foreach ($products as $product)
                <article class="product" data-product-card data-product-name="{{ Str::lower($product->nama_produk) }}" data-product-description="{{ Str::lower((string) $product->deskripsi) }}" data-product-id="{{ $product->id }}" data-product-title="{{ $product->nama_produk }}" data-product-copy="{{ $product->deskripsi }}" data-product-price="{{ $product->harga_rupiah }}" data-product-stock="{{ $product->stok }}">
                    <div class="product-art {{ $product->gambar ? 'product-art--foto' : '' }}" role="group" aria-label="{{ $product->gambar ? 'Foto kemasan '.$product->nama_produk : 'Ilustrasi sementara untuk '.$product->nama_produk }}">@if ($product->gambar)<img src="{{ asset('storage/'.$product->gambar) }}" alt="Kemasan {{ $product->nama_produk }}" width="675" height="900" loading="lazy" decoding="async">@else<span>Ilustrasi produk</span>@endif<button type="button" class="detail-trigger" data-open-detail aria-label="Lihat detail {{ $product->nama_produk }}">Lihat detail</button></div>
                    <div class="product-body"><div class="product-heading"><div><p class="product-overline">{{ $product->category->nama_kategori }}</p><h3>{{ $product->nama_produk }}</h3></div><span class="product-mark" aria-hidden="true">DD</span></div>
                        <p class="product-description">{{ $product->deskripsi ?: 'Keterangan produk belum diisi.' }}</p>
                        <div class="product-buy">
                            <div class="price">{{ $product->harga_rupiah }} <span>/ per bungkus</span></div>
                            @if ($product->grosir_terendah)
                                <p class="grosir-note">Mulai {{ $product->grosir_terendah->min_qty }} bungkus Rp{{ number_format($product->grosir_terendah->harga_satuan, 0, ',', '.') }} per bungkus</p>
                            @endif
                            <form class="product-form" method="post" action="{{ route('cart.add') }}" data-add-form>@csrf<input type="hidden" name="product_id" value="{{ $product->id }}"><label>Jumlah<input type="number" name="quantity" min="1" max="{{ min(20, max(1, $product->stok)) }}" value="1" required aria-label="Jumlah {{ $product->nama_produk }}"></label><button class="button" type="submit" @disabled($product->stok < 1)>{{ $product->stok > 0 ? 'Tambah' : 'Stok habis' }}</button></form>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="empty filter-empty" hidden data-no-results><h2>Nggak ketemu</h2><p>Coba kata lain, atau pilih semua kategori.</p></div>
    @endif
</section>
<section class="story" id="cerita" aria-labelledby="story-heading"><div class="wrap story-inner"><div class="story-seal" aria-hidden="true"><span>D</span><small>DAVA DAVI</small></div><div><p class="kicker">Cerita</p><h2 id="story-heading">Dari Malang, buat teman santai kamu.</h2><p>Cerita lengkap Dava Davi belum masuk. Nanti diisi soal asal-usul dan cara bikinnya, langsung dari pemiliknya.</p><a href="#produk" class="text-link">Cek variannya</a></div></div></section>
<dialog class="product-dialog" aria-labelledby="detail-title" data-product-dialog><form method="dialog"><button class="dialog-close" aria-label="Tutup detail produk">Tutup</button></form><p class="kicker">Detail varian</p><h2 id="detail-title" data-detail-title></h2><p data-detail-copy></p><p class="detail-price" data-detail-price></p><p class="placeholder-note">Harga dan stok dari data toko. Stok masih angka sementara.</p><form method="post" action="{{ route('cart.add') }}" class="dialog-buy" data-dialog-form>@csrf<input type="hidden" name="product_id" data-detail-id><p class="dialog-error" role="alert" data-dialog-error hidden></p><label for="detail-quantity">Jumlah</label><input id="detail-quantity" type="number" name="quantity" value="1" min="1" max="20"><button type="submit" class="button">Masukkan keranjang</button></form></dialog>
<div class="toast" role="status" aria-live="polite" data-toast hidden></div>
@endsection
@section('scripts')
<script>
(() => {
    const cards = [...document.querySelectorAll('[data-product-card]')];
    const search = document.querySelector('[data-product-search]');
    const count = document.querySelector('[data-product-count]');
    const empty = document.querySelector('[data-no-results]');
    const dialog = document.querySelector('[data-product-dialog]');
    const updateCatalog = () => {
        const term = (search?.value || '').trim().toLocaleLowerCase('id');
        let visible = 0;
        cards.forEach((card) => {
            const matchesText = `${card.dataset.productName} ${card.dataset.productDescription}`.includes(term);
            card.hidden = !matchesText;
            if (!card.hidden) visible += 1;
        });
        if (count) count.textContent = `${visible} produk muncul`;
        if (empty) empty.hidden = visible > 0;
    };
    search?.addEventListener('input', updateCatalog);
    document.querySelectorAll('[data-open-detail]').forEach((button) => button.addEventListener('click', () => {
        const card = button.closest('[data-product-card]');
        if (!card || !dialog) return;
        dialog.querySelector('[data-detail-title]').textContent = card.dataset.productTitle;
        dialog.querySelector('[data-detail-copy]').textContent = card.dataset.productCopy || 'Keterangan produk belum diisi.';
        dialog.querySelector('[data-detail-price]').textContent = `${card.dataset.productPrice} per bungkus`;
        dialog.querySelector('[data-detail-id]').value = card.dataset.productId;
        const quantity = dialog.querySelector('[name="quantity"]');
        quantity.max = String(Math.min(20, Math.max(1, Number(card.dataset.productStock))));
        quantity.value = '1';
        const error = dialog.querySelector('[data-dialog-error]');
        if (error) { error.hidden = true; error.textContent = ''; }
        dialog.showModal();
    }));
    const addToCart = async (form) => {
        const button = form.querySelector('button[type="submit"]');
        const toast = document.querySelector('[data-toast]');
        const error = form.querySelector('[data-dialog-error]');
        const original = button.textContent;
        button.disabled = true;
        button.textContent = 'Menambahkan...';
        if (error) { error.hidden = true; error.textContent = ''; }
        try {
            const response = await fetch(form.action, {method: 'POST', body: new FormData(form), headers: {'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json'}});
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Produk belum dapat ditambahkan.');
            const badge = document.querySelector('[data-cart-count]');
            if (badge) badge.textContent = result.cart_count;
            if (toast) { toast.textContent = result.message; toast.hidden = false; window.setTimeout(() => { toast.hidden = true; }, 3200); }
            if (dialog?.open) dialog.close();
        } catch (failure) {
            if (failure instanceof TypeError) {
                form.submit();
            } else if (error) {
                error.textContent = failure.message;
                error.hidden = false;
            } else if (toast) {
                toast.textContent = failure.message;
                toast.hidden = false;
            }
        } finally {
            button.disabled = false;
            button.textContent = original;
        }
    };
    document.querySelectorAll('[data-add-form], [data-dialog-form]').forEach((form) => form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        addToCart(form);
    }));
    dialog?.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });
    updateCatalog();
})();
</script>
@endsection
