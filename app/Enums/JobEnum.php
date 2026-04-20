<?php

namespace App\Enums;

enum JobEnum: int
{
    case PENDING = 1;
    case COMPLETED = 2;
    case CANCELLED = 3;
    case TRASHED = 4;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::TRASHED => 'Trashed',
        };
    }

    public static function fromName(string $name): ?int
    {
        return match (strtolower($name)) {
            'pending' => self::PENDING->value,
            'completed' => self::COMPLETED->value,
            'cancelled' => self::CANCELLED->value,
            'trashed' => self::TRASHED->value,
            default => null,
        };
    }
}
