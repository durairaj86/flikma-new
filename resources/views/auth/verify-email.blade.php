@section('page-title', 'Verify Your Email')
<x-app-layout>
    <div class="d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 90px);">
        <div class="w-100 p-4" style="max-width: 480px;">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-finance-subtle text-finance mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-shield-lock fs-2"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-2">{{ __('Verify Your Email') }}</h3>
                    <p class="text-muted mb-1">{{ __('We sent a 6 digit code to') }}</p>
                    <p class="fw-semibold text-dark mb-3">{{ $maskedEmail }}</p>

                    @if (session('status') == 'otp-sent')
                        <div class="alert alert-success border-0 shadow-sm small mb-3 text-start"><i class="bi bi-check-circle me-1"></i>{{ __('A new code has been sent. Please check your inbox.') }}</div>
                    @endif
                    @if ($sendFailed || session('status') == 'verification-link-failed')
                        <div class="alert alert-danger border-0 shadow-sm small mb-3 text-start">{{ __('We could not send the code right now. Please try again in a moment.') }}</div>
                    @endif
                    @if (!empty($delayed))
                        <div class="alert alert-warning border-0 shadow-sm small mb-3 text-start"><i class="bi bi-hourglass-split me-1"></i>{{ __('The code email has not been delivered yet. We keep trying. You can also press Resend code.') }}</div>
                    @endif
                    @error('otp')
                        <div class="alert alert-danger border-0 shadow-sm small mb-3 text-start"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                    @enderror

                    <form method="POST" action="{{ route('verification.otp') }}" id="otpForm" autocomplete="off">
                        @csrf
                        <input type="hidden" name="otp" id="otpValue">
                        <div class="d-flex justify-content-center gap-2 mb-2" id="otpBoxes" dir="ltr">
                            @for ($i = 0; $i < 6; $i++)
                                <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" class="otp-box form-control text-center fw-bold" aria-label="{{ __('Digit :n', ['n' => $i + 1]) }}" @if($i === 0) autofocus @endif>
                            @endfor
                        </div>
                        <div class="small mb-3" id="otpTimer" data-seconds="{{ $state['expires_in'] }}" data-active="{{ $state['active'] ? 1 : 0 }}">
                            <span class="text-muted" id="otpLabel">{{ __('Code expires in') }}</span> <strong id="otpLeft" class="text-dark">--:--</strong>
                        </div>
                        <button type="submit" id="otpSubmit" class="btn btn-finance w-100 py-3 fw-bold mb-2 shadow-sm text-uppercase tracking-wider" disabled>{{ __('Verify') }}</button>
                        <div class="small text-muted mb-1">{{ __('The code works one time only and is valid for :m minutes.', ['m' => $ttl]) }}</div>
                    </form>

                    <form method="POST" action="{{ route('verification.send') }}" class="mt-3">
                        @csrf
                        <button type="submit" id="resendBtn" class="btn btn-outline-secondary w-100 py-2" data-wait="{{ $state['resend_in'] }}" disabled>
                            <i class="bi bi-arrow-repeat me-1"></i><span id="resendText">{{ __('Resend code') }}</span>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="pt-3 mt-3 border-top">
                        @csrf
                        <button type="submit" class="btn btn-link text-muted small text-decoration-none">{{ __('Log Out') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .otp-box { width: 48px; height: 56px; font-size: 1.5rem; padding: 0; border-radius: 10px; }
        .otp-box:focus { border-color: #0d9488; box-shadow: 0 0 0 .2rem rgba(13, 148, 136, .2); }
        .otp-box.filled { border-color: #0d9488; background: #f0fdfa; }
        @media (max-width: 420px) { .otp-box { width: 40px; height: 50px; } }
    </style>
    <script>
        (function () {
            var boxes = Array.prototype.slice.call(document.querySelectorAll('.otp-box'));
            var form = document.getElementById('otpForm');
            var hidden = document.getElementById('otpValue');
            var submit = document.getElementById('otpSubmit');
            var T = {expired: @json(__('Code expired')), noCode: @json(__('No code yet. Press Resend code.')), resendIn: @json(__('Resend code in')), resend: @json(__('Resend code'))};

            function collect() { return boxes.map(function (b) { return b.value; }).join(''); }
            function update() {
                var v = collect();
                boxes.forEach(function (b) { b.classList.toggle('filled', b.value !== ''); });
                submit.disabled = v.length !== 6 || expired;
                return v;
            }
            boxes.forEach(function (b, i) {
                b.addEventListener('input', function () {
                    b.value = b.value.replace(/\D/g, '').slice(-1);
                    if (b.value && boxes[i + 1]) boxes[i + 1].focus();
                    if (update().length === 6 && !expired) { hidden.value = collect(); form.submit(); }
                });
                b.addEventListener('keydown', function (e) {
                    if (e.key === 'Backspace' && !b.value && boxes[i - 1]) { boxes[i - 1].focus(); boxes[i - 1].value = ''; update(); }
                    if (e.key === 'ArrowLeft' && boxes[i - 1]) boxes[i - 1].focus();
                    if (e.key === 'ArrowRight' && boxes[i + 1]) boxes[i + 1].focus();
                });
                b.addEventListener('paste', function (e) {
                    var t = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                    if (!t) return;
                    e.preventDefault();
                    t.split('').forEach(function (ch, k) { if (boxes[k]) boxes[k].value = ch; });
                    (boxes[Math.min(t.length, 5)]).focus();
                    if (update().length === 6 && !expired) { hidden.value = collect(); form.submit(); }
                });
            });
            form.addEventListener('submit', function (e) {
                var v = collect();
                if (v.length !== 6 || expired) { e.preventDefault(); return; }
                hidden.value = v;
            });

            // Code expiry countdown
            var left = parseInt(document.getElementById('otpTimer').dataset.seconds, 10) || 0;
            var expired = left <= 0;
            var hadCode = document.getElementById('otpTimer').dataset.active === '1';
            var leftEl = document.getElementById('otpLeft');
            function paintLeft() {
                if (left <= 0) { expired = true; leftEl.textContent = hadCode ? T.expired : T.noCode; leftEl.className = 'text-danger'; update(); return; }
                var m = Math.floor(left / 60), s = left % 60;
                leftEl.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
                leftEl.className = left <= 60 ? 'text-danger' : 'text-dark';
            }
            paintLeft();
            setInterval(function () { if (left > 0) { left--; paintLeft(); } }, 1000);

            // Resend cooldown
            var wait = parseInt(document.getElementById('resendBtn').dataset.wait, 10) || 0;
            var rBtn = document.getElementById('resendBtn'), rTxt = document.getElementById('resendText');
            function paintWait() {
                if (wait > 0) { rBtn.disabled = true; rTxt.textContent = T.resendIn + ' ' + wait + 's'; }
                else { rBtn.disabled = false; rTxt.textContent = T.resend; }
            }
            paintWait();
            setInterval(function () { if (wait > 0) { wait--; paintWait(); } }, 1000);
            update();
        })();
    </script>

    <style>
        :root { --finance-primary: #0d9488; }
        .bg-finance-subtle { background-color: rgba(13, 148, 136, 0.1); }
        .text-finance { color: var(--finance-primary); }

        .btn-finance {
            background-color: var(--finance-primary);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }
        .btn-finance:hover {
            background-color: #0f766e;
            color: white;
            transform: translateY(-1px);
        }

        .tracking-wider { letter-spacing: 0.05em; }
    </style>

    <script>
        // The menu is shown as usual, but nothing in it opens until the email is verified.
        document.addEventListener('DOMContentLoaded', function () {
            var side = document.getElementById('sidebar-container');
            if (!side) return;
            var msg = @json(__('Please verify your email address to use this feature.'));
            side.addEventListener('click', function (e) {
                var a = e.target.closest('a[href]');
                if (!a) return;
                var href = (a.getAttribute('href') || '').trim();
                if (href === '' || href === '#' || href.indexOf('javascript:') === 0) return; // submenu toggles still work
                e.preventDefault();
                if (window.toastr) toastr.warning(msg, '', {timeOut: 6000, closeButton: true}); else alert(msg);
            });
        });
    </script>
</x-app-layout>
