@extends('includes.print-header')
@section('print-content')

    <div class="quotation-wrapper">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <div class="fw-bold text-uppercase title">Asset</div>
                <div class="small text-muted">#{{ $asset->row_no ?? $asset->id }}</div>
            </div>
            <div class="text-end">
                <div><strong>{{ $asset->name_en }}</strong></div>
                @if($asset->category)
                    <small class="text-muted">{{ $asset->category->name_en }}</small>
                @endif
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-6">
                <table class="table table-borderless small">
                    <tr>
                        <td class="fw-semibold">Acquisition Date</td>
                        <td>{{ showDate($asset->acquisition_date) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Depreciation Start</td>
                        <td>{{ showDate($asset->depreciation_start_date) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Useful Life (Months)</td>
                        <td>{{ $asset->useful_life_months ?? $asset->category?->useful_life_months }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-6">
                <table class="table table-borderless small">
                    <tr>
                        <td class="fw-semibold">Cost</td>
                        <td class="text-end">{{ number_format($asset->cost, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Residual</td>
                        <td class="text-end">{{ number_format($asset->residual_value, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="fw-semibold">Book Value</td>
                        <td class="text-end">{{ number_format(max(0, $asset->cost - $asset->depreciations->sum('amount')), 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section mt-3">
            <h6 class="fw-semibold border-bottom pb-1 mb-3">Depreciation Schedule</h6>
            <table class="table table-bordered align-middle small">
                <thead class="bg-light">
                <tr>
                    <th>#</th>
                    <th>Period</th>
                    <th class="text-end">Amount</th>
                    <th class="text-end">Accumulated</th>
                    <th class="text-end">Book Value</th>
                </tr>
                </thead>
                <tbody>
                @forelse($asset->depreciations as $i => $d)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ showDate($d->period_start) }} - {{ showDate($d->period_end) }}</td>
                        <td class="text-end">{{ number_format($d->amount, 2) }}</td>
                        <td class="text-end">{{ number_format($d->accumulated, 2) }}</td>
                        <td class="text-end">{{ number_format($d->book_value, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No schedule generated yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection
