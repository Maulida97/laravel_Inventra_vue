<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $location = $this->route('location');
        $locationId = $location ? $location->id : null;
        $warehouseId = $this->warehouse_id ?? ($location ? $location->warehouse_id : null);

        return [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'parent_id' => ['nullable', 'exists:locations,id'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('locations')->where('warehouse_id', $warehouseId)->ignore($locationId)
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
        ];
    }
}
