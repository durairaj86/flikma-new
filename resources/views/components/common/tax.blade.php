{{-- The tax dropdown, as in the fatoora invoice app: built from the saudi_vat_framework table (see vat() in pre-defined-helpers.php).
     Groups: Standard Rated / Zero Rated / Exempt / Out of Scope; the text is "rate% - Name", and under it the VATEX reason code and description. --}}
<select name="tax[]" class="tom-select tax" required data-live-search="true" placeholder="{{ __('Select Tax') }}" data-max-width="{{ $width ?? '300' }}" data-dropdown-width="{{ $dropdownWidth ?? '300' }}">
    <option value="">{{ __('Select Tax') }}</option>
    @foreach(collect(vatSelectable($value ?? null))->groupBy('group') as $group => $rates)
        <optgroup label="{{ __($group) }}">
            @foreach($rates as $vat)
                <option value="{{ $vat['code'] }}"
                        data-subtext="{{ $vat['subtext'] }}"
                        data-percent="{{ $vat['percent'] }}" @selected($vat['code'] == ($value ?? null))>
                    {{ $vat['option'] }}
                </option>
            @endforeach
        </optgroup>
    @endforeach
</select>
