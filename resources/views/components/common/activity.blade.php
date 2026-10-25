<select class="tom-select" id="activity-id" name="activity_id" data-placeholder="{{ __('Select Activity') }}" data-live-search="true" required>
    <option value="" @selected(!$value)>{{ __('--Select--') }}</option>
    @foreach(\App\Models\Master\LogisticActivity::activities() as $activity)
        <option data-shipment-type="{{ $activity->mode }}"
                value="{{ $activity->id }}" @selected($activity->id == ($value ?? null))>{{ $activity->name }}</option>
    @endforeach
</select>
<input type="hidden" id="activity-id-hidden" class="form-control" value="{{ $shipmentMode ?? 'sea' }}">
