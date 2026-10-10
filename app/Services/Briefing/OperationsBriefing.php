<?php

namespace App\Services\Briefing;

use App\Models\Arrival\ArrivalNotice;
use App\Models\Job\Job;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * "Today's briefing": what needs attention right now, worked out from the live data with plain rules.
 * Shown after sign-in to the account owner and the Operations department, and any time from the bell in the right rail.
 */
class OperationsBriefing
{
    private const ROWS = 4;

    /** Account owner (role 1) and anyone in the Operations department. */
    public static function allowed(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        if ((string) $user->role === '1') {
            return true;
        }

        return $user->department_id && stripos((string) optional($user->department)->name, 'operation') !== false;
    }

    public function build(User $user): array
    {
        $today = today();
        $isOwner = (string) $user->role === '1';
        $open = [\App\Enums\JobEnum::PENDING->value];

        $jobs = fn() => Job::whereIn('status', $open);
        $jobRows = fn($q, string $date) => $q->with('customer:id,name_en')->orderBy($date)->limit(self::ROWS)->get()
            ->map(function ($j) use ($date, $today) {
                $d = $j->{$date} ? \Illuminate\Support\Carbon::parse($j->{$date})->startOfDay() : null;
                $tag = ['eta' => 'ETA ', 'etd' => 'ETD '][$date] ?? '';
                $when = $d ? $tag . $d->format('d-m-Y') . ($d->lt($today) ? ' (' . trans_choice('{1} :n day ago|[2,*] :n days ago', $d->diffInDays($today), ['n' => $d->diffInDays($today)]) . ')' : '') : '';

                return [
                    'label' => ($j->row_no ?: $j->job_no ?: '#' . $j->id) . ' · ' . ($j->customer->name_en ?? '-'),
                    'sub' => collect([($j->pol || $j->pod) ? ($j->pol ?: '?') . ' → ' . ($j->pod ?: '?') : null, $j->carrier, $when])->filter()->implode(' · '),
                ];
            })->all();

        $items = [];
        $group = '';
        $add = function (string $tone, string $icon, string $title, string $text, ?string $label = null, ?string $url = null, array $rows = [], int $count = 0) use (&$items, &$group) {
            $items[] = compact('tone', 'icon', 'title', 'text', 'rows', 'count', 'group') + ['link_label' => $label, 'link_url' => $url];
        };
        $ago = fn($d) => $d->lt($today) ? trans_choice('{1} :n day ago|[2,*] :n days ago', $d->diffInDays($today), ['n' => $d->diffInDays($today)])
            : ($d->eq($today) ? __('today') : trans_choice('{1} in :n day|[2,*] in :n days', $today->diffInDays($d), ['n' => $today->diffInDays($d)]));

        $group = __('Shipments');
        // 1. ETA already passed but the job is still open
        $q = $jobs()->whereNotNull('eta')->whereNull('ata')->whereDate('eta', '<', $today);
        if (($n = (clone $q)->count()) > 0) {
            $add('bad', 'bi-exclamation-octagon', trans_choice('{1} :n shipment is past its ETA|[2,*] :n shipments are past their ETA', $n, ['n' => $n]),
                __('Still open with no arrival date. Confirm whether they have arrived and update the job, or revise the ETA.'),
                __('Open jobs'), url('/operation/jobs'), $jobRows(clone $q, 'eta'), $n);
        }

        // 2. Arriving today
        $q = $jobs()->whereDate('eta', $today);
        $arrivingToday = (clone $q)->count();
        if ($arrivingToday > 0) {
            $add('info', 'bi-box-arrow-in-down', trans_choice('{1} :n shipment arrives today|[2,*] :n shipments arrive today', $arrivingToday, ['n' => $arrivingToday]),
                __('Call the consignees, check the delivery orders and be ready to arrange clearance and transport.'),
                __('Open jobs'), url('/operation/jobs'), $jobRows(clone $q, 'eta'), $arrivingToday);
        }

        // 3. Departing today
        $q = $jobs()->whereDate('etd', $today);
        $departingToday = (clone $q)->count();
        if ($departingToday > 0) {
            $add('info', 'bi-box-arrow-up-right', trans_choice('{1} :n shipment departs today|[2,*] :n shipments depart today', $departingToday, ['n' => $departingToday]),
                __('Make sure the documents are with the carrier and the customer has the departure details.'),
                __('Open jobs'), url('/operation/jobs'), $jobRows(clone $q, 'etd'), $departingToday);
        }

        // 4. Arriving in the next 3 days and the consignee has not been told
        $informed = ArrivalNotice::whereNotNull('job_id')->whereNotIn('status', [\App\Enums\ArrivalNoticeEnum::CANCELLED->value])->pluck('job_id');
        $q = $jobs()->whereDate('eta', '>', $today)->whereDate('eta', '<=', $today->copy()->addDays(3))->whereNotIn('id', $informed);
        if (($n = (clone $q)->count()) > 0) {
            $add('warn', 'bi-megaphone', trans_choice('{1} :n arriving shipment has no arrival notice|[2,*] :n arriving shipments have no arrival notice', $n, ['n' => $n]),
                __('These arrive within 3 days. Send the arrival notice so the consignees can collect the delivery order in time.'),
                __('Create arrival notice'), url('/operation/arrival-notices'), $jobRows(clone $q, 'eta'), $n);
        }
        $draft = ArrivalNotice::where('status', \App\Enums\ArrivalNoticeEnum::DRAFT->value)->whereNotNull('eta')->whereDate('eta', '<=', $today->copy()->addDays(3))->count();
        if ($draft > 0) {
            $add('warn', 'bi-send', trans_choice('{1} :n arrival notice is still a draft|[2,*] :n arrival notices are still drafts', $draft, ['n' => $draft]),
                __('The vessel is close but the consignee has not been notified yet.'), __('Arrival notices'), url('/operation/arrival-notices'), [], $draft);
        }

        $group = __('Deliveries');
        // 5. Delivery orders: late, and due today
        $late = DB::table('delivery_orders')->whereNull('deleted_at')->whereIn('status', [1, 2])->whereDate('delivery_date', '<', $today);
        $late = $this->companyScoped($late);
        if (($n = (clone $late)->count()) > 0) {
            $rows = (clone $late)->orderBy('delivery_date')->limit(self::ROWS)->get(['row_no', 'consignee', 'delivery_date'])
                ->map(fn($r) => ['label' => $r->row_no . ' · ' . ($r->consignee ?: '-'), 'sub' => __('Planned') . ' ' . date('d-m-Y', strtotime($r->delivery_date))])->all();
            $add('bad', 'bi-truck', trans_choice('{1} :n delivery is late|[2,*] :n deliveries are late', $n, ['n' => $n]),
                __('The planned delivery date has passed and the cargo is not marked delivered.'), __('Delivery orders'), url('/operation/delivery-orders'), $rows, $n);
        }
        $dueToday = $this->companyScoped(DB::table('delivery_orders')->whereNull('deleted_at')->whereIn('status', [1, 2])->whereDate('delivery_date', $today))->count();
        if ($dueToday > 0) {
            $add('info', 'bi-calendar-check', trans_choice('{1} :n delivery is planned for today|[2,*] :n deliveries are planned for today', $dueToday, ['n' => $dueToday]),
                __('Confirm vehicle and driver, and follow each one until the proof of delivery.'), __('Delivery orders'), url('/operation/delivery-orders'), [], $dueToday);
        }

        $group = __('Bookings');
        // 6. Carrier bookings with a cut-off in the next 48 hours
        $cut = $this->companyScoped(DB::table('bookings')->whereNull('deleted_at')->whereIn('status', [1, 2])
            ->where(fn($w) => $w->whereBetween('cargo_cutoff', [now(), now()->addHours(48)])->orWhereBetween('doc_cutoff', [now(), now()->addHours(48)])));
        if (($n = (clone $cut)->count()) > 0) {
            $rows = (clone $cut)->limit(self::ROWS)->get(['row_no', 'pol', 'pod', 'cargo_cutoff', 'doc_cutoff'])->map(function ($r) {
                $c = collect([$r->cargo_cutoff ? __('Cargo') . ' ' . date('d-m H:i', strtotime($r->cargo_cutoff)) : null, $r->doc_cutoff ? __('Docs') . ' ' . date('d-m H:i', strtotime($r->doc_cutoff)) : null])->filter()->implode(' · ');

                return ['label' => $r->row_no . ' · ' . $r->pol . ' → ' . $r->pod, 'sub' => $c];
            })->all();
            $add('warn', 'bi-alarm', trans_choice('{1} :n booking has a cut-off within 48 hours|[2,*] :n bookings have a cut-off within 48 hours', $n, ['n' => $n]),
                __('Late cargo or documents can mean the container is rolled to the next vessel.'), __('Bookings'), url('/operation/bookings'), $rows, $n);
        }

        $group = __('Port & Customs');
        // 7. Customs clearance on hold
        $hold = $this->companyScoped(DB::table('job_clearances')->where('clearance_status', 'on-hold'));
        if (($n = (clone $hold)->count()) > 0) {
            $add('bad', 'bi-shield-exclamation', trans_choice('{1} :n clearance is on hold|[2,*] :n clearances are on hold', $n, ['n' => $n]),
                __('Check the reason with the broker. Demurrage and detention are running while cargo waits.'), __('Customs clearance'), url('/operation/customs'), [], $n);
        }


        // 7b. Demurrage date close or passed, cargo still not cleared
        $dem = DB::table('job_clearances as c')->join('jobs as j', 'j.id', '=', 'c.job_id')->whereNull('j.deleted_at')->whereNull('c.clearance_date')
            ->whereNotNull('c.demurrage_date')->whereDate('c.demurrage_date', '<=', $today->copy()->addDays(3));
        $dem = $this->companyScoped($dem, 'c.company_id');
        if (($n = (clone $dem)->count()) > 0) {
            $rows = (clone $dem)->orderBy('c.demurrage_date')->limit(self::ROWS)->get(['j.row_no', 'c.demurrage_date'])->map(function ($r) use ($ago) {
                $d = \Illuminate\Support\Carbon::parse($r->demurrage_date)->startOfDay();

                return ['label' => $r->row_no, 'sub' => ($d->lt(today()) ? __('Demurrage started') : __('Demurrage starts')) . ' ' . $d->format('d-m-Y') . ' (' . $ago($d) . ')'];
            })->all();
            $past = (clone $dem)->whereDate('c.demurrage_date', '<', $today)->exists();
            $add($past ? 'bad' : 'warn', 'bi-currency-exchange', trans_choice('{1} :n shipment is at demurrage risk|[2,*] :n shipments are at demurrage risk', $n, ['n' => $n]),
                __('Cargo is still not cleared and the demurrage date has come or is within 3 days. Clear and move it to avoid daily charges.'), __('Customs clearance'), url('/operation/customs'), $rows, $n);
        }

        // 7c. Free time (line detention) ending or ended, empties not returned
        $notices = ArrivalNotice::with('customer:id,name_en')->where('status', \App\Enums\ArrivalNoticeEnum::NOTIFIED->value)->whereNotNull('eta')->where('free_days', '>', 0)->get()
            ->map(function ($a) use ($today) {
                $a->free_end = $a->eta->copy()->startOfDay()->addDays((int) $a->free_days);

                return $a;
            })->filter(fn($a) => $a->free_end->lte($today->copy()->addDays(2)))->sortBy('free_end');
        if ($notices->count() > 0) {
            $rows = $notices->take(self::ROWS)->map(fn($a) => [
                'label' => $a->row_no . ' · ' . ($a->consignee ?: ($a->customer->name_en ?? '-')),
                'sub' => ($a->free_end->lt($today) ? __('Free time ended') : __('Free time ends')) . ' ' . $a->free_end->format('d-m-Y') . ' (' . $ago($a->free_end) . ')' . ($a->carrier_name ? ' · ' . $a->carrier_name : ''),
            ])->values()->all();
            $add($notices->contains(fn($a) => $a->free_end->lt($today)) ? 'bad' : 'warn', 'bi-box-seam',
                trans_choice('{1} :n shipment: free time is ending|[2,*] :n shipments: free time is ending', $notices->count(), ['n' => $notices->count()]),
                __('Line detention is charged per day after the free time. Collect the delivery order and return the empty containers before it starts.'),
                __('Arrival notices'), url('/operation/arrival-notices'), $rows, $notices->count());
        }

        // 7d. Port may treat cargo as abandoned after 30 days from discharge
        $aband = ArrivalNotice::with('customer:id,name_en')->whereIn('status', [\App\Enums\ArrivalNoticeEnum::DRAFT->value, \App\Enums\ArrivalNoticeEnum::NOTIFIED->value])->whereNotNull('eta')
            ->whereDate('eta', '<=', $today->copy()->subDays(25))->orderBy('eta')->get();
        if ($aband->count() > 0) {
            $rows = $aband->take(self::ROWS)->map(function ($a) use ($today) {
                $d = $a->eta->copy()->startOfDay()->addDays(30);

                return ['label' => $a->row_no . ' · ' . ($a->consignee ?: ($a->customer->name_en ?? '-')), 'sub' => __('Port deadline') . ' ' . $d->format('d-m-Y') . ($d->lt($today) ? ' (' . __('passed') . ')' : '')];
            })->all();
            $add('bad', 'bi-exclamation-diamond', trans_choice('{1} :n shipment is close to the 30-day port deadline|[2,*] :n shipments are close to the 30-day port deadline', $aband->count(), ['n' => $aband->count()]),
                __('Cargo not collected within 30 days of discharge can be auctioned or destroyed by the port, at the consignee\'s cost.'),
                __('Arrival notices'), url('/operation/arrival-notices'), $rows, $aband->count());
        }

        // 7e. Arriving within 5 days but the original documents are not recorded as received
        $docs = DB::table('jobs as j')->leftJoin('job_clearances as c', 'c.job_id', '=', 'j.id')->whereNull('j.deleted_at')->where('j.status', 1)
            ->whereNotNull('j.eta')->whereDate('j.eta', '>=', $today)->whereDate('j.eta', '<=', $today->copy()->addDays(5))
            ->where(fn($w) => $w->whereNull('c.id')->orWhereNull('c.original_doc_received'));
        $docs = $this->companyScoped($docs, 'j.company_id');
        if (($n = (clone $docs)->count()) > 0) {
            $rows = (clone $docs)->orderBy('j.eta')->limit(self::ROWS)->get(['j.row_no', 'j.eta'])->map(fn($r) => ['label' => $r->row_no, 'sub' => 'ETA ' . date('d-m-Y', strtotime($r->eta))])->all();
            $add('warn', 'bi-file-earmark-text', trans_choice('{1} :n arriving shipment: original documents not received|[2,*] :n arriving shipments: original documents not received', $n, ['n' => $n]),
                __('Without the original bill of lading the delivery order cannot be released, which delays clearance and adds storage cost.'),
                __('Customs clearance'), url('/operation/customs'), $rows, $n);
        }

        // 8. Money and sales: owner only
        if ($isOwner) {
            $inv = $this->companyScoped(DB::table('customer_invoices')->whereNull('deleted_at')->where('status', \App\Enums\CustomerInvoiceEnum::APPROVED->value)
                ->whereNotNull('due_date')->whereDate('due_date', '<', $today)->whereRaw('COALESCE(base_paid_amount,0) < base_grand_total'));
            $n = (clone $inv)->count();
            if ($n > 0) {
                $due = (float) (clone $inv)->selectRaw('SUM(base_grand_total - COALESCE(base_paid_amount,0)) as d')->value('d');
                $add('bad', 'bi-cash-coin', trans_choice('{1} :n customer invoice is overdue|[2,*] :n customer invoices are overdue', $n, ['n' => $n]),
                    __(':amount is waiting to be collected.', ['amount' => number_format($due, 2)]), __('Customer invoices'), url('/invoice/customer'), [], $n);
            }
            $qt = $this->companyScoped(DB::table('quotations')->whereNull('deleted_at')->where('status', \App\Enums\QuotationEnum::PENDING->value)
                ->whereNotNull('valid_until')->whereBetween('valid_until', [$today, $today->copy()->addDays(3)]));
            if (($n = (clone $qt)->count()) > 0) {
                $add('warn', 'bi-hourglass-split', trans_choice('{1} :n quotation expires within 3 days|[2,*] :n quotations expire within 3 days', $n, ['n' => $n]),
                    __('Follow up with the customer before the offer lapses.'), __('Quotations'), url('/sales/quotations'), [], $n);
            }
            $rs = $this->companyScoped(DB::table('rate_sheets')->whereNull('deleted_at')->where('is_active', 1)->whereNotNull('valid_to')->whereBetween('valid_to', [$today, $today->copy()->addDays(7)]));
            if (($n = (clone $rs)->count()) > 0) {
                $add('warn', 'bi-tags', trans_choice('{1} :n rate sheet expires within 7 days|[2,*] :n rate sheets expire within 7 days', $n, ['n' => $n]),
                    __('Ask the carrier for renewed rates so quotations keep a valid cost.'), __('Rate sheets'), url('/sales/rate-sheets'), [], $n);
            }

            $group = __('Finance');
            $si = $this->companyScoped(DB::table('supplier_invoices')->whereNull('deleted_at')->where('status', \App\Enums\SupplierInvoiceEnum::APPROVED->value)
                ->whereNotNull('due_date')->whereDate('due_date', '<=', $today->copy()->addDays(3))->whereRaw('COALESCE(base_paid_amount,0) < base_grand_total'));
            if (($n = (clone $si)->count()) > 0) {
                $amount = (float) (clone $si)->selectRaw('SUM(base_grand_total - COALESCE(base_paid_amount,0)) as d')->value('d');
                $over = (clone $si)->whereDate('due_date', '<', $today)->count();
                $add($over ? 'bad' : 'warn', 'bi-wallet2', trans_choice('{1} :n supplier invoice is due or overdue|[2,*] :n supplier invoices are due or overdue', $n, ['n' => $n]),
                    __(':amount to pay within 3 days (:over already overdue). Pay on time to keep supplier credit and avoid late fees.', ['amount' => number_format($amount, 2), 'over' => $over]),
                    __('Supplier invoices'), url('/invoice/supplier'), [], $n);
            }
            $company = authUserCompany();
            if ($company && $company->zatca_registered) {
                $zt = $this->companyScoped(DB::table('customer_invoices')->whereNull('deleted_at')->where('status', \App\Enums\CustomerInvoiceEnum::APPROVED->value)
                    ->whereNull('tax_submitted_at')->where('created_at', '<=', now()->subDay()));
                if (($n = (clone $zt)->count()) > 0) {
                    $add('bad', 'bi-qr-code-scan', trans_choice('{1} :n invoice is not reported to ZATCA|[2,*] :n invoices are not reported to ZATCA', $n, ['n' => $n]),
                        __('Tax invoices must be reported within 24 hours. Late reporting can attract a penalty.'), __('Customer invoices'), url('/invoice/customer'), [], $n);
                }
            }
            $uninv = Job::where('status', \App\Enums\JobEnum::COMPLETED->value)
                ->whereNotExists(fn($w) => $w->select(DB::raw(1))->from('customer_invoices as ci')->whereColumn('ci.job_id', 'jobs.id')->whereNull('ci.deleted_at')->where('ci.status', '!=', \App\Enums\CustomerInvoiceEnum::CANCELLED->value));
            if (($n = (clone $uninv)->count()) > 0) {
                $add('warn', 'bi-receipt', trans_choice('{1} :n completed job has no invoice|[2,*] :n completed jobs have no invoice', $n, ['n' => $n]),
                    __('Work that is finished but not billed is money left on the table. Raise the invoices now.'),
                    __('Open jobs'), url('/operation/jobs'), $jobRows((clone $uninv), 'updated_at'), $n);
            }
            $dr = $this->companyScoped(DB::table('customer_invoices')->whereNull('deleted_at')->where('status', \App\Enums\CustomerInvoiceEnum::DRAFT->value)->where('created_at', '<=', now()->subDays(3)));
            if (($n = (clone $dr)->count()) > 0) {
                $add('info', 'bi-file-earmark-medical', trans_choice('{1} :n customer invoice is still a draft|[2,*] :n customer invoices are still drafts', $n, ['n' => $n]),
                    __('Drafts older than 3 days are not in your books or receivables yet. Approve or delete them.'), __('Customer invoices'), url('/invoice/customer'), [], $n);
            }
            if ($today->day >= 25) {
                $missing = DB::table('users as e')->where('e.company_id', session('company_id') ?: $user->company_id)->where('e.is_employee', 1)->whereNull('e.terminated_at')
                    ->whereExists(fn($w) => $w->select(DB::raw(1))->from('salary_structures as s')->whereColumn('s.employee_id', 'e.id'))
                    ->whereNotExists(fn($w) => $w->select(DB::raw(1))->from('payroll_records as m')->whereColumn('m.employee_id', 'e.id')->where('m.month', $today->month)->where('m.year', $today->year)->where('m.status', '!=', 'cancelled'))->count();
                if ($missing > 0) {
                    $add('warn', 'bi-people', trans_choice('{1} :n employee has no salary generated this month|[2,*] :n employees have no salary generated this month', $missing, ['n' => $missing]),
                        __('Month end is near. Generate and pay salaries on time.'), __('Payroll Runs'), url('/payroll/runs'), [], $missing);
                }
            }
            $group = __('Sales');
            $enq = $this->companyScoped(DB::table('enquiries')->whereNull('deleted_at')->where('status', \App\Enums\EnquiryEnum::PENDING->value)->where('created_at', '<=', now()->subDays(2)));
            if (($n = (clone $enq)->count()) > 0) {
                $add('warn', 'bi-chat-left-dots', trans_choice('{1} :n enquiry has waited over 2 days|[2,*] :n enquiries have waited over 2 days', $n, ['n' => $n]),
                    __('A quick reply wins the job. Send the quotation or call the customer.'), __('Enquiries'), url('/sales/enquiries'), [], $n);
            }
            $qf = $this->companyScoped(DB::table('quotations')->whereNull('deleted_at')->where('status', \App\Enums\QuotationEnum::PENDING->value)->where('created_at', '<=', now()->subDays(7)));
            if (($n = (clone $qf)->count()) > 0) {
                $add('info', 'bi-telephone-outbound', trans_choice('{1} :n quotation has had no answer for 7 days|[2,*] :n quotations have had no answer for 7 days', $n, ['n' => $n]),
                    __('Follow up. Customers often just need a reminder.'), __('Quotations'), url('/sales/quotations'), [], $n);
            }
            $group = __('Account');
            if ($company && $company->subscription_expiry_date) {
                $exp = \Illuminate\Support\Carbon::parse($company->subscription_expiry_date)->startOfDay();
                if ($exp->lte($today->copy()->addDays(7))) {
                    $add($exp->lt($today) ? 'bad' : 'warn', 'bi-credit-card-2-front', $exp->lt($today) ? __('Your subscription has expired') : __('Your subscription expires :when', ['when' => $ago($exp)]),
                        __('Renew to keep using Flikma without interruption.'), __('Billing'), url('/billing'), [], 1);
                }
            }
        }

        // Good news: completed jobs this week
        $done = Job::where('status', \App\Enums\JobEnum::COMPLETED->value)->where('updated_at', '>=', $today->copy()->startOfWeek())->count();
        if ($done > 0 && count($items) < 6) {
            $add('good', 'bi-check2-circle', trans_choice('{1} :n job completed this week|[2,*] :n jobs completed this week', $done, ['n' => $done]), __('Good work. Keep the momentum.'));
        }

        $attention = collect($items)->whereIn('tone', ['bad', 'warn'])->sum(fn($i) => max(1, (int) $i['count']));
        $order = ['bad' => 0, 'warn' => 1, 'info' => 2, 'good' => 3];
        usort($items, fn($a, $b) => $order[$a['tone']] <=> $order[$b['tone']]);

        return [
            'name' => explode(' ', trim($user->name))[0] ?? '',
            'items' => $items,
            'stats' => [
                ['label' => __('Arriving today'), 'value' => $arrivingToday, 'icon' => 'bi-box-arrow-in-down', 'tone' => 'info'],
                ['label' => __('Departing today'), 'value' => $departingToday, 'icon' => 'bi-box-arrow-up-right', 'tone' => 'info'],
                ['label' => __('Needs attention'), 'value' => $attention, 'icon' => 'bi-bell', 'tone' => $attention ? 'bad' : 'good'],
            ],
            'count' => $attention + $arrivingToday + $departingToday,
            'actionable' => collect($items)->whereIn('tone', ['bad', 'warn', 'info'])->count(),
        ];
    }

    /** Tables queried without a model still have to stay inside the user's company. */
    private function companyScoped($query, string $column = 'company_id')
    {
        $company = session('company_id') ?: auth()->user()?->company_id;

        return $company ? $query->where($column, $company) : $query;
    }
}
