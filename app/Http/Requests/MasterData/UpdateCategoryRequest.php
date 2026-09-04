<?php

/**
 * File: UpdateCategoryRequest.php
 * Module: Master Data
 * Layer: Form Request
 *
 * Purpose:
 * Memvalidasi input saat memperbarui Category yang sudah ada.
 */

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:categories,code,' . $this->route('category')->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ];
    }
}
