@extends('admin.layouts.app')
@section('title', 'Settings')
@section('content')
    <div class="mb-4">
        <h4 class="fw-bold text-dark mb-1">Settings</h4>
        <p class="text-muted small mb-0">Platform-wide settings: application, mail, payment and social links.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white border-0 py-3 px-4"><span class="fw-bold text-dark">General settings</span></div>
        <div class="card-body p-4 pt-2">
            <form action="{{ route('admin.settings.general.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="sAppName">Application name</label>
                        <input type="text" id="sAppName" name="app_name" maxlength="255" class="form-control" value="{{ old('app_name', $settings['app_name'] ?? config('app.name')) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="sAppUrl">Application URL</label>
                        <input type="url" id="sAppUrl" name="app_url" maxlength="255" class="form-control" value="{{ old('app_url', $settings['app_url'] ?? config('app.url')) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="sAdminEmail">Admin email</label>
                        <input type="email" id="sAdminEmail" name="admin_email" maxlength="255" class="form-control" value="{{ old('admin_email', $settings['admin_email'] ?? '') }}" placeholder="support@flikma.com">
                        <div class="form-text">Website contact-form messages are emailed here (support@flikma.com when empty).</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="sDesc">App description</label>
                        <input type="text" id="sDesc" name="app_description" maxlength="500" class="form-control" value="{{ old('app_description', $settings['app_description'] ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="sLogo">Logo</label>
                        <input type="file" id="sLogo" name="logo" class="form-control" accept="image/png,image/jpeg,image/svg+xml">
                        @if(!empty($settings['logo']))<img src="{{ asset('storage/' . $settings['logo']) }}" alt="Current logo" class="mt-2" style="height: 32px;">@endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="sFavicon">Favicon</label>
                        <input type="file" id="sFavicon" name="favicon" class="form-control" accept="image/png,image/x-icon">
                        @if(!empty($settings['favicon']))<img src="{{ asset('storage/' . $settings['favicon']) }}" alt="Current favicon" class="mt-2" style="height: 24px;">@endif
                    </div>
                </div>
                <div class="mt-4"><button type="submit" class="btn btn-primary">Save general settings</button></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-4">
        <div class="card-header bg-white border-0 py-3 px-4">
            <span class="fw-bold text-dark">SMTP settings</span>
            <div class="small text-muted">Kept for reference. Emails are still sent with the MAIL_* values in <code>.env</code> (check them with <code>php artisan mail:check</code>).</div>
        </div>
        <div class="card-body p-4 pt-2">
            <form action="{{ route('admin.settings.smtp.update') }}" method="POST">@csrf
                <div class="row g-3">
                    <div class="col-md-4"><label class="form-label" for="mMailer">Mail driver</label><input type="text" id="mMailer" name="mail_mailer" maxlength="50" class="form-control" value="{{ $smtp['mail_mailer'] ?? 'smtp' }}" required></div>
                    <div class="col-md-4"><label class="form-label" for="mHost">Host</label><input type="text" id="mHost" name="mail_host" maxlength="255" class="form-control" value="{{ $smtp['mail_host'] ?? '' }}" required></div>
                    <div class="col-md-4"><label class="form-label" for="mPort">Port</label><input type="text" inputmode="numeric" id="mPort" name="mail_port" maxlength="5" class="form-control integer" value="{{ $smtp['mail_port'] ?? '587' }}" required></div>
                    <div class="col-md-4"><label class="form-label" for="mUser">Username</label><input type="text" id="mUser" name="mail_username" maxlength="255" class="form-control" value="{{ $smtp['mail_username'] ?? '' }}" autocomplete="off"></div>
                    <div class="col-md-4"><label class="form-label" for="mPass">Password</label><input type="password" id="mPass" name="mail_password" maxlength="255" class="form-control" autocomplete="new-password" placeholder="{{ !empty($smtp['mail_password']) ? '•••••••• (saved — leave empty to keep)' : '' }}"></div>
                    <div class="col-md-4"><label class="form-label" for="mEnc">Encryption</label><input type="text" id="mEnc" name="mail_encryption" maxlength="20" class="form-control" value="{{ $smtp['mail_encryption'] ?? 'tls' }}"></div>
                    <div class="col-md-6"><label class="form-label" for="mFrom">From address</label><input type="email" id="mFrom" name="mail_from_address" maxlength="255" class="form-control" value="{{ $smtp['mail_from_address'] ?? '' }}"></div>
                    <div class="col-md-6"><label class="form-label" for="mFromName">From name</label><input type="text" id="mFromName" name="mail_from_name" maxlength="255" class="form-control" value="{{ $smtp['mail_from_name'] ?? '' }}"></div>
                </div>
                <div class="mt-4"><button type="submit" class="btn btn-primary">Save SMTP settings</button></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-4">
        <div class="card-header bg-white border-0 py-3 px-4">
            <span class="fw-bold text-dark">Payment gateway</span>
            <div class="small text-muted">Stored for when online payment is connected; secrets are kept encrypted.</div>
        </div>
        <div class="card-body p-4 pt-2">
            <form action="{{ route('admin.settings.payment.update') }}" method="POST">@csrf
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label" for="pGateway">Default gateway</label>
                        <select id="pGateway" name="payment_gateway" class="form-select">
                            @foreach(['manual' => 'Manual / offline', 'stripe' => 'Stripe', 'paypal' => 'PayPal'] as $k => $l)
                                <option value="{{ $k }}" @selected(($payment['payment_gateway'] ?? 'manual') === $k)>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6"><label class="form-label" for="pStripeKey">Stripe key</label><input type="text" id="pStripeKey" name="stripe_key" maxlength="255" class="form-control" value="{{ $payment['stripe_key'] ?? '' }}" autocomplete="off"></div>
                    <div class="col-md-6"><label class="form-label" for="pStripeSecret">Stripe secret</label><input type="password" id="pStripeSecret" name="stripe_secret" maxlength="255" class="form-control" autocomplete="new-password" placeholder="{{ !empty($payment['stripe_secret']) ? '•••••••• (saved — leave empty to keep)' : '' }}"></div>
                    <div class="col-md-6"><label class="form-label" for="pPaypalId">PayPal client ID</label><input type="text" id="pPaypalId" name="paypal_client_id" maxlength="255" class="form-control" value="{{ $payment['paypal_client_id'] ?? '' }}" autocomplete="off"></div>
                    <div class="col-md-6"><label class="form-label" for="pPaypalSecret">PayPal secret</label><input type="password" id="pPaypalSecret" name="paypal_secret" maxlength="255" class="form-control" autocomplete="new-password" placeholder="{{ !empty($payment['paypal_secret']) ? '•••••••• (saved — leave empty to keep)' : '' }}"></div>
                </div>
                <div class="mt-4"><button type="submit" class="btn btn-primary">Save payment settings</button></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-4">
        <div class="card-header bg-white border-0 py-3 px-4"><span class="fw-bold text-dark">Social media links</span></div>
        <div class="card-body p-4 pt-2">
            <form action="{{ route('admin.settings.social.update') }}" method="POST">@csrf
                <div class="row g-3">
                    @foreach(['facebook' => 'Facebook', 'twitter' => 'Twitter / X', 'linkedin' => 'LinkedIn', 'instagram' => 'Instagram', 'youtube' => 'YouTube'] as $k => $l)
                        <div class="col-md-4"><label class="form-label" for="so{{ $k }}">{{ $l }}</label><input type="url" id="so{{ $k }}" name="{{ $k }}" maxlength="255" class="form-control" value="{{ $social[$k] ?? '' }}" placeholder="https://"></div>
                    @endforeach
                </div>
                <div class="mt-4"><button type="submit" class="btn btn-primary">Save social links</button></div>
            </form>
        </div>
    </div>
@endsection
