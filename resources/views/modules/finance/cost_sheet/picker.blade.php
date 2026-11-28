{{-- New Cost Sheet: choose the job; its cost sheet opens next (one cost sheet per job) --}}
<div class="g-3 align-items-center border-bottom py-3 px-4 small cin-modal-head">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span class="fw-semibold fs-5">{{ __('New Cost Sheet') }}</span>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" id="btn-cancel"><i class="bi bi-x-circle me-1"></i> {{ __('Cancel') }}</button>
            <button type="button" class="btn btn-primary" id="csPickOpen"><i class="bi bi-arrow-right-circle me-1"></i> {{ __('Continue') }}</button>
        </div>
    </div>
</div>
<div class="px-4 py-4" id="csPicker">
    @if($jobs->isEmpty())
        <div class="text-center text-muted py-4">{{ __('Every open or completed job already has a cost sheet.') }}</div>
    @else
        <label class="form-label" for="csPickJob">{{ __('Job') }} <span class="text-danger">*</span></label>
        <x-common.jobs :jobs="$jobs" id="csPickJob" name="cs_job" :required="true"/>
        <div class="form-text">{{ __('Open and completed jobs without a cost sheet.') }}</div>
    @endif
</div>
<script>
    (function () {
        initTomSelectForm($('#csPicker'));
        const open = () => {
            const id = $('#csPickJob').val();
            if (!id) { toastr.error(@json(__('Choose the job.'))); return; }
            const no = $('#csPickJob option:selected').text().trim();
            bootstrap.Modal.getInstance(document.getElementById('globalModal'))?.hide();
            // the next modal opens once this one has closed
            $('#globalModal').one('hidden.bs.modal', () => webModal.openGlobalModal({
                title: @json(__('Cost Sheet')) + ' - ' + no, url: GLOBAL_FN.buildUrl('invoice/cost-sheets/job/' + id),
                content: null, size: '3xl', scroll: true, heightPct: 90,
            }));
        };
        $('#csPickOpen').on('click', open);
        $('#csPickJob').on('change', function () { if (this.value) open(); });
        setTimeout(() => $('#csPickJob')[0]?.tomselect?.focus(), 400);
    })();
</script>
