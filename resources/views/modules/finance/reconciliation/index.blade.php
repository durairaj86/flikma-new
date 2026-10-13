@section('page-title', __('Bank Reconciliation'))
@section('page-subtitle', __('Match your bank statement with the books'))
@section('hide-topbar', true)
<x-app-layout>
    <div class="bg-light pb-4 imp-page">
        @include('modules.finance.reconciliation._style')
        <div class="imp-wrap" id="rcStart">
            <div>
                <div class="imp-crumb"><a href="/finance/reconciliation">{{ __('Finance') }}</a> &nbsp;/&nbsp; {{ __('Bank Reconciliation') }}</div>
                <h1 class="imp-h1">{{ __('Bank Reconciliation') }}</h1>
                <div class="imp-sub">{{ __('Enter the closing balance of your bank statement, then tick the entries that appear on it. When the difference is 0.00 the account is reconciled.') }}</div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger py-2 mt-3">{{ $errors->first() }}</div>
            @endif

            <div class="row justify-content-center mt-3">
                <div class="col-lg-9">
                    <div class="imp-card">
                        <h6><span class="imp-step">1</span>{{ __('Start a reconciliation') }}</h6>
                        <form method="POST" action="{{ route('finance.reconciliation.store') }}" class="row g-3 align-items-end">
                            @csrf
                            <div class="col-md-12">
                                <div class="rc-lbl">{{ __('Bank / cash account') }}</div>
                                <select name="account_id" class="tom-select" data-live-search="true" data-placeholder="{{ __('Select an account...') }}" required>
                                    @foreach($accounts as $a)
                                        <option value="{{ $a->id }}" @selected(old('account_id') == $a->id)>{{ $a->code }} {{ $a->name }} ({{ __('books') }}: {{ number_format($balances[$a->id] ?? 0, 2) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <div class="rc-lbl">{{ __('Statement date') }}</div>
                                <input type="text" name="statement_date" class="form-control datepicker" data-min-date="01-01-2000" data-max-date="31-12-2035" value="{{ old('statement_date', now()->format('d-m-Y')) }}" autocomplete="off" required>
                            </div>
                            <div class="col-md-4">
                                <div class="rc-lbl">{{ __('Closing balance') }}</div>
                                <input type="text" name="statement_balance" class="form-control decimal" data-negative value="{{ old('statement_balance') }}" placeholder="0.00" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">{{ __('Start') }}</button>
                            </div>
                        </form>
                        <div class="imp-help mt-3">{{ __('The closing balance is the last balance printed on the statement from your bank. If an unfinished reconciliation exists for the account, it is continued with the new date and balance.') }}</div>
                    </div>
                </div>
            </div>

            <div class="row justify-content-center mt-3">
                <div class="col-lg-9">
                    <div class="imp-card">
                        <h6><span class="imp-step">2</span>{{ __('Statements') }}</h6>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                <tr>
                                    <th>{{ __('Account') }}</th>
                                    <th>{{ __('Statement date') }}</th>
                                    <th class="text-end">{{ __('Statement balance') }}</th>
                                    <th class="text-end">{{ __('Difference') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th class="text-end"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($reconciliations as $r)
                                    <tr>
                                        <td><b>{{ $r->account->code }}</b> {{ $r->account->name }}</td>
                                        <td>{{ $r->statement_date->format('d-m-Y') }}</td>
                                        <td class="text-end">{{ number_format($r->statement_balance, 2) }}</td>
                                        <td class="text-end">{{ $r->isCompleted() ? number_format($r->difference, 2) : '-' }}</td>
                                        <td>
                                            @if($r->isCompleted())
                                                <span class="badge text-bg-success">{{ __('Reconciled') }}</span>
                                                <span class="text-muted small ms-1">{{ $r->completed_at?->format('d-m-Y') }}</span>
                                            @else
                                                <span class="badge text-bg-warning">{{ __('In progress') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('finance.reconciliation.show', $r->id) }}">{{ $r->isCompleted() ? __('View') : __('Continue') }}</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">{{ __('No reconciliation yet. Start one above.') }}</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        window.addEventListener('load', function () {
            if (typeof datepicker === 'function') datepicker();
            if (typeof initTomSelectForm === 'function') initTomSelectForm($('#rcStart'));
        });
    </script>
</x-app-layout>
