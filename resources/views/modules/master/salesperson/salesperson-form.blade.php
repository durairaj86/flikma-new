@php
    $type = $salesperson->commission_type ?? 'flat';
    $slabs = $salesperson->commission_slabs ?? [];
    if (!$slabs) $slabs = [['from' => 0, 'to' => null, 'rate' => null, 'late_rate' => null]];
    $num = fn ($v) => ($v === null || $v === '') ? '' : rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
@endphp
<div class="modal-header justify-content-between border-bottom py-3" data-close-title="enquiry">
    <div class="row align-items-center bg-white  small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $salesperson->name ?? __('New Salesperson') }}</span></div>
        </div>
    </div>
    <div id="show-buttons"></div>
</div>
<div class="modal-body p-0">
    <style>
        .sp-type { display: flex; gap: .5rem; }
        .sp-type label { flex: 1; border: 1px solid #dee2e6; border-radius: 10px; padding: .6rem .8rem; cursor: pointer; display: flex; gap: .55rem; align-items: flex-start; }
        .sp-type input:checked + div b { color: #0d6efd; }
        .sp-type label:has(input:checked) { border-color: #0d6efd; background: rgba(13, 110, 253, .04); }
        .sp-type small { display: block; color: #6c757d; font-size: .76rem; line-height: 1.3; }
        .sp-slabs th { font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; color: #6c757d; font-weight: 600; background: #f8f9fa; }
        .sp-slabs td, .sp-slabs th { padding: .35rem .4rem; vertical-align: middle; }
        .sp-section { font-size: .78rem; text-transform: uppercase; letter-spacing: .05em; color: #6c757d; font-weight: 600; border-bottom: 1px solid #eef0f3; padding-bottom: .35rem; margin: 1.1rem 0 .7rem; }
    </style>
    <div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
         data-button-save="{{ __('Save Salesperson') }}">
        <div class="row">
            <form id="moduleForm" novalidate action="{{ request()->url() }}">
                @csrf
                <input type="hidden" name="data-id" value="{{ $salesperson->id ?? '' }}">
                <div class="model-form-tab-div">
                    <div class="row mt-2">
                        <div class="col-12 form-group">
                            <label class="form-label">{{ __('Name') }}</label>
                            <input type="text" name="name" id="name" class="form-control" required
                                   value="{{ $salesperson->name ?? '' }}">
                        </div>
                    </div>

                    {{-- commission scheme used by the Sales Commission report --}}
                    <div class="sp-section">{{ __('Commission') }}</div>
                    <div class="sp-type mb-3">
                        <label><input type="radio" class="form-check-input mt-1" name="commission_type" value="flat" @checked($type !== 'slab')>
                            <div><b>{{ __('Flat') }}</b><small>{{ __('One rate on the gross profit') }}</small></div></label>
                        <label><input type="radio" class="form-check-input mt-1" name="commission_type" value="slab" @checked($type === 'slab')>
                            <div><b>{{ __('Slab wise') }}</b><small>{{ __('The rate depends on how much gross profit is made in the period') }}</small></div></label>
                    </div>

                    <div class="row g-3" id="spFlat" @if($type === 'slab') style="display:none" @endif>
                        <div class="col-6 form-group">
                            <label class="form-label">{{ __('Rate % (paid on time)') }}</label>
                            <input type="text" name="commission_rate" class="form-control decimal" value="{{ $num($salesperson->commission_rate ?? null) }}" placeholder="6">
                            <small class="text-muted">{{ __('Collected on or before the due date') }}</small>
                        </div>
                        <div class="col-6 form-group">
                            <label class="form-label">{{ __('Rate % (paid late)') }}</label>
                            <input type="text" name="late_rate" class="form-control decimal" value="{{ $num($salesperson->late_rate ?? null) }}" placeholder="5">
                            <small class="text-muted">{{ __('Collected after the due date, within the late limit') }}</small>
                        </div>
                    </div>

                    <div id="spSlab" @if($type !== 'slab') style="display:none" @endif>
                        <table class="table table-sm sp-slabs mb-1">
                            <thead><tr>
                                <th>{{ __('Gross profit from') }}</th><th>{{ __('To') }}</th><th>{{ __('Rate % on time') }}</th><th>{{ __('Rate % late') }}</th><th></th>
                            </tr></thead>
                            <tbody id="spSlabRows">
                            @foreach($slabs as $slab)
                                <tr>
                                    <td><input type="text" name="slab_from[]" class="form-control form-control-sm decimal" value="{{ $num($slab['from'] ?? null) }}"></td>
                                    <td><input type="text" name="slab_to[]" class="form-control form-control-sm decimal" value="{{ $num($slab['to'] ?? null) }}" placeholder="{{ __('and above') }}"></td>
                                    <td><input type="text" name="slab_rate[]" class="form-control form-control-sm decimal" value="{{ $num($slab['rate'] ?? null) }}"></td>
                                    <td><input type="text" name="slab_late_rate[]" class="form-control form-control-sm decimal" value="{{ $num($slab['late_rate'] ?? null) }}"></td>
                                    <td class="text-end"><button type="button" class="btn btn-sm btn-link text-danger sp-del" title="{{ __('Remove') }}"><i class="bi bi-x-lg"></i></button></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="spAddSlab"><i class="bi bi-plus-lg me-1"></i>{{ __('Add slab') }}</button>
                        <div class="small text-muted mt-2">{{ __('The gross profit of the whole period picks one slab; its rates apply to all of that profit.') }}</div>
                    </div>

                    <div class="row g-3 mt-1">
                        <div class="col-6 form-group">
                            <label class="form-label">{{ __('Late limit (days)') }}</label>
                            <input type="text" name="late_days" class="form-control integer" value="{{ $salesperson->late_days ?? 120 }}">
                            <small class="text-muted">{{ __('From the invoice date; collected later earns nothing') }}</small>
                        </div>
                        <div class="col-6 form-group">
                            <label class="form-label">{{ __('Monthly target (gross profit)') }}</label>
                            <input type="text" name="monthly_target" class="form-control decimal" value="{{ $num($salesperson->monthly_target ?? null) }}">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    (function () {
        const form = document.getElementById('moduleForm');
        const toggle = () => {
            const slab = form.querySelector('input[name="commission_type"]:checked')?.value === 'slab';
            document.getElementById('spFlat').style.display = slab ? 'none' : '';
            document.getElementById('spSlab').style.display = slab ? '' : 'none';
        };
        form.querySelectorAll('input[name="commission_type"]').forEach(r => r.addEventListener('change', toggle));
        const rows = document.getElementById('spSlabRows');
        document.getElementById('spAddSlab').addEventListener('click', function () {
            const last = rows.querySelector('tr:last-child');
            const tr = last.cloneNode(true);
            tr.querySelectorAll('input').forEach(i => i.value = '');
            // the next slab starts where the previous one ended
            const prevTo = last.querySelector('input[name="slab_to[]"]').value;
            if (prevTo) tr.querySelector('input[name="slab_from[]"]').value = prevTo;
            rows.appendChild(tr);
        });
        rows.addEventListener('click', function (e) {
            const btn = e.target.closest('.sp-del');
            if (!btn) return;
            if (rows.querySelectorAll('tr').length > 1) btn.closest('tr').remove();
            else btn.closest('tr').querySelectorAll('input').forEach(i => i.value = '');
        });
    })();
</script>
