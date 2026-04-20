<div class="input-group align-items-center position-relative">
    <!-- Currency Rate -->
    <input type="text" name="currency_rate" id="currency-rate" @if(isset($wireModel)) wire:model="currency_rate"
           @endif
           class="form-control text-end" value="{{ $exchangeRate ?? 1 }}"
           style="max-width: 120px;">

    <!-- Spinner inside input group -->
    <span id="rate-loader"
          class="position-absolute end-0 me-5 d-none"
          style="top: 50%; transform: translateY(-50%); z-index: 5;">
        <div class="spinner-border spinner-border-sm text-secondary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </span>

    <!-- Currency Code -->
    <select name="currency" id="currency-code" @if(isset($wireModel)) wire:model.live="currency"
            @endif placeholder="--Select Currency--" @disabled($disabled ?? false)
            class="tom-select" data-live-search="true" data-max-width="{{ $width ?? '150' }}" required>
        @foreach(currencies() as $code => $name)
            <option value="{{ $code }}" data-subtext="{{ $name }}" @selected($code == ($value ?? 'SAR'))>
                {{ $code }}
            </option>
        @endforeach
    </select>
</div>
