@extends('layouts.app')

@section('title', __('Opening Balance Details'))
@section('page-title', __('Opening Balance Details'))

@section('content')
<div class="bg-white min-vh-100">
    <div class="container-fluid py-4 px-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('finance.opening-balance') }}" class="btn btn-light btn-sm rounded-circle border">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="font-size: 0.75rem;">
                            <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}" class="text-decoration-none text-muted">{{ __('Dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('finance.opening-balance') }}" class="text-decoration-none text-muted">{{ __('Opening Balance') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __('Details') }}</li>
                        </ol>
                    </nav>
                    <h4 class="fw-bold text-dark mb-0">{{ __('Opening Balance Details') }}</h4>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm px-3 fw-medium" onclick="window.history.back()">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('Back') }}
                </button>
                <div class="vr mx-1"></div>
                <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                    <i class="bi bi-printer"></i>
                </button>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light py-3">
                <div class="row">
                    <div class="col-md-6">
                        <h5 class="mb-0">{{ __('Voucher #') }}: {{ $finance->voucher_no }}</h5>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <span class="text-muted">{{ __('Date') }}: {{ $finance->date }}</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p class="mb-1"><strong>{{ __('Description') }}:</strong> {{ $finance->description }}</p>
                        <p class="mb-1"><strong>{{ __('Reference') }}:</strong> {{ $finance->reference_no }}</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-1"><strong>{{ __('Total Debit') }}:</strong> {{ number_format($finance->total_debit, 2) }}</p>
                        <p class="mb-1"><strong>{{ __('Total Credit') }}:</strong> {{ number_format($finance->total_credit, 2) }}</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('Account') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th>{{ __('Debit') }}</th>
                                <th>{{ __('Credit') }}</th>
                                <th>{{ __('Entity') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($finance->financeSubs as $sub)
                                <tr>
                                    <td>
                                        @if($sub->account)
                                            {{ $sub->account->name }}
                                        @else
                                            {{ __('N/A') }}
                                        @endif
                                    </td>
                                    <td>{{ $sub->description }}</td>
                                    <td class="text-end">{{ number_format($sub->debit, 2) }}</td>
                                    <td class="text-end">{{ number_format($sub->credit, 2) }}</td>
                                    <td>
                                        @if($sub->customer_id)
                                            {{ __('Customer') }}: {{ $sub->customer->name_en ?? __('N/A') }}
                                        @elseif($sub->supplier_id)
                                            {{ __('Supplier') }}: {{ $sub->supplier->name_en ?? __('N/A') }}
                                        @else
                                            {{ __('N/A') }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end">{{ __('Total') }}</td>
                                <td class="text-end">{{ number_format($finance->total_debit, 2) }}</td>
                                <td class="text-end">{{ number_format($finance->total_credit, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light py-3">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-0"><strong>{{ __('Created By') }}:</strong> {{ $finance->user->name ?? __('N/A') }}</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-0"><strong>{{ __('Created At') }}:</strong> {{ $finance->created_at->format('d-m-Y H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button class="btn btn-danger me-2" onclick="deleteOpeningBalance({{ $finance->id }})">
                <i class="bi bi-trash"></i> {{ __('Delete') }}
            </button>
            <a href="{{ route('finance.opening-balance') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> {{ __('Back to List') }}
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function deleteOpeningBalance(id) {
        if (confirm("{{ __('Are you sure you want to delete this opening balance entry? This action cannot be undone.') }}")) {
            fetch(`/finance/opening-balance/${id}/delete`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    window.location.href = "{{ route('finance.opening-balance') }}";
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("{{ __('An error occurred while deleting the opening balance entry.') }}");
            });
        }
    }
</script>
@endsection
