<?php

namespace App\Enums;

enum VehicleEnum: int
{
    case ACTIVE = 1;
    case MAINTENANCE = 2;
    case INACTIVE = 3;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::MAINTENANCE => 'Maintenance',
            self::INACTIVE => 'Inactive',
        };
    }

    public static function fromName(string $name): ?int
    {
        return match (strtolower($name)) {
            'active' => self::ACTIVE->value,
            'maintenance' => self::MAINTENANCE->value,
            'inactive' => self::INACTIVE->value,
            default => null,
        };
    }
}
