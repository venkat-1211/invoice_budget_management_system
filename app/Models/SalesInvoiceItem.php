<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\BinaryUuid;
use App\Models\Concerns\HasBinaryUuid;

class SalesInvoiceItem extends Model
{
    use HasFactory, HasBinaryUuid;

    protected $fillable = [
        'sales_invoice_id', 'product_id', 'description',
        'quantity', 'unit', 'unit_price', 'tax_rate', 'tax_amount',
        'discount_percent', 'discount_amount', 'total', 'status'
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'uuid' => BinaryUuid::class,
        ];
    }

    public function salesInvoice()
    {
        return $this->belongsTo(SalesInvoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted(): void
    {
        static::saving(function ($item) {
            $item->tax_amount = ($item->unit_price * $item->quantity) * ($item->tax_rate / 100);
            $item->discount_amount = ($item->unit_price * $item->quantity) * ($item->discount_percent / 100);
            $item->total = ($item->unit_price * $item->quantity) + $item->tax_amount - $item->discount_amount;
        });
    }
}
