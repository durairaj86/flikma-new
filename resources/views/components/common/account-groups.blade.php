<select name="{{ $name ?? 'account[]' }}" class="tom-select" data-live-search="true" required
        placeholder="Search Account">
    <option value="">Select Account</option>

    @foreach($parentAccount as $parent)
        @php
            // Get all sub-accounts belonging to this parent
            $children = $subAccounts->where('parent_id', $parent->id);
        @endphp

        @if($children->count() > 0)
            <optgroup label="{{ $parent->name }} ({{ $parent->code }})">
                @foreach($children as $child)
                    <option value="{{ $child->id }}" @selected($child->id == ($value ?? null))>
                        {{ $child->name }} - {{ $child->code }}
                    </option>
                @endforeach
            </optgroup>
        @endif
    @endforeach

    {{-- Handle accounts that have no parent (Miscellaneous) --}}
    @php
        $orphans = $subAccounts->whereNull('parent_id');
    @endphp
    @if($orphans->count() > 0)
        <optgroup label="Other">
            @foreach($orphans as $orphan)
                <option value="{{ $orphan->id }}" @selected($orphan->id == ($value ?? null))>
                    {{ $orphan->name }}
                </option>
            @endforeach
        </optgroup>
    @endif
</select>
