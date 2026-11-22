@extends('admin.layout')
@section('title', 'Pesanan '.$order->kode_pesanan)
@section('content')
<p class="hint"><a class="table-link" href="{{ route('admin.pesanan.index') }}">Kembali ke daftar pesanan</a></p>
<h1>Pesanan {{ $order->kode_pesanan }}</h1>
<p class="lede">Dibuat {{ $order->created_at?->format('d-m-Y H:i') }} waktu server. Tanggal pesanan {{ $order->tanggal->format('d-m-Y') }}.</p>

<div class="grid-two">
    <div class="stack">
        <section class="panel">
            <h2>Rincian item</h2>
            <div class="table-wrap">
                <table>
                    <caption>{{ $order->items->count() }} baris item</caption>
                    <thead>
                    <tr><th scope="col">Produk</th><th scope="col" class="num">Jumlah</th><th scope="col" class="num">Harga satuan</th><th scope="col" class="num">Subtotal</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <td data-label="Produk">{{ $item->product->nama_produk }}</td>
                            <td class="num" data-label="Jumlah">{{ $item->jumlah }}</td>
                            <td class="num" data-label="Harga satuan">Rp{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                            <td class="num" data-label="Subtotal">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <th scope="row" colspan="3">Subtotal item</th>
                        <td class="num" data-label="Subtotal item"><strong>Rp{{ number_format($order->subtotalItem(), 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <th scope="row" colspan="3">Ongkos kirim</th>
                        <td class="num" data-label="Ongkos kirim">Rp{{ number_format($order->ongkir, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th scope="row" colspan="3">Total pesanan</th>
                        <td class="num" data-label="Total"><strong>Rp{{ number_format($order->total, 0, ',', '.') }}</strong></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel">
            <h2>Data pemesan</h2>
            <dl class="detail-list">
                <div><dt>Nama</dt><dd>{{ $order->customer->nama }}</dd></div>
                <div><dt>Nomor HP</dt><dd>{{ $order->customer->no_hp }}</dd></div>
                <div><dt>Alamat</dt><dd>{{ $order->customer->alamat ?: 'Belum diisi' }}</dd></div>
                <div><dt>Catatan pembeli</dt><dd>{{ $order->catatan ?: 'Tidak ada' }}</dd></div>
            </dl>
        </section>
    </div>

    <section class="panel">
        <h2>Ubah status</h2>
        <form method="post" action="{{ route('admin.pesanan.update', $order) }}" class="stack">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="status">Status pesanan</label>
                <select id="status" name="status" required>
                    @foreach (\App\Models\Order::STATUS as $kunci => $label)
                        <option value="{{ $kunci }}" @selected(old('status', $order->status) === $kunci)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="payment_status">Status pembayaran</label>
                <select id="payment_status" name="payment_status" required>
                    @foreach (\App\Models\Order::PEMBAYARAN as $kunci => $label)
                        <option value="{{ $kunci }}" @selected(old('payment_status', $order->payment_status) === $kunci)>{{ $label }}</option>
                    @endforeach
                </select>
                <span class="hint">Waktu pembayaran dicatat otomatis saat status diubah ke sudah dibayar.</span>
            </div>
            <div class="field">
                <label for="ongkir">Ongkos kirim (Rp)</label>
                <input id="ongkir" name="ongkir" type="number" min="0" max="10000000" value="{{ old('ongkir', $order->ongkir) }}" required>
                <span class="hint">Tulis 0 bila gratis. Total dihitung otomatis dari subtotal item ditambah ongkir.</span>
                @error('ongkir')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
                <label for="catatan">Catatan internal</label>
                <textarea id="catatan" name="catatan" maxlength="500">{{ old('catatan', $order->catatan) }}</textarea>
            </div>
            <button class="button" type="submit">Simpan perubahan</button>
        </form>

        <h2 style="margin-top:22px">Ringkasan status</h2>
        <dl class="detail-list">
            <div><dt>Status pesanan</dt><dd><span class="chip {{ $order->status }}">{{ $order->labelStatus() }}</span></dd></div>
            <div><dt>Status pembayaran</dt><dd><span class="chip {{ $order->payment_status === 'dibayar' ? 'bayar' : 'belum' }}">{{ $order->labelPembayaran() }}</span></dd></div>
            <div><dt>Dibayar pada</dt><dd>{{ $order->paid_at?->format('d-m-Y H:i') ?? 'Belum ada' }}</dd></div>
            <div><dt>Metode pembayaran</dt><dd>{{ $order->payment_method ?: 'Belum dipilih' }}</dd></div>
        </dl>
    </section>
</div>
@endsection
