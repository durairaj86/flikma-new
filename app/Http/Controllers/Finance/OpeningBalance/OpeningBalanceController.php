<?php

namespace App\Http\Controllers\Finance\OpeningBalance;

use App\Http\Controllers\Controller;
use App\Models\Finance\Finance;
use App\Models\Finance\FinanceSub;
use App\Models\Finance\JournalVoucher\JournalVoucher;
use App\Models\Finance\JournalVoucher\JournalVoucherItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpeningBalanceController extends Controller
{
    /**
     * Display the opening balance page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        // Get existing opening balance entries
        $openingBalances = Finance::where('voucher_type', 'OB')
            ->where('company_id', companyId())
            ->with('financeSubs')
            ->orderBy('posted_at', 'desc')
            ->get();

        return view('modules.finance.opening-balance.index', compact('openingBalances'));
    }

    /**
     * View opening balance details.
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function view($id)
    {
        $finance = Finance::with('financeSubs')->findOrFail($id);
        return view('modules.finance.opening-balance.view', compact('finance'));
    }

    /**
     * Delete opening balance entry.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $finance = Finance::findOrFail($id);

            // Delete related journal voucher items
            if ($finance->linked_type === 'journal_voucher' && $finance->linked_id) {
                JournalVoucherItem::where('journal_voucher_id', $finance->linked_id)->delete();
                JournalVoucher::where('id', $finance->linked_id)->delete();
            }

            // Delete finance sub entries
            FinanceSub::where('finance_id', $finance->id)->delete();

            // Delete finance entry
            $finance->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Opening balance deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting opening balance: ' . $e->getMessage()
            ], 500);
        }
    }
}
