{{-- Shared ZATCA QR code block. Expects $company, $customerInvoice in scope. --}}
@if ($company->zatca_registered && $customerInvoice->qr && false)
    <div width="200" height="auto">{{ SimpleSoftwareIO\QrCode\Facades\QrCode::size(135)->generate($customerInvoice->qr) }}</div>
@elseif ($company->currency == 'SAR')
    @php
        $currencyRate = 1;
        $generatedString = Salla\ZATCA\GenerateQrCode::fromArray([
            new Salla\ZATCA\Tags\Seller($company->name),
            new Salla\ZATCA\Tags\TaxNumber($company->tax_number),
            new Salla\ZATCA\Tags\InvoiceDate(formDate($customerInvoice->invoice_date) . "T" . \Illuminate\Support\Carbon::parse($customerInvoice->created_at, 'UTC')->format('H:i:s')) . 'Z',
            new Salla\ZATCA\Tags\InvoiceTotalAmount(amountFormat(($customerInvoice->total_amount + $customerInvoice->total_tax_amount) / $currencyRate)),
            new Salla\ZATCA\Tags\InvoiceTaxAmount(amountFormat($customerInvoice->total_tax_amount / $currencyRate)),
        ])->render();
    @endphp
    <img src="{{ $generatedString }}" width="200" height="auto" alt="QR Code"/>
@endif
