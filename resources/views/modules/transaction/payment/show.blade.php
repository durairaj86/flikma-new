@extends('layouts.app')

@section('title', 'Payment Details')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Payment Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('transaction.payments.index') }}">Payments</a></li>
                        <li class="breadcrumb-item active">View</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Payment #{{ $payment->row_no }}</h3>
                        <div>
                            <a href="{{ route('transaction.payments.print', $payment->id) }}" target="_blank" class="btn btn-outline-secondary">
                                <i class="bi bi-printer me-1"></i> Print
                            </a>
                            <a href="{{ route('transaction.payments.download', $payment->id) }}" target="_blank" class="btn btn-outline-primary">
                                <i class="bi bi-download me-1"></i> Download
                            </a>
                            @if($payment->status == 1)
                                <a href="{{ route('transaction.payments.edit', $payment->id) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Payment Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">Payment Number:</th>
                                            <td>{{ $payment->row_no }}</td>
                                        </tr>
                                        <tr>
                                            <th>Payment Date:</th>
                                            <td>{{ $payment->payment_date }}</td>
                                        </tr>
                                        <tr>
                                            <th>Payment Method:</th>
                                            <td>{{ $payment->payment_method }}</td>
                                        </tr>
                                        <tr>
                                            <th>Reference Number:</th>
                                            <td>{{ $payment->reference_no ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Currency:</th>
                                            <td>{{ strtoupper($payment->currency) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Currency Rate:</th>
                                            <td>{{ number_format($payment->currency_rate, 4) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                @if($payment->status == 1)
                                                    <span class="badge bg-warning text-dark">Draft</span>
                                                @elseif($payment->status == 2)
                                                    <span class="badge bg-success">Approved</span>
                                                @elseif($payment->status == 3)
                                                    <span class="badge bg-danger">Disapproved</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($payment->status == 3)
                                            <tr>
                                                <th>Disapproval Reason:</th>
                                                <td>{{ $payment->disapproval_reason }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Supplier & Job Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">Supplier:</th>
                                            <td>{{ $payment->supplier->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Supplier Address:</th>
                                            <td>{{ $payment->supplier->address ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Supplier Contact:</th>
                                            <td>{{ $payment->supplier->phone ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Job Number:</th>
                                            <td>{{ $payment->job_no ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Payment Totals</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">Sub Total:</th>
                                            <td>{{ number_format($payment->sub_total, 2) }} {{ strtoupper($payment->currency) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tax Total:</th>
                                            <td>{{ number_format($payment->tax_total, 2) }} {{ strtoupper($payment->currency) }}</td>
                                        </tr>
                                        <tr>
                                            <th>Grand Total:</th>
                                            <td class="fw-bold">{{ number_format($payment->grand_total, 2) }} {{ strtoupper($payment->currency) }}</td>
                                        </tr>
                                        @if($payment->currency != 'SAR')
                                            <tr>
                                                <th>Base Currency Total:</th>
                                                <td>{{ number_format($payment->base_grand_total, 2) }} SAR</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Invoices Paid</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Invoice Number</th>
                                                    <th>Invoice Date</th>
                                                    <th>Due Date</th>
                                                    <th>Invoice Total</th>
                                                    <th>Payment Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($payment->paymentInvoices as $index => $paymentInvoice)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $paymentInvoice->supplierInvoice->row_no ?? 'N/A' }}</td>
                                                        <td>{{ $paymentInvoice->supplierInvoice->invoice_date ?? 'N/A' }}</td>
                                                        <td>{{ $paymentInvoice->supplierInvoice->due_at ?? 'N/A' }}</td>
                                                        <td>{{ number_format($paymentInvoice->supplierInvoice->grand_total ?? 0, 2) }} {{ strtoupper($payment->currency) }}</td>
                                                        <td>{{ number_format($paymentInvoice->amount, 2) }} {{ strtoupper($payment->currency) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center">No invoices found</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="5" class="text-end">Total:</th>
                                                    <th>{{ number_format($payment->grand_total, 2) }} {{ strtoupper($payment->currency) }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($payment->notes)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Notes</h5>
                                    </div>
                                    <div class="card-body">
                                        {{ $payment->notes }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Audit Information</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="20%">Created By:</th>
                                            <td>{{ $payment->createdBy->name ?? 'N/A' }}</td>
                                            <th width="20%">Created At:</th>
                                            <td>{{ $payment->created_at ? $payment->created_at->format('d-m-Y H:i:s') : 'N/A' }}</td>
                                        </tr>
                                        @if($payment->status == 2)
                                            <tr>
                                                <th>Approved By:</th>
                                                <td>{{ $payment->approvedBy->name ?? 'N/A' }}</td>
                                                <th>Approved At:</th>
                                                <td>{{ $payment->approved_at ?? 'N/A' }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('transaction.payments.index') }}" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
@endsection
