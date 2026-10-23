{{-- Invoice-details / party-details rows switched on in Settings > Invoice (customer info on the left, shipment info on the right).
     Every row carries data-toggle="<key>" so the settings preview can show/hide it live; rows that are off are display:none.
     Each row is a fixed three-cell line — English label | value | Arabic label — with every cell bidi-isolated, so mixed
     Arabic/English text and numbers can no longer re-order each other. --}}
@php
    $extraRow = function (array $field) {
        $hidden = $field['visible'] ? '' : 'display:none;';
        return '<tr data-toggle="' . e($field['key']) . '" style="font-size: 8pt; line-height: 1.55; ' . $hidden . '">'
            . '<td dir="ltr" style="width: 34%; color: #6b7280; text-align: left; unicode-bidi: isolate; padding: 1px 4px 1px 0;">' . e($field['label_en']) . '</td>'
            . '<td dir="ltr" style="width: 34%; font-weight: 700; text-align: left; unicode-bidi: isolate; padding: 1px 4px;">' . e($field['value']) . '</td>'
            . '<td dir="rtl" lang="ar" style="width: 32%; color: #6b7280; text-align: right; unicode-bidi: isolate; padding: 1px 0 1px 4px;">' . e($field['label_ar']) . '</td>'
            . '</tr>';
    };
@endphp
@php
    // Customer rows first, then job rows; the rows that are switched on are split evenly across the two columns.
    // Rows switched off stay in the markup (display:none) so the settings preview can still toggle them.
    $allExtra = collect($extraPartyFields ?? [])->concat($extraJobFields ?? [])->values();
    $shownExtra = $allExtra->filter(fn ($f) => $f['visible'])->values();
    $hiddenExtra = $allExtra->reject(fn ($f) => $f['visible'])->values();
    $half = (int) ceil($shownExtra->count() / 2);
    $leftExtra = $shownExtra->slice(0, $half)->concat($hiddenExtra);
    $rightExtra = $shownExtra->slice($half);
@endphp
<table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 8px; border-collapse: collapse;">
    <tr>
        <td width="48%" valign="top" style="padding-right: 10px;">
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse: collapse; table-layout: fixed;">
                @foreach($leftExtra as $field)
                    {!! $extraRow($field) !!}
                @endforeach
            </table>
        </td>
        <td width="4%"></td>
        <td width="48%" valign="top" style="padding-left: 10px;">
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-collapse: collapse; table-layout: fixed;">
                @foreach($rightExtra as $field)
                    {!! $extraRow($field) !!}
                @endforeach
            </table>
        </td>
    </tr>
</table>
