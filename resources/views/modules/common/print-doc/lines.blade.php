{{-- The charge lines of a document print: $lines = [[description, unit, qty, unit_price, before_vat, vat_pct, vat], ...], $emptyText. --}}
@php
    $money = fn($v) => number_format((float) $v, 2);
    $pct = fn($v) => rtrim(rtrim(number_format((float) $v, 2), '0'), '.') . '%';
@endphp
<table class="items" style="margin-top: 26px;">
    <thead>
    <tr>
        <th align="left" style="width: 4%;">#</th>
        <th align="left" style="width: 33%;">Description <span class="ar">الوصف</span></th>
        <th align="left" style="width: 12%;">Unit <span class="ar">الوحدة</span></th>
        <th align="right" style="width: 7%;">Qty <span class="ar">الكمية</span></th>
        <th align="right" style="width: 12%;">Unit Price <span class="ar">سعر الوحدة</span></th>
        <th align="right" style="width: 12%;">Before VAT <span class="ar">قبل الضريبة</span></th>
        <th align="right" style="width: 8%;">VAT <span class="ar">النسبة</span></th>
        <th align="right" style="width: 12%;">VAT Amt <span class="ar">قيمة الضريبة</span></th>
    </tr>
    </thead>
    <tbody>
    @forelse($lines as $i => $l)
        <tr>
            <td class="muted">{{ $i + 1 }}</td>
            <td>{{ $l['description'] }}@if(!empty($l['remarks']))<div class="muted" style="font-size: 8pt; margin-top: 2px;">{{ $l['remarks'] }}</div>@endif</td>
            <td>{{ $l['unit'] ?: '-' }}</td>
            <td class="num">{{ $money($l['qty']) }}</td>
            <td class="num">{{ $money($l['unit_price']) }}</td>
            <td class="num">{{ $money($l['before_vat']) }}</td>
            <td class="num">{{ $pct($l['vat_pct']) }}</td>
            <td class="num">{{ $money($l['vat']) }}</td>
        </tr>
    @empty
        <tr><td colspan="8" class="muted" style="text-align: center; padding: 18px;">{{ $emptyText }}</td></tr>
    @endforelse
    </tbody>
</table>
