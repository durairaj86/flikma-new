<?php

namespace App\Enums;

enum CustomerStatusEnum: int
{
    case PENDING = 1;
    case VERIFIED = 2;
    case CONFIRMED = 3;
    case BLOCKED = 4;
    case REJECTED = 5;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'pending',
            self::VERIFIED => 'verified',
            self::CONFIRMED => 'confirmed',
            self::BLOCKED => 'blocked',
            self::REJECTED => 'rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',   // Bootstrap badge-warning
            self::VERIFIED => 'info',      // Bootstrap badge-info
            self::CONFIRMED => 'success',   // Bootstrap badge-success
            self::BLOCKED => 'danger',    // Bootstrap badge-danger
            self::REJECTED => 'secondary', // Bootstrap badge-secondary
        };
    }

    /**
     * Return all statuses as [value => label] array
     * Useful for dropdowns or filters
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }

    public static function fromName(string $name): ?int
    {
        return match (strtolower($name)) {
            'pending' => self::PENDING->value,
            'verified' => self::VERIFIED->value,
            'confirmed' => self::CONFIRMED->value,
            'blocked' => self::BLOCKED->value,
            'rejected' => self::REJECTED->value,
            default => null,
        };
    }
}
