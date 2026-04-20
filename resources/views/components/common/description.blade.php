<select name="description_id[]" class="tom-select" placeholder="--Select Description--" data-live-search="true" {{ $required ?? '' }} data-max-width="{{ $width ?? 'auto' }}"
        data-dropdown-width="{{ $dropdownWidth ?? 'auto' }}">
    <option value="">Select Description</option>
    @foreach(\App\Models\Master\Description::descriptions() as $desc)
        <option value="{{ $desc->id }}" @selected($desc->id == ($value ?? null))>
            {{ $desc->description }}
        </option>
    @endforeach
</select>
