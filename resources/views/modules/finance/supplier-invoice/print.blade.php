{{-- Supplier invoice print (the quotation print's FastFatoora layout): Supplier + Invoice Details, lines with VAT, notes + totals,
     amount in words + terms, signatures. Amounts are in the invoice currency. --}}
@extends('modules.common.print-doc.layout', [
    'pageTitle' => __('Supplier Invoice') . ' — ' . $supplierInvoice->row_no,
    'docTitle' => ['SUPPLIER INVOICE', 'فاتورة مورد'],
    'refLabelAr' => 'رقم الفاتورة',
    'refNo' => $supplierInvoice->row_no,
    'pill' => $supplierInvoice->due_at ? 'Due ' . $supplierInvoice->due_at : null,
    'toLabel' => ['Supplier', 'المورد'],
    'detailsLabel' => ['Invoice Details', 'تفاصيل الفاتورة'],
    'details' => array_values(array_filter([
        ['Supplier Inv. No', 'رقم فاتورة المورد', $supplierInvoice->invoice_number],
        ['Invoice Date', 'تاريخ الفاتورة', $supplierInvoice->invoice_date],
        ['Due Date', 'تاريخ الاستحقاق', $supplierInvoice->due_at],
        ['Job No', 'رقم المهمة', $supplierInvoice->job_no],
        ['Currency', 'العملة', $currency],
        strtoupper($currency) !== strtoupper($company->base_currency ?: 'SAR')
            ? ['Exchange Rate', 'سعر الصرف', number_format((float) $supplierInvoice->currency_rate, 4)] : null,
        ['Status', 'الحالة', $statusLabel],
    ])),
    'footerLine' => ['THANK YOU FOR YOUR SERVICE!', 'شكراً لتعاونكم!'],
])

@section('body')
    @include('modules.common.print-doc.lines', ['lines' => $lines, 'emptyText' => 'No lines on this invoice yet.'])

    @include('modules.common.print-doc.totals', [
        'notesText' => $notes !== '' ? $notes : 'No specific notes for this invoice.',
        'subTotal' => $subTotal, 'vatTotal' => $vatTotal, 'grandTotal' => $grandTotal, 'afterGrand' => $afterGrand,
    ])

    {{-- amount in words | terms --}}
    <div class="rule" style="margin-top: 16px;"></div>
    <table style="margin-top: 8px; page-break-inside: avoid;">
        <tr>
            <td width="52%" valign="top" style="padding-right: 22px; border-right: 1px solid #e5e7eb;">
                <div class="label" style="color: #111827; font-size: 9pt;">Amount in Words | <span class="ar">المبلغ كتابة</span></div>
                <div style="font-size: 8.5pt; color: #374151; margin-top: 8px;">{{ $amountInWords }}</div>
            </td>
            <td width="48%" valign="top" style="padding-left: 22px;">
                <div class="label" style="color: #111827; font-size: 9pt;">Terms &amp; Conditions | <span class="ar">الشروط والأحكام</span></div>
                <ul class="terms" style="margin-top: 8px;">
                    @forelse($terms as $t)
                        <li>• {{ $t }}</li>
                    @empty
                        <li class="muted">• No terms on this invoice.</li>
                    @endforelse
                </ul>
            </td>
        </tr>
    </table>

    @include('modules.common.print-doc.signatures', ['left' => 'Prepared By', 'right' => 'Approved By'])
@endsection
