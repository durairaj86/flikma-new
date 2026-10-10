<?php

namespace App\Enums;

enum ArrivalNoticeEnum: int
{
    case DRAFT = 1;
    case NOTIFIED = 2;
    case COLLECTED = 3;
    case CANCELLED = 4;

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::NOTIFIED => 'Notified',
            self::COLLECTED => 'D.O Collected',
            self::CANCELLED => 'Cancelled',
        };
    }

    public static function fromName(string $name): ?int
    {
        return match (strtolower($name)) {
            'draft' => self::DRAFT->value,
            'notified' => self::NOTIFIED->value,
            'collected' => self::COLLECTED->value,
            'cancelled' => self::CANCELLED->value,
            default => null,
        };
    }
}
