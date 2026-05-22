<?php

namespace App\Enums;

enum SupplierStatusEnum: int
{
    case CONFIRMED = 1;
    case BLOCKED = 2;

    public function label(): string
    {
        return match ($this) {
            self::CONFIRMED => 'confirmed',
            self::BLOCKED => 'blocked',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CONFIRMED => 'success',   // Bootstrap badge-success
            self::BLOCKED => 'danger',    // Bootstrap badge-danger
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
            'confirmed' => self::CONFIRMED->value,
            'blocked' => self::BLOCKED->value,
            default => null,
        };
    }
}
