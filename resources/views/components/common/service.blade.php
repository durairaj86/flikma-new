<select id="services" name="services[]" class="tom-select" data-live-search="true" placeholder="--Select Services--" multiple required data-max-items="3">
    @foreach(services() as $serviceId => $serviceValue)
        <option value="{{ $serviceId }}" @selected(in_array($serviceId, $value ?? []))>{{ $serviceValue }}</option>
    @endforeach
</select>
