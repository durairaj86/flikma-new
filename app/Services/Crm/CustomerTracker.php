<?php

namespace App\Services\Crm;

use App\Enums\EnquiryEnum;
use App\Enums\JobEnum;
use App\Enums\QuotationEnum;
use App\Models\Crm\CrmAction;
use App\Models\Crm\CrmActivity;
use App\Models\Customer\Customer;
use App\Models\Enquiry\Enquiry;
use App\Models\Prospect\Prospect;
use App\Models\Quotation\Quotation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The sales workbook's CRM sheet as a list: one line per customer and per prospect that is not a customer yet, with the
 * automatic columns worked out from what is already in the system:
 * - status: from the documents (open RFQ -> "RFQ Received", open quote -> "Quoted", jobs in the last 180 days -> "Active" ...);
 * - touches (outreach actions logged), RFQs (enquiries), jobs, the score and the rank it suggests (CrmSettings);
 * - the last action and how many days ago (stale after CrmSettings stale_days), and the next planned action.
 * Activities on a customer's prospect, enquiries and quotations count for the customer.
 */
class CustomerTracker
{
    /** Activities of the last rows() call, per line key ("c12" / "p7"), for the CRM Dashboard. */
    public array $activities = [];

    public const STATUSES = ['Prospect', 'Following Up', 'RFQ Received', 'Quoted', 'Negotiating', 'Confirmed', 'Active', 'On Hold', 'Lost', 'Inactive'];

    /** Customers and open prospects of the salespeople given (null = all), each as an array of display values. */
    public function rows(?array $salespersons = null): Collection
    {
        $today = CrmActivity::localNowNaive()->startOfDay();
        $prospectCustomer = Prospect::whereNotNull('customer_id')->pluck('customer_id', 'id')->all();
        $party = function ($customerId, $prospectId) use ($prospectCustomer) {
            if ($customerId) {
                return 'c' . $customerId;
            }
            if ($prospectId) {
                return isset($prospectCustomer[$prospectId]) ? 'c' . $prospectCustomer[$prospectId] : 'p' . $prospectId;
            }

            return null;
        };

        $lines = [];
        foreach (Customer::when($salespersons, fn($q) => $q->whereIn('salesperson_id', $salespersons))
                     ->get(['id', 'row_no', 'name_en', 'rank', 'contact_person', 'phone', 'email', 'city_en', 'salesperson_id']) as $c) {
            $lines['c' . $c->id] = ['type' => 'customer', 'id' => $c->id, 'no' => $c->row_no, 'name' => $c->name_en, 'rank' => $c->rank,
                'contact' => $c->contact_person, 'phone' => $c->phone, 'email' => $c->email, 'location' => $c->city_en, 'salesperson_id' => $c->salesperson_id,
                'lost' => false, 'lead' => null];
        }
        foreach (Prospect::whereNull('customer_id')->when($salespersons, fn($q) => $q->whereIn('salesperson_id', $salespersons))
                     ->get(['id', 'row_no', 'name', 'company_name', 'rank', 'contact_person', 'phone', 'email', 'city', 'salesperson_id', 'status',
                         'route', 'service_id', 'approx_volume', 'incoterm', 'approx_cost', 'approx_selling']) as $p) {
            $lines['p' . $p->id] = ['type' => 'prospect', 'id' => $p->id, 'no' => $p->row_no, 'name' => $p->name, 'rank' => $p->rank,
                'contact' => $p->contact_person, 'phone' => $p->phone, 'email' => $p->email, 'location' => $p->city, 'salesperson_id' => $p->salesperson_id,
                'lost' => $p->status === 'lost', 'lead' => $p];
        }

        // documents of every line
        $enquiryParty = $quotationParty = [];
        $enquiries = [];
        foreach (Enquiry::orderBy('id')->get(['id', 'customer_id', 'prospect_id', 'status', 'pol', 'pod', 'shipment_mode', 'services', 'volume',
            'incoterm', 'handled_by', 'approx_cost', 'approx_selling']) as $e) {
            $key = $party($e->customer_id, $e->prospect_id);
            $enquiryParty[$e->id] = $key;
            if ($key) {
                $enquiries[$key][] = $e;
            }
        }
        $quotations = [];
        foreach (Quotation::withSum('charges as charges_total', 'local_amount')
                     ->get(['id', 'customer_id', 'prospect_id', 'status', 'grand_total', 'base_grand_total', 'currency_rate']) as $q) {
            $key = $party($q->customer_id, $q->prospect_id);
            // activities on an old version (revised) still belong to the customer; only the deal counts skip it
            $quotationParty[$q->id] = $key;
            if ($key && (int) $q->status !== QuotationEnum::REVISED->value) {
                $quotations[$key][] = $q;
            }
        }
        $jobs = [];
        foreach (DB::table('jobs')->where('company_id', companyId())->whereNull('deleted_at')
                     ->whereNotIn('status', [JobEnum::CANCELLED->value, JobEnum::TRASHED->value])->get(['customer_id', 'prospect_id', 'created_at']) as $j) {
            if ($key = $party($j->customer_id, $j->prospect_id)) {
                $jobs[$key][] = $j;
            }
        }

        // activities, each counted for the line it belongs to
        $acts = [];
        $rows = DB::table('crm_activities as a')->join('crm_actions as c', 'c.id', '=', 'a.action_id')
            ->where('a.company_id', companyId())->whereNull('a.deleted_at')
            ->get(['a.subject_type', 'a.subject_id', 'a.activity_at', 'a.follow_up_at', 'a.follow_up_done_at', 'a.action_id', 'c.name', 'c.category', 'c.code']);
        foreach ($rows as $a) {
            $key = match ($a->subject_type) {
                'customer' => 'c' . $a->subject_id,
                'prospect' => $party(null, $a->subject_id),
                'enquiry' => $enquiryParty[$a->subject_id] ?? null,
                'quotation' => $quotationParty[$a->subject_id] ?? null,
                default => null,
            };
            if ($key) {
                $acts[$key][] = $a;
            }
        }

        $this->activities = array_intersect_key($acts, $lines);
        $users = User::where('company_id', companyId())->pluck('name', 'id')->all();
        $stale = (int) CrmSettings::get('stale_days');

        return collect($lines)->map(function ($line, $key) use ($enquiries, $quotations, $jobs, $acts, $today, $users, $stale) {
            $myEnquiries = collect($enquiries[$key] ?? []);
            $myQuotes = collect($quotations[$key] ?? []);
            $myJobs = collect($jobs[$key] ?? []);
            $myActs = collect($acts[$key] ?? []);

            $done = $myActs->whereNull('follow_up_at');
            $touches = $done->where('category', 'outreach')->count();
            $last = $done->sortByDesc('activity_at')->first();
            $next = $myActs->whereNotNull('follow_up_at')->whereNull('follow_up_done_at')->sortBy('follow_up_at')->first();
            $rfqs = $myEnquiries->count();
            $jobCount = $myJobs->count();
            $score = CrmSettings::score($touches, $rfqs, $jobCount);

            $openEnquiries = $myEnquiries->whereIn('status', [EnquiryEnum::PENDING->value, EnquiryEnum::CONFIRMED->value, EnquiryEnum::QUOTATION->value]);
            $openQuotes = $myQuotes->where('status', QuotationEnum::PENDING->value);
            $deal = $openEnquiries->last() ?? $myEnquiries->last();
            $lead = $line['lead'];

            $status = $this->status($line, $last?->code, $openEnquiries->isNotEmpty(), $openQuotes->isNotEmpty(),
                $myQuotes->where('status', QuotationEnum::ACCEPTED->value)->isNotEmpty(),
                $myJobs->contains(fn($j) => Carbon::parse($j->created_at)->gte($today->copy()->subDays(180))), $touches > 0);

            $cost = $openEnquiries->sum(fn($e) => (float) $e->approx_cost);
            $selling = $openEnquiries->sum(fn($e) => (float) $e->approx_selling);
            if (!$selling) {
                $selling = $openQuotes->sum(fn($q) => (float) $q->base_grand_total > 0 ? (float) $q->base_grand_total : (float) $q->charges_total);
            }
            if (!$cost && !$selling && $lead) {
                $cost = (float) $lead->approx_cost;
                $selling = (float) $lead->approx_selling;
            }
            $margin = $cost && $selling ? $selling - $cost : null;
            $days = $last ? (int) Carbon::parse($last->activity_at)->startOfDay()->diffInDays($today) : null;

            return [
                'key' => $key,
                'type' => $line['type'],
                'id' => $line['id'],
                'no' => $line['no'],
                'name' => $line['name'],
                'rank' => $line['rank'],
                'suggested_rank' => CrmSettings::suggestedRank($score),
                'status' => $status,
                'contact' => $line['contact'],
                'phone' => $line['phone'],
                'email' => $line['email'],
                'location' => $line['location'],
                'salesperson_id' => $line['salesperson_id'],
                'route' => $deal ? $this->route($deal->shipment_mode, $deal->pol, $deal->pod) : ($lead?->route),
                'service' => $deal ? getSelectedServices($deal->services, true) : ($lead?->service_id ? (services()[$lead->service_id] ?? null) : null),
                'volume' => $deal && $deal->volume ? (string) $deal->volume : ($lead?->approx_volume),
                'incoterm' => $deal?->incoterm ?: $lead?->incoterm,
                'handled_by' => $deal && $deal->handled_by ? ($users[$deal->handled_by] ?? null) : null,
                'cost' => $cost ?: null,
                'selling' => $selling ?: null,
                'margin' => $margin,
                'margin_pct' => $margin !== null && $selling ? round($margin * 100 / $selling, 1) : null,
                'touches' => $touches,
                'rfqs' => $rfqs,
                'jobs' => $jobCount,
                'score' => $score,
                'last_at' => $last?->activity_at,
                'last_action' => $last ? __($last->name) : null,
                'last_category' => $last?->category,
                'days_since' => $days,
                'stale' => !in_array($status, ['Lost', 'Inactive'], true) && ($days === null || $days >= $stale),
                'next_at' => $next?->follow_up_at,
                'next_action' => $next ? __($next->name) : null,
                'next_overdue' => $next && Carbon::parse($next->follow_up_at)->lt($today),
            ];
        })->values();
    }

    /** The workbook's customer status, from the documents and the last action (most advanced stage first). */
    private function status(array $line, ?string $lastCode, bool $openRfq, bool $openQuote, bool $confirmed, bool $recentJob, bool $contacted): string
    {
        if ($line['lost']) {
            return 'Lost';
        }
        if ($lastCode === 'on_hold') {
            return 'On Hold';
        }
        if ($confirmed) {
            return 'Confirmed';
        }
        if ($openQuote) {
            return $lastCode === 'negotiating' ? 'Negotiating' : 'Quoted';
        }
        if ($openRfq) {
            return 'RFQ Received';
        }
        if ($line['type'] === 'customer') {
            return $recentJob ? 'Active' : 'Inactive';
        }

        return $contacted ? 'Following Up' : 'Prospect';
    }

    /** "JED → DXB": port codes; older rows store the port id, newer ones "CODE - Name". */
    private function route(?string $mode, $pol, $pod): ?string
    {
        $code = function ($value) use ($mode) {
            if ($value === null || $value === '') {
                return null;
            }
            if (is_numeric($value)) {
                static $cache = [];
                $table = strtolower((string) $mode) === 'air' ? 'airports' : 'ports';

                return $cache[$table][$value] ??= DB::table($table)->where('id', $value)->value('code') ?? (string) $value;
            }

            return trim(preg_split('/\s+-\s+|-/', (string) $value, 2)[0]);
        };
        $from = $code($pol);
        $to = $code($pod);

        return ($from || $to) ? ($from ?? '?') . ' → ' . ($to ?? '?') : null;
    }

    public static function statusLabel(string $status): string
    {
        return __($status);
    }

    public static function categoryStyle(?string $category): string
    {
        return CrmAction::badgeStyle($category);
    }
}
