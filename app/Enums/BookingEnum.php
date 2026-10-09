<?php

namespace App\Enums;

enum BookingEnum: int
{
    case PENDING = 1;
    case CONFIRMED = 2;
    case SHIPPED = 3;
    case CANCELLED = 4;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::SHIPPED => 'Shipped',
            self::CANCELLED => 'Cancelled',
        };
    }

    public static function fromName(string $name): ?int
    {
        return match (strtolower($name)) {
            'pending' => self::PENDING->value,
            'confirmed' => self::CONFIRMED->value,
            'shipped' => self::SHIPPED->value,
            'cancelled' => self::CANCELLED->value,
            default => null,
        };
    }
}
