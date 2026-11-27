<select name="unit_id[]" class="tom-select" placeholder="{{ __('--Select Unit--') }}" data-live-search="true" required data-max-width="150" data-dropdown-width="200">
    <option value="">{{ __('Select Unit') }}</option>
    @foreach(\App\Models\Master\Unit::units([$value ?? null]) as $desc)
        {{-- code first (CM), full name as the small line under it (Centimeter) --}}
        {{-- data-off: switched off, listed only because this line already uses it (not offered on a new line) --}}
        <option value="{{ $desc->id }}" data-subtext="{{ $desc->unit_name }}" @if($desc->off) data-off="1" @endif @selected($desc->id == ($value ?? null))>
            {{ $desc->unit_symbol ?: $desc->unit_name }}
        </option>
    @endforeach
</select>
