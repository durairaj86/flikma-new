@foreach($customers as $c)
                        <a href="{{ url('/customers/' . $c->id) }}" data-name="{{ mb_strtolower(implode(' ', array_filter([$c->name_en, $c->name_ar, $c->email, $c->phone, $c->row_no, $c->vat_number]))) }}"
                           class="cust-split-item text-decoration-none {{ $c->id === ($activeId ?? $customer->id) ? 'active' : '' }}">
                            <div class="d-flex justify-content-between gap-2">
                                <span class="fw-semibold text-truncate">{{ $c->name_en }}</span>
                                <span class="text-muted small flex-shrink-0">{{ number_format($c->balance, 2) }}</span>
                            </div>
                            <div class="small text-muted text-truncate">{{ $c->email }}</div>
                        </a>
                    @endforeach
@if($customers->isEmpty())
    <div class="text-center text-muted small p-4">{{ __('No match found.') }}</div>
@endif

@if($customers->hasPages())
    <div class="cust-split-pager d-flex align-items-center justify-content-between gap-2 px-3 py-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-split-page="{{ $customers->currentPage() - 1 }}" @disabled($customers->onFirstPage()) aria-label="{{ __('Previous') }}"><i class="bi bi-chevron-left"></i></button>
        <span class="small text-muted text-center">{{ __('Page') }} {{ $customers->currentPage() }} / {{ $customers->lastPage() }}<br>{{ $customers->total() }} {{ __('customers') }}</span>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-split-page="{{ $customers->currentPage() + 1 }}" @disabled(!$customers->hasMorePages()) aria-label="{{ __('Next') }}"><i class="bi bi-chevron-right"></i></button>
    </div>
@endif
