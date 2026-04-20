<?php

namespace App\Enums;

enum QuotationEnum: int
{
    case PENDING = 1;
    case ACCEPTED = 2;
    case CANCELLED = 3;
    case EXPIRED = 4;

    case CONVERTED = 5;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ACCEPTED => 'Accepted',
            self::CANCELLED => 'Cancelled',
            self::EXPIRED => 'Expired',
            self::CONVERTED => 'Converted',
        };
    }

    public static function fromName(string $name): ?int
    {
        return match (strtolower($name)) {
            'pending' => self::PENDING->value,
            'accepted' => self::ACCEPTED->value,
            'cancelled' => self::CANCELLED->value,
            'expired' => self::EXPIRED->value,
            'converted' => self::CONVERTED->value,
            default => null,
        };
    }
}
