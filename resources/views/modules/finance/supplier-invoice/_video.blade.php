{{-- "Watch video" for the Supplier Invoice module: the modal (opened from the link next to the page title) --}}
<div class="modal fade" id="supplierInvoiceVideoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-play-circle me-2 text-primary"></i>{{ __('Supplier Invoices: how it works') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body p-0 bg-dark">
                <video id="supplierInvoiceVideoEl" class="w-100 d-block" controls preload="none" poster="{{ asset('videos/supplier-invoice-module-poster.png') }}">
                    <source src="{{ asset('videos/supplier-invoice-module.mp4') }}" type="video/mp4">
                    {{ __('Your browser cannot play this video.') }}
                </video>
            </div>
            <div class="modal-footer py-2 justify-content-start"><span class="imp-help text-muted small">{{ __('How a supplier invoice works, the supplier invoice list explained, and how to inspect one. With a voice-over.') }}</span></div>
        </div>
    </div>
</div>
<script>
    (function () {
        const m = document.getElementById('supplierInvoiceVideoModal'), v = document.getElementById('supplierInvoiceVideoEl');
        if (!m || !v) return;
        m.addEventListener('shown.bs.modal', () => { v.play().catch(() => {}); });
        m.addEventListener('hidden.bs.modal', () => { v.pause(); v.currentTime = 0; });
    })();
</script>
