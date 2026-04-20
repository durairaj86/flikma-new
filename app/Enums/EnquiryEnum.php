<?php

namespace App\Enums;

enum EnquiryEnum: int
{
    case PENDING = 1;
    case CONFIRMED = 2;
    case QUOTATION = 3;
    case CANCELLED = 4;

    case COMPLETED = 5;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::CONFIRMED => 'Confirmed',
            self::QUOTATION => 'Quotation',
            self::CANCELLED => 'Cancelled',
            self::COMPLETED => 'Completed',
        };
    }

    public static function fromName(string $name): ?int
    {
        return match (strtolower($name)) {
            'pending' => self::PENDING->value,
            'confirmed' => self::CONFIRMED->value,
            'quotation' => self::QUOTATION->value,
            'cancelled' => self::CANCELLED->value,
            'completed' => self::COMPLETED->value,
            default => null,
        };
    }
}
