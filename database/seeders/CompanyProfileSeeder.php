<?php

/**
 * File: CompanyProfileSeeder.php
 * Module: Master Data / Settings
 * Layer: Database Seeder
 *
 * Purpose:
 * Menginisialisasi data awal profil perusahaan untuk keperluan sistem dan testing.
 *
 * Related Documentation:
 * - docs/sprints/SPRINT-03-MASTER-DATA.md
 */

namespace Database\Seeders;

use App\Models\CompanyProfile;
use Illuminate\Database\Seeder;

class CompanyProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CompanyProfile::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'PT Inventra Solusi Logistik',
                'code' => 'INV-HQ',
                'email' => 'info@inventra.co.id',
                'phone' => '+62 21 555 1234',
                'website' => 'https://inventra.co.id',
                'address' => 'Jl. Jendral Sudirman No. 88, Jakarta Selatan 12190, Indonesia',
                'tax_id' => '01.234.567.8-012.000',
                'currency' => 'IDR',
                'timezone' => 'Asia/Jakarta',
                'fiscal_year_start' => 1,
            ]
        );
    }
}
