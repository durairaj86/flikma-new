{{-- Quotation print (FastFatoora quotation layout, shared with the supplier invoice and job prints): Quotation To + Details, charge
     lines with VAT, notes + totals, bank + terms, signatures. --}}
@extends('modules.common.print-doc.layout', [
    'pageTitle' => __('Quotation') . ' — ' . $quotation->row_no,
    'docTitle' => ['QUOTATION', 'عرض سعر'],
    'refLabelAr' => 'رقم العرض',
    'refNo' => $quotation->row_no,
    'pill' => $validUntil ? 'Valid Until ' . $validUntil->format('d-m-Y') : null,
    'toLabel' => ['Quotation To', 'عرض سعر إلى'],
    'detailsLabel' => ['Quotation Details', 'تفاصيل العرض'],
    'details' => [
        ['Issue Date', 'تاريخ الإصدار', $issued?->format('d-m-Y')],
        ['Valid Until', 'صالح حتى', $validUntil?->format('d-m-Y')],
        ['Currency', 'العملة', $currency],
    ],
    'footerLine' => ['WE APPRECIATE THE OPPORTUNITY TO QUOTE FOR YOUR BUSINESS!', 'نقدّر لكم فرصة تقديم هذا العرض!'],
])

@section('body')
    @include('modules.common.print-doc.lines', ['lines' => $lines, 'emptyText' => 'No charges on this quotation yet.'])

    @include('modules.common.print-doc.totals', [
        'notesText' => $notes !== '' ? $notes : 'No specific notes for this quotation.',
        'subTotal' => $subTotal, 'vatTotal' => $vatTotal, 'grandTotal' => $grandTotal,
    ])

    {{-- bank | terms --}}
    <div class="rule" style="margin-top: 16px;"></div>
    <table style="margin-top: 8px; page-break-inside: avoid;">
        <tr>
            <td width="52%" valign="top" style="padding-right: 22px; border-right: 1px solid #e5e7eb;">
                <div class="label" style="color: #111827; font-size: 9pt;">Bank Details | <span class="ar">بيانات البنك</span></div>
                <table class="bank" style="margin-top: 8px;">
                    <tr><td width="34%">Bank Name</td><td><b>: {{ $bank->name ?? '' }}</b></td></tr>
                    <tr><td>Account Name</td><td>: {{ ($bank->account_name ?? null) ?: $company->name }}</td></tr>
                    <tr><td>Account No.</td><td>: {{ $bank->account_no ?? '' }}</td></tr>
                    <tr><td>IBAN No.</td><td>: {{ $bank->iban ?? '' }}</td></tr>
                    <tr><td>SWIFT Code</td><td>: {{ $bank->swift ?? '' }}</td></tr>
                </table>
            </td>
            <td width="48%" valign="top" style="padding-left: 22px;">
                <div class="label" style="color: #111827; font-size: 9pt;">Terms &amp; Conditions | <span class="ar">الشروط والأحكام</span></div>
                <ul class="terms" style="margin-top: 8px;">
                    @forelse($terms as $t)
                        <li>• {{ $t }}</li>
                    @empty
                        <li>• Prices are subject to VAT as per KSA regulations.</li>
                        @if($validUntil)<li>• This quotation is valid until {{ $validUntil->format('d-m-Y') }}.</li>@endif
                        <li>• Delivery timelines are subject to timely approval of requirements.</li>
                    @endforelse
                </ul>
            </td>
        </tr>
    </table>

    @include('modules.common.print-doc.signatures', ['left' => 'Authorized Signature', 'right' => 'Customer Acceptance'])
@endsection
