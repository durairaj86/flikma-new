@php
    $stMap = [
        1 => ['label' => __('Planned'), 'class' => 'bg-warning-subtle text-warning'],
        2 => ['label' => __('Started'), 'class' => 'bg-primary-subtle text-primary'],
        3 => ['label' => __('Completed'), 'class' => 'bg-success-subtle text-success'],
        4 => ['label' => __('Cancelled'), 'class' => 'bg-danger-subtle text-danger'],
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
    <h6>{{ __('Trip') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Trip Date') }}:</strong><span>{{ $item->trip_date?->format('d-m-Y') ?? '-' }}</span></div>
        <div><strong>{{ __('Vehicle') }}:</strong><span>{{ $item->vehicle->plate_no ?? '-' }}</span></div>
        <div><strong>{{ __('Driver') }}:</strong><span>{{ $item->driver->name ?? '-' }}</span></div>
        <div><strong>{{ __('Distance (km)') }}:</strong><span>{{ $item->distance_km !== null ? number_format($item->distance_km, 2) : '-' }}</span></div>
        <div><strong>{{ __('From') }}:</strong><span>{{ $item->origin !== null && $item->origin !== '' ? $item->origin : '-' }}</span></div>
        <div><strong>{{ __('To') }}:</strong><span>{{ $item->destination !== null && $item->destination !== '' ? $item->destination : '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Linked To') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Job') }}:</strong><span>{{ $item->job->row_no ?? '-' }}</span></div>
        <div><strong>{{ __('Delivery Order') }}:</strong><span>{{ $item->deliveryOrder->row_no ?? '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Money') }}</h6>
    <div class="info-grid">
        <div><strong>{{ __('Freight Charged') }}:</strong><span>{{ $item->freight_amount !== null ? number_format($item->freight_amount, 2) : '-' }}</span></div>
        <div><strong>{{ __('Driver Allowance') }}:</strong><span>{{ $item->driver_allowance !== null ? number_format($item->driver_allowance, 2) : '-' }}</span></div>
        <div><strong>{{ __('Fuel') }}:</strong><span>{{ $item->fuel_cost !== null ? number_format($item->fuel_cost, 2) : '-' }}</span></div>
        <div><strong>{{ __('Other Costs') }}:</strong><span>{{ $item->other_cost !== null ? number_format($item->other_cost, 2) : '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Notes') }}</h6>
    <div class="info-grid">
        <div style="grid-column: 1 / -1;"><strong>{{ __('Remarks') }}:</strong><span>{{ $item->remarks !== null && $item->remarks !== '' ? $item->remarks : '-' }}</span></div>
    </div>
</div>

<div class="section">
    <h6>{{ __('Result') }}</h6>
    @php
        $cost = (float) $item->driver_allowance + (float) $item->fuel_cost + (float) $item->other_cost;
        $margin = (float) $item->freight_amount - $cost;
    @endphp
    <div class="info-grid">
        <div><strong>{{ __('Freight') }}:</strong><span>{{ number_format((float) $item->freight_amount, 2) }}</span></div>
        <div><strong>{{ __('Total Cost') }}:</strong><span>{{ number_format($cost, 2) }}</span></div>
        <div><strong>{{ __('Margin') }}:</strong><span class="{{ $margin < 0 ? 'text-danger fw-semibold' : 'text-success fw-semibold' }}">{{ number_format($margin, 2) }}</span></div>
        <div><strong>{{ __('Started') }}:</strong><span>{{ $item->started_at ? \Illuminate\Support\Carbon::parse($item->started_at)->format('d-m-Y H:i') : '-' }}</span></div>
        <div><strong>{{ __('Completed') }}:</strong><span>{{ $item->completed_at ? \Illuminate\Support\Carbon::parse($item->completed_at)->format('d-m-Y H:i') : '-' }}</span></div>
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
