<?php

namespace App\Http\Controllers\Job;

use App\Enums\JobEnum;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Finance\Invoice\CustomerInvoiceController;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Job\CostSheet;
use App\Models\Job\Job;
use App\Services\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * The job's cost sheet (Settings › Finance › Cost sheet on): the same Sale | Cost | GP grid as the customer invoice, with a
 * "Bill to customer" tick per line. "Create final invoice" makes the customer invoice from the ticked lines and locks the sheet.
 * Rights: finances.cost-sheets (Invoices › Cost Sheet); creating the invoice also needs Customer Invoice create.
 */
class CostSheetController extends Controller
{
    public const TABS = ['all' => 'All', 'open' => 'Open', 'invoiced' => 'Invoiced'];

    /** Invoices › Cost Sheet: every job's cost sheet, the invoice number under the sheet once it is invoiced. */
    public function index(Request $request)
    {
        abort_unless(CostSheet::enabled(), 404);
        $tab = array_key_exists($request->query('tab'), self::TABS) ? $request->query('tab') : 'all';
        $q = trim((string) $request->query('q', ''));
        $base = fn () => CostSheet::query()->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('row_no', 'like', "%$q%")
            ->orWhereHas('job', fn ($j) => $j->where('row_no', 'like', "%$q%")->orWhereHas('customer', fn ($c) => $c->where('name_en', 'like', "%$q%")))
            ->orWhereHas('invoice', fn ($i) => $i->where('row_no', 'like', "%$q%"))));
        $sheets = $base()->with(['job:id,row_no,customer_id,status', 'job.customer:id,name_en,row_no', 'invoice:id,row_no,status'])
            ->when($tab === 'open', fn ($w) => $w->where('status', CostSheet::OPEN))
            ->when($tab === 'invoiced', fn ($w) => $w->where('status', CostSheet::INVOICED))
            ->orderByDesc('id')->limit(500)->get();
        $counts = [
            'all' => $base()->count(),
            'open' => $base()->where('status', CostSheet::OPEN)->count(),
            'invoiced' => $base()->where('status', CostSheet::INVOICED)->count(),
        ];

        return view('modules.finance.cost_sheet.list', compact('sheets', 'counts', 'tab', 'q'));
    }

    /** New Cost Sheet: pick the job (open or completed jobs that have no cost sheet yet). */
    public function picker()
    {
        abort_unless(CostSheet::enabled(), 404);
        $jobs = Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')
            ->whereIn('status', [JobEnum::PENDING->value, JobEnum::COMPLETED->value])
            ->whereNotIn('id', CostSheet::query()->select('job_id'))
            ->orderByDesc('id')->get();

        return view('modules.finance.cost_sheet.picker', compact('jobs'));
    }

    /** Delete an open cost sheet (an invoiced one is locked: its invoice must be deleted or credited first). */
    public function destroy($id)
    {
        abort_unless(CostSheet::enabled(), 404);
        $sheet = CostSheet::findOrFail($id);
        if ($sheet->locked()) {
            return response()->json(['status' => 'error', 'message' => __('This cost sheet is invoiced and locked. Delete the draft invoice or raise a full credit note to change it.')], 422);
        }
        $sheet->delete();

        return response()->json(['status' => 'success', 'message' => __('Cost sheet deleted')]);
    }

    private function job($id): Job
    {
        return Job::with('customer:id,name_en,currency,credit_days')->findOrFail($id);
    }

    private function sheetFor(Job $job): CostSheet
    {
        return CostSheet::firstOrNew(['job_id' => $job->id], [
            'company_id' => companyId(), 'currency' => $job->customer?->currency ?: 'SAR', 'currency_rate' => 1, 'lines' => [], 'status' => CostSheet::OPEN,
        ]);
    }

    public function form(Request $request, $id)
    {
        abort_unless(CostSheet::enabled(), 404);
        $job = $this->job($id);
        $sheet = $this->sheetFor($job);
        $request->merge(['jobId' => encodeId($job->id)]);
        $data = app(CustomerInvoiceController::class)->gpFormData($request);
        if (!$data['jobs']->contains('id', $job->id)) {
            $data['jobs']->push($job);
        }
        $data['selectedJob'] = $job->id;
        $lines = collect($sheet->lines ?? []);
        $data['units'] = \App\Models\Master\Unit::units($lines->pluck('unit_id')->filter()->all());

        // invoices this job already has that were not made from the cost sheet (e.g. created while the cost sheet was off)
        $otherInvoices = CustomerInvoice::where('job_id', $job->id)->where('status', '!=', \App\Enums\CustomerInvoiceEnum::CANCELLED->value)
            ->when($sheet->customer_invoice_id, fn ($q) => $q->where('id', '!=', $sheet->customer_invoice_id))
            ->pluck('row_no')->filter()->values();

        return view('modules.finance.customer-invoice._gp-form', $data + [
            'inModal' => true, 'costSheet' => $sheet, 'editLines' => $lines->values(), 'otherInvoices' => $otherInvoices,
            // a new sheet needs the Create right, changing a saved one the Edit right
            'canEdit' => userCan('finances.cost-sheets', $sheet->exists ? 'edit' : 'create') && !$sheet->locked() && (int) $job->status !== JobEnum::CANCELLED->value,
            'canInvoice' => userCan('finances.customer-invoices', 'create'),
        ]);
    }

    /** Save: a new sheet posts to /create with job_id (Create right), a saved one to /job/{id} (Edit right). */
    public function save(Request $request, $id = null)
    {
        abort_unless(CostSheet::enabled(), 404);
        $job = $this->job($id ?? $request->input('job_id'));
        $sheet = $this->sheetFor($job);
        if ($sheet->exists && $id === null) {
            return response()->json(['status' => 'error', 'message' => __('This job already has a cost sheet.')], 422);
        }
        if (!$sheet->exists && $id !== null && !userCan('finances.cost-sheets', 'create')) {
            abort(403);
        }
        if ($sheet->locked()) {
            return response()->json(['status' => 'error', 'message' => __('This cost sheet is invoiced and locked. Delete the draft invoice or raise a full credit note to change it.')], 422);
        }
        if ((int) $job->status === JobEnum::CANCELLED->value) {
            return response()->json(['status' => 'error', 'message' => __('This job is cancelled.')], 422);
        }

        $data = $request->validate([
            'currency' => 'required|exists:currencies,code',
            'currency_rate' => 'required|numeric|gt:0',
            'terms' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:1|max:200',
            'lines.*.description_id' => ['required', 'integer', fn ($a, $v, $fail) => DB::table('descriptions')->where('id', $v)
                ->where(fn ($q) => $q->where('company_id', companyId())->orWhereNull('company_id'))->exists() ?: $fail(__('Choose a description from the list.'))],
            'lines.*.unit_id' => 'required|integer',
            'lines.*.comment' => 'nullable|string|max:500',
            'lines.*.bill' => 'required|boolean',
            'lines.*.s_qty' => 'nullable|numeric|min:0',
            'lines.*.s_price' => 'nullable|numeric|min:0',
            'lines.*.tax' => 'nullable|string|max:40',
            'lines.*.c_qty' => 'nullable|numeric|min:0',
            'lines.*.c_price' => 'nullable|numeric|min:0',
            'lines.*.c_tax' => 'nullable|string|max:40',
            'lines.*.source_type' => 'nullable|in:quotation,supplier_invoice',
            'lines.*.source_id' => 'nullable|integer',
            'lines.*.source_sub_id' => 'nullable|integer',
            'lines.*.source_label' => 'nullable|string|max:100',
        ], [], ['lines' => __('Lines')]);

        $lines = [];
        foreach (array_values($data['lines']) as $n => $l) {
            $bill = (bool) $l['bill'];
            // a billed line needs its sale price and quantity; a cost-only line needs neither
            if ($bill && (!((float) ($l['s_price'] ?? 0) > 0) || !((float) ($l['s_qty'] ?? 0) > 0))) {
                throw \Illuminate\Validation\ValidationException::withMessages(['lines' => __('Line :n: enter the sale quantity and price, or untick "Bill to customer".', ['n' => $n + 1])]);
            }
            if ($bill && vatNeedsExemption($l['tax'] ?? null)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['lines' => __('Line :n: choose the VAT exemption reason for this zero rated / exempt / out of scope line.', ['n' => $n + 1])]);
            }
            $lines[] = [
                'description_id' => (int) $l['description_id'], 'unit_id' => (int) $l['unit_id'], 'comment' => $l['comment'] ?? null, 'bill' => $bill,
                's_qty' => (float) ($l['s_qty'] ?? 0), 's_price' => round((float) ($l['s_price'] ?? 0), 4), 'tax' => $l['tax'] ?? null,
                'c_qty' => (float) ($l['c_qty'] ?? 0), 'c_price' => round((float) ($l['c_price'] ?? 0), 4), 'c_tax' => $l['c_tax'] ?? null,
                'source_type' => $l['source_type'] ?? null, 'source_id' => $l['source_id'] ?? null, 'source_sub_id' => $l['source_sub_id'] ?? null,
                'source_label' => $l['source_label'] ?? null,
            ];
        }
        $totals = CostSheet::totals($lines, (float) $data['currency_rate']);

        $sheet->fill([
            'company_id' => companyId(), 'currency' => $data['currency'], 'currency_rate' => $data['currency_rate'], 'terms' => $data['terms'] ?? null,
            'lines' => $lines, 'sale_total' => $totals['sale'], 'cost_total' => $totals['cost'], 'updated_by' => auth()->id(),
        ]);
        $sheet->created_by ??= auth()->id();
        DB::transaction(function () use ($sheet) {
            if (!$sheet->row_no) {
                $prefix = 'CS/' . date('y') . '/';
                $sheet->row_no = $prefix . sprintf('%04d', DocumentNumber::next(CostSheet::class, $prefix));
            }
            $sheet->save();
        });

        return response()->json(['status' => 'success', 'message' => __('Cost sheet saved'), 'id' => $sheet->id, 'row_no' => $sheet->row_no]);
    }

    /** "Create final invoice": a draft customer invoice from the ticked lines, through the invoice's own save (same checks); then the sheet locks. */
    public function invoice($id)
    {
        abort_unless(CostSheet::enabled(), 404);
        abort_unless(userCan('finances.customer-invoices', 'create'), 403);
        $job = $this->job($id);
        $sheet = CostSheet::where('job_id', $job->id)->first();
        if (!$sheet) {
            return response()->json(['status' => 'error', 'message' => __('Save the cost sheet first.')], 422);
        }
        if ($sheet->locked()) {
            return response()->json(['status' => 'error', 'message' => __('This cost sheet is already invoiced.')], 422);
        }
        if (!$sheet->billableLines()) {
            return response()->json(['status' => 'error', 'message' => __('No line is ticked "Bill to customer".')], 422);
        }
        if (!$job->customer) {
            return response()->json(['status' => 'error', 'message' => __('This job has no customer.')], 422);
        }

        $payload = [
            'job_id' => $job->id, 'customer' => encodeId($job->customer->id),
            'invoice_date' => now()->format('d-m-Y'), 'due_date' => now()->addDays((int) ($job->customer->credit_days ?: 30))->format('d-m-Y'),
            'terms' => $sheet->terms,
        ] + $sheet->invoiceLines();

        $request = Request::create('/invoice/customer/create', 'POST', $payload);
        $request->attributes->set('cost_sheet_id', $sheet->id);   // set by the server only: the one way past "invoices come from the cost sheet"
        $request->setUserResolver(fn () => auth()->user());

        return DB::transaction(function () use ($request, $sheet) {
            $res = app(CustomerInvoiceController::class)->store($request);
            $body = $res->getData(true);
            if (($body['status'] ?? null) !== 'success' || empty($body['customer_id'])) {
                return $res;
            }
            $sheet->update(['status' => CostSheet::INVOICED, 'customer_invoice_id' => $body['customer_id'], 'invoiced_at' => now(), 'updated_by' => auth()->id()]);
            $invoice = CustomerInvoice::find($body['customer_id']);

            return response()->json(['status' => 'success', 'message' => __('Draft invoice :no created from the cost sheet.', ['no' => $invoice?->row_no]),
                'invoice_id' => $body['customer_id'], 'invoice_no' => $invoice?->row_no]);
        });
    }
}
