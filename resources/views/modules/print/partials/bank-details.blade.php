{{-- Shared bank details block. Expects $bank in scope. --}}
<table style="font-size: inherit; width: 100%;">
    <tbody>
    <tr><td style="font-weight:700; width:35%;">Account Name:</td><td>{{ $bank?->account_holder }}</td></tr>
    <tr><td style="font-weight:700;">Account Name (Arabic):</td><td>{{ $bank?->account_holder_arabic }}</td></tr>
    <tr><td style="font-weight:700;">Bank Name:</td><td>{{ $bank?->bank_name }}</td></tr>
    <tr><td style="font-weight:700;">Account No:</td><td>{{ $bank?->account_number }}</td></tr>
    <tr><td style="font-weight:700;">IBAN No:</td><td>{{ $bank?->iban_code }}</td></tr>
    <tr><td style="font-weight:700;">Bank Address:</td><td>{{ $bank?->bank_address }}</td></tr>
    <tr><td style="font-weight:700;">SWIFT Code:</td><td>{{ $bank?->swift_code }}</td></tr>
    </tbody>
</table>
