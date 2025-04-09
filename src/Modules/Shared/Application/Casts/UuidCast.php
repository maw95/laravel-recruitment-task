<?php

declare(strict_types=1);

namespace Modules\Shared\Application\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class UuidCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?UuidInterface
    {
        return $value ? Uuid::fromString($value) : null;
    }

    public function set($model, string $key, $value, array $attributes): string
    {
        return (string) $value;
    }
}
