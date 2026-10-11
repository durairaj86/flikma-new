<?php

namespace App\Enums;

enum DriverEnum: int
{
    case ACTIVE = 1;
    case INACTIVE = 2;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
        };
    }

    public static function fromName(string $name): ?int
    {
        return match (strtolower($name)) {
            'active' => self::ACTIVE->value,
            'inactive' => self::INACTIVE->value,
            default => null,
        };
    }
}
