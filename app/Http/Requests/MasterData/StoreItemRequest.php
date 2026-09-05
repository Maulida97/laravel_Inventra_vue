<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->hasPermission('master.create'); // Assuming master.create is used for all master data based on existing UI
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'code' => ['required', 'string', 'max:50', 'unique:items'],
            'sku' => ['nullable', 'string', 'max:100', 'unique:items'],
            'barcode' => ['nullable', 'string', 'max:100', 'unique:items'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:100'],
            'item_type' => ['required', 'in:quantity,asset'],
            'base_unit_id' => ['required', 'exists:units,id'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
            'department_ids' => ['nullable', 'array'],
            'department_ids.*' => ['exists:departments,id'],
        ];
    }
}
