<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Ramsey\Uuid\Uuid;

class BinaryUuid implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?string
    {
        return $value
            ? Uuid::fromBytes($value)->toString()
            : null;
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if (empty($value)) {
            return null;
        }

        return Uuid::fromString($value)->getBytes();
    }
}
