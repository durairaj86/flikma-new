<?php

namespace App\Http\Controllers\Documents;

use Illuminate\Support\Str;

use App\Http\Controllers\Controller;
use App\Models\Documents\Documents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

/** One place for every document attached anywhere in the system, with expiry tracking. */
class DocumentCenterController extends Controller
{
    /** What a document can belong to: key => [model class, label, column shown as its number / name]. */
    public const OWNERS = [
        'job' => [\App\Models\Job\Job::class, 'Job', 'row_no'],
        'customer' => [\App\Models\Customer\Customer::class, 'Customer', 'name_en'],
        'supplier' => [\App\Models\Supplier\Supplier::class, 'Supplier', 'name_en'],
        'booking' => [\App\Models\Booking\Booking::class, 'Booking', 'row_no'],
        'delivery_order' => [\App\Models\Delivery\DeliveryOrder::class, 'Delivery Order', 'row_no'],
        'arrival_notice' => [\App\Models\Arrival\ArrivalNotice::class, 'Arrival Notice', 'row_no'],
        'master_bl' => [\App\Models\BL\MasterBl::class, 'Master B/L', 'row_no'],
        'seaway' => [\App\Models\BL\SeawayBill::class, 'Seaway Bill', 'row_no'],
        'airway' => [\App\Models\BL\AirwayBill::class, 'Airway Bill', 'row_no'],
        'driver' => [\App\Models\Fleet\Driver::class, 'Driver', 'name'],
        'vehicle' => [\App\Models\Fleet\Vehicle::class, 'Vehicle', 'plate_no'],
    ];

    public const TYPES = [
        'bl' => 'Bill of Lading', 'awb' => 'Airway Bill', 'invoice' => 'Invoice', 'packing_list' => 'Packing List',
        'origin' => 'Certificate of Origin', 'customs' => 'Customs Declaration', 'do' => 'Delivery Order', 'insurance' => 'Insurance',
        'licence' => 'Licence / Permit', 'contract' => 'Contract', 'pod' => 'Proof of Delivery', 'other' => 'Other',
    ];

    private function ownerKey(?string $class): ?string
    {
        foreach (self::OWNERS as $k => $o) {
            if ($o[0] === $class) {
                return $k;
            }
        }

        return null;
    }

    private function formData(Documents $doc): array
    {
        return ['doc' => $doc, 'owners' => self::OWNERS, 'types' => self::TYPES, 'ownerKey' => $this->ownerKey($doc->documentable_type)];
    }

    public function modal()
    {
        $doc = new Documents();
        $doc->posted_date = today();

        return view('modules.document-center.document-form', $this->formData($doc));
    }

    public function edit($id)
    {
        return view('modules.document-center.document-form', $this->formData(Documents::findOrFail($id)));
    }

    /** Records of one owner type for the picker in the form. */
    public function owners($type, Request $request)
    {
        abort_unless(isset(self::OWNERS[$type]), 404);
        [$class, , $col] = self::OWNERS[$type];
        $q = $class::query()->select('id', $col);
        if ($s = $request->query('q')) {
            $q->where($col, 'like', "%{$s}%");
        }

        return response()->json($q->orderByDesc('id')->limit(300)->get()->map(fn($r) => ['id' => $r->id, 'name' => (string) $r->{$col}]));
    }

    private function applyFilters($query, array $filter)
    {
        return $query
            ->when(!empty($filter['owner_type']) && isset(self::OWNERS[$filter['owner_type']]), fn($q) => $q->where('documentable_type', self::OWNERS[$filter['owner_type']][0]))
            ->when(!empty($filter['doc_type']), fn($q) => $q->where('doc_type', $filter['doc_type']))
            ->when(!empty($filter['customSearch']), function ($q) use ($filter) {
                $s = $filter['customSearch'];
                $q->where(fn($w) => $w->where('title', 'like', "%{$s}%")->orWhere('file_name', 'like', "%{$s}%"));
            });
    }

    public function fetchAllRows(Request $request)
    {
        $filter = $request->filterData ?? [];
        $tab = $request->tab ?: 'all';
        $soon = today()->addDays(30);

        $tabbed = function ($q, $t) use ($soon) {
            return match ($t) {
                'valid' => $q->where(fn($w) => $w->whereNull('expiry_date')->orWhereDate('expiry_date', '>', $soon)),
                'expiring' => $q->whereNotNull('expiry_date')->whereDate('expiry_date', '>=', today())->whereDate('expiry_date', '<=', $soon),
                'expired' => $q->whereNotNull('expiry_date')->whereDate('expiry_date', '<', today()),
                default => $q,
            };
        };

        $rows = $tabbed($this->applyFilters(Documents::with('documentable'), $filter), $tab)->orderByDesc('id');
        $counts = [];
        foreach (['all', 'valid', 'expiring', 'expired'] as $t) {
            $counts[$t] = $tabbed($this->applyFilters(Documents::query(), $filter), $t)->count();
        }

        return DataTables::eloquent($rows)
            ->addIndexColumn()
            ->setRowAttr(['data-id' => fn($m) => $m->id, 'data-name' => fn($m) => htmlspecialchars((string) $m->title, ENT_QUOTES, 'UTF-8'), 'class' => 'row-item', 'id' => fn($m) => 'doc-' . $m->id])
            ->addColumn('owner_label', function ($m) {
                $k = $this->ownerKey($m->documentable_type);
                $o = $k ? self::OWNERS[$k] : null;
                $name = $m->documentable ? ($m->documentable->{$o[2] ?? 'row_no'} ?? $m->documentable->row_no ?? $m->documentable->invoice_no ?? null) : null;

                return ['type' => __($o[1] ?? Str::headline(class_basename((string) $m->documentable_type))), 'name' => $name];
            })
            ->addColumn('type_label', fn($m) => isset(self::TYPES[$m->doc_type]) ? __(self::TYPES[$m->doc_type]) : ($m->doc_type ? __(Str::headline($m->doc_type)) : null))
            ->addColumn('expiry_f', fn($m) => $m->expiry_date?->format('d-m-Y'))
            ->addColumn('posted_f', fn($m) => $m->posted_date?->format('d-m-Y'))
            ->addColumn('expiry_state', fn($m) => $m->expiry_date ? ($m->expiry_date->lt(today()) ? 'expired' : ($m->expiry_date->lte($soon) ? 'soon' : 'ok')) : null)
            ->addColumn('days_left', fn($m) => $m->expiry_date ? today()->diffInDays($m->expiry_date, false) : null)
            ->addColumn('url', fn($m) => $m->file_path ? asset('storage/' . $m->file_path) : null)
            ->with(['statusCounts' => $counts])
            ->toJson();
    }

    public function store(Request $request)
    {
        $editing = $request->filled('data-id');
        $v = $request->validate([
            'title' => 'required|string|max:255',
            'doc_type' => 'nullable|in:' . implode(',', array_keys(self::TYPES)),
            'owner_type' => ($editing ? 'nullable' : 'required') . '|in:' . implode(',', array_keys(self::OWNERS)),
            'owner_id' => ($editing ? 'nullable' : 'required') . '|integer',
            'expiry_date' => 'nullable|date',
            'file' => ($editing ? 'nullable' : 'required') . '|file|max:20480|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,csv,txt',
        ]);

        if ($editing) {
            $doc = Documents::findOrFail($request->input('data-id'));
        } else {
            [$class] = self::OWNERS[$v['owner_type']];
            $owner = $class::find($v['owner_id']);
            if (!$owner) {
                return response()->json(['status' => 'error', 'message' => __('Choose the record this document belongs to.')], 422);
            }
            $doc = new Documents();
            $doc->documentable_type = $class;
            $doc->documentable_id = $owner->id;
            $doc->posted_date = today();
            $this->setBaseColumns($doc);
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            if ($editing && $doc->file_path) {
                Storage::disk('public')->delete($doc->file_path);
            }
            $doc->file_path = $file->store('documents/' . companyId() . '/center', 'public');
            $doc->file_name = $file->getClientOriginalName();
        }
        $doc->title = $v['title'];
        $doc->doc_type = $v['doc_type'] ?? null;
        $doc->expiry_date = !empty($v['expiry_date']) ? formDate($v['expiry_date']) : null;
        $doc->save();

        return response()->json(['status' => 'success', 'message' => __('Document saved successfully'), 'id' => $doc->id]);
    }

    public function actions($id)
    {
        $d = Documents::select('id', 'file_path')->findOrFail($id);
        $menu = collect();
        if ($d->file_path) {
            $menu->push(['label' => __('Open'), 'code' => '01CSVW', 'id' => 'row_open', 'class' => 'row_open', 'data-id' => $d->id, 'type' => 'item', 'icon' => 'view']);
            $menu->push(['label' => __('Download'), 'code' => '01CSDW', 'id' => 'row_download', 'class' => 'row_download', 'data-id' => $d->id, 'type' => 'item', 'icon' => 'print']);
        }
        $menu->push(['label' => __('Edit'), 'code' => '01CSED', 'id' => 'row_edit', 'class' => 'row_edit', 'data-id' => $d->id, 'type' => 'item', 'icon' => 'edit']);
        $menu->push(['label' => __('Delete'), 'code' => '01CSDL', 'id' => 'row_delete', 'class' => 'row_delete', 'data-id' => $d->id, 'type' => 'item', 'icon' => 'delete']);

        return response()->json($menu->values());
    }

    public function download($id, Request $request)
    {
        $d = Documents::findOrFail($id);
        abort_unless($d->file_path && Storage::disk('public')->exists($d->file_path), 404);
        $path = Storage::disk('public')->path($d->file_path);

        return $request->boolean('inline')
            ? response()->file($path, ['Content-Disposition' => 'inline; filename="' . $d->file_name . '"'])
            : response()->download($path, $d->file_name);
    }

    public function delete($id)
    {
        $d = Documents::findOrFail($id);
        if ($d->file_path) {
            Storage::disk('public')->delete($d->file_path);
        }
        $d->delete();

        return response()->json(['status' => 'success', 'message' => __('Document deleted successfully')]);
    }
}
