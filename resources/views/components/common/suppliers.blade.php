@props(['value' => null, 'required' => false, 'multiple' => null, 'suppliers' => null, 'disabled' => false,
        'id' => null, 'name' => null, 'placeholder' => null, 'allLabel' => null])
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
        data-selected-text-format="count>3" data-live-search="true" placeholder="{{ $placeholder ?? 'Search Supplier' }}" data-placeholder="{{ $placeholder ?? 'Select Supplier' }}" data-summary-label="suppliers"
        @disabled($disabled ?? false)
        id="{{ $id ?? ($multipleSuppliers ? 'suppliers' : 'supplier') }}"
        name="{{ $name ?? ($multipleSuppliers ? 'suppliers' : 'supplier') }}"
        {{ $multipleSuppliers ? 'multiple' : '' }} {{ $required }}
        {{ $attributes }}>
    @if(!$multipleSuppliers)
        <option value="">{{ $allLabel ?? '--Select--' }}</option>
    @endif
    @foreach(($suppliers ?? \App\Models\Supplier\Supplier::suppliers()) as $supplierData)
        <option
            value="{{ encodeId($supplierData->id) }}" @selected(in_array($supplierData->id, $value))
            data-subtext="{{ $supplierData->row_no }}" data-credit-days="{{ $supplierData->credit_days }}"
            data-currency="{{ $supplierData->currency }}">{{ $supplierData->name_en }}</option>
    @endforeach
</select>
