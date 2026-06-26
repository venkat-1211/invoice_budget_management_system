<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Casts\BinaryUuid;
use App\Models\Concerns\HasBinaryUuid;

class Customer extends Model
{
    use HasFactory, SoftDeletes, HasBinaryUuid;

    protected $fillable = [
        'code', 'name', 'email', 'phone', 'tax_number',
        'billing_address', 'shipping_address', 'city', 'country',
        'postal_code', 'credit_limit', 'balance', 'status'
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'balance' => 'decimal:2',
            'uuid' => BinaryUuid::class,
        ];
    }

    public function salesInvoices()
    {
        return $this->hasMany(SalesInvoice::class);
    }

    public function updateBalance(): void
    {
        $totalInvoiced = $this->salesInvoices()
            ->where('status', 1)
            ->sum('total_amount');
        $totalPaid = $this->salesInvoices()
            ->where('status', 1)
            ->sum('paid_amount');

        $this->balance = $totalInvoiced - $totalPaid;
        $this->save();
    }
}
