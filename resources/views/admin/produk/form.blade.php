@extends('admin.layout')
@section('title', $product->exists ? 'Ubah produk' : 'Produk baru')
@section('content')
<p class="hint"><a class="table-link" href="{{ route('admin.produk.index') }}">Kembali ke daftar produk</a></p>
<h1>{{ $product->exists ? 'Ubah produk' : 'Produk baru' }}</h1>
<p class="lede">Slug dibuat otomatis dari nama produk. Harga dan stok wajib angka bulat rupiah tanpa titik.</p>

@if ($product->exists)
    <form method="post" action="{{ route('admin.produk.update', $product) }}" class="panel stack" style="max-width:640px">
        @csrf
        @method('PUT')
@else
    <form method="post" action="{{ route('admin.produk.store') }}" class="panel stack" style="max-width:640px">
        @csrf
@endif
        <div class="field">
            <label for="nama_produk">Nama produk</label>
            <input id="nama_produk" name="nama_produk" value="{{ old('nama_produk', $product->nama_produk) }}" maxlength="255" required>
            @error('nama_produk')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label for="category_id">Kategori</label>
            <select id="category_id" name="category_id" required>
                <option value="">Pilih kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>{{ $category->nama_kategori }}</option>
                @endforeach
            </select>
            @error('category_id')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label for="harga">Harga per bungkus (Rp)</label>
            <input id="harga" name="harga" type="number" min="1" max="100000000" value="{{ old('harga', $product->harga) }}" required>
            @error('harga')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label for="stok">Stok (bungkus)</label>
            <input id="stok" name="stok" type="number" min="0" max="100000" value="{{ old('stok', $product->stok) }}" required>
            @error('stok')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <div class="field">
            <label for="deskripsi">Keterangan</label>
            <textarea id="deskripsi" name="deskripsi" maxlength="2000">{{ old('deskripsi', $product->deskripsi) }}</textarea>
            @error('deskripsi')<p class="field-error">{{ $message }}</p>@enderror
        </div>
        <label class="check" style="display:flex;align-items:center;gap:9px;font-size:14px">
            <input type="checkbox" name="status_aktif" value="1" style="width:20px;min-height:20px" @checked(old('status_aktif', $product->status_aktif))>
            Tampilkan di katalog
        </label>
        <button class="button" type="submit">{{ $product->exists ? 'Simpan perubahan' : 'Simpan produk' }}</button>
    </form>

@if ($product->exists)
    <section class="panel" style="max-width:640px;margin-top:20px">
        <h2>Harga grosir</h2>
        <p class="hint">Harga grosir dipakai otomatis di keranjang bila jumlah mencapai ambang. Harga grosir harus lebih rendah dari harga satuan.</p>
        @if ($product->priceTiers->isEmpty())
            <p class="hint">Belum ada harga grosir untuk produk ini.</p>
        @else
            <div class="table-wrap" style="margin-top:12px">
                <table>
                    <caption>{{ $product->priceTiers->count() }} ambang grosir</caption>
                    <thead><tr><th scope="col">Mulai jumlah</th><th scope="col" class="num">Harga satuan</th><th scope="col"><span class="sr-only">Aksi</span></th></tr></thead>
                    <tbody>
                    @foreach ($product->priceTiers as $tier)
                        <tr>
                            <td data-label="Mulai jumlah">{{ $tier->min_qty }} bungkus</td>
                            <td class="num" data-label="Harga satuan">Rp{{ number_format($tier->harga_satuan, 0, ',', '.') }}</td>
                            <td data-label="Aksi">
                                <form method="post" action="{{ route('admin.produk.grosir.destroy', [$product, $tier]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="remove" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <form method="post" action="{{ route('admin.produk.grosir.store', $product) }}" class="stack" style="margin-top:16px">
            @csrf
            <div class="field">
                <label for="min_qty">Mulai jumlah (bungkus)</label>
                <input id="min_qty" name="min_qty" type="number" min="2" max="100000" value="{{ old('min_qty') }}" required>
                @error('min_qty')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="harga_satuan">Harga per bungkus (Rp)</label>
                <input id="harga_satuan" name="harga_satuan" type="number" min="1" max="100000000" value="{{ old('harga_satuan') }}" required>
                @error('harga_satuan')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <button class="button" type="submit">Tambah harga grosir</button>
        </form>
    </section>
@endif
@endsection
