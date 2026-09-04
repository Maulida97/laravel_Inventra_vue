<?php

/**
 * File: UpdateCompanyProfileRequest.php
 * Module: Master Data / Settings
 * Layer: Form Request Validation
 *
 * Purpose:
 * Melakukan validasi data input pengguna saat memperbarui Company Profile & Settings.
 *
 * Related Documentation:
 * - docs/sprints/SPRINT-03-MASTER-DATA.md
 */

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermission('setting.update');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyProfileId = $this->route('company_profile')?->id ?? 1;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:company_profiles,code,' . $companyProfileId],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'tax_id' => ['nullable', 'string', 'max:50'],
            'currency' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'string', 'max:50'],
            'fiscal_year_start' => ['required', 'integer', 'min:1', 'max:12'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:2048'],
        ];
    }

    /**
     * Custom attribute names for user friendly error messages.
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nama Perusahaan',
            'code' => 'Kode Perusahaan',
            'email' => 'Alamat Email',
            'phone' => 'Nomor Telepon',
            'website' => 'Situs Web',
            'address' => 'Alamat Lengkap',
            'tax_id' => 'NPWP / Tax ID',
            'currency' => 'Mata Uang Default',
            'timezone' => 'Zona Waktu',
            'fiscal_year_start' => 'Awal Tahun Fiskal',
            'logo' => 'Logo Perusahaan',
        ];
    }
}
