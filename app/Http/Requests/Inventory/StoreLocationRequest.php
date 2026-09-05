<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'parent_id' => ['nullable', 'exists:locations,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('locations')->where('warehouse_id', $this->warehouse_id)
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ];
    }
}
