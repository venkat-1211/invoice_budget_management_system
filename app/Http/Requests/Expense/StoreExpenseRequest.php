<?php

namespace App\Http\Requests\Expense;

use App\Http\Requests\BaseFormRequest;

class StoreExpenseRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'budget_id' => ['nullable', 'exists:budgets,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'expense_date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:100'],
            'payment_method' => ['required', 'in:cash,bank_transfer,credit_card,check'],
            'reference' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'description' => ['nullable', 'string'],
            'attachments' => ['nullable', 'array'],
            'status' => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->filled('budget_id')) {
                $budget = \App\Models\Budget::find($this->budget_id);
                if ($budget) {
                    $totalAmount = ($this->amount ?? 0) + ($this->tax_amount ?? 0);
                    if (($budget->remaining_amount - $totalAmount) < 0 && $this->status != 0) {
                        $validator->errors()->add('budget_id', 'This expense exceeds the remaining budget amount');
                    }
                }
            }
        });
    }
}
