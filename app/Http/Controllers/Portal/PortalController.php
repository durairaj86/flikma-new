<?php

namespace App\Http\Controllers\Portal;

use App\Enums\CustomerInvoiceEnum;
use App\Enums\JobEnum;
use App\Enums\QuotationEnum;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Crm\QuotationResponseController;
use App\Http\Controllers\Finance\Invoice\CustomerInvoiceController;
use App\Http\Controllers\Job\TrackingController;
use App\Models\Customer\Customer;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Job\Job;
use App\Models\Portal\PortalUser;
use App\Models\Quotation\Quotation;
use App\Services\Customer\CustomerAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * The customer portal pages. The PortalCustomer middleware has set the request's company; on top of that every
 * query here is filtered by the signed-in user's customer, and a record of another customer is a plain 404.
 */
class PortalController extends Controller
{
    private function user(): PortalUser
    {
        return Auth::guard('portal')->user();
    }

    private function customer(): Customer
    {
        return $this->user()->customer;
    }

    /** Jobs of this customer only (shipments). */
    private function jobs()
    {
        return Job::where('customer_id', $this->customer()->id)->where('company_id', $this->user()->company_id)
            ->where('status', '!=', JobEnum::TRASHED->value);
    }

    private function invoiceQuery()
    {
        return CustomerInvoice::where('customer_id', $this->customer()->id)->where('company_id', $this->user()->company_id)
            ->where('status', CustomerInvoiceEnum::APPROVED->value);
    }

    private function pendingQuotations()
    {
        return Quotation::where('customer_id', $this->customer()->id)->where('company_id', $this->user()->company_id)
            ->where('status', QuotationEnum::PENDING->value)->whereDate('valid_until', '>=', Carbon::today());
    }

    private function base(array $data = []): array
    {
        $company = DB::table('companies')->where('id', $this->user()->company_id)->first(['name', 'name_ar', 'logo', 'email', 'phone', 'base_currency']);

        return $data + ['portalUser' => $this->user(), 'customer' => $this->customer(), 'company' => $company,
                'currency' => strtoupper($company->base_currency ?? 'SAR')];
    }

    public function dashboard()
    {
        $account = new CustomerAccount();
        $open = collect($account->openInvoices($this->customer()));

        return view('portal.dashboard', $this->base([
            'inProgress' => $this->jobs()->where('status', JobEnum::PENDING->value)->count(),
            'balanceDue' => $account->balanceDue($this->customer()),
            'overdue' => round($open->where('days', '>', 0)->sum('balance_base'), 2),
            'quotations' => $this->pendingQuotations()->count(),
            'recent' => $this->jobs()->orderByDesc('id')->limit(5)->get(),
        ]));
    }

    public function shipments(Request $request)
    {
        $jobs = $this->jobs()
            ->when($request->input('show') !== 'all', fn($q) => $q->where('status', JobEnum::PENDING->value))
            ->when(trim((string) $request->input('q')), fn($q, $s) => $q->where(fn($w) => $w->where('row_no', 'like', "%{$s}%")
                ->orWhere('hbl_number', 'like', "%{$s}%")->orWhere('awb_number', 'like', "%{$s}%")->orWhere('container_no', 'like', "%{$s}%")))
            ->orderByDesc('id')->paginate(20)->withQueryString();

        return view('portal.shipments', $this->base(['jobs' => $jobs]));
    }

    public function shipment($id)
    {
        $job = $this->jobs()->findOrFail($id);

        return view('portal.shipment', $this->base(['job' => $job, 'steps' => TrackingController::steps($job)]));
    }

    public function invoices()
    {
        $account = new CustomerAccount();
        $open = collect($account->openInvoices($this->customer()))->keyBy('id');
        $invoices = $this->invoiceQuery()->orderByDesc('invoice_date')->orderByDesc('id')->paginate(20);

        return view('portal.invoices', $this->base(['invoices' => $invoices, 'open' => $open]));
    }

    /** The same invoice print the office uses (opens in its own tab, printable / save as PDF). */
    public function invoicePrint($id)
    {
        $invoice = $this->invoiceQuery()->findOrFail($id);

        return app(CustomerInvoiceController::class)->print($invoice->id);
    }

    public function statement(Request $request)
    {
        $from = $request->filled('from') ? Carbon::createFromFormat('Y-m-d', $request->input('from'))->startOfDay() : Carbon::today()->subMonths(6)->startOfMonth();
        $to = $request->filled('to') ? Carbon::createFromFormat('Y-m-d', $request->input('to'))->endOfDay() : Carbon::today()->endOfDay();
        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return view('portal.statement', $this->base([
            'statement' => (new CustomerAccount())->statement($this->customer(), $from, $to),
            'from' => $from, 'to' => $to,
        ]));
    }

    public function quotations()
    {
        $quotations = $this->pendingQuotations()->withSum('charges as total', 'local_amount')->orderByDesc('id')->get();

        return view('portal.quotations', $this->base(['quotations' => $quotations]));
    }

    /** Answer a quotation: the same accept / reject page as the link in the quotation email. */
    public function quotation($id)
    {
        $quotation = $this->pendingQuotations()->findOrFail($id);

        return redirect()->to(QuotationResponseController::urlFor($quotation) . (app()->getLocale() === 'ar' ? '?lang=ar' : ''));
    }
}
