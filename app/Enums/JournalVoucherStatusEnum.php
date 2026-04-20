<?php

namespace App\Enums;

enum JournalVoucherStatusEnum: int
{
    case DRAFT = 1;
    case APPROVED = 2;
    case DISAPPROVED = 3;

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
            self::DISAPPROVED => 'Disapproved',
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
            'disapproved' => self::DISAPPROVED->value,
            default => self::DRAFT->value,
        };
    }
}
