@extends('includes.print-header')
@section('print-content')

    <div class="quotation-wrapper">
        {{-- DRAFT Watermark --}}
        @if($expense->status == 1)
            <div class="draft-watermark">DRAFT</div>
        @endif

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end align-items-center gap-2 mb-3 no-print">
            <button type="button" class="btn btn-outline-secondary btn-sm"
                    onclick="EXPENSE.printPreview('{{ $expense->id }}')">
                <i class="bi bi-printer me-1"></i> Print
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm"
                    onclick="EXPENSE.downloadPDF('{{ $expense->id }}')">
                <i class="bi bi-file-earmark-pdf me-1"></i> Download PDF
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-x-circle me-1"></i> Cancel
            </button>
        </div>

        <!-- Company Header -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="company-logo">
                <img src="{{ asset('img/logo.png') }}" alt="Company Logo" style="max-height: 60px;">
            </div>
            <div class="company-info text-end">
                {{-- <h5 class="mb-1">{{ companyName() }}</h5>
                 <small>
                     {{ companyAddress() }}<br>
                     {{ companyEmail() }} | {{ companyPhone() }}
                 </small>--}}
            </div>
        </div>

        <!-- Title Block -->
        <div class="invoice-title d-flex justify-content-between">
            <div class="fw-bold text-uppercase title">Expense</div>
            <div class="">#{{ $expense->row_no }}</div>
        </div>

        <!-- Vendor Info -->
        <div class="row mb-4">
            @if($expense->supplier)
                <div class="col-6">
                    <h6 class="fw-semibold">Vendor:</h6>
                    <div><strong>{{ $expense->supplier->name_en ?? '-' }}</strong></div>
                    <div>{{ $expense->supplier->address1_en ?? '-' }}</div>

                    @if($expense->supplier->email)
                        <div>Email: {{ $expense->supplier->email }}</div>
                    @endif

                    @if($expense->supplier->phone)
                        <div>Phone: {{ $expense->supplier->phone }}</div>
                    @endif
                </div>
            @elseif($expense->customer)
                <div class="col-6">
                    <h6 class="fw-semibold">Customer:</h6>
                    <div><strong>{{ $expense->customer->name_en ?? '-' }}</strong></div>
                    <div>{{ $expense->customer->address1_en ?? '-' }}</div>

                    @if($expense->customer->email)
                        <div>Email: {{ $expense->customer->email }}</div>
                    @endif

                    @if($expense->customer->phone)
                        <div>Phone: {{ $expense->customer->phone }}</div>
                    @endif
                </div>
            @else
                <div class="col-6"></div>
            @endif

            <div class="col-6">
                <div class="d-flex flex-column align-items-end gap-2">

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width:140px;">Expense Date:</div>
                        <div class="text-end" style="min-width: 120px;">{{ showDate($expense->posted_at) }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width:140px;">Reference No:</div>
                        <div class="text-end" style="min-width: 120px;">{{ $expense->reference_number ?? '-' }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width:140px;">Payment Mode:</div>
                        <div class="text-end"
                             style="min-width: 120px;">{{ paymentModes()[$expense->payment_mode] ?? '-' }}</div>
                    </div>

                    <div class="d-flex w-100 justify-content-end">
                        <div class="text-end fw-semibold me-2" style="min-width:140px;">Status:</div>
                        <div class="text-end" style="min-width: 120px;">
                            <span class="badge bg-primary">
                                {{ \App\Enums\ExpenseEnum::from($expense->status)->label() }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Expense Items -->
        @if($expense->expenseSubs && $expense->expenseSubs->count())
            <div class="section mt-3">
                <h6 class="fw-semibold border-bottom pb-1 mb-3">Expense Items</h6>

                <table class="table table-bordered align-middle">
                    <thead class="bg-light">
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Account</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Tax %</th>
                        <th class="text-end">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($expense->expenseSubs as $i => $itm)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $itm->item_name }}</td>
                            <td>{{ $itm->account_type }}</td>
                            <td class="text-end">{{ $itm->qty }}</td>
                            <td class="text-end">{{ number_format($itm->unit_price, 2) }}</td>
                            <td class="text-end">{{ $itm->tax_percent }}%</td>
                            <td class="text-end">{{ number_format($itm->total, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Totals -->
        <div class="d-flex justify-content-end mt-4">
            <table class="table table-borderless" style="max-width: 350px;">
                <tr>
                    <td><strong>Subtotal</strong></td>
                    <td class="text-end">{{ number_format($expense->base_sub_total, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Tax</strong></td>
                    <td class="text-end">{{ number_format($expense->base_tax_total, 2) }}</td>
                </tr>

                @if($expense->discount > 0)
                    <tr>
                        <td><strong>Discount</strong></td>
                        <td class="text-end">-{{ number_format($expense->discount, 2) }}</td>
                    </tr>
                @endif

                <tr class="border-top">
                    <td><strong>Grand Total</strong></td>
                    <td class="text-end fw-bold">{{ number_format($expense->grand_total, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        @if($expense->notes)
            <div class="section mt-3">
                <h6 class="fw-semibold border-bottom pb-1 mb-2">Notes</h6>
                <p>{{ $expense->notes }}</p>
            </div>
        @endif

        <!-- Footer -->
        {{--<footer class="mt-5 pt-3 border-top text-center text-muted small">
            <div>Email: {{ companyEmail() }} | Phone: {{ companyPhone() }}</div>
        </footer>--}}

    </div>

@endsection
