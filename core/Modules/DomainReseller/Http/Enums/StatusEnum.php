<?php

namespace Modules\DomainReseller\Http\Enums;

use ReflectionClass;

enum StatusEnum
{
    const ACTIVE = 1;
    const INACTIVE = 0;

    public static function getText($status, $isPayment = false): string
    {
        $attributes = self::allAttributes();
        $flip_array = array_flip($attributes);

        if ($isPayment)
        {
            return str_replace('active', 'complete', strtolower($flip_array[$status] ?? ''));
        }

        return strtolower($flip_array[$status] ?? '');
    }

    private static function allAttributes(): array
    {
        return (new ReflectionClass(self::class))->getConstants();
    }
}
