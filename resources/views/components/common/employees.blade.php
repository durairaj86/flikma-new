{{-- Shared employee dropdown (TomSelect + live search, code shown under the name like the customer dropdown).
     Usage: <x-common.employees name="employee_id" :value="$id" placeholder="All Employees" :required="true" />
     Extra attributes (class, onchange, id...) pass straight through to the <select>.
     placeholder=null removes the blank option; activeOnly hides terminated employees (use on create forms). --}}
@props(['name' => 'employee_id', 'value' => null, 'placeholder' => 'Select Employee', 'required' => false, 'activeOnly' => false])
@php
    $selectId = $attributes->get('id') ?: 'emp-' . \Illuminate\Support\Str::random(8);
    $selected = is_array($value) ? $value : [$value];
    $employees = \App\Models\User::where('is_employee', true)
        ->when($activeOnly, fn ($q) => $q->whereNull('terminated_at'))
        ->orderBy('name')->get(['id', 'name', 'employee_code', 'terminated_at']);
@endphp
<select name="{{ $name }}" id="{{ $selectId }}"
        {{ $attributes->except('id')->merge(['class' => 'form-select'])->class('tom-select') }}
        data-live-search="true" data-placeholder="{{ __($placeholder ?? 'Select Employee') }}"
        @required($required)>
    @if($placeholder !== null)
        <option value="">{{ __($placeholder) }}</option>
    @endif
    @foreach($employees as $e)
        <option value="{{ $e->id }}" @selected(in_array((string) $e->id, array_map('strval', array_filter($selected, fn ($v) => $v !== null))))
                data-subtext="{{ $e->employee_code }}{{ $e->terminated_at ? ' · ' . __('Terminated') : '' }}">{{ $e->name }}</option>
    @endforeach
</select>
{{-- Self-initialising, so it also works on pages whose content sits outside #content-wrapper (global init skips those). --}}
<script>
    (function () {
        var sel = document.getElementById('{{ $selectId }}');   // by id: the script may be re-run from elsewhere in the page (AJAX list swaps)
        var go = function () { if (sel && !sel.tomselect && typeof initTomSelectForm === 'function' && window.jQuery) { initTomSelectForm(null, false, sel); } };
        if (document.readyState === 'complete' || document.readyState === 'interactive') { go(); } else { window.addEventListener('load', go); }
    })();
</script>
