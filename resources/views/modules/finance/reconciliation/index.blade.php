@section('page-title', __('Bank Reconciliation'))
@section('page-subtitle', __('Match your bank statement with the books'))
@section('hide-topbar', true)
<x-app-layout>
    <main class="gmail-content bg-white px-3 pb-5">
        <div class="pt-2 pb-3">
            <h4 class="fw-bold text-dark mb-1">{{ __('Bank Reconciliation') }}</h4>
            <div class="text-muted small">{{ __('Enter the closing balance of your bank statement, then tick the entries that appear on it. When the difference is 0.00 the account is reconciled.') }}</div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
        @endif

        <div class="card border-0 shadow-sm mb-4" style="max-width: 920px">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3">{{ __('Start a reconciliation') }}</h6>
                <form method="POST" action="{{ route('finance.reconciliation.store') }}" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-uppercase text-muted">{{ __('Bank / cash account') }}</label>
                        <select name="account_id" class="form-select" required>
                            @foreach($accounts as $a)
                                <option value="{{ $a->id }}" @selected(old('account_id') == $a->id)>{{ $a->code }} {{ $a->name }} ({{ __('books') }}: {{ number_format($balances[$a->id] ?? 0, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">{{ __('Statement date') }}</label>
                        <input type="text" name="statement_date" class="form-control datepicker" value="{{ old('statement_date', now()->format('d-m-Y')) }}" autocomplete="off" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-uppercase text-muted">{{ __('Closing balance') }}</label>
                        <input type="text" name="statement_balance" class="form-control decimal" data-negative value="{{ old('statement_balance') }}" placeholder="0.00" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">{{ __('Start') }}</button>
                    </div>
                </form>
                <div class="text-muted small mt-2">{{ __('The closing balance is the last balance printed on the statement from your bank. If an unfinished reconciliation exists for the account, it is continued with the new date and balance.') }}</div>
            </div>
        </div>

        <h6 class="fw-bold mb-2">{{ __('Statements') }}</h6>
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="max-width: 1100px">
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
    </main>
</x-app-layout>
