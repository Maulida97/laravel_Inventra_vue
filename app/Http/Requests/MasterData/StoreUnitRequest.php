<?php

/**
 * File: StoreUnitRequest.php
 * Module: Master Data
 * Layer: Form Request
 *
 * Purpose:
 * Memvalidasi input saat pembuatan Unit baru.
 */

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:units,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ];
    }
}
