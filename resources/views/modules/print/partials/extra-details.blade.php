{{-- Invoice-details / party-details rows switched on in Settings > Invoice (customer info on the left, shipment info on the right).
     Every row carries data-toggle="<key>" so the settings preview can show/hide it live; rows that are off are display:none. --}}
<table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 8px; border-collapse: collapse;">
    <tr>
        <td width="48%" valign="top" style="padding-right: 10px;">
            @foreach($extraPartyFields ?? [] as $field)
                <div data-toggle="{{ $field['key'] }}" style="font-size: 8pt; line-height: 1.55; {{ $field['visible'] ? '' : 'display:none;' }}">
                    <span style="color: #6b7280;">{{ $field['label_en'] }} <span class="ar">/ {{ $field['label_ar'] }}</span> :</span>
                    <strong>{{ $field['value'] }}</strong>
                </div>
            @endforeach
        </td>
        <td width="4%"></td>
        <td width="48%" valign="top" style="padding-left: 10px;">
            @foreach($extraJobFields ?? [] as $field)
                <div data-toggle="{{ $field['key'] }}" style="font-size: 8pt; line-height: 1.55; {{ $field['visible'] ? '' : 'display:none;' }}">
                    <span style="color: #6b7280;">{{ $field['label_en'] }} <span class="ar">/ {{ $field['label_ar'] }}</span> :</span>
                    <strong>{{ $field['value'] }}</strong>
                </div>
            @endforeach
        </td>
    </tr>
</table>
