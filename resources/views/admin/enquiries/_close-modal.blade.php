{{-- Close-with-reason modal. Open it from any button with data-bs-toggle="modal" data-bs-target="#closeEnquiryModal"
     data-close-url="…" data-name="…" --}}
<div class="modal fade" id="closeEnquiryModal" tabindex="-1" aria-labelledby="closeEnquiryTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content border-0 shadow" id="closeEnquiryForm">
            @csrf
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold" id="closeEnquiryTitle"><i class="bi bi-check2-circle text-success me-2"></i>Close enquiry</h5>
                    <div class="text-muted small mt-1" data-close-name></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label class="form-label small fw-semibold text-secondary">Why are you closing it?</label>
                <div class="ce-reasons">
                    @foreach(\App\Models\ContactMessage::CLOSE_REASONS as $key => $label)
                        <label class="ce-reason">
                            <input type="radio" name="close_reason" value="{{ $key }}" required>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="mt-3">
                    <label class="form-label small fw-semibold text-secondary" for="closeEnquiryNote">
                        Note <span class="text-muted fw-normal" data-note-hint>(optional)</span>
                    </label>
                    <textarea name="close_note" id="closeEnquiryNote" rows="3" maxlength="1000" class="form-control" placeholder="Add details, or type your own reason…"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-1"><i class="bi bi-check2-circle"></i> Close enquiry</button>
            </div>
        </form>
    </div>
</div>

@push('styles')
<style>
    .ce-reasons { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
    @media (max-width: 575.98px) { .ce-reasons { grid-template-columns: 1fr; } }
    .ce-reason { display: flex; align-items: center; gap: 8px; border: 1px solid #e5e7eb; border-radius: 10px; padding: 9px 12px; cursor: pointer; font-size: .875rem; margin: 0; transition: border-color .12s, background .12s; }
    .ce-reason:hover { border-color: #a7f3d0; }
    .ce-reason input { accent-color: #16a34a; margin: 0; }
    .ce-reason:has(input:checked) { border-color: #16a34a; background: #f0fdf4; font-weight: 600; }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        const modal = document.getElementById('closeEnquiryModal');
        const form = document.getElementById('closeEnquiryForm');
        const note = document.getElementById('closeEnquiryNote');
        const hint = modal.querySelector('[data-note-hint]');
        // a typed reason is required only for "Other"
        const syncNote = () => {
            const other = form.querySelector('input[name=close_reason]:checked')?.value === 'other';
            note.required = other;
            hint.textContent = other ? '(required)' : '(optional)';
        };
        form.addEventListener('change', syncNote);
        modal.addEventListener('show.bs.modal', (e) => {
            const btn = e.relatedTarget;
            if (! btn) return;
            form.action = btn.dataset.closeUrl;
            modal.querySelector('[data-close-name]').textContent = btn.dataset.name || '';
            form.reset();
            syncNote();
        });
        modal.addEventListener('shown.bs.modal', () => form.querySelector('input[name=close_reason]').focus());
    })();
</script>
@endpush
