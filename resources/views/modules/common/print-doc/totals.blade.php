{{-- Notes next to the totals of a document print: $notesText, $subTotal, $vatTotal, $grandTotal, and optional $afterGrand
     rows [[en, ar, value], ...] printed under the grand total (amount paid, balance, base-currency total). --}}
@php $money = fn($v) => number_format((float) $v, 2); @endphp
<table style="margin-top: 20px; page-break-inside: avoid;">
    <tr>
        <td width="55%" valign="top" style="padding-right: 30px;">
            <div class="label">Notes | <span class="ar">ملاحظات</span></div>
            <div style="font-size: 8pt; color: #374151; margin-top: 8px; white-space: pre-line;">{{ $notesText }}</div>
        </td>
        <td width="45%" valign="top">
            <table class="totals">
                <tr><td style="text-align: right; padding-right: 14px;">Total Before VAT | <span class="ar">قبل الضريبة</span></td><td class="v" width="26%">{{ $money($subTotal) }}</td></tr>
                <tr><td style="text-align: right; padding-right: 14px;">Total VAT | <span class="ar">الضريبة</span></td><td class="v">{{ $money($vatTotal) }}</td></tr>
            </table>
            <div style="border-top: 2px solid #111827; margin-top: 12px;"></div>
            <table class="grand">
                <tr>
                    <td style="text-align: right; padding-right: 14px;">
                        <div style="font-weight: bold; font-size: 9.5pt; color: #111827; letter-spacing: .4px;">GRAND TOTAL</div>
                        <div class="ar" style="font-weight: bold; font-size: 9pt; color: #111827; text-align: right;">المجموع الكلي</div>
                    </td>
                    <td width="26%" style="text-align: right; vertical-align: middle;"><span style="font-size: 15pt; font-weight: bold; color: var(--accent);">{{ $money($grandTotal) }}</span></td>
                </tr>
            </table>
            @if(!empty($afterGrand))
                <table class="totals" style="margin-top: 8px;">
                    @foreach($afterGrand as [$en, $ar, $value])
                        <tr><td style="text-align: right; padding-right: 14px;">{{ $en }} | <span class="ar">{{ $ar }}</span></td><td class="v" width="26%">{{ $value }}</td></tr>
                    @endforeach
                </table>
            @endif
        </td>
    </tr>
</table>
