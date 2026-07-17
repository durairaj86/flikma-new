<select class="tom-select" data-live-search="true" id="role" name="role" required>
    <option value="">--Select--</option>
    {{-- Role 1 (Super User) is reserved for the person who registered the
         company and cannot be assigned to anyone else. --}}
    @foreach(roles() as $roleId => $roleType)
        @continue($roleId === 1)
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
