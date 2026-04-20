<select class="form-control selectpicker" data-live-search="true" id="cargo_type" name="cargo_type">
    <option value="">--Select--</option>
    @foreach(cargoTypes() as $cargoTypeId => $cargoSubType)
        @php
            $cargoValue = array_key_first($cargoSubType);   // "General"
            $cargoSubText = $cargoSubType[$cargoValue];     // "normal goods, non-special handling"
        @endphp
        <option value="{{ $cargoTypeId }}"
                @selected($cargoTypeId == ($value ?? null))
                data-subtext="{{ $cargoSubText }}">
            {{ $cargoValue }}
        </option>
    @endforeach
</select>
