<?php

/**
 * File: StoreDepartmentRequest.php
 * Module: Master Data
 * Layer: Form Request
 *
 * Purpose:
 * Memvalidasi input saat pembuatan Department baru.
 */

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreDepartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:departments,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:ACTIVE,INACTIVE',
        ];
    }
}
