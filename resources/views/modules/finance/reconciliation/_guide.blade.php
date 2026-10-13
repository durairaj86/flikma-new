{{-- Right-hand "how to reconcile" panel + the video modal (opened from here and from the link next to the title) --}}
<aside class="rc-aside d-none d-xl-block">
    <div class="imp-card">
        <h6 class="mb-2"><i class="bi bi-lightbulb text-warning"></i>{{ __('How to reconcile') }}</h6>
        <ol class="rc-steps">
            <li><b>{{ __('Start') }}</b><span>{{ __('Choose the bank account, the statement date and the closing balance printed on your bank statement.') }}</span></li>
            <li><b>{{ __('Tick') }}</b><span>{{ __('Tick every entry you can see on the statement. Collections and payments are in the list. Or import the CSV / Excel file and let matching entries tick themselves.') }}</span></li>
            <li><b>{{ __('Add what is missing') }}</b><span>{{ __('Bank charges or interest that are not in your books: post them from the screen. It creates a proper entry and ticks it.') }}</span></li>
            <li><b>{{ __('Difference 0.00') }}</b><span>{{ __('When the Difference card turns green, the books agree with the bank.') }}</span></li>
            <li><b>{{ __('Finish') }}</b><span>{{ __('Press Finish reconciliation. The statement is locked; use Undo to open it again.') }}</span></li>
        </ol>
        <button type="button" class="btn btn-outline-primary w-100 mt-2" data-bs-toggle="modal" data-bs-target="#rcVideo"><i class="bi bi-play-circle me-1"></i>{{ __('Watch the video') }}</button>
    </div>
</aside>

<div class="modal fade" id="rcVideo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-play-circle me-2 text-primary"></i>{{ __('How to reconcile') }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body p-0 bg-dark">
                <video id="rcVideoEl" class="w-100 d-block" controls preload="none" poster="{{ asset('videos/bank-reconciliation-poster.png') }}">
                    <source src="{{ asset('videos/bank-reconciliation.mp4') }}" type="video/mp4">
                    {{ __('Your browser cannot play this video.') }}
                </video>
            </div>
            <div class="modal-footer py-2 justify-content-start"><span class="imp-help">{{ __('About two minutes, with a voice-over. The steps are also listed on the right of the page.') }}</span></div>
        </div>
    </div>
</div>
<script>
    (function () {
        const m = document.getElementById('rcVideo'), v = document.getElementById('rcVideoEl');
        if (!m || !v) return;
        m.addEventListener('shown.bs.modal', () => { v.play().catch(() => {}); });
        m.addEventListener('hidden.bs.modal', () => { v.pause(); v.currentTime = 0; });
    })();
</script>
