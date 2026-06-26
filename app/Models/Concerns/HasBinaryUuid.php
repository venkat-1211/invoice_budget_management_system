<?php

namespace App\Models\Concerns;

use Ramsey\Uuid\Uuid;

trait HasBinaryUuid
{
    protected static function bootHasBinaryUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Uuid::uuid7()->toString();
            }
        });
    }

    public function scopeByUuid($query, string $uuid)
    {
        if (! Uuid::isValid($uuid)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(
            'uuid',
            Uuid::fromString($uuid)->getBytes()
        );
    }
}
