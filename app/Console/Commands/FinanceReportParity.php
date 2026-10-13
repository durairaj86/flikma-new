<?php

namespace App\Console\Commands;

use App\Livewire\Report\Finance as F;
use Illuminate\Console\Command;

/**
 * Runs each switched finance report twice, from the previous per-table queries and from the finance_ledgers view, and compares
 * the numbers (read-only). Run on real data before relying on the view (docs/finance-ledger-design.md, phase 5).
 * The Supplier Ledger is not compared: the old one is wrong (it always netted to zero) and is expected to differ.
 */
class FinanceReportParity extends Command
{
    protected $signature = 'finance:report-parity {--company=1 : Company id} {--from=2020-01-01} {--to=2035-12-31}';

    protected $description = 'Compare the finance reports computed the old way and from the ledger view (read-only)';

    public function handle(): int
    {
        session(['company_id' => (int) $this->option('company')]);
        $first = $this->option('from');
        $last = $this->option('to');
        $y = (int) date('Y');
        // all time, this year, each of the last 12 months
        $ranges = [[$first, $last], ["$y-01-01", "$y-12-31"]];
        for ($i = 0; $i < 12; $i++) {
            $m = now()->subMonths($i);
            $ranges[] = [$m->copy()->startOfMonth()->toDateString(), $m->copy()->endOfMonth()->toDateString()];
        }
        $searches = ['', 'VAT', 'Asset'];

        $table = fn ($class, $method) => function ($s, $e, $q) use ($class, $method) {
            $c = app("App\\Livewire\\Report\\Finance\\$class");
            $c->startDate = $s;
            $c->endDate = $e;
            $c->search = $q;

            return $c->$method();
        };
        $card = fn ($class) => function ($s, $e) use ($class) {
            $c = app("App\\Livewire\\Report\\Finance\\$class");
            $c->startDate = $s;
            $c->endDate = $e;

            return $c->render()->getData()['summary'];
        };
        $cases = [
            'Trial Balance' => $table('TrialBalanceTable', 'getTrialBalanceData'),
            'Profit & Loss' => $table('ProfitAndLossTable', 'getProfitAndLossData'),
            'Balance Sheet' => function ($s, $e, $q) use ($table) {
                $d = $table('BalanceSheetTable', 'getBalanceSheetData')($s, $e, $q);
                unset($d['net_income']);

                return $d;
            },
            'Tax Summary' => $table('TaxSummaryTable', 'getTaxSummaryData'),
            'Input Tax' => $table('InputTaxTable', 'getInputTaxData'),
            'Output Tax' => $table('OutputTaxTable', 'getOutputTaxData'),
            'Balance Sheet cards' => $card('BalanceSheet'),
            'Profit & Loss cards' => $card('ProfitAndLoss'),
            'Tax Summary cards' => $card('TaxSummary'),
            'Input Tax cards' => $card('InputTax'),
            'Output Tax cards' => $card('OutputTax'),
        ];

        $norm = function ($v) use (&$norm) {
            if (is_array($v)) {
                return array_map($norm, $v);
            }
            if (is_numeric($v) && !is_string($v)) {
                return round((float) $v, 2);
            }
            if (is_string($v) && is_numeric($v) && strlen($v) < 15 && !preg_match('/^0\d/', $v) && !str_contains($v, '-')) {
                return round((float) $v, 2);
            }

            return $v;
        };

        $total = 0;
        $bad = 0;
        foreach ($cases as $name => $fn) {
            $diffs = 0;
            foreach ($ranges as [$s, $e]) {
                foreach ($searches as $q) {
                    config(['finance.reports.use_ledger' => false]);
                    $old = $norm(json_decode(json_encode($fn($s, $e, $q)), true));
                    config(['finance.reports.use_ledger' => true]);
                    $new = $norm(json_decode(json_encode($fn($s, $e, $q)), true));
                    $total++;
                    if ($old != $new) {
                        $diffs++;
                        $bad++;
                        if ($diffs <= 2) {
                            $this->line("  differs: $name [$s .. $e] search '$q'");
                        }
                    }
                }
            }
            $this->line(($diffs ? '<error>FAIL</error>' : '<info>PASS</info>') . " $name" . ($diffs ? " ($diffs differences)" : ''));
        }
        config(['finance.reports.use_ledger' => true]);
        $this->newLine();
        $this->line("$total comparisons, $bad differences.");

        return $bad ? self::FAILURE : self::SUCCESS;
    }
}
