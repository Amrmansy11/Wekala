<?php

namespace App\Traits;

trait HasArrayValues
{
    /**
     * Get all enum values as an array
     *
     * @return array<string>
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all enum cases as an array with name as key and value as value
     *
     * @return array<string, string>
     */
    public static function toAssociativeArray(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->name] = $case->value;
        }

        return $result;
    }

    /**
     * Check if a value is valid for this enum
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, self::toArray());
    }
}
