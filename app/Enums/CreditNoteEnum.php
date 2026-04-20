<?php

namespace App\Enums;

enum CreditNoteEnum: int
{
    case DRAFT = 1;
    case APPROVED = 2;
    case CANCELLED = 3;

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::APPROVED => 'Approved',
            self::CANCELLED => 'Cancelled',
        };
    }

    public static function fromName(string $name): ?int
    {
        return match (strtolower($name)) {
            'draft' => self::DRAFT->value,
            'approved' => self::APPROVED->value,
            'cancelled' => self::CANCELLED->value,
            default => null,
        };
    }
}
