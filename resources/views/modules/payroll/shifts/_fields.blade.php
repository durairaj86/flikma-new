@php($prefix = $prefix ?? '')
@php($id = fn($name) => $prefix ? $prefix . '_shift_' . $name : 'shift_' . $name)

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label small fw-bold required">{{ __('Name') }}</label>
        <input type="text" name="name" id="{{ $id('name') }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold required">{{ __('Type') }}</label>
        <select name="type" id="{{ $id('type') }}" class="form-select" required>
            <option value="morning">{{ __('Morning') }}</option>
            <option value="night">{{ __('Night') }}</option>
            <option value="rotational">{{ __('Rotational') }}</option>
            <option value="custom">{{ __('Custom') }}</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold required">{{ __('Start Time') }}</label>
        <input type="time" name="start_time" id="{{ $id('start_time') }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold required">{{ __('End Time') }}</label>
        <input type="time" name="end_time" id="{{ $id('end_time') }}" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label small fw-bold">{{ __('Grace Period (minutes)') }}</label>
        <input type="text" min="0" max="180" name="grace_minutes" id="{{ $id('grace') }}" class="integer form-control" value="15">
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" id="{{ $id('active') }}" class="form-check-input" checked>
            <label class="form-check-label small fw-bold" for="{{ $id('active') }}">{{ __('Active') }}</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label small fw-bold d-block">{{ __('Week Off Days') }}</label>
        @foreach($weekDayOptions as $value => $label)
            <div class="form-check form-check-inline">
                <input type="checkbox" name="week_off_days[]" value="{{ $value }}"
                       class="form-check-input {{ $prefix ? 'edit-week-off-day' : '' }}"
                       id="{{ $id('day_' . $value) }}">
                <label class="form-check-label small" for="{{ $id('day_' . $value) }}">{{ $label }}</label>
            </div>
        @endforeach
    </div>
</div>
