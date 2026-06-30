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

    protected function uuidColumn(): string
    {
        return 'uuid';
    }

    public function getRouteKeyName(): string
    {
        return $this->uuidColumn();
    }


    // public function resolveRouteBinding(
    //     $value,
    //     $field = null
    // ): ?Model {
    //     if (!is_string($value) || !Uuid::isValid($value)) {
    //         return null;
    //     }

    //     return $this->newQuery()
    //         ->where(
    //             $field ?? $this->getRouteKeyName(),
    //             Uuid::fromString($value)->getBytes()
    //         )
    //         ->first();
    // }

    // public function scopeByUuid(
    //     Builder $query,
    //     string $uuid
    // ): Builder {
    //     if (!Uuid::isValid($uuid)) {
    //         return $query->whereRaw('1 = 0');
    //     }

    //     return $query->where(
    //         'uuid',
    //         Uuid::fromString($uuid)->getBytes()
    //     );
    // }
    public function scopeByUuid(
        Builder $query,
        string $uuid
    ): Builder {
        if (!Uuid::isValid($uuid)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(
            $this->uuidColumn(),
            Uuid::fromString($uuid)->getBytes()
        );
    }

    public function resolveRouteBinding(   // this function is automarically work. So no need for specific place call. Intha function automatic aa work aagum. namma yengaium call pannanum nu avasiyam illa.
        $value,
        $field = null
    ): ?Model {
        if (!is_string($value) || !Uuid::isValid($value)) {
            return null;
        }

        return $this->newQuery()
            ->where(
                $field ?? $this->uuidColumn(),
                Uuid::fromString($value)->getBytes()
            )
            ->first();
    }

}
