<?php

namespace App\Http\Controllers\Finance\Credit;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Finance\Credit\CreditLimitRequest;
use App\Services\Finance\CreditCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Finances › Credit Approval: requests to change a customer's credit limit / credit days. Anyone with Create may ask; a user
 * with Approve decides, and approving writes the new limit / days on the customer. Requests also come from the customer form
 * when someone without Approve changes the credit fields (CustomerController).
 */
class CreditApprovalController extends Controller
{
    public function index(Request $request)
    {
        $tab = in_array($request->query('tab'), array_keys(CreditLimitRequest::STATUSES), true) ? $request->query('tab') : 'pending';
        $requests = CreditLimitRequest::with(['customer:id,name_en,row_no', 'requestedBy:id,name', 'decidedBy:id,name'])
            ->where('status', $tab)->orderByDesc('id')->limit(500)->get();
        $counts = CreditLimitRequest::select('status', DB::raw('COUNT(*) n'))->groupBy('status')->pluck('n', 'status');
        $check = app(CreditCheck::class);
        $outstanding = $requests->pluck('customer_id')->unique()->mapWithKeys(fn ($id) => [$id => $check->outstanding((int) $id)]);

        return view('modules.finance.credit_approval.list', compact('requests', 'counts', 'tab', 'outstanding'));
    }

    /** The new-request form; ?customer=<encoded id> picks the customer. */
    public function modal(Request $request)
    {
        $customerId = $request->filled('customer') ? decodeId((string) $request->query('customer')) : null;

        return view('modules.finance.credit_approval.form', ['customerId' => $customerId]);
    }

    /** Current limit / days, receivable and overdue invoices of a customer, for the form. */
    public function customer($id)
    {
        $customer = Customer::findOrFail(decodeId((string) $id));
        $check = app(CreditCheck::class);

        return response()->json([
            'limit' => (float) $customer->credit_limit, 'days' => (int) $customer->credit_days,
            'outstanding' => $check->outstanding($customer->id),
            'overdue' => count($check->overdue($customer->id, (int) $customer->credit_days)),
            'pending' => CreditLimitRequest::where('customer_id', $customer->id)->where('status', 'pending')->exists(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer' => 'required|string',
            'requested_limit' => 'nullable|numeric|min:0|max:9999999999999',
            'requested_days' => 'nullable|integer|min:0|max:3650',
            'reason' => 'required|string|max:1000',
        ], [], ['customer' => __('Customer'), 'requested_limit' => __('Credit Limit'), 'requested_days' => __('Credit Days'), 'reason' => __('Reason')]);
        $customer = Customer::findOrFail(decodeId($data['customer']));
        if (($data['requested_limit'] ?? null) === null && ($data['requested_days'] ?? null) === null) {
            throw ValidationException::withMessages(['requested_limit' => __('Enter the new credit limit, the new credit days, or both.')]);
        }

        return response()->json(['status' => 'success', 'message' => __('The request was sent for approval.'),
            'id' => self::raise($customer, $data['requested_limit'] ?? null, $data['requested_days'] ?? null, $data['reason'])->id]);
    }

    /** One pending request per customer: a new one replaces what the earlier one asked. */
    public static function raise(Customer $customer, $limit, $days, string $reason): CreditLimitRequest
    {
        $req = CreditLimitRequest::firstOrNew(['customer_id' => $customer->id, 'status' => 'pending']);
        $req->fill([
            'company_id' => companyId(),
            'current_limit' => $customer->credit_limit !== null && $customer->credit_limit !== '' ? (float) $customer->credit_limit : null,
            'current_days' => $customer->credit_days !== null && $customer->credit_days !== '' ? (int) $customer->credit_days : null,
            'requested_limit' => $limit !== null && $limit !== '' ? (float) $limit : ($customer->credit_limit !== '' ? $customer->credit_limit : null),
            'requested_days' => $days !== null && $days !== '' ? (int) $days : ($customer->credit_days !== '' ? $customer->credit_days : null),
            'reason' => $reason,
            'requested_by' => Auth::id(),
        ]);
        $req->save();

        return $req;
    }

    public function approve(Request $request, $id)
    {
        return $this->decide($request, $id, 'approved');
    }

    public function disapprove(Request $request, $id)
    {
        return $this->decide($request, $id, 'rejected');
    }

    private function decide(Request $request, $id, string $status)
    {
        $note = $request->validate(['note' => ($status === 'rejected' ? 'required' : 'nullable') . '|string|max:1000'], [], ['note' => __('Reason')])['note'] ?? null;

        return DB::transaction(function () use ($id, $status, $note) {
            $req = CreditLimitRequest::lockForUpdate()->findOrFail($id);
            if ($req->status !== 'pending') {
                return response()->json(['status' => 'error', 'message' => __('This request is already decided.')], 422);
            }
            if ($status === 'approved') {
                $customer = Customer::findOrFail($req->customer_id);
                $customer->credit_limit = $req->requested_limit;
                $customer->credit_days = $req->requested_days;
                $customer->save();
            }
            $req->update(['status' => $status, 'decided_by' => Auth::id(), 'decided_at' => now(), 'decision_note' => $note]);

            return response()->json(['status' => 'success', 'message' => $status === 'approved'
                ? __('Approved: the customer\'s credit limit and days are updated.') : __('The request was rejected.')]);
        });
    }
}
