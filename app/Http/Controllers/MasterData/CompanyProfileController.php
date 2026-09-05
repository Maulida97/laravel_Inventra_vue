<?php

/**
 * File: CompanyProfileController.php
 * Module: Master Data / Settings
 * Layer: Controller
 *
 * Purpose:
 * Menangani HTTP request untuk mengelola Company Profile & Settings.
 *
 * Responsibilities:
 * - Menampilkan form edit profil & pengaturan perusahaan.
 * - Memproses update data dan pengunggahan logo perusahaan.
 * - Mengembalikan view Inertia.js.
 *
 * Related Documentation:
 * - docs/sprints/SPRINT-03-MASTER-DATA.md
 */

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Http\Requests\MasterData\UpdateCompanyProfileRequest;
use App\Models\CompanyProfile;
use App\Services\Setting\CompanyProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CompanyProfileController extends Controller
{
    public function __construct(
        private CompanyProfileService $companyProfileService
    ) {}

    /**
     * Menampilkan form edit profil & pengaturan perusahaan.
     */
    public function edit(): Response
    {
        $companyProfile = CompanyProfile::getSettings();

        if ($companyProfile->logo_path) {
            $companyProfile->logo_url = Storage::url($companyProfile->logo_path);
        } else {
            $companyProfile->logo_url = null;
        }

        return Inertia::render('MasterData/CompanyProfile/Edit', [
            'companyProfile' => $companyProfile,
            'currencies' => [
                ['code' => 'IDR', 'label' => 'IDR - Indonesian Rupiah (Rp)'],
                ['code' => 'USD', 'label' => 'USD - United States Dollar ($)'],
                ['code' => 'EUR', 'label' => 'EUR - Euro (€)'],
                ['code' => 'SGD', 'label' => 'SGD - Singapore Dollar (S$)'],
                ['code' => 'MYR', 'label' => 'MYR - Malaysian Ringgit (RM)'],
            ],
            'timezones' => [
                ['value' => 'Asia/Jakarta', 'label' => 'Asia/Jakarta (WIB - UTC+7)'],
                ['value' => 'Asia/Makassar', 'label' => 'Asia/Makassar (WITA - UTC+8)'],
                ['value' => 'Asia/Jayapura', 'label' => 'Asia/Jayapura (WIT - UTC+9)'],
                ['value' => 'UTC', 'label' => 'UTC (Coordinated Universal Time)'],
            ],
            'months' => [
                ['value' => 1, 'label' => 'Januari'],
                ['value' => 2, 'label' => 'Februari'],
                ['value' => 3, 'label' => 'Maret'],
                ['value' => 4, 'label' => 'April'],
                ['value' => 5, 'label' => 'Mei'],
                ['value' => 6, 'label' => 'Juni'],
                ['value' => 7, 'label' => 'Juli'],
                ['value' => 8, 'label' => 'Agustus'],
                ['value' => 9, 'label' => 'September'],
                ['value' => 10, 'label' => 'Oktober'],
                ['value' => 11, 'label' => 'November'],
                ['value' => 12, 'label' => 'Desember'],
            ],
        ]);
    }

    /**
     * Memperbarui profil & pengaturan perusahaan.
     */
    public function update(UpdateCompanyProfileRequest $request): RedirectResponse
    {
        $companyProfile = CompanyProfile::getSettings();

        $this->companyProfileService->updateProfile(
            $companyProfile,
            $request->validated(),
            $request->file('logo')
        );

        return redirect()->route('company-profile.edit')
            ->with('success', 'Profil & Pengaturan Perusahaan berhasil diperbarui.');
    }
}
