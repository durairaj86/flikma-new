<?php

namespace App\Http\Controllers\Crm;

use App\Enums\EnquiryEnum;
use App\Enums\QuotationEnum;
use App\Http\Controllers\Controller;
use App\Models\Crm\CrmActivity;
use App\Models\Crm\LostReason;
use App\Models\Enquiry\Enquiry;
use App\Models\Master\Salesperson\Salesperson;
use App\Models\Prospect\Prospect;
use App\Models\Quotation\Quotation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Sales pipeline board. The stages are not stored anywhere: they come from the documents that already exist
 * (prospect → enquiry → quotation → won / lost), so the board can never disagree with the enquiry and quotation lists.
 */
class PipelineController extends Controller
{
    /** Cards per column; the column header still shows the full count and value. */
    private const LIMIT = 60;

    public function index()
    {
        $own = $this->ownSalespersonIds();
        $salespersons = Salesperson::orderBy('name')->when($own, fn($q) => $q->whereIn('id', $own))->get(['id', 'name']);

        return view('modules.crm.pipeline', compact('salespersons', 'own'));
    }

    /**
     * A salesman (a user linked to a salesperson, not the Super User or an Admin) only sees his own deals.
     * Null = sees everyone. One login may be linked to several salesperson records.
     */
    private function ownSalespersonIds(): ?array
    {
        if (isSuperUser() || (int) auth()->user()->role === 3) {
            return null;
        }
        $ids = Salesperson::where('user_id', auth()->id())->pluck('id')->all();

        return $ids ?: null;
    }

    public function data(Request $request): \Illuminate\Http\JsonResponse
    {
        $picked = $request->filled('salesperson_id') ? (int) $request->input('salesperson_id') : null;
        $own = $this->ownSalespersonIds();
        // a salesman can narrow to one of his own records, never widen past them
        $salesperson = $own ? ($picked && in_array($picked, $own, true) ? [$picked] : $own) : ($picked ? [$picked] : null);
        $days = in_array((int) $request->input('days'), [30, 90, 180, 365], true) ? (int) $request->input('days') : 90;
        $since = Carbon::today()->subDays($days);
        $term = trim((string) $request->input('q'));

        $columns = [
            'lead' => $this->leads($salesperson, $term, ['new', 'contacted', 'qualified']),
            'enquiry' => $this->enquiries($salesperson, $term, [EnquiryEnum::PENDING->value, EnquiryEnum::CONFIRMED->value]),
            'quoted' => $this->quotations($salesperson, $term, [QuotationEnum::PENDING->value]),
            'won' => $this->quotations($salesperson, $term, [QuotationEnum::ACCEPTED->value, QuotationEnum::CONVERTED->value], $since),
            'lost' => $this->lost($salesperson, $term, $since),
        ];

        $this->attachFollowUps($columns);

        $out = [];
        foreach ($columns as $key => $cards) {
            $out[$key] = [
                'count' => $cards->count(),
                'value' => round((float) $cards->sum('value'), 2),
                'cards' => $cards->take(self::LIMIT)->values(),
            ];
        }
        $won = $out['won']['count'];
        $lostCount = $out['lost']['count'];

        return response()->json([
            'columns' => $out,
            'currency' => $this->baseCurrency(),
            'win_rate' => ($won + $lostCount) > 0 ? round($won * 100 / ($won + $lostCount)) : null,
            'days' => $days,
        ]);
    }

    /** Quick view of one card in the drawer: the document's key facts; the activity timeline is loaded beside it. */
    public function card($type, $id)
    {
        $own = $this->ownSalespersonIds();
        $card = match ($type) {
            'prospect' => $this->leads($own, '', null, null, (int) $id)->first(),
            'enquiry' => $this->enquiries($own, '', null, null, (int) $id)->first(),
            'quotation' => $this->quotations($own, '', null, null, (int) $id)->first(),
            default => null,
        };
        abort_unless($card, 404);
        $columns = ['x' => collect([$card])];
        $this->attachFollowUps($columns);

        return response()->json($columns['x']->first());
    }

    /** Lost reasons for the "Mark as lost" dialog (any page that can cancel a quotation / enquiry / prospect). */
    public function lostReasons(): \Illuminate\Http\JsonResponse
    {
        return response()->json(LostReason::options());
    }

    // ───────────────────────── stages ─────────────────────────

    /** Prospects not yet a customer and with no enquiry / quotation (those are shown at their document's stage). */
    private function leads(?array $salesperson, string $term, ?array $statuses, ?Carbon $since = null, ?int $onlyId = null): Collection
    {
        $rows = Prospect::with('salesperson:id,name')
            ->whereNull('customer_id')
            ->when($statuses, fn($q) => $q->whereIn('status', $statuses))
            ->when($since, fn($q) => $q->where('updated_at', '>=', $since))
            ->when(!$onlyId, fn($q) => $q
                ->whereNotExists(fn($s) => $s->from('enquiries')->whereColumn('enquiries.prospect_id', 'prospects.id')->whereNull('enquiries.deleted_at'))
                ->whereNotExists(fn($s) => $s->from('quotations')->whereColumn('quotations.prospect_id', 'prospects.id')->whereNull('quotations.deleted_at')))
            ->when($onlyId, fn($q) => $q->whereKey($onlyId))
            ->when($salesperson, fn($q) => $q->whereIn('salesperson_id', $salesperson))
            ->when($term !== '', fn($q) => $q->where(fn($w) => $w->where('name', 'like', "%{$term}%")->orWhere('row_no', 'like', "%{$term}%")
                ->orWhere('company_name', 'like', "%{$term}%")->orWhere('phone', 'like', "%{$term}%")))
            ->orderByDesc('id')
            ->get(['id', 'row_no', 'name', 'company_name', 'phone', 'email', 'source', 'status', 'salesperson_id', 'lost_reason_id', 'lost_note', 'created_at', 'updated_at']);

        $reasons = $this->reasonNames($rows);

        return $rows->map(fn($p) => [
            'type' => 'prospect',
            'id' => $p->id,
            'no' => $p->row_no,
            'name' => $p->name,
            'sub' => $p->company_name ?: $p->phone,
            'stage' => $p->status,
            'stage_label' => __(ucfirst($p->status)),
            'value' => 0,
            'salesperson' => $p->salesperson->name ?? null,
            'age' => (int) Carbon::parse($p->created_at)->startOfDay()->diffInDays(Carbon::today()),
            'lost_reason' => $reasons[$p->lost_reason_id] ?? null,
            'lost_note' => $p->lost_note,
            'phone' => $p->phone,
            'email' => $p->email,
        ]);
    }

    private function enquiries(?array $salesperson, string $term, ?array $statuses, ?Carbon $since = null, ?int $onlyId = null): Collection
    {
        $rows = Enquiry::with(['customer:id,name_en', 'prospect:id,name', 'salesperson:id,name'])
            ->when($statuses, fn($q) => $q->whereIn('status', $statuses))
            ->when($since, fn($q) => $q->where('updated_at', '>=', $since))
            ->when($onlyId, fn($q) => $q->whereKey($onlyId))
            ->when($salesperson, fn($q) => $q->whereIn('salesperson_id', $salesperson))
            ->when($term !== '', fn($q) => $this->searchParty($q, $term))
            ->orderByDesc('id')
            ->get(['id', 'row_no', 'customer_id', 'prospect_id', 'salesperson_id', 'shipment_mode', 'pol', 'pod', 'status', 'lost_reason_id', 'lost_note', 'created_at', 'updated_at']);

        $reasons = $this->reasonNames($rows);

        return $rows->map(fn($e) => [
            'type' => 'enquiry',
            'id' => $e->id,
            'links' => array_filter(['prospect' => $e->prospect_id]),
            'no' => $e->row_no,
            'name' => $e->customer->name_en ?? $e->prospect->name ?? '—',
            'sub' => $this->route($e->shipment_mode, $e->pol, $e->pod),
            'stage' => (int) $e->status,
            'stage_label' => EnquiryEnum::tryFrom((int) $e->status)?->label(),
            'value' => 0,
            'salesperson' => $e->salesperson->name ?? null,
            'age' => (int) Carbon::parse($e->created_at)->startOfDay()->diffInDays(Carbon::today()),
            'lost_reason' => $reasons[$e->lost_reason_id] ?? null,
            'lost_note' => $e->lost_note,
            'mode' => $e->shipment_mode,
        ]);
    }

    private function quotations(?array $salesperson, string $term, ?array $statuses, ?Carbon $since = null, ?int $onlyId = null): Collection
    {
        $rows = Quotation::with(['customer:id,name_en', 'prospect:id,name', 'salesperson:id,name'])
            // grand_total is not kept up to date by the quotation form: the charge lines are the amount (as in the quotation drawer)
            ->withSum('charges as charges_total', 'local_amount')
            ->when($statuses, fn($q) => $q->whereIn('status', $statuses))
            ->when($since, fn($q) => $q->where('updated_at', '>=', $since))
            ->when($onlyId, fn($q) => $q->whereKey($onlyId))
            ->when($salesperson, fn($q) => $q->whereIn('salesperson_id', $salesperson))
            ->when($term !== '', fn($q) => $this->searchParty($q, $term))
            ->orderByDesc('id')
            ->get(['id', 'row_no', 'customer_id', 'prospect_id', 'enquiry_id', 'salesperson_id', 'shipment_mode', 'pol', 'pod', 'status', 'valid_until',
                'currency', 'currency_rate', 'grand_total', 'base_grand_total', 'job_id', 'customer_status', 'revision_count', 'lost_reason_id', 'lost_note', 'created_at', 'updated_at']);

        $reasons = $this->reasonNames($rows);
        $today = Carbon::today();

        return $rows->map(function ($r) use ($reasons, $today) {
            $validUntil = $r->valid_until ? Carbon::parse($r->valid_until) : null;

            return [
                'type' => 'quotation',
                'id' => $r->id,
                'links' => array_filter(['prospect' => $r->prospect_id, 'enquiry' => $r->enquiry_id]),
                'no' => $r->row_no,
                'name' => $r->customer->name_en ?? $r->prospect->name ?? '—',
                'sub' => $this->route($r->shipment_mode, $r->pol, $r->pod),
                'stage' => (int) $r->status,
                'stage_label' => QuotationEnum::tryFrom((int) $r->status)?->label(),
                // the board adds values up, so it uses the amount in the company currency
                'value' => (float) $r->base_grand_total > 0 ? (float) $r->base_grand_total
                    : ((float) $r->grand_total > 0 ? round((float) $r->grand_total * ((float) $r->currency_rate ?: 1), 2) : (float) $r->charges_total),
                'doc_value' => (float) $r->grand_total > 0 ? (float) $r->grand_total : (float) $r->charges_total,
                'doc_currency' => (float) $r->grand_total > 0 ? $r->currency : $this->baseCurrency(),
                'salesperson' => $r->salesperson->name ?? null,
                'age' => (int) Carbon::parse($r->created_at)->startOfDay()->diffInDays($today),
                'valid_until' => $validUntil?->format('d-m-Y'),
                'expires_in' => $validUntil ? (int) $today->diffInDays($validUntil, false) : null,
                'job_id' => $r->job_id,
                'customer_status' => (int) $r->customer_status,
                'lost_reason' => $reasons[$r->lost_reason_id] ?? null,
                'lost_note' => $r->lost_note,
                'mode' => $r->shipment_mode,
            ];
        });
    }

    /** Lost in the period: cancelled / expired quotations, cancelled enquiries, lost prospects. */
    private function lost(?array $salesperson, string $term, Carbon $since): Collection
    {
        return $this->quotations($salesperson, $term, [QuotationEnum::CANCELLED->value, QuotationEnum::EXPIRED->value], $since)
            ->concat($this->enquiries($salesperson, $term, [EnquiryEnum::CANCELLED->value], $since))
            ->concat($this->leads($salesperson, $term, ['lost'], $since))
            ->values();
    }

    // ───────────────────────── helpers ─────────────────────────

    private function baseCurrency(): string
    {
        static $currency = null;

        return $currency ??= (DB::table('companies')->where('id', companyId())->value('base_currency') ?: 'SAR');
    }

    /**
     * Next open follow-up and last activity of every card (one query per subject type). A card also counts the
     * activities logged on the documents it came from (a quotation's prospect and enquiry): the deal moved on,
     * the follow-up set on the prospect is still due.
     */
    private function attachFollowUps(array &$columns): void
    {
        $ids = [];
        foreach ($columns as $cards) {
            foreach ($cards as $c) {
                $ids[$c['type']][] = $c['id'];
                foreach ($c['links'] ?? [] as $type => $id) {
                    $ids[$type][] = $id;
                }
            }
        }
        $next = $last = [];
        foreach ($ids as $type => $list) {
            $rows = CrmActivity::where('subject_type', $type)->whereIn('subject_id', array_unique($list))
                ->select('subject_id', DB::raw('MAX(activity_at) AS last_at'),
                    DB::raw('MIN(CASE WHEN follow_up_at IS NOT NULL AND follow_up_done_at IS NULL THEN follow_up_at END) AS next_at'))
                ->groupBy('subject_id')->get();
            foreach ($rows as $r) {
                $next[$type][$r->subject_id] = $r->next_at;
                $last[$type][$r->subject_id] = $r->last_at;
            }
        }
        $now = CrmActivity::localNowNaive();
        foreach ($columns as $key => $cards) {
            $columns[$key] = $cards->map(function ($c) use ($next, $last, $now) {
                $n = $next[$c['type']][$c['id']] ?? null;
                $l = $last[$c['type']][$c['id']] ?? null;
                foreach ($c['links'] ?? [] as $type => $id) {
                    $ln = $next[$type][$id] ?? null;
                    $ll = $last[$type][$id] ?? null;
                    $n = $ln && (!$n || $ln < $n) ? $ln : $n;
                    $l = $ll && (!$l || $ll > $l) ? $ll : $l;
                }
                unset($c['links']);
                $c['next_follow_up'] = $n ? Carbon::parse($n)->format('d-m-Y H:i') : null;
                $c['follow_up_state'] = $n ? (Carbon::parse($n)->lt($now->copy()->startOfDay()) ? 'overdue' : (Carbon::parse($n)->isToday() ? 'today' : 'later')) : null;
                $c['last_activity'] = $l ? Carbon::parse($l)->format('d-m-Y') : null;

                return $c;
            });
        }
    }

    private function searchParty($query, string $term)
    {
        return $query->where(fn($w) => $w->where('row_no', 'like', "%{$term}%")
            ->orWhereHas('customer', fn($c) => $c->where('name_en', 'like', "%{$term}%"))
            ->orWhereHas('prospect', fn($p) => $p->where('name', 'like', "%{$term}%")));
    }

    private function reasonNames(Collection $rows): array
    {
        $ids = $rows->pluck('lost_reason_id')->filter()->unique()->all();

        return $ids ? LostReason::whereIn('id', $ids)->pluck('name', 'id')->all() : [];
    }

    /** "JED → DXB": the port code. Older rows store the port id, newer ones "CODE - Name". */
    private function route(?string $mode, $pol, $pod): ?string
    {
        $code = function ($value) use ($mode) {
            if ($value === null || $value === '') {
                return null;
            }
            if (is_numeric($value)) {
                $table = strtolower((string) $mode) === 'air' ? 'airports' : 'ports';
                static $cache = [];

                return $cache[$table][$value] ??= DB::table($table)->where('id', $value)->value('code') ?? (string) $value;
            }

            return trim(preg_split('/\s+-\s+|-/', (string) $value, 2)[0]);
        };
        $from = $code($pol);
        $to = $code($pod);

        return ($from || $to) ? trim(($from ?? '?') . ' → ' . ($to ?? '?')) : null;
    }
}
