<select name="account[]" class="tom-select" data-live-search="true" required placeholder="Search Account">
    <option value="">Select Account</option>
    @foreach($accounts as $acc)
        <option value="{{ $acc->code }}" @selected($acc->code == ($value ?? null))>
            {{ $acc->name }}
        </option>
    @endforeach
</select>
