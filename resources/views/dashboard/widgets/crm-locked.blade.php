{{-- A CRM widget for a user without the CRM Dashboard right: says so instead of showing numbers. --}}
<div class="kpi {{ $size === 'small' ? 'kpi-small' : 'kpi-medium' }}" style="--kc: #64748b; --kbg: #f1f5f9;">
    <div class="kpi-head">
        <span class="kpi-icon"><i class="bi bi-lock"></i></span>
        <span class="kpi-title">{{ $title }}</span>
    </div>
    <div class="kpi-note" style="margin-top: 12px;">{{ __('You do not have access to the CRM Dashboard.') }}</div>
</div>
