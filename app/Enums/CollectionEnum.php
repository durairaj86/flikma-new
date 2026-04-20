<?php

namespace App\Enums;

enum CollectionEnum: int
{
    case DRAFT = 1;
    case APPROVED = 2;
    case CANCELLED = 3;

    /**
     * Get the label for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::APPROVED => 'Approved',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * Get the enum value from its name.
     *
     * @param string $name
     * @return int
     */
    public static function fromName(string $name): int
    {
        return match(strtolower($name)) {
            'draft' => self::DRAFT->value,
            'approved' => self::APPROVED->value,
            'cancelled' => self::CANCELLED->value,
            default => self::DRAFT->value,
        };
    }
}
