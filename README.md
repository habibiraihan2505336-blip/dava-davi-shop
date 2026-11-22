# Dava Davi Shop

Toko online keripik tempe Dava Davi: Laravel 13, PHP 8.5, Blade, dan basis data **PostgreSQL Supabase** hasil rancangan RPL (`nama_produk`, `harga`, `stok`, `status_aktif`). Katalog, keranjang, checkout, akun pembeli, dan panel admin memakai tabel yang sama dengan dokumen ERD dan SKPL.

Harga, varian, stok, cerita usaha, dan informasi pengiriman masih data uji berlabel "Contoh". Ganti dan mintakan persetujuan pemilik sebelum dipakai menerima pesanan sungguhan.

## Menjalankan di Mac

```bash
export PATH="/opt/homebrew/bin:$PATH"
cd /Users/ibibubebo/dava-davi-shop
composer install
php artisan config:clear
php artisan serve --port=8123
```

Buka `http://127.0.0.1:8123`. Port 8000 di mesin ini dipakai aplikasi lain, jadi contoh memakai 8123.

**Jangan** menjalankan `php artisan migrate`. Tujuh tabel sudah dibuat oleh berkas SQL di folder rancangan (`KrepekDavaDavi-Supabase/sql/`), sehingga migrasi hanya akan bentrok. Koneksi dibaca dari `.env` (`DB_CONNECTION=pgsql`, host pooler `aws-1-ap-southeast-1.pooler.supabase.com`).

## Akun admin

Panel admin ada di `/admin`. Akun dibuat sekali oleh seeder:

```bash
php artisan db:seed            # membuat akun admin bila belum ada
```

Sandi acak hasil seeder disimpan di `storage/app/admin-akun.txt` (izin 600). Ganti sandi lewat Supabase (SQL Editor, kolom `admins.password` berisi hash bcrypt) atau buat akun baru dengan `ADMIN_EMAIL` dan `ADMIN_PASSWORD` di `.env`, lalu jalankan seeder lagi setelah menghapus baris lama.

Login admin dibatasi 5 percobaan gagal per menit untuk setiap kombinasi email dan alamat IP.

## Fitur yang sudah jalan

- Katalog dari basis data: filter kategori, pencarian, dialog detail, harga grosir bertingkat (`product_price_tiers`).
- Keranjang berbasis session, validasi jumlah terhadap stok, harga grosir otomatis saat jumlah mencapai ambang.
- Checkout tamu atau dengan akun. Data pemesan masuk ke `customers`, pesanan ke `orders` dengan kode `DD-YYMMDD-XXXX` dari database, item ke `order_items` (subtotal dihitung database), stok dikurangi dalam satu transaksi.
- Akun pembeli: daftar, masuk, keluar, riwayat pesanan, dan tombol pesan ulang yang mengisi keranjang dari pesanan lama.
- Halaman pesanan dibuka lewat kode pesanan, bukan nomor urut.
- Ongkos kirim diisi admin per pesanan; total selalu dihitung ulang dari subtotal item ditambah ongkir.
- Panel admin: ringkasan angka, daftar dan pencarian pesanan, ubah status pesanan dan pembayaran (waktu bayar tercatat otomatis), daftar produk, tambah dan ubah produk, kelola kategori, kelola harga grosir.
- Antarmuka Indonesia, fokus keyboard, tabel menumpuk di layar sempit.

## Belum ada

- Payment gateway (Midtrans), integrasi kurir, notifikasi. Ongkir masih diisi manual, bukan tarif otomatis.
- Ganti sandi dan lupa sandi dari dalam aplikasi.
- Kebijakan privasi, syarat layanan, foto produk asli.
- Pengembalian stok otomatis saat pesanan dibatalkan (masih dicatat manual oleh penjual).

## Uji

```bash
php artisan test
```

35 tes, 140 asersi. Tes memakai koneksi `.env` (Supabase) dengan `DatabaseTransactions`, jadi data uji dibatalkan otomatis setelah setiap tes. Jangan arahkan tes ke basis data produksi.

## Model data

- `categories`, `products` (kategori, harga, stok, gambar, status aktif), `product_price_tiers` (harga grosir per ambang jumlah).
- `customers` (nama, no HP, alamat, email, sandi hash opsional), `orders` (kode pesanan, tanggal, total, ongkir, status, status pembayaran), `order_items` (jumlah, harga satuan, subtotal hasil hitung database).
- `admins` (nama, email, sandi hash, peran). Perubahan dari rancangan awal: kolom `password` ditambahkan dan kaitan `id` ke `auth.users` dilepas, lihat `sql/05_admin_login.sql`.

Lihat `PRD.md` untuk sasaran dan kriteria penerimaan, `DESIGN.md` untuk arahan visual.
