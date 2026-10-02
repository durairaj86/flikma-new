<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Billing\Plan;
use App\Models\Billing\Subscription;
use App\Models\ContactMessage;
use App\Models\Master\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(): View
    {
        // Read the company fresh rather than through Company::companies(), which
        // caches forever — a stale row would report the wrong plan/trial state.
        $company = Company::currentFresh();

        $plans = array_values(Plan::selectable());

        $currentPlan = $company?->package_id ? Plan::find($company->package_id) : null;

        $payments = Subscription::where('company_id', companyId())
            ->latest()
            ->take(20)
            ->get();

        // Plans come from config, not a table, so bind the Plan object onto each
        // row to satisfy the view's $payment->package?->name lookup.
        foreach ($payments as $payment) {
            $payment->package = Plan::find($payment->package_id);
        }

        $isInTrial = $company ? (bool) $company->is_in_trial : false;
        $planLabel = $company ? planLabelFor($company) : __('Free');
        $subscriptionExpired = $company ? $company->subscription_expired : false;
        $subscriptionExpiry = $company?->subscription_expiry_date;

        return view('billing.index', compact(
            'company', 'plans', 'payments', 'currentPlan', 'isInTrial',
            'planLabel', 'subscriptionExpired', 'subscriptionExpiry'
        ));
    }

    public function contact(Request $request, string $package): RedirectResponse
    {
        $plan = Plan::find($package);

        abort_unless($plan && $plan->id > 1 && $plan->is_active, 404);

        $validated = $request->validate([
            'billing_cycle' => 'required|in:monthly,yearly',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'message' => 'nullable|string|max:2000',
        ]);

        $company = Company::currentFresh();
        $amount = $validated['billing_cycle'] === 'yearly'
            ? $plan->yearly_price
            : $plan->monthly_price;

        $cycle = $validated['billing_cycle'];
        $companyName = $company?->name ?? 'Not provided';

        $message = trim((string) ($validated['message'] ?? ''));
        if ($message === '') {
            $message = __(
                'Company :company would like to subscribe to the :plan plan (SAR :amount/:cycle).',
                [
                    'company' => $companyName,
                    'plan' => $plan->label,
                    'amount' => number_format($amount, 2),
                    'cycle' => $cycle,
                ]
            );
        }

        ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'company' => $companyName,
            'phone' => $validated['phone'] ?: 'Not provided',
            'interest' => 'Upgrade request: ' . $plan->label . ' (' . $cycle . ')',
            'message' => $message,
        ]);

        return redirect()->route('billing.index')->with(
            'success',
            __('Your upgrade request has been received. One of our executives will get in touch with you soon.')
        );
    }
}
