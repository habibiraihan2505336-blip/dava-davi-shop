<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Produk dan kategori sudah ada di basis data Supabase (data uji "Contoh"),
     * jadi seeder tidak mengisinya lagi. Yang dibuat di sini hanya akun admin.
     */
    public function run(): void
    {
        $this->call([AdminSeeder::class]);
    }
}
