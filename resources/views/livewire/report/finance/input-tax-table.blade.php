<div>
    <div class="table-responsive d-print-none">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr class="bg-light text-muted small text-uppercase fw-bold ls-1">
                    <th class="ps-4 border-0 py-3">{{ __('Account') }}</th>
                    <th class="border-0 py-3">{{ __('Reference No') }}</th>
                    <th class="border-0 py-3">{{ __('Date') }}</th>
                    <th class="border-0 py-3">{{ __('Description') }}</th>
                    <th class="text-end pe-4 border-0 py-3">{{ __('Tax Amount') }}</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @if(isset($inputTaxData['input_tax_transactions']) && count($inputTaxData['input_tax_transactions']) > 0)
                    @foreach($inputTaxData['input_tax_transactions'] as $transaction)
                        <tr wire:key="it-{{ $loop->index }}">
                            <td class="ps-4">
                                <span class="fw-bold text-dark">{{ $transaction['account_name'] }}</span>
                                <div class="text-muted" style="font-size:0.7rem;">{{ $transaction['account_code'] }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border fw-normal px-2 py-1">
                                    {{ $transaction['reference_no'] }}
                                </span>
                            </td>
                            <td class="small text-muted">
                                {{ \Carbon\Carbon::parse($transaction['reference_date'])->format('d M Y') }}
                            </td>
                            <td class="small text-muted">{{ $transaction['description'] }}</td>
                            <td class="pe-4 text-end tabular-nums fw-bold text-indigo">
                                {{ number_format($transaction['amount'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                                <i class="bi bi-journal-arrow-down h2 text-muted"></i>
                            </div>
                            <div class="small">{{ __('No input tax transactions found for this period.') }}</div>
                        </td>
                    </tr>
                @endif
            </tbody>
            @if(isset($inputTaxData['total_input_tax']) && $inputTaxData['total_input_tax'] > 0)
            <tfoot class="bg-light border-top-2">
                <tr class="fw-bold">
                    <td colspan="4" class="ps-4 py-3 text-uppercase small text-muted">{{ __('Total Input Tax') }}</td>
                    <td class="pe-4 text-end tabular-nums text-indigo py-3">
                        {{ number_format($inputTaxData['total_input_tax'], 2) }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    {{-- Bank-statement style layout: used for Print and PDF export only --}}
    <div id="it-print" class="stmt-print d-none d-print-block"
         data-pdf-filename="InputTax-{{ $startDate ?? '' }}-{{ $endDate ?? '' }}.pdf">

        <table class="stmt-meta">
            <tr>
                <td>
                    <div class="stmt-company">{{ optional(authUserCompany())->name ?? config('app.name') }}</div>
                </td>
                <td class="text-end">
                    <div class="stmt-title">{{ __('INPUT TAX REPORT') }}</div>
                    <div class="stmt-sub">{{ __('Period:') }} {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</div>
                    <div class="stmt-sub">{{ __('Generated:') }} {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp; {{ __('Currency:') }} SAR</div>
                </td>
            </tr>
        </table>

        <table class="stmt-table">
            <thead>
            <tr>
                <th>{{ __('Account') }}</th>
                <th>{{ __('Reference No') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Description') }}</th>
                <th class="text-end">{{ __('Tax Amount') }}</th>
            </tr>
            </thead>
            <tbody>
            @forelse($inputTaxData['input_tax_transactions'] as $transaction)
                <tr>
                    <td>{{ $transaction['account_code'] }} {{ $transaction['account_name'] }}</td>
                    <td>{{ $transaction['reference_no'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($transaction['reference_date'])->format('d M Y') }}</td>
                    <td>{{ $transaction['description'] }}</td>
                    <td class="text-end">{{ number_format($transaction['amount'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center">{{ __('No input tax transactions found for this period.') }}</td></tr>
            @endforelse
            </tbody>
            <tfoot>
            <tr class="stmt-strong"><td colspan="4">{{ __('Total Input Tax') }}</td><td class="text-end">{{ number_format($inputTaxData['total_input_tax'] ?? 0, 2) }}</td></tr>
            </tfoot>
        </table>

        <div class="stmt-signatures">
            <table class="stmt-meta">
                <tr>
                    <td>{{ __('Prepared By:') }} _________________</td>
                    <td>{{ __('Verified By:') }} _________________</td>
                    <td>{{ __('Approved By:') }} _________________</td>
                </tr>
            </table>
        </div>
    </div>

    @include('includes.report-print-css', ['orientation' => 'portrait'])
</div>
