<?php

namespace App\Enums;

enum TripEnum: int
{
    case PLANNED = 1;
    case STARTED = 2;
    case COMPLETED = 3;
    case CANCELLED = 4;

    public function label(): string
    {
        return match ($this) {
            self::PLANNED => 'Planned',
            self::STARTED => 'Started',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public static function fromName(string $name): ?int
    {
        return match (strtolower($name)) {
            'planned' => self::PLANNED->value,
            'started' => self::STARTED->value,
            'completed' => self::COMPLETED->value,
            'cancelled' => self::CANCELLED->value,
            default => null,
        };
    }
}
