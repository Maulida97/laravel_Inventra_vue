<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'unique:warehouses,code'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ];
    }
}
