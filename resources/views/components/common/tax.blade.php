<select name="tax[]" class="tom-select tax" required data-live-search="true" placeholder="Select Tax" data-max-width="{{ $width ?? '300' }}" data-dropdown-width="{{ $dropdownWidth ?? '300' }}">
    <option value="">{{ __('Select Tax') }}</option>
    {{-- Grouped by VAT category: Standard Rated / Zero Rated / Exempt / Out of Scope --}}
    @foreach(collect(vat())->groupBy('group') as $group => $rates)
        <optgroup label="{{ __($group) }}">
            @foreach($rates as $vat)
                <option value="{{ $vat['code'] }}"
                        data-subtext="{{ __($vat['description']) }}"
                        data-percent="{{ $vat['percent'] }}" @selected($vat['code'] == $value)>
                    {{ number_format($vat['percent'], 2) }}% - {{ __($vat['label']) }}
                </option>
            @endforeach
        </optgroup>
    @endforeach
</select>
