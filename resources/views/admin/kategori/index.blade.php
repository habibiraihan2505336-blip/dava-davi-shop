@extends('admin.layout')
@section('title', 'Kategori')
@section('content')
<h1>Kategori</h1>
<p class="lede">Kategori dipakai sebagai filter di katalog. Kategori yang masih dipakai produk tidak bisa dihapus.</p>

<div class="grid-two">
    <section class="panel">
        <h2>Daftar kategori</h2>
        @if ($kategori->isEmpty())
            <div class="empty"><strong>Belum ada kategori</strong>Tambahkan kategori pertama lewat formulir di samping.</div>
        @else
            <div class="table-wrap">
                <table>
                    <caption>{{ $kategori->count() }} kategori</caption>
                    <thead>
                    <tr><th scope="col">Nama</th><th scope="col">Slug</th><th scope="col" class="num">Produk</th><th scope="col"><span class="sr-only">Aksi</span></th></tr>
                    </thead>
                    <tbody>
                    @foreach ($kategori as $item)
                        <tr>
                            <td data-label="Nama">
                                <form method="post" action="{{ route('admin.kategori.update', $item) }}" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center">
                                    @csrf
                                    @method('PUT')
                                    <label class="sr-only" for="nama-{{ $item->id }}">Nama kategori {{ $item->nama_kategori }}</label>
                                    <input id="nama-{{ $item->id }}" name="nama_kategori" value="{{ $item->nama_kategori }}" maxlength="255" required style="min-height:40px;max-width:220px">
                                    <button class="button secondary" type="submit">Simpan</button>
                                </form>
                            </td>
                            <td data-label="Slug">{{ $item->slug }}</td>
                            <td class="num" data-label="Produk">{{ $item->products_count }}</td>
                            <td data-label="Aksi">
                                <form method="post" action="{{ route('admin.kategori.destroy', $item) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="remove" type="submit" @disabled($item->products_count > 0)>Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="panel">
        <h2>Tambah kategori</h2>
        <form method="post" action="{{ route('admin.kategori.store') }}" class="stack">
            @csrf
            <div class="field">
                <label for="nama_kategori">Nama kategori</label>
                <input id="nama_kategori" name="nama_kategori" value="{{ old('nama_kategori') }}" maxlength="255" required>
                <span class="hint">Slug dibuat otomatis dari nama.</span>
                @error('nama_kategori')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <button class="button" type="submit">Tambah kategori</button>
        </form>
    </section>
</div>
@endsection
