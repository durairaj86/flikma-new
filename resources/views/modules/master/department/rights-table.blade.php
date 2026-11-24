{{-- The rights table of Department & Rights and of Employees > User Rights: one row per module / sub menu, a box per action it has. --}}
{{-- filters the rows below; no name, so it is not saved with the form --}}
<div class="input-group input-group-sm mb-2" style="max-width: 320px;">
    <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
    <input type="search" class="form-control" id="rights-search" autocomplete="off"
           placeholder="{{ __('Search module') }}" aria-label="{{ __('Search module') }}">
</div>
<div class="table-responsive">
    {{-- while searching, module rows are titles only: their boxes would also tick / untick sub menus the search hides --}}
    <style>
        #rights-table.rights-searching tr[data-module-row]:not([data-parent]) td:not(:first-child) > * { visibility: hidden; }
    </style>
    <table class="table table-sm align-middle" id="rights-table">
        <thead>
        <tr>
            <th>{{ __('Module') }}</th>
            <th class="text-center">{{ __('View') }}</th>
            <th class="text-center">{{ __('Create') }}</th>
            <th class="text-center">{{ __('Edit') }}</th>
            <th class="text-center">{{ __('Delete') }}</th>
            <th class="text-center">{{ __('Approve') }}</th>
            <th class="text-center">{{ __('Confirm') }}</th>
            <th class="text-center">{{ __('All') }}</th>
        </tr>
        </thead>
        <tbody>
        @foreach($rights as $moduleKey => $right)
            @php($rightParent = $right['parent'] ?? null)
            <tr data-module-row="{{ $moduleKey }}" @if($rightParent) data-parent="{{ $rightParent }}" @endif
                data-search="{{ mb_strtolower($right['label'] . ' ' . __($right['label'])) }}"
                class="{{ $rightParent ? '' : 'table-light' }}">
                <td class="{{ $rightParent ? 'ps-4 text-secondary' : 'fw-semibold' }}">
                    @if($rightParent)<i class="bi bi-arrow-return-right me-1 small"></i>@endif{{ __($right['label']) }}
                </td>
                {{-- only the actions this section has (rightActions(): a report only views, Payroll Runs have no Edit ...) --}}
                @foreach(['view','create','edit','delete','approve','confirm'] as $action)
                    <td class="text-center">
                        @if(in_array($action, $right['actions'] ?? [], true))
                            {{-- a section can name the box for what it does there (Period Closing: Approve = Close, Confirm = Reopen) --}}
                            @php($actionLabel = isset($right['action_labels'][$action]) ? __($right['action_labels'][$action]) : null)
                            <input type="checkbox" class="form-check-input right-checkbox"
                                   data-action="{{ $action }}"
                                   aria-label="{{ __($right['label']) }}: {{ $actionLabel ?? __(ucfirst($action)) }}"
                                   @if($actionLabel) title="{{ $actionLabel }}" @endif
                                   name="rights[{{ $moduleKey }}][{{ $action }}]" value="1"
                                   @checked($right[$action])>
                            @if($actionLabel)<div class="small text-secondary lh-1 mt-1">{{ $actionLabel }}</div>@endif
                        @else
                            <span class="text-body-tertiary" title="{{ __('Not used in this section') }}">–</span>
                        @endif
                    </td>
                @endforeach
                <td class="text-center">
                    <input type="checkbox" class="form-check-input row-select-all">
                </td>
            </tr>
        @endforeach
        <tr id="rights-no-match" class="d-none">
            <td colspan="8" class="text-center text-secondary py-3">{{ __('No module matches your search.') }}</td>
        </tr>
        </tbody>
    </table>
</div>

<script>
    (function () {
        const syncAll = function (row) {
            const total = row.find('.right-checkbox').length;
            const checkedCount = row.find('.right-checkbox:checked').length;
            row.find('.row-select-all').prop('checked', total === checkedCount);
        };
        // a module row's box sets the same box on each of its sub menus
        const cascade = function (row, action, checked) {
            const key = row.data('module-row');
            if (row.data('parent')) return;
            $('tr[data-parent="' + key + '"]').each(function () {
                const child = $(this);
                child.find(action ? '.right-checkbox[data-action="' + action + '"]' : '.right-checkbox').prop('checked', checked);
                syncAll(child);
            });
        };

        $('.row-select-all').off().on('change', function () {
            const checked = $(this).is(':checked');
            const row = $(this).closest('tr');
            row.find('.right-checkbox').prop('checked', checked);
            cascade(row, null, checked);
        });

        // Keep each row's "All" checkbox in sync when individual boxes change.
        $('.right-checkbox').off().on('change', function (e, initial) {
            const row = $(this).closest('tr');
            syncAll(row);
            if (!initial) cascade(row, $(this).data('action'), $(this).is(':checked'));
        });

        $('.right-checkbox').trigger('change', [true]);

        // search: a module that matches shows with all its sub menus; a sub menu that matches shows with its module
        const filterRights = function () {
            const term = ($('#rights-search').val() || '').trim().toLowerCase();
            const rows = $('tr[data-module-row]');
            $('#rights-table').toggleClass('rights-searching', term !== '');
            if (!term) {
                rows.removeClass('d-none');
                $('#rights-no-match').addClass('d-none');
                return;
            }
            const show = {};
            rows.each(function () {
                const row = $(this);
                if (String(row.data('search')).indexOf(term) === -1) return;
                const key = row.data('module-row'), parent = row.data('parent');
                show[key] = true;
                if (parent) show[parent] = true;
                else $('tr[data-parent="' + key + '"]').each(function () { show[$(this).data('module-row')] = true; });
            });
            rows.each(function () { $(this).toggleClass('d-none', !show[$(this).data('module-row')]); });
            $('#rights-no-match').toggleClass('d-none', Object.keys(show).length > 0);
        };
        $('#rights-search').off().on('input', filterRights).on('keydown', function (e) {
            if (e.key === 'Enter') e.preventDefault(); // don't submit the form
            if (e.key === 'Escape' && this.value) { e.preventDefault(); e.stopPropagation(); this.value = ''; filterRights(); }
        });
        // opening the tab puts the cursor in the search box (keyboard-first)
        $('button[data-bs-target="#tab-rights"]').off('shown.bs.tab.rights').on('shown.bs.tab.rights', function () { $('#rights-search').trigger('focus'); });
    })();
</script>
