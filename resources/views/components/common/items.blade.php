@php
    $createItem = $new ?? true;
    if(isset($value)){
        $value = is_array($value) ? $value : [$value];
    }else{
        $value = [];
    }
@endphp
<select
    class="tom-select" data-placeholder="Select Item" data-live-search="true" data-create="{{ $createItem }}"
    name="item_id[]"
    required
>
    <option value="">--Select--</option>
    @foreach(($items ?? \App\Models\Item\Item::items()) as $itemData)
        <option
            value="{{ encodeId($itemData->id) }}"
            @selected(filled($itemData->id) && in_array($itemData->id, $value))
            data-subtext="{{ $itemData->sku_code }}"
        >
            {{ $itemData->name_en }}
        </option>
    @endforeach
</select>
