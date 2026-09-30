@extends('layouts.app')

@section('title', __('Payment Details'))

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Payment Details') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/dashboard">{{ __('Dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('transaction.payments.index') }}">{{ __('Payments') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('View') }}</li>
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
                        <h3 class="card-title">{{ __('Payment') }} #{{ $payment->row_no }}</h3>
                        <div>
                            <a href="{{ route('transaction.payments.print', $payment->id) }}" target="_blank" class="btn btn-outline-secondary">
                                <i class="bi bi-printer me-1"></i> {{ __('Print') }}
                            </a>
                            <a href="{{ route('transaction.payments.download', $payment->id) }}" target="_blank" class="btn btn-outline-primary">
                                <i class="bi bi-download me-1"></i> {{ __('Download') }}
                            </a>
                            @if($payment->status == 1)
                                <a href="{{ route('transaction.payments.edit', $payment->id) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil me-1"></i> {{ __('Edit') }}
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
                                    <h5 class="mb-0">{{ __('Payment Information') }}</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">{{ __('Payment Number') }}:</th>
                                            <td>{{ $payment->row_no }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Payment Date') }}:</th>
                                            <td>{{ $payment->payment_date }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Payment Method') }}:</th>
                                            <td>{{ $payment->payment_method }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Reference Number') }}:</th>
                                            <td>{{ $payment->reference_no ?? __('N/A') }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Currency') }}:</th>
                                            <td>{{ strtoupper($payment->currency) }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Currency Rate') }}:</th>
                                            <td>{{ number_format($payment->currency_rate, 4) }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Status') }}:</th>
                                            <td>
                                                @if($payment->status == 1)
                                                    <span class="badge bg-warning text-dark">{{ __('Draft') }}</span>
                                                @elseif($payment->status == 2)
                                                    <span class="badge bg-success">{{ __('Approved') }}</span>
                                                @elseif($payment->status == 3)
                                                    <span class="badge bg-danger">{{ __('Disapproved') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($payment->status == 3)
                                            <tr>
                                                <th>{{ __('Disapproval Reason') }}:</th>
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
                                    <h5 class="mb-0">{{ __('Supplier & Job Information') }}</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">{{ __('Supplier') }}:</th>
                                            <td>{{ $payment->supplier->name ?? __('N/A') }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Supplier Address') }}:</th>
                                            <td>{{ $payment->supplier->address ?? __('N/A') }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Supplier Contact') }}:</th>
                                            <td>{{ $payment->supplier->phone ?? __('N/A') }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Job Number') }}:</th>
                                            <td>{{ $payment->job_no ?? __('N/A') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">{{ __('Payment Totals') }}</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="30%">{{ __('Sub Total') }}:</th>
                                            <td>{{ number_format($payment->sub_total, 2) }} {{ strtoupper($payment->currency) }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Tax Total') }}:</th>
                                            <td>{{ number_format($payment->tax_total, 2) }} {{ strtoupper($payment->currency) }}</td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Grand Total') }}:</th>
                                            <td class="fw-bold">{{ number_format($payment->grand_total, 2) }} {{ strtoupper($payment->currency) }}</td>
                                        </tr>
                                        @if($payment->currency != 'SAR')
                                            <tr>
                                                <th>{{ __('Base Currency Total') }}:</th>
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
                                    <h5 class="mb-0">{{ __('Invoices Paid') }}</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>{{ __('Invoice Number') }}</th>
                                                    <th>{{ __('Invoice Date') }}</th>
                                                    <th>{{ __('Due Date') }}</th>
                                                    <th>{{ __('Invoice Total') }}</th>
                                                    <th>{{ __('Payment Amount') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($payment->paymentInvoices as $index => $paymentInvoice)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td>{{ $paymentInvoice->supplierInvoice->row_no ?? __('N/A') }}</td>
                                                        <td>{{ $paymentInvoice->supplierInvoice->invoice_date ?? __('N/A') }}</td>
                                                        <td>{{ $paymentInvoice->supplierInvoice->due_at ?? __('N/A') }}</td>
                                                        <td>{{ number_format($paymentInvoice->supplierInvoice->grand_total ?? 0, 2) }} {{ strtoupper($payment->currency) }}</td>
                                                        <td>{{ number_format($paymentInvoice->amount, 2) }} {{ strtoupper($payment->currency) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center">{{ __('No invoices found') }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="5" class="text-end">{{ __('Total') }}:</th>
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
                                        <h5 class="mb-0">{{ __('Notes') }}</h5>
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
                                    <h5 class="mb-0">{{ __('Audit Information') }}</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="20%">{{ __('Created By') }}:</th>
                                            <td>{{ $payment->createdBy->name ?? __('N/A') }}</td>
                                            <th width="20%">{{ __('Created At') }}:</th>
                                            <td>{{ $payment->created_at ? $payment->created_at->format('d-m-Y H:i:s') : __('N/A') }}</td>
                                        </tr>
                                        @if($payment->status == 2)
                                            <tr>
                                                <th>{{ __('Approved By') }}:</th>
                                                <td>{{ $payment->approvedBy->name ?? __('N/A') }}</td>
                                                <th>{{ __('Approved At') }}:</th>
                                                <td>{{ $payment->approved_at ?? __('N/A') }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('transaction.payments.index') }}" class="btn btn-secondary">{{ __('Back to List') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
