@php
    $v = $journalVoucher;
    $typeLabel = \App\Enums\JournalVoucherTypeEnum::tryFrom((int) $v->voucher_type)?->label() ?? '-';
    $statusLabel = \App\Enums\JournalVoucherStatusEnum::tryFrom((int) $v->status)?->label() ?? '-';
@endphp
<div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pb-3">
    <div>
        <div class="fw-bold fs-5">{{ $v->row_no }}</div>
        <div class="text-muted small">{{ $typeLabel }} · {{ \Carbon\Carbon::parse($v->voucher_date)->translatedFormat('d M Y') }}</div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge rounded-pill {{ (int) $v->status === 2 ? 'bg-success' : ((int) $v->status === 3 ? 'bg-danger' : 'bg-secondary') }} px-3 py-2">{{ $statusLabel }}</span>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="JOURNAL_VOUCHER.printPreview('{{ $v->id }}')"><i class="bi bi-printer me-1"></i>{{ __('Print') }}</button>
        <button type="button" class="btn btn-sm btn-primary" onclick="JOURNAL_VOUCHER.downloadPDF('{{ $v->id }}')"><i class="bi bi-download me-1"></i>{{ __('Download') }}</button>
    </div>
</div>
@include('modules.finance.journal_voucher._overview')
