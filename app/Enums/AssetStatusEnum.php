<?php

namespace App\Enums;

enum AssetStatusEnum: int
{
    case CURRENT = 1;   // Purchased, not yet started depreciation
    case RUNNING = 2;   // Depreciation in progress
    case CLOSED = 3;    // Fully depreciated or disposed

    public function label(): string
    {
        return match ($this) {
            self::CURRENT => 'Current',
            self::RUNNING => 'Running',
            self::CLOSED => 'Closed',
        };
    }

    public static function fromName(string $name): ?int
    {
        return match (strtolower($name)) {
            'current' => self::CURRENT->value,
            'running' => self::RUNNING->value,
            'closed' => self::CLOSED->value,
            default => null,
        };
    }
}
