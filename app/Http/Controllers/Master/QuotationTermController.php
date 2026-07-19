<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\LogisticActivity;
use App\Models\Master\QuotationTerm;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class QuotationTermController extends Controller
{
    public function fetchAllRows()
    {
        $rows = QuotationTerm::with('activity:id,name')
            ->select('id', 'title', 'terms', 'is_general', 'activity_id', 'is_active', 'company_id');

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => fn($model) => $model->id,
                'class' => 'row-item',
                'id' => fn($model) => 'quotation-term-' . $model->id,
            ])
            ->editColumn('is_general', function ($row) {
                return $row->is_general
                    ? '<span class="badge bg-primary-subtle text-primary">General</span>'
                    : '<span class="badge bg-secondary-subtle text-secondary">' . e($row->activity->name ?? '—') . '</span>';
            })
            ->editColumn('terms', function ($row) {
                return \Illuminate\Support\Str::limit(strip_tags($row->terms), 80);
            })
            ->rawColumns(['is_general'])
            ->toJson();
    }

    public function modal()
    {
        $quotationTerm = new QuotationTerm();
        $activities = LogisticActivity::activities();
        return view('modules.master.quotation-term.quotation-term-form', compact('quotationTerm', 'activities'));
    }

    public function edit($id)
    {
        $quotationTerm = QuotationTerm::findOrFail($id);
        $activities = LogisticActivity::activities();
        return view('modules.master.quotation-term.quotation-term-form', compact('quotationTerm', 'activities'));
    }

    public function store(Request $request)
    {
        $id = filled($request->input('data-id')) ? $request->input('data-id') : null;
        $isGeneral = $request->boolean('is_general');
        $companyId = companyId();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'terms' => ['required', 'string'],
            'activity_id' => [Rule::requiredIf(!$isGeneral), 'nullable', 'exists:logistic_activities,id'],
        ];

        $validated = $request->validate($rules);

        // Only one General term, and only one term per Activity — there's
        // no way to pick between two otherwise-identical matches when
        // auto-filling a quotation's Terms & Conditions, so duplicates
        // aren't allowed rather than silently picking one.
        if ($isGeneral) {
            $duplicate = QuotationTerm::where('company_id', $companyId)
                ->where('is_general', true)
                ->when($id, fn($q) => $q->where('id', '!=', $id))
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'is_general' => 'A General term already exists. Edit that one instead of creating another.',
                ]);
            }
        } else {
            $duplicate = QuotationTerm::where('company_id', $companyId)
                ->where('is_general', false)
                ->where('activity_id', $validated['activity_id'])
                ->when($id, fn($q) => $q->where('id', '!=', $id))
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'activity_id' => 'A term already exists for this Activity. Edit that one instead of creating another.',
                ]);
            }
        }

        if ($id) {
            $quotationTerm = QuotationTerm::findOrFail($id);
        } else {
            $quotationTerm = new QuotationTerm();
            $this->setBaseColumns($quotationTerm);
        }

        $quotationTerm->title = $validated['title'];
        $quotationTerm->terms = $validated['terms'];
        $quotationTerm->is_general = $isGeneral;
        $quotationTerm->activity_id = $isGeneral ? null : $validated['activity_id'];
        $quotationTerm->is_active = $request->boolean('is_active', true);
        $quotationTerm->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Quotation term saved successfully',
            'module_id' => $quotationTerm->id,
        ]);
    }

    public function actions($id)
    {
        $quotationTerm = QuotationTerm::select('id')->findOrFail($id);

        $contextMenu = collect([[
            'label' => __('Edit'),
            'code' => '01CSED',
            'id' => 'row_edit',
            'class' => 'row_edit',
            'data-id' => $quotationTerm->id,
            'type' => 'item',
            'icon' => 'edit',
        ], [
            'label' => __('Delete'),
            'code' => '01CSDL',
            'id' => 'row_delete',
            'class' => 'row_delete',
            'data-id' => $quotationTerm->id,
            'type' => 'item',
            'icon' => 'delete',
        ]]);

        return response()->json($contextMenu->values());
    }

    public function destroy($id)
    {
        $quotationTerm = QuotationTerm::findOrFail($id);
        $quotationTerm->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Quotation term deleted successfully',
        ]);
    }

    /**
     * Used by the Quotation create/edit form: given the selected Activity,
     * return the term text that should prefill Terms & Conditions —
     * an activity-specific term if one exists, otherwise the general term.
     * The master record stores rich-text HTML (edited via Quill), but the
     * Quotation form's own Terms & Conditions field is a plain textarea, so
     * this converts block/line boundaries to newlines and strips the rest
     * of the markup rather than dumping raw HTML tags into it.
     */
    public function forActivity($activityId)
    {
        $html = QuotationTerm::forActivity($activityId);

        return response()->json([
            'terms' => $html ? $this->htmlToPlainText($html) : null,
        ]);
    }

    private function htmlToPlainText(string $html): string
    {
        $text = preg_replace('/<\/(p|div|h[1-6]|li)>|<br\s*\/?>/i', "\n", $html);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES);

        return trim(preg_replace("/\n{3,}/", "\n\n", $text));
    }
}
