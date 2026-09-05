<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $warehouse = $this->route('warehouse');
        $warehouseId = $warehouse ? $warehouse->id : null;

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('warehouses', 'code')->ignore($warehouseId)],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
            'user_ids' => ['nullable', 'array'],
            'user_ids.*' => ['exists:users,id'],
        ];
    }
}
