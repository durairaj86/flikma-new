<select name="{{ $name ?? 'container_size[]' }}" class="tom-select" data-max-width="100" data-dropdown-width="250" data-live-search="true">
    <option value="">Select</option>
    @foreach(containerSize() as $containerId => $containerText)
        <option value="{{ $containerId }}" @selected($containerId == ($value ?? null)) data-subtext="{{ $containerText }}">{{ $containerId }}</option>
    @endforeach
</select>
