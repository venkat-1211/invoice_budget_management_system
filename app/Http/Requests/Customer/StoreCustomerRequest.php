<?php

namespace App\Http\Requests\Customer;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customer = $this->route('customer');

        $customerId = $customer instanceof \App\Models\Customer
            ? $customer->id
            : $customer;

        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('customers')->ignore($customerId)],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', Rule::unique('customers')->ignore($customerId)],
            'phone' => ['required', 'string', 'max:20'],
            'tax_number' => ['required', 'string', 'max:50'],
            'billing_address' => ['required', 'string'],
            'shipping_address' => ['required', 'string'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:20'],
            'credit_limit' => ['required', 'numeric', 'min:0'],
            'status' => ['boolean'],
        ];
    }
}
