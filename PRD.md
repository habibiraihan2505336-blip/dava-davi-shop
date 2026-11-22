# PRD: Toko Online Keripik Tempe Dava Davi

## Ringkasan

Website katalog dan pemesanan keripik tempe untuk usaha Dava Davi. Pembeli dapat memilih produk, menaruhnya di keranjang, mengisi detail penerima, lalu menyimpan pesanan. Versi awal ini adalah demo belajar lokal dengan Laravel. Ini belum siap menerima pembayaran atau pesanan pelanggan sungguhan.

## Masalah dan tujuan

Pembeli membutuhkan satu halaman untuk melihat pilihan produk dan mengirim pesanan. Pemilik membutuhkan catatan pesanan yang memuat item dan jumlah. Tujuan versi awal adalah menunjukkan alur dasar toko dari katalog sampai pencatatan pesanan.

## Pengguna

- Pembeli: mencari produk, memilih jumlah, mengecek subtotal, memasukkan detail penerima.
- Pemilik/admin: mengelola produk, stok, dan status pesanan lewat panel `/admin` (login memakai tabel `admins`).

## Cakupan versi demo

- Halaman katalog dengan konten produk berlabel data demo.
- Keranjang sesi browser dengan tambah/hapus produk dan subtotal.
- Validasi jumlah terhadap stok.
- Checkout tamu dengan validasi nama, email, nomor telepon, alamat.
- Penyimpanan order beserta item dalam transaksi database.
- Stok dikurangi setelah pesanan tercatat.
- Halaman ringkasan dengan status menunggu konfirmasi.

## Tidak termasuk versi demo

Payment gateway (Midtrans), integrasi kurir, notifikasi, kebijakan privasi, syarat layanan, dan deployment. Login pembeli, panel admin, dan ongkir manual sudah termasuk dan dijelaskan pada bagian sebelumnya.

## Perjalanan pembeli

1. Membuka katalog dan membaca bahwa informasi produk masih contoh.
2. Menambahkan jumlah yang diinginkan.
3. Melihat keranjang dan subtotal produk. Ongkir belum dihitung.
4. Mengisi nama, email, telepon, dan alamat penerima.
5. Mengirim pesanan. Server memeriksa stok dan menyimpan pesanan serta item sebagai `pending` dalam transaksi.
6. Melihat ringkasan yang menyatakan pembayaran belum dikonfirmasi.

## Persyaratan nonfungsional

- Bahasa antarmuka Indonesia.
- Layout responsif pada layar ponsel dan desktop.
- Form berlabel, navigasi keyboard, fokus terlihat.
- CSRF Laravel, validasi sisi server, dan pengurangan stok dalam transaksi.
- Tidak menampilkan testimoni atau angka penjualan tanpa sumber.
- Informasi pribadi hanya untuk demo lokal; akses order perlu diamankan sebelum layanan publik.

## Kriteria penerimaan

- Katalog hanya menampilkan produk aktif.
- Pembeli dapat menambah dan menghapus produk pada keranjang.
- Jumlah di luar stok tidak diterima.
- Checkout menolak data wajib yang kosong atau email tidak sah.
- Total dihitung dari harga di database, bukan nilai dari browser.
- Pesanan, snapshot item, dan pengurangan stok disimpan konsisten.
- Jika stok berubah atau transaksi gagal, server memberi pesan dan tidak mengklaim pembayaran berhasil.
- Halaman ringkasan menyebut pesanan menunggu konfirmasi, tidak menyatakan lunas.

## Asumsi dan pertanyaan bisnis yang harus dijawab

- Varian, berat/ukuran, harga, ketersediaan, dan foto asli belum diberikan.
- Ongkir, area pengiriman, layanan kurir, waktu proses, dan kontak resmi belum dikonfirmasi.
- Metode pembayaran dan kebijakan pembatalan/retur belum disepakati.
- Pemilik perlu menyetujui penggunaan nama, cerita usaha, materi produk, serta kebijakan privasi.

## Teknologi

Laravel 13, PHP 8.5, Blade, SQLite untuk demo lokal. Django tidak dicampurkan sebagai backend kedua. Jika ada kebutuhan tugas membandingkan framework, buat implementasi Django terpisah agar model order dan checkout tidak dikelola ganda.
