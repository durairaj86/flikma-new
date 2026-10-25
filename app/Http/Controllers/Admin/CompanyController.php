<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Billing\Plan;
use App\Models\Master\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Access groups (same idea as fatoora): an end date means a paying customer, none means a trial customer.
     * paid = end date not passed; paid_expired = end date passed; trial = no end date, trial running; trial_ended = no end date, trial over.
     */
    private const SQL_PAID = '(companies.subscription_expiry_date IS NOT NULL AND companies.subscription_expiry_date >= CURDATE())';
    private const SQL_PAID_EXPIRED = '(companies.subscription_expiry_date IS NOT NULL AND companies.subscription_expiry_date < CURDATE())';
    private const SQL_TRIAL = '(companies.subscription_expiry_date IS NULL AND companies.trial_ends_at >= CURDATE())';
    private const SQL_TRIAL_ENDED = '(companies.subscription_expiry_date IS NULL AND (companies.trial_ends_at IS NULL OR companies.trial_ends_at < CURDATE()))';

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'status', 'package', 'access', 'country']);

        $stats = [
            'total' => Company::count(),
            'paid' => Company::whereRaw(self::SQL_PAID)->count(),
            'paid_expired' => Company::whereRaw(self::SQL_PAID_EXPIRED)->count(),
            'trial' => Company::whereRaw(self::SQL_TRIAL)->count(),
            'trial_ended' => Company::whereRaw(self::SQL_TRIAL_ENDED)->count(),
            'suspended' => Company::where('subscription_status', 'suspended')->count(),
        ];

        $count = fn (string $table, ?callable $extra = null) => function ($q) use ($table, $extra) {
            $q->from($table)->whereColumn("$table.company_id", 'companies.id')->selectRaw('count(*)');
            if ($extra) $extra($q);
        };

        $companies = Company::select('companies.*')
            ->selectSub($count('jobs', fn ($q) => $q->whereNull('jobs.deleted_at')), 'jobs_count')
            ->selectSub($count('customer_invoices', fn ($q) => $q->whereNull('customer_invoices.deleted_at')), 'invoices_count')
            ->selectSub($count('customers', fn ($q) => $q->whereNull('customers.deleted_at')), 'customers_count')
            ->selectSub($count('users'), 'users_count')
            ->selectSub($count('users', fn ($q) => $q->where('users.is_employee', true)), 'employees_count')
            ->selectSub(fn ($q) => $q->from('users')->whereColumn('users.company_id', 'companies.id')->selectRaw('MAX(users.last_login)'), 'last_login_at')
            ->when(trim($filters['q'] ?? '') !== '', function ($q) use ($filters) {
                $term = '%' . trim($filters['q']) . '%';
                $q->where(fn ($w) => $w->where('companies.name', 'like', $term)->orWhere('companies.email', 'like', $term)
                    ->orWhere('companies.phone', 'like', $term)->orWhere('companies.vat_number', 'like', $term)
                    ->orWhere('companies.city', 'like', $term)->orWhere('companies.id', trim($filters['q'])));
            })
            ->when($filters['status'] ?? null, fn ($q, $v) => $v === 'inactive' ? $q->whereNull('subscription_status') : $q->where('subscription_status', $v))
            ->when($filters['package'] ?? null, fn ($q, $v) => $v === 'none' ? $q->whereNull('package_id') : $q->where('package_id', $v))
            ->when($filters['country'] ?? null, fn ($q, $v) => $q->where('country', $v))
            ->when($filters['access'] ?? null, fn ($q, $v) => match ($v) {
                'paid' => $q->whereRaw(self::SQL_PAID),
                'paid_expired' => $q->whereRaw(self::SQL_PAID_EXPIRED),
                'trial' => $q->whereRaw(self::SQL_TRIAL),
                'trial_ended' => $q->whereRaw(self::SQL_TRIAL_ENDED),
                default => $q,
            })
            ->latest('companies.created_at')
            ->paginate(25)
            ->withQueryString();

        $packages = collect(Plan::all())->map(fn ($p) => ['id' => $p->id, 'name' => $p->label])->values();
        $countries = Company::whereNotNull('country')->where('country', '!=', '')->distinct()->orderBy('country')->pluck('country');

        return view('admin.companies.index', compact('companies', 'stats', 'filters', 'packages', 'countries'));
    }

    /** [label, state] for a company's plan badge: paid / lapsed / trial / trial_ended. */
    public static function planBadge(Company $company): array
    {
        $plan = Plan::find($company->package_id)?->label ?? 'No plan';
        $end = $company->subscription_expiry_date;

        if ($end) {
            return $end->isPast() && !$end->isToday() ? [$plan . ' (Expired)', 'lapsed'] : [$plan, 'paid'];
        }

        return $company->trial_ends_at && !$company->trial_ends_at->isPast() || $company->trial_ends_at?->isToday()
            ? ['Trial', 'trial']
            : ['Trial (Ended)', 'trial_ended'];
    }
}
