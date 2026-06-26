<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Casts\BinaryUuid;
use App\Models\Concerns\HasBinaryUuid;

class ExchangeRate extends Model
{
    use HasFactory, HasBinaryUuid;

    protected $fillable = [
        'base_currency', 'target_currency', 'rate', 'rate_date', 'source', 'status'
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:6',
            'rate_date' => 'date',
            'uuid' => BinaryUuid::class,
        ];
    }

    public function scopeForCurrency($query, string $base, string $target)
    {
        return $query->where('base_currency', $base)
                     ->where('target_currency', $target);
    }

    public function scopeLatestRate($query, string $base, string $target)
    {
        return $query->forCurrency($base, $target)
                     ->where('rate_date', '<=', now())
                     ->orderBy('rate_date', 'desc');
    }
}
