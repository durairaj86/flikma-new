<select
    name="{{ isset($multiple) ? 'employee_id[]' : 'employee_id' }}"
    class="tom-select"
    {{ isset($id) ? "id=$id" : '' }}
>
        placeholder="--Select Employee--" data-live-search="true"
        {{ $required ?? '' }} data-max-width="{{ $width ?? 'auto' }}"
        data-dropdown-width="{{ $dropdownWidth ?? 'auto' }}">
    <option value="">Select Employee</option>
    @foreach(\App\Models\User::all() as $user)
        <option value="{{ $user->id }}" @selected($user->id == ($value ?? null))>
            {{ $user->name }}
        </option>
    @endforeach
</select>
