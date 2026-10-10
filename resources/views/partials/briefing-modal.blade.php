{{-- Today's briefing: opens by itself once after sign-in (owner / Operations only), and from any [data-briefing-open] button. --}}
@auth
    @if(\App\Services\Briefing\OperationsBriefing::allowed(auth()->user()))
        @php($briefingAuto = !session('briefing_seen'))
        <link href="{{ asset('css/briefing.css') }}?v={{ appVersion() }}" rel="stylesheet">
        <div class="modal fade" id="briefingModal" tabindex="-1" aria-hidden="true"
             data-url="{{ route('briefing.show') }}" data-auto="{{ $briefingAuto ? '1' : '0' }}"
             data-morning="{{ __('Good morning') }}" data-afternoon="{{ __('Good afternoon') }}" data-evening="{{ __('Good evening') }}"
             data-today="{{ __('Today is') }}" data-s-lead="{{ __('Here is what needs your attention today: ') }}" data-s-bad="{{ __('to act on now') }}" data-s-warn="{{ __('coming up') }}" data-s-info="{{ __('for today') }}" data-s-first="{{ __('Start with:') }}" data-more="{{ __('more') }}" data-sec-bad="{{ __('Act now') }}" data-sec-warn="{{ __('Coming up, do not miss') }}" data-sec-info="{{ __('Today') }}" data-sec-good="{{ __('Good news') }}" data-locale="{{ app()->getLocale() }}"
             data-empty="{{ __('All clear. Nothing needs your attention right now.') }}"
             data-error="{{ __('Could not load the briefing right now. Please try again.') }}">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-sm-down">
                <div class="modal-content border-0 shadow brief-modal">
                    <div class="brief-hero">
                        <button type="button" class="btn-close btn-close-white brief-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                        <div class="brief-hero-row">
                            <div class="brief-avatar" aria-hidden="true"><i class="bi bi-stars"></i></div>
                            <div class="brief-hero-text">
                                <div class="brief-greet" id="briefGreet"></div>
                                <div class="brief-date" id="briefDate"></div>
                            </div>
                        </div>
                        <div class="brief-stats" id="briefStats"></div>
                    </div>
                    <div class="modal-body brief-body" id="briefBody">
                        <div class="brief-skel"></div><div class="brief-skel"></div><div class="brief-skel short"></div>
                    </div>
                    <div class="modal-footer brief-foot">
                        <span class="brief-src">{{ __('From your live jobs, bookings, deliveries and invoices') }}</span>
                        <button type="button" class="brief-btn ghost" id="briefRefresh"><i class="bi bi-arrow-clockwise me-1"></i>{{ __('Refresh') }}</button>
                        <button type="button" class="brief-btn solid" data-bs-dismiss="modal">{{ __('Got it') }}</button>
                    </div>
                </div>
            </div>
        </div>
        <script src="{{ asset('js/briefing.js') }}?v={{ appVersion() }}"></script>
    @endif
@endauth
