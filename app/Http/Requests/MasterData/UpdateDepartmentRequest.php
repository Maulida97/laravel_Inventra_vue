<?php

/**
 * File: UpdateDepartmentRequest.php
 * Module: Master Data
 * Layer: Form Request
 *
 * Purpose:
 * Memvalidasi input saat memperbarui Department yang sudah ada.
 */

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:departments,code,' . $this->route('department')->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ];
    }
}
