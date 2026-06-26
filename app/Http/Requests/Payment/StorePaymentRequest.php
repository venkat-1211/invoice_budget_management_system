<?php

namespace App\Http\Requests\Payment;

use App\Http\Requests\BaseFormRequest;

class StorePaymentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payable_type' => ['required', 'in:App\Models\SalesInvoice,App\Models\PurchaseInvoice'],
            'payable_id' => ['required', 'integer'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:cash,bank_transfer,credit_card,check,online'],
            'reference' => ['nullable', 'string', 'max:100'],
            'transaction_id' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $model = $this->payable_type::find($this->payable_id);
            if (!$model) {
                $validator->errors()->add('payable_id', 'Invoice not found');
                return;
            }

            $remaining = $model->total_amount - $model->paid_amount;
            if ($this->amount > $remaining) {
                $validator->errors()->add('amount', "Payment amount cannot exceed remaining balance: {$remaining}");
            }
        });
    }
}
