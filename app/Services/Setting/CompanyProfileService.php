<?php

namespace App\Services\Setting;

use App\Models\CompanyProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Service: CompanyProfileService
 * Domain: Setting
 * 
 * Responsibility:
 * Menangani business operation terkait pengaturan dan profil perusahaan.
 */
class CompanyProfileService
{
    /**
     * Memperbarui profil perusahaan termasuk manipulasi file logo.
     *
     * @param CompanyProfile $companyProfile
     * @param array $data
     * @param UploadedFile|null $logo
     * @return CompanyProfile
     */
    public function updateProfile(CompanyProfile $companyProfile, array $data, ?UploadedFile $logo): CompanyProfile
    {
        if ($logo) {
            // Hapus logo lama jika ada
            if ($companyProfile->logo_path && Storage::disk('public')->exists($companyProfile->logo_path)) {
                Storage::disk('public')->delete($companyProfile->logo_path);
            }

            $path = $logo->store('company', 'public');
            $data['logo_path'] = $path;
        }

        // Pastikan instance UploadedFile tidak masuk ke proses update
        unset($data['logo']);

        $companyProfile->update($data);

        return $companyProfile;
    }
}
