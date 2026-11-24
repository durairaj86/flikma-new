<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Rights') }}">
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ __('User Rights') }}: {{ $user->name }}</span>
                <div class="text-secondary">{{ __('Department') }}: {{ $department?->name ?? __('None') }}</div>
            </div>
            <div id="show-buttons"></div>
        </div>
    </div>
    <form id="moduleForm" novalidate action="{{ request()->url() }}">
        @csrf
        <input type="hidden" name="data-id" value="{{ $user->id }}">

        {{-- department rights: the boxes show the department's rights, locked; manual rights: the user's own, to change --}}
        <div class="d-flex flex-wrap gap-2 mb-3" id="rights-mode" role="radiogroup" aria-label="{{ __('User Rights') }}">
            @php($mode = $user->rights_mode === 'manual' ? 'manual' : 'department')
            <input type="radio" class="btn-check" name="rights_mode" id="rights-mode-department" value="department" @checked($mode === 'department')>
            <label class="btn btn-outline-primary" for="rights-mode-department">
                <i class="bi bi-diagram-3 me-1"></i> {{ __('Apply department rights') }}
            </label>
            <input type="radio" class="btn-check" name="rights_mode" id="rights-mode-manual" value="manual" @checked($mode === 'manual')>
            <label class="btn btn-outline-primary" for="rights-mode-manual">
                <i class="bi bi-sliders me-1"></i> {{ __('Apply manual rights') }}
            </label>
        </div>
        <p class="text-secondary small mb-3" id="rights-mode-help-department" @if($mode !== 'department') hidden @endif>
            @if($user->department_id)
                {{ __('This user has the rights of the :department department. A change to the department\'s rights applies to this user too.', ['department' => $department?->name]) }}
            @else
                {{ __('This user has no department, so department rights give no access. Choose a department in Edit, or apply manual rights.') }}
            @endif
        </p>
        <p class="text-secondary small mb-3" id="rights-mode-help-manual" @if($mode !== 'manual') hidden @endif>
            {{ __('This user has their own rights, whatever their department has. They start from the department\'s rights; tick or untick what this user may do.') }}
        </p>

        @include('modules.master.department.rights-table', ['rights' => $rights])
    </form>
</div>

<script>
    (function () {
        // department rights per box, to show (locked) while "Apply department rights" is chosen
        const departmentRights = @json($departmentBoxes);
        const boxes = $('#rights-table .right-checkbox');
        let manual = null;   // the manual ticks, kept while the department's are shown

        const apply = function () {
            const isManual = $('#rights-mode-manual').is(':checked');
            $('#rights-mode-help-department').prop('hidden', isManual);
            $('#rights-mode-help-manual').prop('hidden', !isManual);
            if (isManual) {
                if (manual) boxes.each(function () { this.checked = !!manual[this.name]; });
            } else {
                manual = {};
                boxes.each(function () {
                    manual[this.name] = this.checked;
                    const key = $(this).closest('tr').data('module-row');
                    this.checked = !!(departmentRights[key] && departmentRights[key][$(this).data('action')]);
                });
            }
            boxes.prop('disabled', !isManual);
            $('#rights-table .row-select-all').prop('disabled', !isManual);
            boxes.trigger('change', [true]);   // each row's All box follows
        };
        $('input[name="rights_mode"]').off('change.rights').on('change.rights', apply);
        apply();
    })();
</script>
