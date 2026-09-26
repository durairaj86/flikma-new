<?php

namespace App\Http\Controllers\QuotationNew;

use App\Http\Controllers\Controller;
use App\Models\QuotationNew\QuotationNew;
use App\Models\QuotationNew\QuotationNewCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class QuotationNewController extends Controller
{
    // ─── List ────────────────────────────────────────────────────────────────

    public function index()
    {
        return view('modules.quotation-new.list');
    }

    public function fetchAllRows(Request $request): \Illuminate\Http\JsonResponse
    {
        $filter = $request->filterData ?? [];
        $status = (int) ($request->tab ?? QuotationNew::STATUS_PENDING);

        $rows = QuotationNew::with(['client:id,name_en', 'user:id,name'])
            ->withSum('charges as p_sale', 'amount_inr')
            ->withSum('charges as p_cost', 'cost_amount')
            ->where('status', $status)
            ->when(
                isset($filter['filter-from-date'], $filter['filter-to-date']),
                function ($q) use ($filter) {
                    $from = Carbon::parse($filter['filter-from-date'])->startOfDay();
                    $to   = Carbon::parse($filter['filter-to-date'])->addDay()->startOfDay();
                    $q->whereBetween('quotation_date', [$from, $to]);
                }
            )
            ->when(!empty($filter['clients']), function ($q) use ($filter) {
                $q->whereIn('client_id', decodeIds($filter['clients']));
            })
            ->orderByDesc('id');

        $statusCounts = QuotationNew::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $allCounts = [];
        foreach (QuotationNew::allStatuses() as $k => $label) {
            $allCounts[$k] = $statusCounts[$k] ?? 0;
        }

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id'   => fn($m) => $m->id,
                'data-name' => fn($m) => $m->row_no,
                'class'     => 'row-item',
            ])
            ->addColumn('client_name', fn($m) => $m->client ? $m->client->name_en : '')
            ->addColumn('user_name', fn($m) => $m->user ? $m->user->name : '')
            ->addColumn('gp', function ($m) {
                $sale = (float) ($m->p_sale ?? 0);
                $cost = (float) ($m->p_cost ?? 0);
                return number_format($sale - $cost, 2);
            })
            ->addColumn('gp_pct', function ($m) {
                $sale = (float) ($m->p_sale ?? 0);
                $cost = (float) ($m->p_cost ?? 0);
                if ($sale == 0) return '';
                return number_format((($sale - $cost) / $sale) * 100, 2);
            })
            ->addColumn('etd_fmt', fn($m) => $m->etd ? Carbon::parse($m->etd)->format('d-m-Y') : '')
            ->addColumn('eta_fmt', fn($m) => $m->eta ? Carbon::parse($m->eta)->format('d-m-Y') : '')
            ->with(['statusCounts' => $allCounts])
            ->toJson();
    }

    // ─── Wizard Steps ────────────────────────────────────────────────────────

    /**
     * Step 1 — create a blank draft record then go straight to step 2 (Port Details).
     * Step 1 is a minimal landing; all real data entry starts at step 2.
     */
    public function create(Request $request)
    {
        // Auto-create a draft and redirect directly to step 2 (Port Details)
        $year = Carbon::today()->format('Y');
        $q    = new QuotationNew();
        $q->unique_row_no    = sprintf('%04d', (QuotationNew::where('row_created_year', $year)->max('unique_row_no') ?? 0) + 1);
        $q->row_no           = 'QN/' . date('y') . '/' . $q->unique_row_no;
        $q->row_created_year = $year;
        $q->status           = QuotationNew::STATUS_PENDING;
        $q->company_id       = companyId();
        $q->user_id          = Auth::id();
        $q->quotation_date   = Carbon::today()->format('Y-m-d');
        $q->valid_from       = Carbon::today()->format('Y-m-d');
        $q->valid_to         = Carbon::today()->addDays(29)->format('Y-m-d');
        $q->save();

        return redirect(url('sales/quotations-new/' . $q->id . '/step2'));
    }

    /**
     * Step 1 POST — not used (create auto-redirects to step 2).
     */
    public function storeStep1(Request $request)
    {
        return response()->json(['status' => 'success', 'next' => 2]);
    }

    /**
     * Step 2 — Port Details (all fields from image 3).
     */
    public function step2(Request $request, int $id)
    {
        $quotation = QuotationNew::findOrFail($id);
        $polPod    = preloadPOLAndPOD();
        return view('modules.quotation-new.wizard.step2', compact('quotation', 'polPod'));
    }

    public function storeStep2(Request $request, int $id)
    {
        $request->merge(['client_id' => decodeId($request->input('customer'))]);

        $request->validate([
            'client_id'      => 'nullable|exists:customers,id',
            'quotation_date' => 'nullable|date',
            'valid_from'     => 'nullable|date',
            'valid_to'       => 'nullable|date',
        ]);

        $quotation = QuotationNew::findOrFail($id);

        // Fields from image 3 — all port details fields
        $quotation->branch            = $request->branch;
        $quotation->department        = $request->department;
        $quotation->quotation_date    = $request->quotation_date ?: $quotation->quotation_date;
        $quotation->client_id         = $request->client_id ?: $quotation->client_id;
        $quotation->client_address    = $request->client_address;
        $quotation->origin            = $request->origin;
        $quotation->destination       = $request->destination;
        $quotation->place_of_receipt  = $request->place_of_receipt;
        $quotation->place_of_delivery = $request->place_of_delivery;
        $quotation->inco_terms        = $request->inco_terms;
        $quotation->valid_from        = $request->valid_from ?: $quotation->valid_from;
        $quotation->valid_to          = $request->valid_to   ?: $quotation->valid_to;
        $quotation->service_type      = $request->service_type;
        $quotation->pp_cc             = $request->pp_cc;
        $quotation->transit_time           = $request->transit_time;
        $quotation->frequency              = $request->frequency;
        $quotation->etd                    = $request->etd ?: null;
        $quotation->eta                    = $request->eta ?: null;
        $quotation->destination_free_days  = $request->destination_free_days;
        $quotation->remarks                = $request->remarks;
        $quotation->save();

        return response()->json(['status' => 'success', 'id' => $id, 'next' => 3]);
    }

    /**
     * Step 3 — Container / Consignment.
     */
    public function step3(Request $request, int $id)
    {
        $quotation = QuotationNew::findOrFail($id);
        return view('modules.quotation-new.wizard.step3', compact('quotation'));
    }

    public function storeStep3(Request $request, int $id)
    {
        $quotation = QuotationNew::findOrFail($id);

        $quotation->carrier              = $request->carrier;
        $quotation->vessel_name          = $request->vessel_name;
        $quotation->voyage_no            = $request->voyage_no;
        $quotation->no_of_pcs            = $request->no_of_pcs;
        $quotation->gross_weight         = $request->gross_weight;
        $quotation->weight_unit          = $request->weight_unit;
        $quotation->volume               = $request->volume;
        $quotation->volume_weight        = $request->volume_weight;
        $quotation->volume_unit          = $request->volume_unit;
        $quotation->chargeable_unit      = $request->chargeable_unit;
        $quotation->hs_code              = $request->hs_code;
        $quotation->description          = $request->description;
        $quotation->consignment_remarks  = $request->consignment_remarks;
        $quotation->save();

        return response()->json(['status' => 'success', 'id' => $id, 'next' => 4]);
    }

    /**
     * Step 4 — Charge Details.
     */
    public function step4(Request $request, int $id)
    {
        $quotation = QuotationNew::with('charges')->findOrFail($id);
        return view('modules.quotation-new.wizard.step4', compact('quotation'));
    }

    public function storeStep4(Request $request, int $id)
    {
        $quotation = QuotationNew::findOrFail($id);

        // Rebuild charges
        $quotation->charges()->delete();

        $chargeDescriptions = $request->input('charge_description', []);
        foreach ($chargeDescriptions as $idx => $desc) {
            if (empty($desc)) continue;
            QuotationNewCharge::create([
                'quotation_new_id'  => $quotation->id,
                'charge_description'=> $desc,
                'ofd_type'          => $request->ofd_type[$idx] ?? null,
                'unit'              => $request->unit[$idx] ?? null,
                'qty'               => $request->qty[$idx] ?? 1,
                'freight'           => $request->freight[$idx] ?? null,
                'dr_cr'             => $request->dr_cr[$idx] ?? null,
                'qty_amount'        => $request->qty_amount[$idx] ?? null,
                'fcy_amount'        => $request->fcy_amount[$idx] ?? null,
                'amount_inr'        => $request->amount_inr[$idx] ?? null,
                'tax_amount_inr'    => $request->tax_amount_inr[$idx] ?? 0,
                'is_standard'       => true,
                'sort_order'        => $idx,
            ]);
        }

        return response()->json(['status' => 'success', 'id' => $id, 'next' => 5]);
    }

    /**
     * Step 5 — Summary.
     */
    public function step5(Request $request, int $id)
    {
        $quotation = QuotationNew::with(['client', 'charges'])->findOrFail($id);
        return view('modules.quotation-new.wizard.step5', compact('quotation'));
    }

    /**
     * Finalise — mark quotation as submitted/pending.
     */
    public function finalise(Request $request, int $id)
    {
        $quotation = QuotationNew::findOrFail($id);
        $quotation->status = QuotationNew::STATUS_PENDING;
        $quotation->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Quotation submitted successfully.',
        ]);
    }

    // ─── Edit Modal ──────────────────────────────────────────────────────────

    public function editModal(int $id)
    {
        $quotation = QuotationNew::findOrFail($id);
        $polPod    = preloadPOLAndPOD();
        return view('modules.quotation-new.edit-modal', compact('quotation', 'polPod'));
    }

    public function update(Request $request, int $id)
    {
        $request->merge(['client_id' => decodeId($request->input('client_id'))]);

        $quotation = QuotationNew::findOrFail($id);

        $quotation->branch         = $request->branch;
        $quotation->department     = $request->department;
        $quotation->quotation_date = $request->quotation_date;
        $quotation->client_id      = $request->client_id ?: $quotation->client_id;
        $quotation->valid_from     = $request->valid_from;
        $quotation->valid_to       = $request->valid_to;
        $quotation->freight        = $request->freight;
        $quotation->place_of_receipt = $request->place_of_receipt;
        $quotation->por            = $request->por;
        $quotation->pol            = $request->pol;
        $quotation->pod            = $request->pod;
        $quotation->pof            = $request->pof;
        $quotation->place_of_delivery = $request->place_of_delivery;
        $quotation->service_type   = $request->service_type;
        $quotation->carrier        = $request->carrier;
        $quotation->transit_time   = $request->transit_time;
        $quotation->frequency      = $request->frequency;
        $quotation->inco_terms     = $request->inco_terms;
        $quotation->mark_no        = $request->mark_no;
        $quotation->internal_notes = $request->internal_notes;
        $quotation->remarks        = $request->remarks;
        $quotation->save();

        return response()->json(['status' => 'success', 'message' => 'Quotation updated successfully.']);
    }

    // ─── Costing Modal ───────────────────────────────────────────────────────

    public function costingModal(int $quotationId, int $chargeId = null)
    {
        $quotation = QuotationNew::with('client')->findOrFail($quotationId);
        $charge    = $chargeId ? QuotationNewCharge::findOrFail($chargeId) : new QuotationNewCharge(['qty' => 1, 'ex_rate' => 1]);
        return view('modules.quotation-new.costing-modal', compact('quotation', 'charge'));
    }

    public function storeCosting(Request $request, int $quotationId)
    {
        $request->merge([
            'bill_to_id' => decodeId($request->input('bill_to_id')),
            'vendor_id'  => decodeId($request->input('vendor_id')),
        ]);

        $chargeId = $request->input('charge_id');
        $charge   = $chargeId
            ? QuotationNewCharge::findOrFail($chargeId)
            : new QuotationNewCharge(['quotation_new_id' => $quotationId]);

        $charge->charge_description = $request->charge_description;
        $charge->unit               = $request->unit;
        $charge->qty                = $request->qty ?? 1;
        $charge->ofd_type           = $request->ofd_type;
        $charge->freight            = $request->freight;
        $charge->dr_cr              = $request->dr_cr;
        $charge->bill_to_id         = $request->bill_to_id ?: null;
        $charge->currency           = $request->currency ?? 'INR';
        $charge->ex_rate            = $request->ex_rate ?? 1;
        $charge->qty_amount         = $request->qty_amount;
        $charge->fcy_amount         = $request->fcy_amount;
        $charge->amount_inr         = $request->amount_inr;
        $charge->tax_group_code     = $request->tax_group_code;
        $charge->taxable_amount     = $request->taxable_amount;
        $charge->tax_amount_sale    = $request->tax_amount_sale ?? 0;
        $charge->sale_remarks       = $request->sale_remarks;
        $charge->vendor_id          = $request->vendor_id ?: null;
        $charge->reference_no       = $request->reference_no;
        $charge->cost_date          = $request->cost_date ?: null;
        $charge->cost_amount        = $request->cost_amount;
        $charge->save();

        return response()->json(['status' => 'success', 'message' => 'Charge saved.', 'charge_id' => $charge->id]);
    }

    public function deleteCharge(int $chargeId)
    {
        QuotationNewCharge::findOrFail($chargeId)->delete();
        return response()->json(['status' => 'success']);
    }

    // ─── Status Update ───────────────────────────────────────────────────────

    public function updateStatus(int $id, int $status)
    {
        $quotation = QuotationNew::findOrFail($id);
        $quotation->status = $status;
        $quotation->save();

        return response()->json(['status' => 'success', 'message' => 'Status updated.']);
    }

    // ─── Actions Context Menu ────────────────────────────────────────────────

    public function actions(int $id)
    {
        $quotation   = QuotationNew::findOrFail($id);
        $contextMenu = collect();

        if ($quotation->status === QuotationNew::STATUS_PENDING) {
            $contextMenu->push([
                'label' => 'Edit',
                'id'    => 'row_edit',
                'type'  => 'item',
                'icon'  => 'edit',
                'data-id' => $quotation->id,
            ]);
            $contextMenu->push([
                'label'    => 'Move to',
                'type'     => 'submenu',
                'icon'     => 'move_to',
                'separator'=> 'after',
                'items'    => [
                    ['label' => 'Approved',  'id' => 'row_approve',  'data-id' => $id, 'data-value' => QuotationNew::STATUS_APPROVED,  'icon' => 'confirmed'],
                    ['label' => 'Cancelled', 'id' => 'row_cancel',   'data-id' => $id, 'data-value' => QuotationNew::STATUS_CANCELLED, 'icon' => 'rejected'],
                ],
            ]);
        }

        if ($quotation->status === QuotationNew::STATUS_APPROVED) {
            $contextMenu->push([
                'label'    => 'Move to',
                'type'     => 'submenu',
                'icon'     => 'move_to',
                'separator'=> 'after',
                'items'    => [
                    ['label' => 'Pending',   'id' => 'row_pending',  'data-id' => $id, 'data-value' => QuotationNew::STATUS_PENDING,   'icon' => 'pending'],
                    ['label' => 'Cancelled', 'id' => 'row_cancel',   'data-id' => $id, 'data-value' => QuotationNew::STATUS_CANCELLED, 'icon' => 'rejected'],
                ],
            ]);
        }

        $contextMenu->push([
            'label'   => 'View',
            'id'      => 'row_view',
            'type'    => 'item',
            'icon'    => 'view',
            'data-id' => $quotation->id,
        ]);

        return response()->json($contextMenu->values());
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function resolveQuotation(Request $request): QuotationNew
    {
        $id = $request->input('quotation_id');
        if ($id) {
            return QuotationNew::findOrFail($id);
        }

        $year = Carbon::today()->format('Y');
        $q    = new QuotationNew();
        $q->unique_row_no     = sprintf('%04d', (QuotationNew::where('row_created_year', $year)->max('unique_row_no') ?? 0) + 1);
        $q->row_no            = 'QN/' . date('y') . '/' . $q->unique_row_no;
        $q->row_created_year  = $year;
        $q->status            = QuotationNew::STATUS_PENDING;
        $q->company_id        = companyId();
        $q->user_id           = Auth::id();

        return $q;
    }
}
