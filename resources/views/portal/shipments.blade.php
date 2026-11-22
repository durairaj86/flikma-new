@extends('portal.layout')
@section('title', __('Shipments'))
@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h4 class="fw-bold mb-0">{{ __('Shipments') }}</h4>
        <form class="d-flex gap-2" method="GET">
            <div style="width:170px"><select name="show" class="tom-select" data-submit aria-label="{{ __('Show') }}">
                <option value="">{{ __('In progress') }}</option>
                <option value="all" @selected(request('show') === 'all')>{{ __('All shipments') }}</option>
            </select></div>
            <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="{{ __('Number, B/L or container') }}" aria-label="{{ __('Search') }}">
            <button class="btn btn-sm btn-primary">{{ __('Search') }}</button>
        </form>
    </div>
    <div class="p-card">
        @include('portal.partials.shipment-rows', ['jobs' => $jobs])
    </div>
    <div class="mt-3">{{ $jobs->links('pagination::bootstrap-5') }}</div>
@endsection
