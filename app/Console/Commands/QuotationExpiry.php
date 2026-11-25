<?php

namespace App\Console\Commands;

use App\Enums\QuotationEnum;
use App\Models\Crm\CrmActivity;
use App\Models\Quotation\Quotation;
use Illuminate\Console\Command;

/**
 * Quotation validity, on each company's local date:
 * - a pending quotation whose "valid until" has passed becomes Expired;
 * - a few days before that, the salesperson gets a CRM follow-up ("expires on ..."), once per validity date,
 *   so it shows in Follow-ups, on the dashboard and in the morning reminder email.
 */
class QuotationExpiry extends Command
{
    protected $signature = 'quotations:expiry {--dry-run : list what would change, change nothing} {--quotation= : only this quotation id}';

    protected $description = 'Expire pending quotations past their validity and add "expires soon" follow-ups';

    public function handle(): int
    {
        $pending = Quotation::withoutGlobalScopes()
            ->whereNull('deleted_at')
            ->where('status', QuotationEnum::PENDING->value)
            ->whereNotNull('valid_until')
            ->when($this->option('quotation'), fn($q, $id) => $q->whereKey((int) $id))
            ->get();

        $expired = $reminded = 0;
        foreach ($pending as $q) {
            $today = CrmActivity::localNowNaive((int) $q->company_id)->startOfDay();
            $validUntil = \Carbon\Carbon::createFromFormat('d-m-Y', $q->valid_until)->startOfDay();

            if ($validUntil->lt($today)) {
                $this->line("expire  {$q->row_no} (valid until {$q->valid_until})");
                if (!$this->option('dry-run')) {
                    // through the model so the change log (drawer time frame) records it
                    $q->status = QuotationEnum::EXPIRED->value;
                    $q->save();
                }
                $expired++;
                continue;
            }

            if ($q->expiry_reminded_at === null && $validUntil->lte($today->copy()->addDays(Quotation::EXPIRY_REMINDER_DAYS))) {
                $owner = $q->followUpOwnerId();
                $this->line("remind  {$q->row_no} (valid until {$q->valid_until}) → user " . ($owner ?? 'none'));
                if (!$this->option('dry-run')) {
                    if ($owner) {
                        CrmActivity::plan((int) $q->company_id, 'quotation', $q->id, 'follow_up_due', $today->copy()->setTime(9, 0),
                            "Quotation {$q->row_no} expires on {$q->valid_until}: ask the customer for a decision, or revise it.", $owner);
                    }
                    // stamped without touching updated_at (the pipeline's won / lost period reads it)
                    Quotation::withoutGlobalScopes()->whereKey($q->id)->update(['expiry_reminded_at' => now()]);
                }
                $reminded++;
            }
        }

        $this->info("Expired: {$expired}, expiry follow-ups: {$reminded}" . ($this->option('dry-run') ? ' (dry run)' : ''));

        return self::SUCCESS;
    }
}
