<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Casts\BinaryUuid;
use App\Models\Concerns\HasBinaryUuid;

class Payment extends Model
{
    use HasFactory, SoftDeletes, HasBinaryUuid;

    protected $fillable = [
        'payment_number', 'payable_type', 'payable_id', 'payment_date',
        'amount', 'payment_method', 'reference', 'transaction_id',
        'notes', 'created_by', 'status'
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'uuid' => BinaryUuid::class,
        ];
    }

    public function payable()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::saved(function ($payment) {
            $invoice = $payment->payable;
            if ($invoice) {
                $invoice->paid_amount = $invoice->payments()->sum('amount');
                $invoice->updatePaymentStatus();
                $invoice->save();

                if ($invoice instanceof SalesInvoice) {
                    $invoice->customer->updateBalance();
                } elseif ($invoice instanceof PurchaseInvoice) {
                    $invoice->supplier->updateBalance();
                }
            }
        });

        static::deleted(function ($payment) {
            $invoice = $payment->payable;
            if ($invoice) {
                $invoice->paid_amount = $invoice->payments()->sum('amount');
                $invoice->updatePaymentStatus();
                $invoice->save();
            }
        });
    }
}
