@php
    $statusMap = [
        1 => ['label' => 'Draft',     'class' => 'bg-warning-subtle text-warning'],
        2 => ['label' => 'Approved',  'class' => 'bg-success-subtle text-success'],
        3 => ['label' => 'Cancelled', 'class' => 'bg-danger-subtle text-danger'],
    ];
    $statusInfo = $statusMap[$creditNote->status] ?? ['label' => 'Unknown', 'class' => 'bg-secondary-subtle text-secondary'];
@endphp

<style>
    .cn-overview-label { font-size: .78rem; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; color: #64748b; }
    .cn-overview-value { font-size: .9rem; color: #1e293b; font-weight: 500; }
    .cn-section-title { font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; border-bottom: 1px solid #f1f5f9; padding-bottom: .4rem; margin-bottom: .75rem; }
    .cn-detail-row { display: flex; justify-content: space-between; align-items: baseline; padding: .35rem 0; border-bottom: 1px solid #f8fafc; }
    .cn-detail-row:last-child { border-bottom: none; }
    .cn-amount-row { display: flex; justify-content: space-between; align-items: center; padding: .3rem 0; }
    .cn-grand-row { background: #f8fafc; margin: 0 -1rem; padding: .5rem 1rem; border-radius: 8px; }
    .x-small { font-size: .75rem; }
    .tabular-nums { font-variant-numeric: tabular-nums; }
</style>

{{-- Action buttons --}}
<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
    <div>
        <span class="badge {{ $statusInfo['class'] }} rounded-pill px-3 py-1 fw-semibold">
            {{ $statusInfo['label'] }}
        </span>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm"
                onclick="CREDIT_NOTE.printPreview('{{ $creditNote->id }}')">
            <i class="bi bi-printer me-1"></i> Print
        </button>
    </div>
</div>

{{-- Header card --}}
<div class="card border-0 shadow-sm mb-3" style="border-radius: .75rem; overflow: hidden;">
    <div class="card-body p-3">

        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <div class="fw-bold fs-5 text-dark">#{{ $creditNote->row_no }}</div>
                <div class="text-muted small">Credit Note</div>
            </div>
            <div class="text-end">
                <div class="fw-bold fs-5" style="color: #0b6aa0;">
                    {{ number_format($creditNote->grand_total, decimals()) }}
                    <small class="text-muted fw-normal fs-6">{{ strtoupper($creditNote->currency ?? 'SAR') }}</small>
                </div>
                <div class="text-muted small">Grand Total</div>
            </div>
        </div>

        {{-- Basic Info --}}
        <div class="cn-section-title">Basic Information</div>

        <div class="cn-detail-row">
            <span class="cn-overview-label">Date</span>
            <span class="cn-overview-value">{{ \Carbon\Carbon::parse($creditNote->posted_at)->format('d M Y') }}</span>
        </div>
        <div class="cn-detail-row">
            <span class="cn-overview-label">Customer</span>
            <span class="cn-overview-value">{{ $creditNote->customer->name_en ?? '-' }}</span>
        </div>
        <div class="cn-detail-row">
            <span class="cn-overview-label">Job</span>
            <span class="cn-overview-value">{{ $creditNote->job_no ?? '-' }}</span>
        </div>
        <div class="cn-detail-row">
            <span class="cn-overview-label">Invoice Ref</span>
            <span class="cn-overview-value">{{ $creditNote->invoice->row_no ?? '-' }}</span>
        </div>
        <div class="cn-detail-row">
            <span class="cn-overview-label">Type</span>
            <span class="cn-overview-value">{{ ucfirst(str_replace('_', ' ', $creditNote->credit_note_type ?? '-')) }}</span>
        </div>
        @if($creditNote->reason)
        <div class="cn-detail-row">
            <span class="cn-overview-label">Reason</span>
            <span class="cn-overview-value text-end" style="max-width: 65%;">{{ $creditNote->reason }}</span>
        </div>
        @endif

    </div>
</div>

{{-- Totals card --}}
<div class="card border-0 shadow-sm mb-3" style="border-radius: .75rem;">
    <div class="card-body p-3">
        <div class="cn-section-title">Amounts</div>

        <div class="cn-amount-row">
            <span class="cn-overview-label">Subtotal</span>
            <span class="cn-overview-value tabular-nums">{{ number_format($creditNote->sub_total, decimals()) }}</span>
        </div>
        <div class="cn-amount-row">
            <span class="cn-overview-label">Tax</span>
            <span class="cn-overview-value tabular-nums">{{ number_format($creditNote->tax_total, decimals()) }}</span>
        </div>
        <hr class="my-2">
        <div class="cn-amount-row cn-grand-row">
            <span class="fw-bold text-dark">Grand Total</span>
            <span class="fw-bold fs-6 tabular-nums" style="color: #0b6aa0;">
                {{ number_format($creditNote->grand_total, decimals()) }}
                <small class="text-muted fw-normal">{{ strtoupper($creditNote->currency ?? 'SAR') }}</small>
            </span>
        </div>
    </div>
</div>

{{-- Line items card --}}
@if($creditNote->creditNoteSubs && $creditNote->creditNoteSubs->count())
<div class="card border-0 shadow-sm mb-3" style="border-radius: .75rem; overflow: hidden;">
    <div class="card-header bg-white border-bottom py-2 px-3">
        <span class="fw-semibold small text-dark">
            <i class="bi bi-list-ul me-1 text-muted"></i>
            Line Items ({{ $creditNote->creditNoteSubs->count() }})
        </span>
    </div>
    <div class="p-0">
        <table class="table table-sm align-middle mb-0" style="font-size: .82rem;">
            <thead style="background: #f8fafc;">
                <tr>
                    <th class="px-3 py-2 fw-semibold text-muted border-0" style="font-size:.72rem;">Description</th>
                    <th class="px-3 py-2 fw-semibold text-muted border-0 text-end" style="font-size:.72rem;">Qty</th>
                    <th class="px-3 py-2 fw-semibold text-muted border-0 text-end" style="font-size:.72rem;">Price</th>
                    <th class="px-3 py-2 fw-semibold text-muted border-0 text-end" style="font-size:.72rem;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($creditNote->creditNoteSubs as $item)
                <tr>
                    <td class="px-3 py-2">
                        <div class="fw-medium">{{ $descriptions[$item->description_id] ?? $item->description ?? '-' }}</div>
                        @if($item->comment)
                            <div class="text-muted x-small">{{ $item->comment }}</div>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-end">{{ $item->quantity }}</td>
                    <td class="px-3 py-2 text-end">{{ number_format($item->unit_price, decimals()) }}</td>
                    <td class="px-3 py-2 text-end fw-semibold">{{ number_format($item->total, decimals()) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Terms --}}
@if($creditNote->terms)
<div class="card border-0 shadow-sm mb-3" style="border-radius: .75rem;">
    <div class="card-body p-3">
        <div class="cn-section-title">Terms &amp; Conditions</div>
        <p class="mb-0 small text-secondary">{{ $creditNote->terms }}</p>
    </div>
</div>
@endif

{{-- Documents --}}
@if($creditNote->documents && $creditNote->documents->count())
<div class="card border-0 shadow-sm mb-3" style="border-radius: .75rem;">
    <div class="card-header bg-white border-bottom py-2 px-3">
        <span class="fw-semibold small text-dark">
            <i class="bi bi-paperclip me-1 text-muted"></i>
            Documents ({{ $creditNote->documents->count() }})
        </span>
    </div>
    <div class="card-body p-3">
        <div class="row g-2">
            @foreach($creditNote->documents as $doc)
            <div class="col-12">
                <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                   class="d-flex align-items-center gap-2 text-decoration-none p-2 rounded"
                   style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <i class="bi bi-file-earmark-text text-primary"></i>
                    <span class="small text-dark fw-medium">{{ $doc->file_name }}</span>
                    <i class="bi bi-box-arrow-up-right ms-auto text-muted" style="font-size: .75rem;"></i>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif
