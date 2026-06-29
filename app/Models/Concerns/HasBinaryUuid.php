<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/**
 * @mixin Model
 */
trait HasBinaryUuid
{
    protected static function bootHasBinaryUuid(): void
    {
        static::creating(function (Model $model): void {
            if ($model->getAttribute('uuid') === null) {
                $model->setAttribute(
                    'uuid',
                    Uuid::uuid7()->toString()
                );
            }
        });
    }

    public function scopeByUuid(
        Builder $query,
        string $uuid
    ): Builder {
        if (!Uuid::isValid($uuid)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(
            'uuid',
            Uuid::fromString($uuid)->getBytes()
        );
    }
}
