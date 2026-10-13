<?php

namespace App\Http\Controllers\Finance\Reconciliation;

use App\Http\Controllers\Controller;
use App\Models\Finance\Account\Account;
use App\Models\Finance\BankReconciliation\BankReconciliation;
use App\Services\Finance\BankReconciliationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Bank reconciliation: a list of statements, and one screen to tick what is on the statement until the difference is 0. */
class ReconciliationController extends Controller
{
    public function __construct(private BankReconciliationService $service)
    {
    }

    public function index()
    {
        $accounts = $this->service->accounts();
        $balances = $accounts->mapWithKeys(fn ($a) => [$a->id => $this->service->bookBalance($a->id, now()->toDateString())]);
        $reconciliations = BankReconciliation::with('account:id,code,name')->orderByDesc('statement_date')->orderByDesc('id')->get();

        return view('modules.finance.reconciliation.index', compact('accounts', 'balances', 'reconciliations'));
    }

    /** Start (or carry on with) the reconciliation of an account for a statement. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'account_id' => 'required|integer',
            'statement_date' => 'required',
            'statement_balance' => 'required',
        ]);
        $date = formDate($data['statement_date']);
        $balance = (float) str_replace(',', '', (string) $data['statement_balance']);
        if (!$date || !$this->service->accounts()->contains('id', (int) $data['account_id'])) {
            return back()->withInput()->withErrors(['account_id' => __('Choose a bank or cash account and a valid statement date.')]);
        }

        // one open statement per account: carry on with it, with the new date and balance
        $rec = BankReconciliation::where('account_id', $data['account_id'])->where('status', 'draft')->first();
        if ($rec) {
            $rec->update(['statement_date' => $date, 'statement_balance' => $balance]);
        } else {
            $rec = BankReconciliation::create([
                'company_id' => companyId(), 'account_id' => (int) $data['account_id'], 'statement_date' => $date,
                'statement_balance' => $balance, 'status' => 'draft', 'created_by' => auth()->id(),
            ]);
        }

        return redirect()->route('finance.reconciliation.show', $rec->id);
    }

    public function show($id)
    {
        $rec = BankReconciliation::with('account:id,code,name')->findOrFail($id);

        return view('modules.finance.reconciliation.show', [
            'rec' => $rec,
            'lines' => $this->service->lines($rec),
            'summary' => $this->service->summary($rec),
            'counterAccounts' => Account::where('is_active', 1)->where('is_posting', 1)->whereIn('type', ['Expense', 'Income'])->where('is_grouped', 0)->orderBy('code')->get(['id', 'code', 'name', 'type']),
            'canUndo' => $rec->isCompleted() && !BankReconciliation::where('account_id', $rec->account_id)->where('status', 'completed')->where('id', '>', $rec->id)->exists(),
        ]);
    }

    /** Tick / untick one line, or all of them. Returns the new figures for the header. */
    public function tick(Request $request, $id): JsonResponse
    {
        $rec = BankReconciliation::findOrFail($id);

        return $this->respond($rec, function () use ($request, $rec) {
            if ($request->input('all') !== null) {
                $this->service->tickAll($rec, (bool) $request->boolean('all'));
            } else {
                $this->service->tick($rec, (int) $request->input('line'), (bool) $request->boolean('on'));
            }
        });
    }

    /** Change the statement date / closing balance of an open statement. */
    public function update(Request $request, $id): JsonResponse
    {
        $rec = BankReconciliation::findOrFail($id);

        return $this->respond($rec, function () use ($request, $rec) {
            abort_if($rec->isCompleted(), 422, __('This reconciliation is finished. Undo it to change it.'));
            $date = formDate($request->input('statement_date'));
            abort_unless($date, 422, __('Enter a valid statement date.'));
            $rec->update(['statement_date' => $date, 'statement_balance' => (float) str_replace(',', '', (string) $request->input('statement_balance'))]);
        });
    }

    /** Post a bank charge / interest / other credit as a proper entry, ticked on this statement. */
    public function addEntry(Request $request, $id): JsonResponse
    {
        $rec = BankReconciliation::findOrFail($id);

        return $this->respond($rec, function () use ($request, $rec) {
            $date = formDate($request->input('date'));
            abort_unless($date, 422, __('Enter a valid date.'));
            $this->service->addEntry(
                $rec, (string) $request->input('kind'), (float) str_replace(',', '', (string) $request->input('amount')),
                $date, trim((string) $request->input('description')), (int) $request->input('account_id')
            );
        });
    }

    /** Read a bank statement file: tick what matches the books, list what the bank has and the books do not. */
    public function import(Request $request, $id): JsonResponse
    {
        $rec = BankReconciliation::findOrFail($id);
        $request->validate(['file' => 'required|file|max:5120']);

        $result = [];
        $response = $this->respond($rec, function () use ($request, $rec, &$result) {
            abort_if($rec->isCompleted(), 422, __('This reconciliation is finished. Undo it to change it.'));
            $importer = app(\App\Services\Finance\BankStatementImport::class);
            $rows = $importer->parse($request->file('file'));
            $result = $importer->match($rec, $rows, $this->service) + ['rows' => count($rows)];
        });
        if ($response->getStatusCode() === 200) {
            $data = $response->getData(true);

            return response()->json($data + $result);
        }

        return $response;
    }

    public function complete($id): JsonResponse
    {
        $rec = BankReconciliation::findOrFail($id);

        return $this->respond($rec, fn () => $this->service->complete($rec), ['redirect' => route('finance.reconciliation.show', $rec->id)]);
    }

    /** Delete an open statement, or undo the latest finished one. */
    public function destroy($id): JsonResponse
    {
        $rec = BankReconciliation::findOrFail($id);

        return $this->respond($rec, function () use ($rec) {
            $rec->isCompleted() ? $this->service->undo($rec) : $rec->delete();
        }, ['redirect' => route('finance.reconciliation')]);
    }

    private function respond(BankReconciliation $rec, \Closure $do, array $extra = []): JsonResponse
    {
        try {
            $do();
        } catch (\RuntimeException|\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
        $rec->refresh();

        return response()->json(['status' => 'success', 'summary' => $this->service->summary($rec)] + $extra);
    }
}
