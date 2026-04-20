<select name="{{$inputName ?? 'country'}}" class="tom-select" data-live-search="true" required>
    @foreach(countries() as $code => $name)
        <option value="{{ $name }}" @selected($name == ($value ?? 'Saudi Arabia'))>{{ $name }}</option>
    @endforeach
</select>
