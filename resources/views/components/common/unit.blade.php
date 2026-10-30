<select name="unit_id[]" class="tom-select" placeholder="{{ __('--Select Unit--') }}" data-live-search="true" required data-max-width="150" data-dropdown-width="200">
    <option value="">{{ __('Select Unit') }}</option>
    @foreach(\App\Models\Master\Unit::units([$value ?? null]) as $desc)
        {{-- code first (CM), full name as the small line under it (Centimeter) --}}
        <option value="{{ $desc->id }}" data-subtext="{{ $desc->unit_name }}" @selected($desc->id == ($value ?? null))>
            {{ $desc->unit_symbol ?: $desc->unit_name }}
        </option>
    @endforeach
</select>
