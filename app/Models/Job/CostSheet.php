<?php

namespace App\Models\Job;

use App\Enums\CreditNoteEnum;
use App\Enums\CustomerInvoiceEnum;
use App\Models\Finance\Adjustment\CreditNote;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Services\Finance\FinanceSettings;
use App\Traits\CompanyScopeTrait;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * A job's cost sheet: expected sale and cost per charge, the "Bill to customer" tick per line. When Settings › Finance › Cost
 * sheet is on, the customer invoice is created from it only, and it is locked from then on (see release()).
 */
class CostSheet extends Model
{
    use CompanyScopeTrait, LogHistoryTrait;

    public const OPEN = 1;
    public const INVOICED = 2;

    protected $fillable = ['company_id', 'job_id', 'currency', 'currency_rate', 'lines', 'sale_total', 'cost_total', 'terms', 'status',
        'customer_invoice_id', 'invoiced_at', 'created_by', 'updated_by'];

    protected $casts = ['lines' => 'array', 'invoiced_at' => 'datetime'];

    public static function enabled(): bool
    {
        return FinanceSettings::get('cost_sheet') === 'on';
    }

    public function job(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CustomerInvoice::class, 'customer_invoice_id');
    }

    public function locked(): bool
    {
        return (int) $this->status === self::INVOICED;
    }

    /** The lines that go on the customer invoice: ticked, with a sale price. */
    public function billableLines(): array
    {
        return array_values(array_filter($this->lines ?? [], fn ($l) => !empty($l['bill']) && (float) ($l['s_price'] ?? 0) > 0));
    }

    /** The billable lines in the shape the customer invoice's save takes (description_id[], quantity[], unit_price[] ...). */
    public function invoiceLines(): array
    {
        $lines = $this->billableLines();
        $rate = (float) $this->currency_rate ?: 1.0;
        $accounts = \Illuminate\Support\Facades\DB::table('descriptions')->whereIn('id', array_column($lines, 'description_id'))->pluck('sale_account_id', 'id');
        $out = array_fill_keys(['description_id', 'comment', 'unit_id', 'quantity', 'unit_price', 'tax', 'account', 'source_type', 'source_id', 'source_sub_id', 'cost_base_amount'], []);
        foreach ($lines as $l) {
            $cost = round((float) $l['c_qty'] * (float) $l['c_price'] * $rate, 4);
            foreach ([
                'description_id' => (string) $l['description_id'], 'comment' => $l['comment'], 'unit_id' => $l['unit_id'], 'quantity' => $l['s_qty'],
                'unit_price' => (string) $l['s_price'], 'tax' => $l['tax'], 'account' => $accounts[$l['description_id']] ?? 32,
                'source_type' => $l['source_type'], 'source_id' => $l['source_id'], 'source_sub_id' => $l['source_sub_id'],
                'cost_base_amount' => $cost > 0 ? $cost : null,
            ] as $k => $v) {
                $out[$k][] = $v;
            }
        }

        return $out + ['currency' => $this->currency, 'currency_rate' => $rate];
    }

    /** Sale (billable lines only) and cost of the lines, in company currency, excl. VAT. */
    public static function totals(array $lines, float $rate): array
    {
        $sale = $cost = 0.0;
        foreach ($lines as $l) {
            if (!empty($l['bill'])) {
                $sale += (float) ($l['s_qty'] ?? 0) * (float) ($l['s_price'] ?? 0) * $rate;
            }
            $cost += (float) ($l['c_qty'] ?? 0) * (float) ($l['c_price'] ?? 0) * $rate;
        }

        return ['sale' => round($sale, 2), 'cost' => round($cost, 2)];
    }

    public static function forInvoice(int $invoiceId): ?self
    {
        return $invoiceId ? static::where('customer_invoice_id', $invoiceId)->first() : null;
    }

    /**
     * The invoice made from a cost sheet is gone (draft deleted), cancelled or credited in full: the cost sheet opens again so
     * it can be corrected and invoiced once more. Called from the CustomerInvoice / CreditNote model events.
     */
    public static function release(int $invoiceId): void
    {
        $sheet = static::forInvoice($invoiceId);
        if (!$sheet) {
            return;
        }
        $invoice = CustomerInvoice::withTrashed()->find($invoiceId);
        $status = $invoice ? (int) ($invoice->status instanceof \BackedEnum ? $invoice->status->value : $invoice->getRawOriginal('status')) : null;
        $gone = !$invoice || $invoice->trashed() || $status === CustomerInvoiceEnum::CANCELLED->value;
        if (!$gone) {
            $credited = (float) CreditNote::where('invoice_id', $invoiceId)->where('status', CreditNoteEnum::APPROVED->value)->sum('grand_total');
            $gone = $credited > 0 && $credited + 0.005 >= (float) $invoice->grand_total;
        }
        if ($gone) {
            $sheet->update(['status' => self::OPEN, 'customer_invoice_id' => null, 'invoiced_at' => null]);
        }
    }
}
