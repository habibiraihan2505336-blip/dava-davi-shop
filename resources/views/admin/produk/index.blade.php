@extends('admin.layout')
@section('title', 'Produk')
@section('content')
<h1>Produk</h1>
<p class="lede">Harga dan stok di sini langsung dipakai halaman toko. Produk nonaktif tidak tampil di katalog.</p>

<p><a class="button" href="{{ route('admin.produk.create') }}">Tambah produk</a></p>

@if ($produk->isEmpty())
    <div class="empty"><strong>Belum ada produk</strong>Tambahkan produk pertama lewat tombol di atas. Katalog akan kosong sampai ada produk aktif.</div>
@else
    <div class="table-wrap">
        <table>
            <caption>{{ $produk->total() }} produk</caption>
            <thead>
            <tr><th scope="col">Nama</th><th scope="col">Kategori</th><th scope="col" class="num">Harga</th><th scope="col" class="num">Stok</th><th scope="col" class="num">Harga grosir</th><th scope="col">Status</th><th scope="col"><span class="sr-only">Aksi</span></th></tr>
            </thead>
            <tbody>
            @foreach ($produk as $item)
                <tr>
                    <td data-label="Nama">{{ $item->nama_produk }}</td>
                    <td data-label="Kategori">{{ $item->category->nama_kategori }}</td>
                    <td class="num" data-label="Harga">Rp{{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="num" data-label="Stok">{{ $item->stok }}</td>
                    <td class="num" data-label="Harga grosir">{{ $item->price_tiers_count }} ambang</td>
                    <td data-label="Status"><span class="chip {{ $item->status_aktif ? 'selesai' : 'batal' }}">{{ $item->status_aktif ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td data-label="Aksi"><a class="table-link" href="{{ route('admin.produk.edit', $item) }}">Ubah</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @include('admin.partials.pager', ['paginator' => $produk])
@endif
@endsection
