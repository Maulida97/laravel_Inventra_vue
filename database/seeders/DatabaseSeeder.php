<?php
/*
 * Berkas: DatabaseSeeder.php
 * Jalur: database/seeders/DatabaseSeeder.php
 * Tujuan: Melakukan pengisian data awal (seeding) ke dalam database aplikasi.
 * Digunakan untuk: Menyediakan pengguna awal Administrator (admin@inventra.com) dan data master demo.
 * Referensi: PRD Sistem & Modul Inventra
 */

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan proses pengisian database.
     */
    public function run(): void
    {
        // ==========================================
        // DATA AWAL PENGGUNA (SEED DEFAULT USER)
        // ==========================================
        $user = User::where('email', 'admin@inventra.com')->first();
        if (!$user) {
            User::factory()->create([
                'name' => 'Administrator',
                'email' => 'admin@inventra.com',
                'password' => bcrypt('password'),
            ]);
        }

        // ==========================================
        // SEED RBAC (ROLES & PERMISSIONS)
        // ==========================================
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            CompanyProfileSeeder::class,
            MasterDataSeeder::class, // Added for Sprint 4 onwards
        ]);
    }
}
