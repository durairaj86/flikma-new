<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="Save Department">
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $department->name ?? 'New Department' }}</span>
            </div>
            <div id="show-buttons"></div>
        </div>
    </div>
    <div class="row">
        <div class="d-flex justify-content-center">
            <div class="d-inline-block p-1">
                <ul class="nav status-tabs align-items-center border-bottom mb-0 mt-0 justify-content-center"
                    id="modalTabs" role="tablist">
                    <li class="nav-item me-2">
                        <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn active"
                                data-bs-toggle="tab" data-bs-target="#tab-basic" type="button">
                            <i class="bi bi-building me-1"></i> Details
                        </button>
                    </li>
                    <li class="nav-item me-2">
                        <button class="nav-link px-3 py-2 d-flex align-items-center justify-content-between status-btn"
                                data-bs-toggle="tab" data-bs-target="#tab-rights" type="button">
                            <i class="bi bi-shield-lock me-1"></i> User Rights
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <form id="moduleForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="data-id" value="{{ $department->id ?? '' }}">

            <div class="tab-content">
                <div class="tab-pane show active" id="tab-basic">
                    <div class="model-form-tab-div">
                        <div class="row mt-2">
                            <div class="col-4 form-group">
                                <label class="form-label">Department Name</label>
                                <input type="text" name="name" class="form-control" required
                                       value="{{ $department->name ?? '' }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">Code</label>
                                <input type="text" name="code" class="form-control"
                                       value="{{ $department->code ?? '' }}">
                            </div>
                            <div class="col-4 form-group">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="tom-select" required>
                                    <option value="1" @selected(($department->is_active ?? true))>Active</option>
                                    <option value="0" @selected(!($department->is_active ?? true))>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="tab-rights">
                    <div class="model-form-tab-div">
                        <p class="text-secondary small mb-3">
                            Choose what users in this department can see and do in each part of the
                            software. The Super User (the account that registered the company) is
                            never limited by these rights.
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                <tr>
                                    <th>Module</th>
                                    <th class="text-center">View</th>
                                    <th class="text-center">Create</th>
                                    <th class="text-center">Edit</th>
                                    <th class="text-center">Delete</th>
                                    <th class="text-center">All</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($rights as $moduleKey => $right)
                                    <tr data-module-row="{{ $moduleKey }}">
                                        <td>{{ $right['label'] }}</td>
                                        @foreach(['view','create','edit','delete'] as $action)
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input right-checkbox"
                                                       name="rights[{{ $moduleKey }}][{{ $action }}]" value="1"
                                                       @checked($right[$action])>
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input row-select-all">
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        $('.row-select-all').off().on('change', function () {
            const checked = $(this).is(':checked');
            $(this).closest('tr').find('.right-checkbox').prop('checked', checked);
        });

        // Keep each row's "All" checkbox in sync when individual boxes change.
        $('.right-checkbox').off().on('change', function () {
            const row = $(this).closest('tr');
            const total = row.find('.right-checkbox').length;
            const checkedCount = row.find('.right-checkbox:checked').length;
            row.find('.row-select-all').prop('checked', total === checkedCount);
        });

        $('.right-checkbox').trigger('change');
    })();
</script>
