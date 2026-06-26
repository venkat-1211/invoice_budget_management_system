<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Casts\BinaryUuid;
use App\Models\Concerns\HasBinaryUuid;

class Supplier extends Model
{
    use HasFactory, SoftDeletes, HasBinaryUuid;

    protected $fillable = [
        'code', 'name', 'email', 'phone', 'tax_number',
        'address', 'city', 'country', 'postal_code',
        'payment_terms', 'balance', 'status'
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'uuid' => BinaryUuid::class,
        ];
    }

    public function purchaseInvoices()
    {
        return $this->hasMany(PurchaseInvoice::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function updateBalance(): void
    {
        $totalInvoiced = $this->purchaseInvoices()
            ->where('status', 1)
            ->sum('total_amount');
        $totalPaid = $this->purchaseInvoices()
            ->where('status', 1)
            ->sum('paid_amount');

        $this->balance = $totalInvoiced - $totalPaid;
        $this->save();
    }
}
