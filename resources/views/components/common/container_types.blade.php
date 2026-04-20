<select name="{{ $name ?? 'container_type[]' }}" class="tom-select" data-max-width="150" data-dropdown-width="250" data-live-search="true">
    <option value="">Select</option>
    @foreach(containerTypes() as $containerTypeId => $containerType)
        @php
            $containerValue = array_key_first($containerType);   // "General"
            $containerSubText = $containerType[$containerValue];     // "normal goods, non-special handling"
        @endphp
        <option value="{{ $containerTypeId }}"
                @selected($containerTypeId == ($value ?? null))
                data-subtext="{{ $containerSubText }}">
            {{ $containerValue }}
        </option>
    @endforeach
</select>
