<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Casts\BinaryUuid;
use App\Models\Concerns\HasBinaryUuid;

class Budget extends Model
{
    use HasFactory, SoftDeletes, HasBinaryUuid;

    protected $fillable = [
        'name', 'type', 'budget_year', 'budget_month', 'category',
        'allocated_amount', 'spent_amount', 'remaining_amount',
        'description', 'start_date', 'end_date', 'created_by', 'status'
    ];

    protected function casts(): array
    {
        return [
            'budget_year' => 'integer',
            'budget_month' => 'integer',
            'allocated_amount' => 'decimal:2',
            'spent_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'uuid' => BinaryUuid::class,
        ];
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recalculateSpent(): void
    {
        $this->spent_amount = $this->expenses()
            ->where('status', 1)
            ->sum('total_amount');
        $this->remaining_amount = $this->allocated_amount - $this->spent_amount;
        $this->save();
    }

    public function getUsagePercentAttribute(): float
    {
        if ($this->allocated_amount <= 0) return 0;
        return min(100, ($this->spent_amount / $this->allocated_amount) * 100);
    }

    public function isOverBudget(): bool
    {
        return $this->spent_amount > $this->allocated_amount;
    }
}
