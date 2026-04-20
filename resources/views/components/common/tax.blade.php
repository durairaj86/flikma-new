<select name="tax[]" class="tom-select tax" required data-live-search="true" placeholder="Select Tax" data-max-width="{{ $width ?? '300' }}" data-dropdown-width="{{ $dropdownWidth ?? '300' }}">
    <option value="">Select Tax</option>
    @foreach(vat() as $vat)
        <option value="{{ $vat['code'] }}"
                data-subtext="{{ $vat['description'] }}"
                data-percent="{{ $vat['percent'] }}" @selected($vat['code'] == $value)>
            {{ $vat['name'] }} ({{ $vat['percent'] }}%)
        </option>
    @endforeach
</select>
