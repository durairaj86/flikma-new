<?php

namespace App\Enums;

enum JournalVoucherTypeEnum: int
{
    case ACCOUNTING_FINANCE = 1;
    case INVENTORY_STOCK = 2;
    case VAT_TAX = 3;
    case BANK_RECONCILIATION = 4;
    case OPENING_BALANCES = 5;
    case MISCELLANEOUS = 6;

    /**
     * Get the label for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::ACCOUNTING_FINANCE => 'Accounting / Finance',
            self::INVENTORY_STOCK => 'Inventory / Stock Adjustments',
            self::VAT_TAX => 'VAT / Tax Adjustments',
            self::BANK_RECONCILIATION => 'Bank Reconciliation',
            self::OPENING_BALANCES => 'Opening Balances',
            self::MISCELLANEOUS => 'Miscellaneous / Others',
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
            'accounting_finance' => self::ACCOUNTING_FINANCE->value,
            'inventory_stock' => self::INVENTORY_STOCK->value,
            'vat_tax' => self::VAT_TAX->value,
            'bank_reconciliation' => self::BANK_RECONCILIATION->value,
            'opening_balances' => self::OPENING_BALANCES->value,
            'miscellaneous' => self::MISCELLANEOUS->value,
            default => self::MISCELLANEOUS->value,
        };
    }
}
