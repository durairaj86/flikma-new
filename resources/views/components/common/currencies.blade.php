<select name="currency" class="tom-select" data-live-search="true" required>
    @foreach(currencies() as $code => $name)
        <option value="{{ $code }}" @selected($code == ($value ?? 'SAR'))>{{ $code }} - {{ $name }}</option>
    @endforeach
</select>
