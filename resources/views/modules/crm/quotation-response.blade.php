<!doctype html>
<html lang="{{ $rtl ? 'ar' : 'en' }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ __('Quotation') }} {{ $quotation->row_no }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap{{ $rtl ? '.rtl' : '' }}.min.css" rel="stylesheet">
    <style>
        body { background: #f6f7f9; }
        .q-card { max-width: 760px; }
        .q-head { background: linear-gradient(135deg, #2d3a4f 0%, #4f46e5 100%); color: #fff; border-radius: 12px 12px 0 0; }
        .q-stat small { display: block; opacity: .75; font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; }
        .q-stat b { font-size: .95rem; }
        .table th { font-size: .75rem; text-transform: uppercase; color: #6b7280; }
    </style>
</head>
<body>
<div class="container py-4 q-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            @if(!empty($company->logo))
                <img src="{{ asset('storage/' . $company->logo) }}" alt="" style="height:36px">
            @endif
            <span class="fw-bold">{{ $rtl ? ($company->name_ar ?: $company->name) : $company->name }}</span>
        </div>
        <a class="small" href="?lang={{ $rtl ? 'en' : 'ar' }}">{{ $rtl ? 'English' : 'العربية' }}</a>
    </div>

    @if(session('notice'))
        <div class="alert alert-info">{{ session('notice') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="q-head p-4">
            <div class="small opacity-75">{{ __('Quotation') }}</div>
            <h3 class="fw-bold mb-1">{{ $quotation->row_no }}</h3>
            <div class="opacity-75">{{ $party }}</div>
            <div class="row g-3 mt-2">
                <div class="col-6 col-md-3 q-stat"><small>{{ __('Route') }}</small><b>{{ $quotation->pol ?: '—' }} → {{ $quotation->pod ?: '—' }}</b></div>
                <div class="col-6 col-md-3 q-stat"><small>{{ __('Quotation Date') }}</small><b>{{ $quotation->posted_at }}</b></div>
                <div class="col-6 col-md-3 q-stat"><small>{{ __('Valid Until') }}</small><b>{{ $quotation->valid_until }}</b></div>
                <div class="col-6 col-md-3 q-stat"><small>{{ __('Total') }}</small><b>{{ number_format($total, 2) }} {{ $currency }}</b></div>
            </div>
        </div>
        <div class="card-body p-4">
            @if($quotation->incoterm || $quotation->carrier || $quotation->commodity)
                <div class="row small mb-3 g-2">
                    @if($quotation->incoterm)<div class="col-md-4"><span class="text-muted">{{ __('Incoterm') }}:</span> {{ $quotation->incoterm }}</div>@endif
                    @if($quotation->carrier)<div class="col-md-4"><span class="text-muted">{{ __('Carrier') }}:</span> {{ $quotation->carrier }}</div>@endif
                    @if($quotation->commodity)<div class="col-md-4"><span class="text-muted">{{ __('Commodity') }}:</span> {{ $quotation->commodity }}</div>@endif
                </div>
            @endif

            @if($quotation->containers->isNotEmpty())
                <div class="small mb-3"><span class="text-muted">{{ __('Containers') }}:</span>
                    {{ $quotation->containers->map(fn($c) => trim(((float) $c->qty > 0 ? (float) $c->qty . ' x ' : '') . $c->container_size))->filter()->implode(', ') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead><tr>
                        <th>{{ __('Charge') }}</th><th class="text-end">{{ __('Qty') }}</th><th>{{ __('Unit') }}</th>
                        <th class="text-end">{{ __('Rate') }}</th><th class="text-end">{{ __('Amount') }} ({{ $currency }})</th>
                    </tr></thead>
                    <tbody>
                    @forelse($quotation->charges as $c)
                        <tr>
                            <td>{{ $c->charge_description }}@if($c->remarks)<div class="small text-muted">{{ $c->remarks }}</div>@endif</td>
                            <td class="text-end">{{ (float) $c->qty }}</td>
                            <td>{{ $c->unit }}</td>
                            <td class="text-end">{{ $c->amount_per_qty !== null ? number_format((float) $c->amount_per_qty, 2) . ' ' . $c->currency : '' }}</td>
                            <td class="text-end">{{ number_format((float) $c->local_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted text-center">{{ __('No charges listed.') }}</td></tr>
                    @endforelse
                    </tbody>
                    <tfoot><tr><td colspan="4" class="text-end fw-bold">{{ __('Total') }}</td><td class="text-end fw-bold">{{ number_format($total, 2) }}</td></tr></tfoot>
                </table>
            </div>

            @if($quotation->terms)
                <div class="small text-muted mb-3" style="white-space:pre-line"><b>{{ __('Terms & Conditions') }}</b>
{{ $quotation->terms }}</div>
            @endif

            <hr>
            @if($closed)
                <div class="alert {{ (int) $quotation->customer_status === \App\Models\Quotation\Quotation::CUSTOMER_ACCEPTED ? 'alert-success' : 'alert-secondary' }} mb-0">
                    {{ $closed }}
                    @if($quotation->customer_response_at)
                        <div class="small mt-1">{{ (int) $quotation->customer_status === \App\Models\Quotation\Quotation::CUSTOMER_ACCEPTED ? __('Accepted on') : __('Answered on') }}
                            {{ $quotation->customer_response_at->translatedFormat('d-M-Y H:i') }}</div>
                    @endif
                </div>
            @else
                <form method="POST" action="{{ url('/q/' . $quotation->customer_token . '/respond') }}">
                    @csrf
                    <input type="hidden" name="lang" value="{{ $rtl ? 'ar' : 'en' }}">
                    <label for="note" class="form-label fw-semibold">{{ __('Your note (optional)') }}</label>
                    <textarea id="note" name="note" class="form-control mb-3" rows="2" maxlength="1000"
                              placeholder="{{ __('e.g. Please book for the first week of next month') }}">{{ old('note') }}</textarea>
                    @error('decision')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" name="decision" value="accept" class="btn btn-success px-4">{{ __('Accept quotation') }}</button>
                        <button type="submit" name="decision" value="reject" class="btn btn-outline-danger px-4"
                                onclick="return confirm({{ Illuminate\Support\Js::from(__('Reject this quotation?')) }})">{{ __('Reject') }}</button>
                    </div>
                    <div class="small text-muted mt-2">{{ __('Accepting confirms the rates above. Our team will contact you to book the shipment.') }}</div>
                </form>
            @endif
        </div>
    </div>
    @if(!empty($company->email) || !empty($company->phone))
        <div class="text-center small text-muted mt-3">{{ __('Questions?') }} {{ $company->email }} @if($company->email && $company->phone)·@endif {{ $company->phone }}</div>
    @endif
</div>
</body>
</html>
