{{-- Container picker for the bill forms: lists the containers of the selected job (loaded by BillContainers.load() in startup.js). --}}
@php($selectedContainers = array_map('intval', (array) ($bill->container_ids ?? [])))
<div class="row g-3 mt-3" id="bl-containers" data-selected='@json($selectedContainers)'>
    <div class="col-12">
        <h5 class="border-bottom pb-2 d-flex align-items-center justify-content-between">
            <span>{{ __('Containers') }}</span>
            <small class="text-muted fw-normal" id="bl-containers-count"></small>
        </h5>
    </div>
    <div class="col-12">
        <div class="table-responsive border rounded">
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th style="width:40px;"><input type="checkbox" class="form-check-input" id="bl-containers-all" title="{{ __('Select all') }}"></th>
                    <th>{{ __('Container No') }}</th>
                    <th>{{ __('Size') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Seal No') }}</th>
                    <th class="text-end">{{ __('Weight') }}</th>
                </tr>
                </thead>
                <tbody id="bl-containers-body">
                <tr><td colspan="6" class="text-center text-muted py-3">{{ __('Select a job to choose its containers.') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
