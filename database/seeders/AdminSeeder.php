<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Membuat satu akun admin. Sandi diambil dari ADMIN_PASSWORD bila diisi;
     * kalau kosong, sandi acak dibuat dan ditulis ke storage/app/admin-akun.txt.
     */
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'admin@davadavi.test');

        if (Admin::query()->where('email', $email)->exists()) {
            $this->command?->warn('Akun admin '.$email.' sudah ada, sandi tidak diubah.');

            return;
        }

        $sandi = (string) env('ADMIN_PASSWORD', '');
        $dibuatOtomatis = $sandi === '';

        if ($dibuatOtomatis) {
            $sandi = Str::password(14, symbols: false);
        }

        Admin::create([
            'nama' => (string) env('ADMIN_NAMA', 'Pengelola Dava Davi'),
            'email' => $email,
            'password' => $sandi,
            'role' => 'superadmin',
        ]);

        if ($dibuatOtomatis) {
            $berkas = storage_path('app/admin-akun.txt');
            file_put_contents($berkas, "email: {$email}\nsandi: {$sandi}\n");
            chmod($berkas, 0600);

            $this->command?->info('Akun admin dibuat. Sandi tersimpan di '.$berkas);
            $this->command?->info('Ganti sandi setelah login pertama, lalu hapus berkas itu.');
        } else {
            $this->command?->info('Akun admin dibuat memakai ADMIN_PASSWORD dari .env.');
        }
    }
}
