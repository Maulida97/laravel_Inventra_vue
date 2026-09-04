<?php

/**
 * File: StoreSupplierRequest.php
 * Module: Master Data
 * Layer: Form Request
 *
 * Purpose:
 * Memvalidasi input saat pembuatan Supplier baru.
 */

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:suppliers,code',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ];
    }
}
