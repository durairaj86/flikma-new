{{-- Contact / address rows of the customer that are switched on in Settings > Invoice (email, alt. phone, postal code, country, region):
     they belong to the Billed To address, like in the Stylish layout. Used together with extra-details's $splitAddress. --}}
@php($addressKeys = array_values(array_diff(['email', 'alt_phone', 'postal_code', 'country', 'region'], $skipAddressKeys ?? [])))
@foreach(collect($extraPartyFields ?? [])->whereIn('key', $addressKeys)->where('visible', true) as $field)
    <div data-toggle="{{ $field['key'] }}" style="font-size: 8pt; line-height: 1.5; overflow-wrap: anywhere;">
        <span style="color: #6b7280;">{{ $field['label_en'] }} :</span> <span dir="ltr" style="unicode-bidi: isolate;">{{ filled($field['value']) ? $field['value'] : '-' }}</span>
    </div>
@endforeach
