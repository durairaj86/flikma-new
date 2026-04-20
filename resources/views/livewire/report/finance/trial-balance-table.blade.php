<div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead class="table-light">
            <tr>
                <th>Account Code</th>
                <th>Account Name</th>
                <th>Account Type</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
            </tr>
            </thead>
            <tbody>
            @if(isset($trialBalanceData['accounts']) && count($trialBalanceData['accounts']) > 0)
                @foreach($trialBalanceData['accounts'] as $account)
                    <tr>
                        <td>{{ $account['account_code'] }}</td>
                        <td>{{ $account['account_name'] }}</td>
                        <td>{{ $account['account_type'] }}</td>
                        <td class="text-end">{{ number_format($account['debit'], 2) }}</td>
                        <td class="text-end">{{ number_format($account['credit'], 2) }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" class="text-center">No data available</td>
                </tr>
            @endif
            </tbody>
            <tfoot class="table-light">
            <tr>
                <th colspan="3" class="text-end">Total</th>
                <th class="text-end">{{ isset($trialBalanceData['total_debit']) ? number_format($trialBalanceData['total_debit'], 2) : '0.00' }}</th>
                <th class="text-end">{{ isset($trialBalanceData['total_credit']) ? number_format($trialBalanceData['total_credit'], 2) : '0.00' }}</th>
            </tr>
            </tfoot>
        </table>
    </div>

    {{-- Difference Warning Logic --}}
    @php
        $debitTotal = $trialBalanceData['total_debit'] ?? 0;
        $creditTotal = $trialBalanceData['total_credit'] ?? 0;
        $diff = abs($debitTotal - $creditTotal);
    @endphp

    @if($diff > 0.001)
        <div class="d-flex align-items-center justify-content-end mt-2 text-danger fw-bold small">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Warning: Difference of {{ number_format($diff, 2) }} detected.
        </div>
    @endif
</div>
