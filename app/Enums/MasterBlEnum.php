<?php

namespace App\Enums;

enum MasterBlEnum: int
{
    case DRAFT = 1;
    case ISSUED = 2;
    case CLOSED = 3;
    case CANCELLED = 4;

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::ISSUED => 'Issued',
            self::CLOSED => 'Closed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public static function fromName(string $name): ?int
    {
        return match (strtolower($name)) {
            'draft' => self::DRAFT->value,
            'issued' => self::ISSUED->value,
            'closed' => self::CLOSED->value,
            'cancelled' => self::CANCELLED->value,
            default => null,
        };
    }
}
