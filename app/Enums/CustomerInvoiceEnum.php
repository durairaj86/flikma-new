<?php

namespace App\Enums;

enum CustomerInvoiceEnum: int
{
    case DRAFT = 1;
    case SENT = 2;
    case APPROVED = 3;
    case REJECTED = 4;
    case CANCELLED = 5;
    case CONVERTED = 6; // converted to invoice

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Sent',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::CANCELLED => 'Cancelled',
            self::CONVERTED => 'Converted to Invoice',
        };
    }

    public static function fromName(string $name): ?int
    {
        return match (strtolower($name)) {
            'draft' => self::DRAFT->value,
            'sent' => self::SENT->value,
            'approved' => self::APPROVED->value,
            'rejected' => self::REJECTED->value,
            'cancelled' => self::CANCELLED->value,
            'converted', 'converted to invoice' => self::CONVERTED->value,
            default => null,
        };
    }
}
