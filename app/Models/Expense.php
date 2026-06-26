<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Casts\BinaryUuid;
use App\Models\Concerns\HasBinaryUuid;

class Expense extends Model
{
    use HasFactory, SoftDeletes, HasBinaryUuid;

    protected $fillable = [
        'expense_number', 'budget_id', 'supplier_id', 'expense_date',
        'category', 'payment_method', 'reference', 'amount', 'tax_amount',
        'total_amount', 'currency', 'description', 'attachments', 'created_by', 'status'
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'attachments' => 'array',
            'uuid' => BinaryUuid::class,
        ];
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::saved(function ($expense) {
            if ($expense->budget_id) {
                $expense->budget->recalculateSpent();
            }
        });

        static::deleted(function ($expense) {
            if ($expense->budget_id) {
                $expense->budget->recalculateSpent();
            }
        });
    }
}
