@section('page-title', __('Journal Voucher') . ' ' . $journalVoucher->row_no)
@section('hide-topbar', true)
<x-app-layout>
    @php
        $v = $journalVoucher;
        $typeLabel = \App\Enums\JournalVoucherTypeEnum::tryFrom((int) $v->voucher_type)?->label() ?? '-';
        $statusLabel = \App\Enums\JournalVoucherStatusEnum::tryFrom((int) $v->status)?->label() ?? '-';
    @endphp


    <main class="px-3 pb-4">
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-3">
            <div>
                <h4 class="fw-bold text-dark mb-0">{{ __('Journal Voucher') }} {{ $v->row_no }}</h4>
                <div class="text-muted small mt-1">{{ $typeLabel }} · {{ \Carbon\Carbon::parse($v->voucher_date)->translatedFormat('d M Y') }}</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-pill {{ (int) $v->status === 2 ? 'bg-success' : ((int) $v->status === 3 ? 'bg-danger' : 'bg-secondary') }} px-3 py-2">{{ $statusLabel }}</span>
                <a href="{{ route('finance.journal_vouchers.index') }}" class="btn btn-outline-secondary rounded-pill px-3"><i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}</a>
                <a href="{{ route('finance.journal_vouchers.print', $v->id) }}" target="_blank" class="btn btn-outline-primary rounded-pill px-3"><i class="bi bi-printer me-1"></i>{{ __('Print') }}</a>
                <a href="{{ route('finance.journal_vouchers.download', $v->id) }}" class="btn btn-primary rounded-pill px-3"><i class="bi bi-download me-1"></i>{{ __('Download') }}</a>
            </div>
        </div>

        @include('modules.finance.journal_voucher._overview')
    </main>
</x-app-layout>
