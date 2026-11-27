@section('page-title', __('Credit Approval'))
@section('hide-topbar', true)
@php
    $dec = decimals();
    $money = fn ($v) => $v === null ? '—' : number_format((float) $v, $dec);
    $canApprove = userCan('finances.credit-approvals', 'approve');
@endphp
<x-app-layout>
    <main class="gmail-content bg-white px-3">
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap pt-2 pb-0">
            <div>
                <h4 class="fw-bold text-dark mb-0">{{ __('Credit Approval') }}</h4>
                <div class="text-muted small">{{ __('Requests to change a customer\'s credit limit or credit days.') }}</div>
            </div>
            @if(userCan('finances.credit-approvals', 'create'))
                <button class="btn btn-primary rounded-pill px-4" id="creditRequestNew">{{ __('New Request') }}</button>
            @endif
        </div>

        <ul class="nav py-3" id="listTabs" role="tablist" aria-label="{{ __('Request status') }}">
            @foreach(\App\Models\Finance\Credit\CreditLimitRequest::STATUSES as $key => $label)
                <li class="nav-item me-2">
                    <a href="{{ url('finance/credit-approvals?tab=' . $key) }}" id="{{ $key }}"
                       class="nav-link px-3 py-2 rounded-pill status-btn {{ $tab === $key ? 'active bg-primary text-white' : 'bg-light text-dark' }}">
                        {{ __($label) }} - <span class="status-count">{{ $counts[$key] ?? 0 }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="shadow bdr-r-10 py-3 px-2">
            <div class="table-responsive">
                <table class="table align-middle mb-0" id="creditRequests">
                    <thead class="table-light">
                    <tr>
                        <th>{{ __('Customer') }}</th>
                        <th class="text-end">{{ __('Outstanding') }}</th>
                        <th class="text-end">{{ __('Current Limit') }}</th>
                        <th class="text-end">{{ __('Requested Limit') }}</th>
                        <th class="text-end">{{ __('Current Days') }}</th>
                        <th class="text-end">{{ __('Requested Days') }}</th>
                        <th>{{ __('Reason') }}</th>
                        <th>{{ __('Requested By') }}</th>
                        <th>{{ $tab === 'pending' ? __('Date') : __('Decided By') }}</th>
                        @if($tab === 'pending' && $canApprove)<th class="text-end">{{ __('Action') }}</th>@endif
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($requests as $r)
                        <tr data-id="{{ $r->id }}">
                            <td><div class="fw-semibold">{{ $r->customer->name_en ?? '#' . $r->customer_id }}</div><div class="small text-muted">{{ $r->customer->row_no ?? '' }}</div></td>
                            <td class="text-end">{{ $money($outstanding[$r->customer_id] ?? 0) }}</td>
                            <td class="text-end">{{ $money($r->current_limit) }}</td>
                            <td class="text-end fw-semibold">{{ $money($r->requested_limit) }}</td>
                            <td class="text-end">{{ $r->current_days ?? '—' }}</td>
                            <td class="text-end fw-semibold">{{ $r->requested_days ?? '—' }}</td>
                            <td class="small" style="max-width:260px;">{{ $r->reason }}@if($r->decision_note)<div class="text-muted mt-1">{{ __('Note') }}: {{ $r->decision_note }}</div>@endif</td>
                            <td class="small">{{ $r->requestedBy->name ?? '—' }}</td>
                            <td class="small">
                                @if($tab === 'pending')
                                    {{ $r->created_at?->format('d-m-Y H:i') }}
                                @else
                                    {{ $r->decidedBy->name ?? '—' }}<div class="text-muted">{{ $r->decided_at?->format('d-m-Y H:i') }}</div>
                                @endif
                            </td>
                            @if($tab === 'pending' && $canApprove)
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-sm btn-success rounded-pill credit-approve" data-id="{{ $r->id }}">{{ __('Approve') }}</button>
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill credit-reject" data-id="{{ $r->id }}">{{ __('Reject') }}</button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-muted py-5">{{ __('No requests here.') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const base = GLOBAL_FN.buildUrl('finance/credit-approvals');
            const reload = () => window.location.reload();
            const fail = (xhr) => toastr.error((xhr.responseJSON || {}).message || trans('Something went wrong!'));

            $('#creditRequestNew').on('click', function () {
                webModal.openGlobalModal({title: @json(__('New Request')), url: base + '/create', content: null, size: 'lg', scroll: true});
            });
            $(document).on('click', '.credit-approve', function () {
                const id = $(this).data('id');
                appConfirm({
                    title: @json(__('Approve request?')), message: @json(__('The customer\'s credit limit and days change to the requested values.')),
                    button: @json(__('Approve')), btnClass: 'btn-success', icon: 'bi-check2', tone: 'success',
                    onConfirm: () => $.post(base + '/' + id + '/approve').done(r => { toastr.success(r.message); reload(); }).fail(fail)
                });
            });
            $(document).on('click', '.credit-reject', function () {
                const id = $(this).data('id');
                $.confirm({
                    title: @json(__('Reject request?')),
                    content: '<label class="form-label" for="creditRejectNote">' + @json(__('Why is the request rejected?')) + '</label>'
                        + '<textarea id="creditRejectNote" class="form-control" rows="3" maxlength="1000"></textarea>',
                    onContentReady: function () { setTimeout(() => this.$content.find('#creditRejectNote').trigger('focus'), 50); },
                    buttons: {
                        cancel: {text: @json(__('Cancel')), btnClass: 'btn-secondary'},
                        reject: {
                            text: @json(__('Reject')), btnClass: 'btn-danger',
                            action: function () {
                                const note = this.$content.find('#creditRejectNote').val().trim();
                                if (!note) { toastr.error(@json(__('Enter the reason.'))); return false; }
                                $.post(base + '/' + id + '/disapprove', {note: note}).done(r => { toastr.success(r.message); reload(); }).fail(fail);
                            }
                        }
                    }
                });
            });

            // the request form saves through the shared modal: reload this server-drawn list after it (only while on this page)
            const afterSave = () => setTimeout(reload, 300);
            window.afterModalSave = afterSave;
            document.addEventListener('turbo:before-render', () => { if (window.afterModalSave === afterSave) window.afterModalSave = null; }, {once: true});
        });
    </script>
</x-app-layout>
