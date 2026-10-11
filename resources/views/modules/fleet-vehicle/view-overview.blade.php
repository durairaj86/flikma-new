@php
    $stMap = [
        1 => ['label' => __('Active'), 'class' => 'bg-success-subtle text-success'],
        2 => ['label' => __('Maintenance'), 'class' => 'bg-warning-subtle text-warning'],
        3 => ['label' => __('Inactive'), 'class' => 'bg-secondary-subtle text-secondary'],
    ];
    $stInfo = $stMap[(int) $item->status] ?? ['label' => __('Unknown'), 'class' => 'bg-secondary-subtle text-secondary'];
@endphp
<style>
    .section { margin-bottom: 1.5rem; }
    .section h6 { font-size: 14px; font-weight: 600; background: #f7f7f9; padding: 8px 10px; border-radius: 4px; margin-bottom: 1rem; }
    .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.6rem 1.5rem; font-size: 13.5px; line-height: 1.6; }
    .info-grid div { display: flex; justify-content: space-between; border-bottom: 1px dotted #eee; padding-bottom: 3px; }
    .info-grid strong { color: #333; min-width: 140px; font-weight: 600; }
    .info-grid span { color: #555; flex: 1; text-align: left; margin-left: 8px; }
    .invoice-no-heading { font-size: 1.5rem; font-weight: 700; color: #1a1a1a; margin-bottom: 1rem; }
    .act-list { list-style: none; margin: 0; padding: 0; }
    .act-list li { padding: .45rem 0; border-bottom: 1px dotted #e5e7eb; font-size: 13px; }
</style>
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div class="invoice-no-heading mb-0">#{{ $item->row_no }}
        <span class="badge {{ $stInfo['class'] }} rounded-pill px-3 py-1 fw-semibold fs-6 align-middle ms-2">{{ $stInfo['label'] }}</span>
    </div>
</div>

<div class="section">
    <h6>{{ __('Vehicle') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Plate No') }}:</strong><span>{{ $item->plate_no !== null && $item->plate_no !== '' ? $item->plate_no : '-' }}</span></div>
        <div><strong>{{ __('Type') }}:</strong><span>{{ ['truck' => __('Truck'), 'trailer' => __('Trailer'), 'container_truck' => __('Container truck'), 'van' => __('Van'), 'pickup' => __('Pickup'), 'other' => __('Other')][$item->type] ?? ($item->type ?: '-') }}</span></div>
        <div><strong>{{ __('Make / Model') }}:</strong><span>{{ $item->make_model !== null && $item->make_model !== '' ? $item->make_model : '-' }}</span></div>
        <div><strong>{{ __('Capacity (kg)') }}:</strong><span>{{ $item->capacity_kg !== null ? number_format($item->capacity_kg, 2) : '-' }}</span></div>
        <div><strong>{{ __('Usual Driver') }}:</strong><span>{{ $item->driver->name ?? '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Papers') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Registration Expiry') }}:</strong><span>{{ $item->registration_expiry?->format('d-m-Y') ?? '-' }}</span></div>
        <div><strong>{{ __('Insurance Expiry') }}:</strong><span>{{ $item->insurance_expiry?->format('d-m-Y') ?? '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Notes') }}</h6>
    <div class="info-grid">
        <div style="grid-column: 1 / -1;"><strong>{{ __('Remarks') }}:</strong><span>{{ $item->remarks !== null && $item->remarks !== '' ? $item->remarks : '-' }}</span></div>
    </div>
</div>

@if($activity->isNotEmpty())
    <div class="section">
        <h6>{{ __('Activity') }}</h6>
        <ul class="act-list">
            @foreach($activity as $a)
                <li><span class="text-muted">{{ $a->created_at?->format('d-m-Y H:i') }}</span> &middot; <strong>{{ $a->user_id['name'] ?? '-' }}</strong> &middot; {{ ucfirst($a->action) }}</li>
            @endforeach
        </ul>
    </div>
@endif
