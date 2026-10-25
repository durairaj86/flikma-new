<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\ActivityLog;
use App\Models\Billing\Plan;
use App\Models\Master\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    /** One company: details, plan and access, users, latest jobs and invoices (read-only). */
    public function show(Company $company)
    {
        $users = User::withoutGlobalScopes()->where('company_id', $company->id)
            ->orderByRaw('role = 1 desc')->orderBy('name')
            ->get(['id', 'name', 'email', 'role', 'is_employee', 'login_enabled', 'last_login', 'email_verified_at', 'terminated_at']);
        $counts = [
            'users' => $users->count(),
            'employees' => $users->where('is_employee', true)->count(),
            'jobs' => DB::table('jobs')->where('company_id', $company->id)->whereNull('deleted_at')->count(),
            'invoices' => DB::table('customer_invoices')->where('company_id', $company->id)->whereNull('deleted_at')->count(),
            'customers' => DB::table('customers')->where('company_id', $company->id)->whereNull('deleted_at')->count(),
            'suppliers' => DB::table('suppliers')->where('company_id', $company->id)->whereNull('deleted_at')->count(),
        ];
        $invoices = DB::table('customer_invoices as i')->leftJoin('customers as c', 'c.id', '=', 'i.customer_id')
            ->where('i.company_id', $company->id)->whereNull('i.deleted_at')->latest('i.id')->take(10)
            ->get(['i.id', 'i.row_no', 'i.invoice_date', 'i.grand_total', 'i.status', 'i.currency', 'c.name_en as customer']);
        $jobs = DB::table('jobs as j')->leftJoin('customers as c', 'c.id', '=', 'j.customer_id')
            ->where('j.company_id', $company->id)->whereNull('j.deleted_at')->latest('j.id')->take(10)
            ->get(['j.id', 'j.row_no', 'j.posted_at', 'j.status', 'c.name_en as customer']);
        $plan = Plan::find($company->package_id);

        return view('admin.companies.show', compact('company', 'users', 'counts', 'invoices', 'jobs', 'plan'));
    }

    /**
     * Open the app as this company: signs in as its owner (role 1), or the chosen user, in this browser.
     * The app shows a bar with "Return to admin" (leaveImpersonation) while this lasts.
     */
    public function impersonate(Request $request, Company $company)
    {
        $user = User::withoutGlobalScopes()->where('company_id', $company->id)
            ->when($request->integer('user'), fn ($q, $id) => $q->where('id', $id), fn ($q) => $q->orderByRaw('role = 1 desc')->orderBy('id'))
            ->first();
        if (!$user) {
            return back()->with('error', 'This company has no user to sign in as.');
        }

        ActivityLog::log('impersonated', 'company', $company->id, "Logged in as {$user->email} ({$company->name})");
        // set first: the login listener leaves last_login alone for an impersonated sign-in
        session(['impersonator_id' => Auth::id(), 'impersonating' => true]);
        Auth::login($user);
        session(['company_id' => $company->id]);
        Company::forgetCached($company->id);

        return redirect()->route('dashboard');
    }

    /** Back from "Login as": sign in as the admin again. */
    public function leaveImpersonation()
    {
        $admin = ($id = session('impersonator_id')) ? User::withoutGlobalScopes()->find($id) : null;
        if (!$admin || !$admin->is_admin) {
            Auth::logout();
            session()->forget(['impersonator_id', 'impersonating']);

            return redirect()->route('admin.login');
        }

        $companyId = session('company_id');
        Auth::login($admin);
        session(['company_id' => $admin->company_id]);
        session()->forget(['impersonator_id', 'impersonating']);
        Company::forgetCached($companyId);

        return redirect()->route('admin.companies.index')->with('success', 'Switched back to the admin panel.');
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
