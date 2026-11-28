@section('page-title', __('Cost Sheet'))
@section('hide-topbar', true)
@php
    $dec = decimals();
    $money = fn ($v) => number_format((float) $v, $dec);
    $canCreate = userCan('finances.cost-sheets', 'create');
    $canDelete = userCan('finances.cost-sheets', 'delete');
@endphp
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <div>
                <h4 class="fw-bold text-dark mb-0">{{ __('Cost Sheet') }}</h4>
                <div class="text-muted small">{{ __('Expected sale and cost per job. The customer invoice is created from the cost sheet.') }}</div>
            </div>
            @if($canCreate)
                <button class="btn btn-primary rounded-pill px-4" id="costSheetNew">{{ __('New Cost Sheet') }}</button>
            @endif
        </div>

        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap py-3">
            <ul class="nav" id="listTabs" role="tablist" aria-label="{{ __('Cost sheet status') }}">
                @foreach(\App\Http\Controllers\Job\CostSheetController::TABS as $key => $label)
                    <li class="nav-item me-2">
                        <a href="{{ url('invoice/cost-sheets?tab=' . $key . ($q !== '' ? '&q=' . urlencode($q) : '')) }}" id="{{ $key }}"
                           class="nav-link px-3 py-2 rounded-pill status-btn {{ $tab === $key ? 'active bg-primary text-white' : 'bg-light text-dark' }}">
                            {{ __($label) }} - <span class="status-count">{{ $counts[$key] ?? 0 }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <form method="get" action="{{ url('invoice/cost-sheets') }}" class="d-flex gap-2" role="search">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="input-group" style="max-width: 320px;">
                    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                    <input type="search" name="q" value="{{ $q }}" class="form-control" maxlength="50" placeholder="{{ __('Cost sheet, job, customer or invoice no.') }}" aria-label="{{ __('Search') }}">
                </div>
            </form>
        </div>

        <div class="shadow bdr-r-10 py-3 px-2">
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="costSheets">
                    <thead class="table-light">
                    <tr>
                        <th>{{ __('Cost Sheet') }}</th>
                        <th>{{ __('Job') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th class="text-end">{{ __('Sale') }}</th>
                        <th class="text-end">{{ __('Cost') }}</th>
                        <th class="text-end">{{ __('Margin') }}</th>
                        <th>{{ __('Updated') }}</th>
                        <th class="text-end">{{ __('Action') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($sheets as $s)
                        @php($margin = (float) $s->sale_total - (float) $s->cost_total)
                        <tr data-id="{{ $s->id }}" data-job="{{ $s->job_id }}" data-no="{{ $s->row_no }}">
                            <td>
                                <a href="#" class="fw-bold text-primary text-decoration-none cs-open">{{ $s->row_no ?: '#' . $s->id }}</a>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    @if($s->locked())
                                        <span class="badge bg-success-subtle text-success-emphasis"><i class="bi bi-lock-fill me-1"></i>{{ __('Invoiced') }}</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary-emphasis">{{ __('Open') }}</span>
                                    @endif
                                </div>
                                {{-- the invoice made from it, under the cost sheet number --}}
                                @if($s->invoice)
                                    <div class="small mt-1"><i class="bi bi-receipt me-1 text-success"></i><span class="text-success fw-semibold">{{ $s->invoice->row_no }}</span></div>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $s->job->row_no ?? '—' }}</td>
                            <td><div>{{ $s->job->customer->name_en ?? '—' }}</div><div class="small text-muted">{{ $s->job->customer->row_no ?? '' }}</div></td>
                            <td class="text-end">{{ $money($s->sale_total) }}</td>
                            <td class="text-end">{{ $money($s->cost_total) }}</td>
                            <td class="text-end fw-semibold {{ $margin < 0 ? 'text-danger' : 'text-success' }}">
                                {{ $money($margin) }}
                                @if((float) $s->sale_total > 0)<div class="small fw-normal">{{ number_format($margin / (float) $s->sale_total * 100, 1) }}%</div>@endif
                            </td>
                            <td class="small text-muted">{{ $s->updated_at?->format('d-m-Y H:i') }}</td>
                            <td class="text-end text-nowrap">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill cs-open">
                                    {{ $s->locked() ? __('View') : __('Edit') }}
                                </button>
                                @if($canDelete && !$s->locked())
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill cs-delete" aria-label="{{ __('Delete') }}"><i class="bi bi-trash"></i></button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5">{{ $q !== '' ? __('No cost sheet matches the search.') : __('No cost sheets yet.') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const base = GLOBAL_FN.buildUrl('invoice/cost-sheets');
            const reload = () => window.location.reload();
            const openSheet = (jobId, no) => webModal.openGlobalModal({
                title: @json(__('Cost Sheet')) + (no ? ' - ' + no : ''), url: base + '/job/' + jobId, content: null, size: '3xl', scroll: true, heightPct: 90,
            });

            $('#costSheetNew').on('click', function () {
                webModal.openGlobalModal({title: @json(__('New Cost Sheet')), url: base + '/create', content: null, size: 'lg', scroll: false});
            });
            $(document).on('click', '#costSheets .cs-open', function (e) {
                e.preventDefault();
                const tr = $(this).closest('tr');
                openSheet(tr.data('job'), tr.data('no'));
            });
            $(document).on('click', '#costSheets .cs-delete', function () {
                const tr = $(this).closest('tr');
                appConfirm({
                    title: @json(__('Delete cost sheet?')), message: tr.data('no') + ': ' + @json(__('its lines are removed. The job and its documents stay.')),
                    button: @json(__('Delete')), btnClass: 'btn-danger', icon: 'bi-trash', tone: 'danger',
                    onConfirm: () => $.ajax({url: base + '/' + tr.data('id'), type: 'DELETE'})
                        .done(r => { toastr.success(r.message); reload(); })
                        .fail(xhr => toastr.error((xhr.responseJSON || {}).message || trans('Something went wrong!')))
                });
            });

            // saving the sheet or creating its invoice in the modal: show the new figures (only while on this page)
            const afterSave = () => setTimeout(reload, 300);
            window.costSheetSaved = afterSave;
            document.addEventListener('turbo:before-render', () => { if (window.costSheetSaved === afterSave) window.costSheetSaved = null; }, {once: true});
        });
    </script>
</x-app-layout>
