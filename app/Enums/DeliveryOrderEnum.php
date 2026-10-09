<?php

namespace App\Enums;

enum DeliveryOrderEnum: int
{
    case PENDING = 1;
    case DISPATCHED = 2;
    case DELIVERED = 3;
    case CANCELLED = 4;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::DISPATCHED => 'Dispatched',
            self::DELIVERED => 'Delivered',
            self::CANCELLED => 'Cancelled',
        };
    }

    public static function fromName(string $name): ?int
    {
        return match (strtolower($name)) {
            'pending' => self::PENDING->value,
            'dispatched' => self::DISPATCHED->value,
            'delivered' => self::DELIVERED->value,
            'cancelled' => self::CANCELLED->value,
            default => null,
        };
    }
}
