<select name="unit_id[]" class="tom-select" placeholder="--Select Unit--" data-live-search="true" required data-max-width="150" data-dropdown-width="200">
    <option value="">Select Unit</option>
    @foreach(\App\Models\Master\Unit::units() as $desc)
        <option value="{{ $desc->id }}" @selected($desc->id == ($value ?? null))>
            {{ $desc->unit_name }} - {{ $desc->unit_symbol }}
        </option>
    @endforeach
</select>
