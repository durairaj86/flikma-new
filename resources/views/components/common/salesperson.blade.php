{{-- extra attributes (e.g. required) go on the select --}}
@props(['value' => null, 'salesperson' => null])
<select class="tom-select" id="salesperson_id" name="salesperson_id" data-placeholder="{{ __('Select Salesperson') }}" {{ $attributes }}>
    <option value="" @selected(!$value)>{{ __('--Select--') }}</option>
    @foreach(($salesperson ?? \App\Models\Master\Salesperson\Salesperson::activeSalesperson()) as $salespersonData)
        <option
            value="{{ encodeId($salespersonData->id) }}" @selected($salespersonData->id == ($value ?? null))>{{ $salespersonData->name }}</option>
    @endforeach
</select>
