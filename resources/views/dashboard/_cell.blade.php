<div class="dash-item dash-{{ $widgetSizes[$key] }}" data-key="{{ $key }}">
    <span class="dash-handle" title="{{ __('Drag to move') }}"><i class="bi bi-grip-horizontal"></i></span>
    <button type="button" class="dash-remove" title="{{ __('Remove widget') }}" aria-label="{{ __('Remove widget') }}"><i class="bi bi-x-lg"></i></button>
    @include('dashboard._item', ['key' => $key])
</div>
