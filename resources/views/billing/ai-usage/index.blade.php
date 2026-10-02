@section('page-title', 'AI Usage')
@section('page-subtitle'){{ __('What was asked, when, and how many tokens it used.') }}@endsection
<x-app-layout>
<main class="gmail-content bg-white">
<div class="container-fluid px-4 py-4">
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-bold mb-1">{{ __('Token Limit') }}</div>
                    <div class="h4 fw-bold text-dark mb-0">{{ $hasNoLimit ? __('Not Set') : formatAiTokens($tokenLimit) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-bold mb-1">{{ __('Tokens Used') }}</div>
                    <div class="h4 fw-bold text-dark mb-0">{{ formatAiTokens($tokensUsed) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-bold mb-1">{{ __('Remaining') }}</div>
                    <div class="h4 fw-bold {{ $hasNoLimit || $tokensRemaining === 0 ? 'text-danger' : ($isLowRemaining ? 'text-warning' : 'text-dark') }} mb-0">
                        {{ $hasNoLimit ? __('Not Set') : formatAiTokens($tokensRemaining) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($hasNoLimit)
        <div class="alert alert-info d-flex align-items-center mb-4">
            <i class="bi bi-info-circle-fill me-2"></i>
            {{ __("You don't have an AI usage limit. Kindly ask your administrator to buy an AI token usage limit.") }}
        </div>
    @elseif($tokensRemaining === 0)
        <div class="alert alert-danger d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ __('Your AI token limit has been reached. Please contact your admin to buy more tokens.') }}
        </div>
    @elseif($isLowRemaining)
        <div class="alert alert-warning d-flex align-items-center mb-4">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            {{ __('Your AI token usage is close to the limit — :remaining tokens left. Consider asking your administrator to top up soon.', ['remaining' => formatAiTokens($tokensRemaining)]) }}
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="GET" action="{{ route('billing.ai-usage') }}" class="row g-2 align-items-end">
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold text-uppercase text-muted mb-1">{{ __('From') }}</label>
                    <input type="text" name="from" value="{{ $filters['from'] }}" class="form-control form-control-sm report-datepicker" placeholder="{{ __('From') }}">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-bold text-uppercase text-muted mb-1">{{ __('To') }}</label>
                    <input type="text" name="to" value="{{ $filters['to'] }}" class="form-control form-control-sm report-datepicker" placeholder="{{ __('To') }}">
                </div>
                <div class="col-8 col-md-4">
                    <label class="form-label small fw-bold text-uppercase text-muted mb-1">{{ __('User') }}</label>
                    <select name="user_id" class="form-select form-select-sm">
                        <option value="">{{ __('All Users') }}</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ (string) $filters['user_id'] === (string) $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-indigo btn-sm flex-grow-1">{{ __('Filter') }}</button>
                    <a href="{{ route('billing.ai-usage') }}" class="btn btn-outline-secondary btn-sm">{{ __('Reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="text-muted small">{{ __(':count records — :tokens tokens in this range', ['count' => number_format($logs->total()), 'tokens' => formatAiTokens($filteredTotalTokens)]) }}</span>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="small text-uppercase text-muted fw-bold">
                        <th>{{ __('Date/Time') }}</th>
                        <th>{{ __('User') }}</th>
                        <th>{{ __('What They Asked') }}</th>
                        <th class="text-end">
                            {{ __('Input Tokens') }}
                            <i class="bi bi-question-circle ms-1" data-bs-toggle="tooltip" title="{{ __('Tokens used for what was sent to the AI — your question plus any context like report data.') }}"></i>
                        </th>
                        <th class="text-end">
                            {{ __('Output Tokens') }}
                            <i class="bi bi-question-circle ms-1" data-bs-toggle="tooltip" title="{{ __('Tokens used for the AI\'s answer that came back.') }}"></i>
                        </th>
                        <th class="text-end">{{ __('Total Tokens') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="text-nowrap">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                            <td>{{ $log->user->name ?? __('Unknown') }}</td>
                            <td>{{ $log->feature ?: __('AI Request') }}</td>
                            <td class="text-end">{{ formatAiTokens($log->input_tokens) }}</td>
                            <td class="text-end">{{ formatAiTokens($log->output_tokens) }}</td>
                            <td class="text-end fw-bold">{{ formatAiTokens($log->total_tokens) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                {{ __('No AI usage recorded yet.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
</main>
</x-app-layout>

<style>
    .btn-indigo { background-color: #4f46e5; border-color: #4f46e5; color: white; }
    .btn-indigo:hover { background-color: #4338ca; border-color: #4338ca; color: white; }
</style>

<script>
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });
</script>
