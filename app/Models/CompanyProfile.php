<?php

/**
 * File: CompanyProfile.php
 * Module: Master Data / Settings
 * Layer: Model
 *
 * Purpose:
 * Representasi entitas profil dan pengaturan umum perusahaan.
 *
 * Responsibilities:
 * - Menyimpan identitas organisasi (nama, logo, kontak, alamat, NPWP/Tax ID).
 * - Menyimpan preferensi lokalisasi & finansial dasar (currency, timezone, fiscal year).
 * - Menyediakan helper static getSettings() untuk akses terpusat di seluruh modul.
 *
 * Related Documentation:
 * - docs/sprints/SPRINT-03-MASTER-DATA.md
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $table = 'company_profiles';

    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'website',
        'address',
        'tax_id',
        'currency',
        'timezone',
        'fiscal_year_start',
        'logo_path',
    ];

    protected $casts = [
        'fiscal_year_start' => 'integer',
    ];

    /**
     * Helper untuk mengambil record singleton / default company profile.
     */
    public static function getSettings(): static
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'PT Inventra Solusi Logistik',
                'code' => 'INV-HQ',
                'email' => 'info@inventra.co.id',
                'phone' => '+62 21 555 1234',
                'website' => 'https://inventra.co.id',
                'address' => 'Jl. Jendral Sudirman No. 88, Jakarta Selatan 12190',
                'tax_id' => '01.234.567.8-012.000',
                'currency' => 'IDR',
                'timezone' => 'Asia/Jakarta',
                'fiscal_year_start' => 1,
            ]
        );
    }
}
