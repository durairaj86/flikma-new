@php
    $multipleSuppliers = $multiple ?? null;
    $required = isset($required) && $required ? 'required' : '';
    if(isset($value)){
        $value = is_array($value) ? $value : [$value];
    }else{
        $value = [];
    }
@endphp
<select class="tom-select"
        data-selected-text-format="count>3" data-live-search="true" placeholder="Search Supplier" data-summary-label="suppliers"
        {{ $multipleSuppliers ? 'id=suppliers name=suppliers multiple' : 'id=supplier name=supplier' }} {{ $required }}>
    @if(!$multipleSuppliers)
        <option value="">--Select--</option>
    @endif
    @foreach(($suppliers ?? \App\Models\Supplier\Supplier::suppliers()) as $supplierData)
        <option
            value="{{ encodeId($supplierData->id) }}" @selected(in_array($supplierData->id, $value))>{{ $supplierData->name_en }}</option>
    @endforeach
</select>
