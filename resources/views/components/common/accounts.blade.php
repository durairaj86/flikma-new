<select name="account[]" class="tom-select" data-live-search="true" required placeholder="{{ __('Search Account') }}">
    <option value="">{{ __('Select Account') }}</option>
    @foreach($accounts as $acc)
        <option value="{{ $acc->code }}" @selected($acc->code == ($value ?? null))>
            {{ $acc->name }}
        </option>
    @endforeach
</select>
