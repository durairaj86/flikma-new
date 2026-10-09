<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /** Columns the Debit Note module needs on the existing debit_notes / debit_note_subs tables (guarded: skipped when present). */
    public function up(): void
    {
        $cols = [
            'debit_notes' => [
                'row_no' => 'VARCHAR(50) NULL', 'unique_row_no' => 'INT UNSIGNED NULL', 'draft_no' => 'VARCHAR(50) NULL',
                'currency_rate' => 'DECIMAL(15,6) NOT NULL DEFAULT 1', 'base_sub_total' => 'DECIMAL(15,2) NULL',
                'base_tax_total' => 'DECIMAL(15,2) NULL', 'base_grand_total' => 'DECIMAL(15,2) NULL',
                'reason' => 'VARCHAR(500) NULL', 'terms' => 'TEXT NULL', 'old_invoice_date' => 'DATE NULL',
            ],
            'debit_note_subs' => [
                'account_id' => 'BIGINT UNSIGNED NULL', 'company_id' => 'BIGINT UNSIGNED NULL', 'comment' => 'VARCHAR(500) NULL',
                'unit_id' => 'BIGINT UNSIGNED NULL', 'tax_code' => 'VARCHAR(50) NULL', 'tax_percent' => 'DECIMAL(8,2) NULL',
                'tax_amount' => 'DECIMAL(15,2) NULL', 'total' => 'DECIMAL(15,2) NULL', 'total_with_tax' => 'DECIMAL(15,2) NULL',
            ],
        ];
        foreach ($cols as $table => $columns) {
            foreach ($columns as $name => $def) {
                if (Schema::hasTable($table) && !Schema::hasColumn($table, $name)) {
                    DB::statement("ALTER TABLE `$table` ADD COLUMN `$name` $def");
                }
            }
        }
    }

    public function down(): void
    {
        // additive only: nothing is dropped
    }
};
