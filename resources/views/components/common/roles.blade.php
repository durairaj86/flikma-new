<select class="tom-select" data-live-search="true" id="role" name="role" required>
    <option value="">--Select--</option>
    @foreach(roles() as $roleId => $roleType)
        @php
            $roleValue = array_key_first($roleType);   // "General"
            $roleSubText = $roleType[$roleValue];     // "normal goods, non-special handling"
        @endphp
        <option value="{{ $roleId }}"
                @selected($roleId == ($value ?? null))
                data-subtext="{{ $roleSubText }}">
            {{ $roleValue }}
        </option>
    @endforeach
</select>
