@props(['value' => null, 'required' => false, 'new' => true, 'customers' => null, 'multiple' => null, 'disabled' => false,
        'id' => null, 'name' => null, 'placeholder' => null, 'allLabel' => null])
@php
    $multipleCustomers = $multiple ?? null;
    $required = isset($required) && $required ? 'required' : '';
    $newCustomer = $new ?? true;
    if(isset($value)){
        $value = is_array($value) ? $value : [$value];
    }else{
        $value = [];
    }
@endphp
{{--<select class="form-control selectpicker"
        data-selected-text-format="count>3" data-live-search="true"
        {{ $multipleCustomers ? 'id=customers name=customers[] multiple' : 'id=customer name=customer' }} required>
    @if(!$multipleCustomers)
        <option value="">--Select--</option>
    @endif
    @foreach(($customers ?? \App\Models\Customer\Customer::confirmedCustomers()) as $customerData)
        <option
            value="{{ encodeId($customerData->id) }}" @selected(in_array($customerData->id, $value))>{{ $customerData->name_en }}</option>
    @endforeach
        <option data-divider="true"></option>
    <option value="__new__" data-type="new">+ Add New Customer</option>
</select>--}}
<select
    class="tom-select" data-placeholder="{{ $placeholder ?? 'Select Customer' }}" data-live-search="true" data-summary-label="customers"
    @disabled($disabled ?? false)
    id="{{ $id ?? ($multipleCustomers ? 'customers' : 'customer') }}"
    name="{{ $name ?? ($multipleCustomers ? 'customers' : 'customer') }}"
    {{ $attributes }}
    {{--{{ $multipleCustomers ? 'multiple' : '' }}--}}
    {{ $required }}

>
    @unless($multipleCustomers)
        <option value="">{{ $allLabel ?? '--Select--' }}</option>
    @else
        {{-- Empty option first so the browser never auto-selects a real
             customer (a bare single-select silently filters every list). --}}
        <option value="">--Select--</option>
    @endunless

    @foreach(($customers ?? \App\Models\Customer\Customer::confirmedCustomers()) as $customerData)
        <option
            value="{{ encodeId($customerData->id) }}"
            @selected(in_array($customerData->id, $value))
            data-subtext="{{ $customerData->row_no }}" data-credit-days="{{ $customerData->credit_days }}"
            data-currency="{{ $customerData->currency }}"
        >
            {{ $customerData->name_en }}
        </option>
    @endforeach
    @if($newCustomer)
        <option data-divider="true"></option>
        <option value="__new__" data-type="new" data-module="CUSTOMER">+ Add New Customer</option>
    @endif
</select>
