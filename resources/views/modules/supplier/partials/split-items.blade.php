@foreach($suppliers as $c)
                        <a href="{{ url('/suppliers/' . $c->id) }}" data-name="{{ mb_strtolower(implode(' ', array_filter([$c->name_en, $c->name_ar, $c->email, $c->phone, $c->row_no, $c->vat_number]))) }}"
                           class="cust-split-item text-decoration-none {{ $c->id === ($activeId ?? $supplier->id) ? 'active' : '' }}">
                            <div class="d-flex justify-content-between gap-2">
                                <span class="fw-semibold text-truncate">{{ $c->name_en }}</span>
                                <span class="text-muted small flex-shrink-0">{{ number_format($c->balance, 2) }}</span>
                            </div>
                            <div class="small text-muted text-truncate">{{ $c->email }}</div>
                        </a>
                    @endforeach
@if($suppliers->isEmpty())
    <div class="text-center text-muted small p-4">{{ __('No match found.') }}</div>
@endif
