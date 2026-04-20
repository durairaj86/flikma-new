<?php

namespace App\Enums;

enum ExpenseEnum: int
{
    case PENDING = 1;
    case APPROVED = 2;
    case CANCELLED = 3;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::CANCELLED => 'Cancelled',
        };
    }

    public static function fromName(string $name): ?int
    {
        return match (strtolower($name)) {
            'pending' => self::PENDING->value,
            'approved' => self::APPROVED->value,
            'cancelled' => self::CANCELLED->value,
            default => null,
        };
    }
}
