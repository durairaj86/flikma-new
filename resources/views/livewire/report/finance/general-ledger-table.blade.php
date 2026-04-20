<div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th colspan="8" class="text-center">General Ledger</th>
                </tr>
                <tr>
                    <th>Date</th>
                    <th>Voucher No</th>
                    <th>Voucher Type</th>
                    <th>Reference</th>
                    <th>Description</th>
                    <th class="text-end">Debit</th>
                    <th class="text-end">Credit</th>
                    <th class="text-end">Balance</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($generalLedgerData['accounts']) && count($generalLedgerData['accounts']) > 0)
                    @foreach($generalLedgerData['accounts'] as $accountCode => $accountData)
                        @php
                            // Skip accounts with zero balance (no opening balance, no closing balance, and no transactions)
                            $hasOpeningBalance = abs($accountData['opening_balance']) > 0.001;
                            $hasClosingBalance = abs($accountData['closing_balance']) > 0.001;
                            $hasTransactions = count($accountData['transactions']) > 0;

                            // Skip this account if it has no activity
                            if (!$hasOpeningBalance && !$hasClosingBalance && !$hasTransactions) continue;
                        @endphp

                        <!-- Account Header -->
                        <tr class="table-secondary">
                            <th colspan="8">
                                {{ $accountData['account_code'] }} - {{ $accountData['account_name'] }}
                                ({{ $accountData['account_type'] }})
                            </th>
                        </tr>

                        <!-- Opening Balance Row -->
                        <tr class="table-light">
                            <td colspan="5"><strong>Opening Balance</strong></td>
                            <td class="text-end">{{ $accountData['opening_balance'] > 0 ? number_format($accountData['opening_balance'], 2) : '0.00' }}</td>
                            <td class="text-end">{{ $accountData['opening_balance'] < 0 ? number_format(abs($accountData['opening_balance']), 2) : '0.00' }}</td>
                            <td class="text-end">{{ number_format($accountData['opening_balance'], 2) }}</td>
                        </tr>

                        <!-- Transactions -->
                        @if(count($accountData['transactions']) > 0)
                            @foreach($accountData['transactions'] as $transaction)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($transaction['date'])->format('d-m-Y') }}</td>
                                    <td>{{ $transaction['voucher_no'] }}</td>
                                    <td>{{ $transaction['voucher_type'] }}</td>
                                    <td>{{ $transaction['reference_no'] }}</td>
                                    <td>{{ $transaction['description'] }}</td>
                                    <td class="text-end">{{ number_format($transaction['debit'], 2) }}</td>
                                    <td class="text-end">{{ number_format($transaction['credit'], 2) }}</td>
                                    <td class="text-end">{{ number_format($transaction['balance'], 2) }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center">No transactions found for this account in the selected period</td>
                            </tr>
                        @endif

                        <!-- Account Total Row -->
                        <tr class="table-light">
                            <td colspan="5"><strong>Total</strong></td>
                            <td class="text-end"><strong>{{ number_format($accountData['total_debit'], 2) }}</strong></td>
                            <td class="text-end"><strong>{{ number_format($accountData['total_credit'], 2) }}</strong></td>
                            <td class="text-end"><strong>{{ number_format($accountData['closing_balance'], 2) }}</strong></td>
                        </tr>

                        <!-- Spacer Row -->
                        <tr><td colspan="8" class="p-0"></td></tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8" class="text-center">No data available for the selected criteria</td>
                    </tr>
                @endif
            </tbody>
            <tfoot class="table-dark">
                <tr>
                    <th colspan="5" class="text-end">Grand Total</th>
                    <th class="text-end">{{ isset($generalLedgerData['grand_total_debit']) ? number_format($generalLedgerData['grand_total_debit'], 2) : '0.00' }}</th>
                    <th class="text-end">{{ isset($generalLedgerData['grand_total_credit']) ? number_format($generalLedgerData['grand_total_credit'], 2) : '0.00' }}</th>
                    <th class="text-end">{{ isset($generalLedgerData['net_balance']) ? number_format($generalLedgerData['net_balance'], 2) : '0.00' }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
