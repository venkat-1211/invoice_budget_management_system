<?php

namespace App\Http\Requests\Budget;

use App\Http\Requests\BaseFormRequest;

class StoreBudgetRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', 'in:monthly,yearly,project'],
            'budget_year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'budget_month' => ['required_if:type,monthly', 'nullable', 'integer', 'min:1', 'max:12'],
            'category' => ['nullable', 'string', 'max:100'],
            'allocated_amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'budget_month.required_if' => 'Month is required for monthly budgets',
            'allocated_amount.min' => 'Allocated amount must be greater than 0',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
        ];
    }
}
