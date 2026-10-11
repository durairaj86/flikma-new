<div class="g-3 align-items-center border-bottom py-3 px-4 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="module-info"><span class="fw-semibold fs-5">{{ $job->row_no }}</span>
            <span class="text-muted ms-2">{{ $job->pol_name ?? $job->origin }} → {{ $job->pod_name ?? $job->destination }}</span></div>
        <div id="show-buttons"></div>
    </div>
</div>
<div class="container-fluid align-items-center px-0 mb-4" id="modal-buttons" data-buttons="cancel,save" data-button-save="{{ __('Save Tracking') }}">
    <form id="moduleForm" novalidate action="/operation/tracking/{{ $job->id }}">
        @csrf
        <div class="px-4 mt-3">
            <p class="text-muted small mb-3">{{ __('Fill the date when each step happened. Documents, ATD, ATA, D/O and customs clearance already entered on the job show here automatically.') }}</p>
            @foreach($steps as $s)
                <div class="row g-2 align-items-center mb-2 trk-row {{ $s['skipped'] ? 'is-skipped' : '' }}">
                    <div class="col-md-3 fw-semibold">{{ __($s['label']) }}</div>
                    <div class="col-md-2"><label class="form-check form-switch m-0 small text-muted" title="{{ __('This step is not needed for this job') }}">
                        <input type="checkbox" class="form-check-input trk-skip" name="skipped[{{ $s['code'] }}]" value="1" @checked($s['skipped'])> {{ __('Skip') }}</label></div>
                    <div class="col-md-3"><input type="text" name="date[{{ $s['code'] }}]" class="form-control datepicker" value="{{ $s['actual']?->format('d-m-Y') }}" placeholder="{{ __('Date') }}">@if($s['auto'])<div class="form-text">{{ __('Taken from the job') }}</div>@endif</div>
                    <div class="col-md-4"><input type="text" name="location[{{ $s['code'] }}]" class="form-control" value="{{ $s['location'] }}" placeholder="{{ __('Place (optional)') }}"></div>
                </div>
            @endforeach
        </div>
    </form>
</div>
<style>.trk-row.is-skipped { background: #fff7ed; border-radius: 8px; } .trk-row.is-skipped .fw-semibold { color: #c2410c; }</style>
<script>
    (function () {
        // Calendar on every date box in this form (set up here so it works wherever the modal is opened from).
        document.querySelectorAll('#moduleForm input.datepicker').forEach(function (el) {
            if (el._flatpickr) return;
            flatpickr(el, {dateFormat: 'd-m-Y', altInput: true, altFormat: 'd-m-Y', allowInput: true, disableMobile: true,
                minDate: '01-01-2020', maxDate: '31-12-2030', defaultDate: el.value || null});
        });
        // A skipped step has no date.
        document.querySelectorAll('#moduleForm .trk-skip').forEach(function (cb) {
            cb.addEventListener('change', function () {
                var row = cb.closest('.trk-row');
                row.classList.toggle('is-skipped', cb.checked);
                if (cb.checked) {
                    var d = row.querySelector('input.datepicker');
                    if (d && d._flatpickr) d._flatpickr.clear();
                }
            });
        });
    })();
</script>
