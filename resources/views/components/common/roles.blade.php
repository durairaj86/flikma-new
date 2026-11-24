<select class="tom-select" data-live-search="true" id="role" name="role" required>
    <option value="">{{ __('--Select--') }}</option>
    {{-- Role 1 (Super User) is reserved for the person who registered the
         company and cannot be assigned to anyone else. --}}
    @foreach(roles() as $roleId => $roleType)
        @continue($roleId === 1)
        @php
            $roleValue = array_key_first($roleType);   // "Admin"
            $roleSubText = $roleType[$roleValue];     // what the role can do
        @endphp
        <option value="{{ $roleId }}"
                @selected($roleId == ($value ?? null))
                data-subtext="{{ __($roleSubText) }}">
            {{ __($roleValue) }}
        </option>
    @endforeach
</select>
