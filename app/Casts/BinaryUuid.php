<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

final readonly class BinaryUuid implements CastsAttributes
{
    public function get(
        mixed $model,
        string $key,
        mixed $value,
        array $attributes
    ): ?string {
        if ($value === null) {
            return null;
        }

        if (!is_string($value) || strlen($value) !== 16) {
            return null;
        }

        return Uuid::fromBytes($value)->toString();
    }

    public function set(
        mixed $model,
        string $key,
        mixed $value,
        array $attributes
    ): ?string {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_string($value) || !Uuid::isValid($value)) {
            throw new InvalidArgumentException('Invalid UUID.');
        }

        return Uuid::fromString($value)->getBytes();
    }
}
