{{--
    The one job dropdown of the software: job number as the option, the customer's name as the small line under it.
    :jobs     jobs to list (each with its customer loaded); defaults to every job of the company
    :value    selected job id (or an array of ids, the first match wins)
    :extra    optional fn($job) => ['data-xxx' => value] for form specific data attributes
    Also available on every option: data-customer-id / data-call-value (hashed customer id), data-customer-name.
    Any other attribute (data-call-back, data-dropdown-parent ...) goes onto the <select>.
--}}
@props(['jobs' => null, 'value' => null, 'name' => 'job_id', 'id' => null, 'required' => false, 'disabled' => false, 'placeholder' => null, 'extra' => null])
@php
    $jobs = $jobs ?? \App\Models\Job\Job::select('id', 'row_no', 'customer_id')->with('customer:id,name_en')->orderByDesc('id')->get();
    $selected = array_values(array_filter((array) $value, fn ($v) => $v !== null && $v !== ''));
    $placeholder = $placeholder ?? __('Select Job');
@endphp
<select name="{{ $name }}" @if($id) id="{{ $id }}" @endif class="tom-select" data-live-search="true"
        data-placeholder="{{ $placeholder }}" @required($required) @disabled($disabled) {{ $attributes }}>
    <option value="">{{ $placeholder }}</option>
    @foreach($jobs as $job)
        @php $hash = $job->customer_id ? encodeId($job->customer_id) : ''; @endphp
        <option value="{{ $job->id }}" @selected(in_array($job->id, $selected))
                data-subtext="{{ $job->customer?->name_en }}" data-customer-name="{{ $job->customer?->name_en }}"
                data-customer-id="{{ $hash }}" data-call-value="{{ $hash }}"
                @foreach(($extra ? $extra($job) : []) as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach>{{ $job->row_no }}</option>
    @endforeach
</select>
