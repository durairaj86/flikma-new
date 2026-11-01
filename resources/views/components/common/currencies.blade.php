@props(['value' => null, 'locked' => false, 'lockedText' => null])
{{-- $locked: the currency can no longer be changed (e.g. the customer already has finance entries): shown, not editable, and still posted --}}
@if($locked)
    <input type="hidden" name="currency" value="{{ $value ?? 'SAR' }}">
    <select class="tom-select" data-live-search="true" disabled>
        @foreach(currencies() as $code => $name)
            <option value="{{ $code }}" @selected($code == ($value ?? 'SAR'))>{{ $code }} - {{ $name }}</option>
        @endforeach
    </select>
    <div class="form-text text-warning-emphasis"><i class="bi bi-lock-fill me-1"></i>{{ $lockedText ?? __('Locked: finance entries already exist.') }}</div>
@else
    <select name="currency" class="tom-select" data-live-search="true" required>
        @foreach(currencies() as $code => $name)
            <option value="{{ $code }}" @selected($code == ($value ?? 'SAR'))>{{ $code }} - {{ $name }}</option>
        @endforeach
    </select>
@endif
