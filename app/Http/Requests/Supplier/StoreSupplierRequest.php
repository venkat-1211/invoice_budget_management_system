<?php

namespace App\Http\Requests\Supplier;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreSupplierRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplier = $this->route('supplier');
        $supplierId = $supplier instanceof \App\Models\Supplier
            ? $supplier->id
            : $supplier;

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('suppliers')->ignore($supplierId)],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('suppliers')->ignore($supplierId)],
            'phone' => ['required', 'string', 'max:20'],
            'tax_number' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'payment_terms' => ['required', 'string', 'max:50'],
            'status' => ['boolean'],
        ];
    }
}
