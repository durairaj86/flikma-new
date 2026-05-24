QUOTATION = {
    title: 'Quotation',
    baseUrl: 'sales/quotation',
    actionUrl: 'sales/quotation',
    load() {
        QUOTATION.form.load();
        QUOTATION.filter.load();
        datepicker();
        QUOTATION.columnSettings.bindBtn();
    },
    filter: {
        load: function () {
            QUOTATION.filter.filterBox();
            QUOTATION.filter.shipmentMode();
            QUOTATION.filter.polPodLoad();
        },
        filterBox: function () {
            $('#apply-filter').off().on({
                click: function () {
                    QUOTATION.list.dataTable();
                    FILTER.filteredColumn();
                }
            });
        },
        default: function (status = 0) {
            let data = {}, tab = status ?? $("#listTabs").find('li button.active').attr('id');
            let params = new URLSearchParams($('#list-filter').serialize());

            params.forEach((value, key) => {
                if (data[key]) {
                    data[key] = [].concat(data[key], value);
                } else {
                    data[key] = value;
                }
            });
            data['tab'] = tab;
            data['limit'] = 25;
            data['customSearch'] = $('#customSearch').val();
            return data;
        },
        shipmentMode() {
            $('input[name=shipment_mode],input[name=shipment_mode_2]').off().on('change', function () {
                let shipmentSelect = $('.pol-pod-select');
                if ($(this).hasClass('sync-sea')) {
                    shipmentSelect.find('.sync-sea').prop('checked', true);
                    shipmentSelect.find('.sync-air').prop('checked', false);
                } else if ($(this).hasClass('sync-air')) {
                    shipmentSelect.find('.sync-sea').prop('checked', false);
                    shipmentSelect.find('.sync-air').prop('checked', true);
                }

                let filterPol = document.querySelector('#filter-pol');
                let filterPod = document.querySelector('#filter-pod');

                filterPol.tomselect.destroy();
                filterPod.tomselect.destroy();

                JOB.filter.polPodLoad();
            })
        },
        polPodLoad(preLoad = null) {
            initTomSelectSearch('#filter-pol', 'sea', 100, preLoad);
            initTomSelectSearch('#filter-pod', 'sea', 100, preLoad);
        }
    },
    printPreview(printId) {
        const iframe = document.getElementById('print-frame');

        iframe.onload = function () {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                const doc = iframe.contentDocument || iframe.contentWindow.document;
                iframe.style.height = doc.body.scrollHeight + 'px';
            } catch (e) {
                console.error('Cannot print iframe content. Cross-origin issue?', e);
            }
        };
        iframe.src = '/' + QUOTATION.baseUrl + '/' + printId + '/print';
    },
    downloadPDF(printId) {
        fetch('/' + QUOTATION.baseUrl + '/' + printId + '/print')
            .then(res => res.text())
            .then(html => {
                const container = document.createElement('div');
                container.id = 'html-pdf';
                container.className = 'px-4';
                container.innerHTML = html;
                const opt = {
                    margin: 0.2,
                    filename: `quotation-${printId}.pdf`,
                };
                html2pdf().set(opt).from(container).save();
            });
    },

    // ─── Column Settings ───────────────────────────────────────────────────────
    columnSettings: {
        page: 'quotation',
        _cache: null,      // {fields, columns, is_custom}
        _state: [],        // columns being edited in modal
        _fields: [],       // all available fields
        _dragAbort: null,  // AbortController — removes stale drag listeners on each re-render

        bindBtn() {
            $('#columnSettingsBtn').off().on('click', () => QUOTATION.columnSettings.openModal());
            $('#csSaveBtn').off().on('click', () => QUOTATION.columnSettings.save());
            $('#csResetBtn').off().on('click', () => QUOTATION.columnSettings.reset());
        },

        fetch(callback) {
            if (this._cache) {
                callback(this._cache);
                return;
            }
            $.get('/column-settings/' + this.page)
                .done((res) => {
                    this._cache = res;
                    callback(res);
                })
                .fail(() => {
                    // Fall back to empty config so DataTable still initialises
                    console.warn('Column settings fetch failed – using defaults.');
                    callback({fields: [], columns: [], is_custom: false});
                });
        },

        openModal() {
            this.fetch((res) => {
                // Deep copy so edits don't mutate the cache until save
                this._state = JSON.parse(JSON.stringify(res.columns));
                this._fields = res.fields;
                this.renderFieldList();
                this.renderColumnOrder();
                this.renderPreview();
                new bootstrap.Modal(document.getElementById('columnSettingsModal')).show();
            });
        },

        // ── helpers ────────────────────────────────────────────────────────────
        _selectedKeys() {
            const keys = new Set();
            this._state.forEach(col => {
                keys.add(col.key);
                (col.children || []).forEach(c => keys.add(c.key));
            });
            return keys;
        },

        _fieldMeta(key) {
            return this._fields.find(f => f.key === key) || {};
        },

        // ── Left panel ─────────────────────────────────────────────────────────
        renderFieldList() {
            const selected = this._selectedKeys();
            const groups = {};

            this._fields.forEach(f => {
                if (!groups[f.category]) groups[f.category] = [];
                groups[f.category].push(f);
            });

            let html = '';
            Object.entries(groups).forEach(([cat, fields]) => {
                html += `<div class="mb-3 cs-field-group">
                    <div class="text-muted fw-semibold small px-2 mb-1 text-uppercase cs-field-group-label" style="font-size:0.7rem;">${cat}</div>`;
                fields.forEach(f => {
                    const isParent = this._state.some(c => c.key === f.key);
                    const isChild = !isParent && selected.has(f.key);
                    const isFixed = f.fixed;

                    // Badges go OUTSIDE the label so search text stays clean
                    let badgeHtml = '';
                    if (isFixed) badgeHtml += `<span class="badge bg-secondary" style="font-size:0.6rem;">Fixed</span>`;
                    if (isParent) badgeHtml += `<span class="badge bg-primary" style="font-size:0.6rem;">Column</span>`;
                    if (isChild) badgeHtml += `<span class="badge bg-info text-dark" style="font-size:0.6rem;">Sub</span>`;

                    const checked = selected.has(f.key) ? 'checked' : '';
                    const disabled = isFixed ? 'disabled' : '';

                    html += `<div class="d-flex align-items-center gap-2 px-2 py-1 cs-field-item rounded hover-bg"
                                  style="cursor:pointer;" data-key="${f.key}" data-label="${f.label.toLowerCase()}">
                        <input type="checkbox" class="form-check-input cs-field-check flex-shrink-0 mt-0"
                               id="csf_${f.key}" data-key="${f.key}" ${checked} ${disabled}>
                        <label class="mb-0 small" for="csf_${f.key}" style="cursor:pointer;flex:1;">
                            ${f.label}
                        </label>
                        <div class="flex-shrink-0 d-flex gap-1">${badgeHtml}</div>
                    </div>`;
                });
                html += '</div>';
            });

            const $list = $('#csFieldList').html(html);

            // Helper: filter items using data-label attribute (avoids reading badge text from DOM)
            const applySearch = () => {
                const q = $('#csFieldSearch').val().trim().toLowerCase();
                $list.find('.cs-field-item').each(function () {
                    const matches = q === '' || ($(this).attr('data-label') || '').includes(q);
                    $(this).toggle(matches).attr('data-matches', matches ? '1' : '0');
                });
                $list.find('.cs-field-group').each(function () {
                    // Show group if query is empty OR if it has at least one matching child
                    $(this).toggle(q === '' || $(this).find('.cs-field-item[data-matches="1"]').length > 0);
                });
            };

            // Re-apply current query after every re-render (checkbox toggle calls renderFieldList again).
            // Only run if there's already something typed — avoids the :visible false-negative
            // that occurs when the modal isn't yet in the DOM / visible.
            if ($('#csFieldSearch').val().trim() !== '') {
                applySearch();
            }

            // Bind input event (namespaced so checkbox re-renders don't stack listeners)
            $('#csFieldSearch').off('input.cs').on('input.cs', applySearch);

            // Toggle click
            $list.find('.cs-field-check').off('change').on('change', (e) => {
                const key = $(e.currentTarget).data('key');
                if ($(e.currentTarget).is(':checked')) {
                    this._addAsParent(key);
                } else {
                    this._removeField(key);
                }
                this.renderFieldList();
                this.renderColumnOrder();
                this.renderPreview();
            });
        },

        _addAsParent(key) {
            const meta = this._fieldMeta(key);
            // Remove from any existing children first
            this._state.forEach(col => {
                col.children = (col.children || []).filter(c => c.key !== key);
            });
            // Add as parent if not already
            if (!this._state.some(c => c.key === key)) {
                this._state.push({key, label: meta.label || key, type: 'parent', children: []});
            }
        },

        _removeField(key) {
            // Remove as parent
            this._state = this._state.filter(c => c.key !== key);
            // Remove as child from all parents
            this._state.forEach(col => {
                col.children = (col.children || []).filter(c => c.key !== key);
            });
        },

        // ── Right panel ────────────────────────────────────────────────────────
        renderColumnOrder() {
            const selected = this._selectedKeys();
            let html = '';

            this._state.forEach((col, idx) => {
                const meta = this._fieldMeta(col.key);
                const isFixed = meta.fixed;

                // Start as draggable="false"; _initDrag enables it only on grip-handle mousedown
                html += `<div class="cs-col-item border rounded mb-2 bg-white shadow-sm"
                              draggable="false"
                              data-idx="${idx}"
                              data-fixed="${isFixed ? '1' : '0'}"
                              style="user-select:none;">
                    <div class="d-flex align-items-center gap-2 px-3 py-2">
                        <i class="bi bi-grip-vertical text-muted cs-drag-handle" style="cursor:${isFixed ? 'default' : 'grab'};"></i>
                        <input type="text" class="form-control form-control-sm border-0 p-0 fw-semibold cs-col-label"
                               data-idx="${idx}" value="${col.label}" style="outline:none;background:transparent;min-width:60px;">
                        <span class="badge bg-light text-muted border ms-auto" style="font-size:0.65rem;">${meta.category || ''}</span>
                        ${isFixed ? '' : `<button type="button" class="btn btn-sm p-0 text-muted cs-remove-col" data-idx="${idx}" title="Remove">
                            <i class="bi bi-x-lg" style="font-size:0.75rem;"></i>
                        </button>`}
                    </div>`;

                // Children
                const children = col.children || [];
                if (children.length) {
                    html += `<div class="border-top mx-3 mb-2 pt-2 ps-2">
                        <div class="text-muted" style="font-size:0.7rem;font-weight:600;text-transform:uppercase;margin-bottom:4px;">Sub-columns</div>`;
                    children.forEach((child, cIdx) => {
                        html += `<div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-arrow-return-right text-muted" style="font-size:0.7rem;"></i>
                            <input type="text" class="form-control form-control-sm border-0 p-0 cs-child-label"
                                   data-idx="${idx}" data-cidx="${cIdx}" value="${child.label}"
                                   style="outline:none;background:transparent;font-size:0.8rem;min-width:60px;">
                            <button type="button" class="btn btn-sm p-0 text-muted ms-auto cs-remove-child"
                                    data-idx="${idx}" data-cidx="${cIdx}" title="Remove sub-column">
                                <i class="bi bi-x" style="font-size:0.75rem;"></i>
                            </button>
                        </div>`;
                    });
                    html += '</div>';
                }

                // Available children to add
                const usedKeys = selected;
                const available = this._fields.filter(f => !usedKeys.has(f.key));
                if (available.length) {
                    html += `<div class="border-top mx-3 mb-2 pt-2">
                        <select class="form-select form-select-sm cs-add-child" data-idx="${idx}"
                                style="font-size:0.78rem;">
                            <option value="">+ Add sub-column…</option>
                            ${available.map(f => `<option value="${f.key}" data-label="${f.label}">${f.label}</option>`).join('')}
                        </select>
                    </div>`;
                }

                html += '</div>';
            });

            const $list = $('#csColumnList').html(html || '<p class="text-muted small text-center pt-4">No columns selected. Click fields on the left to add them.</p>');

            // Label rename
            $list.find('.cs-col-label').off('change').on('change', (e) => {
                const idx = +$(e.currentTarget).data('idx');
                this._state[idx].label = $(e.currentTarget).val();
                this.renderPreview();
            });
            $list.find('.cs-child-label').off('change').on('change', (e) => {
                const idx = +$(e.currentTarget).data('idx');
                const cIdx = +$(e.currentTarget).data('cidx');
                this._state[idx].children[cIdx].label = $(e.currentTarget).val();
                this.renderPreview();
            });

            // Remove parent column
            $list.find('.cs-remove-col').off('click').on('click', (e) => {
                const idx = +$(e.currentTarget).data('idx');
                this._state.splice(idx, 1);
                this.renderFieldList();
                this.renderColumnOrder();
                this.renderPreview();
            });

            // Remove child
            $list.find('.cs-remove-child').off('click').on('click', (e) => {
                const idx = +$(e.currentTarget).data('idx');
                const cIdx = +$(e.currentTarget).data('cidx');
                this._state[idx].children.splice(cIdx, 1);
                this.renderFieldList();
                this.renderColumnOrder();
                this.renderPreview();
            });

            // Add child from select
            $list.find('.cs-add-child').off('change').on('change', (e) => {
                const idx = +$(e.currentTarget).data('idx');
                const key = $(e.currentTarget).val();
                if (!key) return;
                const label = $(e.currentTarget).find('option:selected').data('label') || key;
                this._state[idx].children.push({key, label});
                this.renderFieldList();
                this.renderColumnOrder();
                this.renderPreview();
            });

            // Drag-to-reorder
            this._initDrag($list[0]);
        },

        _initDrag(container) {
            // Abort any listeners from a previous render so we never stack duplicates.
            if (this._dragAbort) this._dragAbort.abort();
            this._dragAbort = new AbortController();
            const signal = this._dragAbort.signal;

            let dragSrcIdx = null;

            // Enable draggable ONLY while the grip handle is held down,
            // so inputs/selects/buttons inside the card don't start a text-drag.
            container.addEventListener('mousedown', (e) => {
                const item = e.target.closest('.cs-col-item');
                if (!item) return;
                const onGrip = !!e.target.closest('.cs-drag-handle');
                const isFixed = item.dataset.fixed === '1';
                item.setAttribute('draggable', (onGrip && !isFixed) ? 'true' : 'false');
            }, {signal});

            container.addEventListener('dragstart', (e) => {
                const item = e.target.closest('.cs-col-item[draggable="true"]');
                if (!item) {
                    e.preventDefault();
                    return;
                }
                dragSrcIdx = +item.dataset.idx;
                item.style.opacity = '0.45';
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', String(dragSrcIdx)); // required by Firefox
            }, {signal});

            container.addEventListener('dragend', () => {
                container.querySelectorAll('.cs-col-item').forEach(el => {
                    el.style.opacity = '';
                    el.classList.remove('cs-drag-over');
                    el.setAttribute('draggable', 'false');
                });
                dragSrcIdx = null;
            }, {signal});

            container.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                const item = e.target.closest('.cs-col-item');
                container.querySelectorAll('.cs-col-item').forEach(el => el.classList.remove('cs-drag-over'));
                // Don't highlight fixed columns — they are not valid drop targets
                if (item && item.dataset.fixed !== '1') item.classList.add('cs-drag-over');
            }, {signal});

            container.addEventListener('dragleave', (e) => {
                if (!container.contains(e.relatedTarget)) {
                    container.querySelectorAll('.cs-col-item').forEach(el => el.classList.remove('cs-drag-over'));
                }
            }, {signal});

            container.addEventListener('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                container.querySelectorAll('.cs-col-item').forEach(el => {
                    el.classList.remove('cs-drag-over');
                    el.setAttribute('draggable', 'false');
                });

                const item = e.target.closest('.cs-col-item');
                if (!item || dragSrcIdx === null) return;

                const targetIdx = +item.dataset.idx;
                if (dragSrcIdx === targetIdx) return;

                // Never allow dropping onto a fixed column — it would displace it
                if (item.dataset.fixed === '1') return;

                const moved = this._state.splice(dragSrcIdx, 1)[0];
                this._state.splice(targetIdx, 0, moved);
                dragSrcIdx = null;
                this.renderColumnOrder();
                this.renderPreview();
            }, {signal});
        },

        // ── Preview ────────────────────────────────────────────────────────────
        renderPreview() {
            let headHtml = '', dataHtml = '';
            this._state.forEach(col => {
                const children = col.children || [];
                let childHead = children.map(c => `<small class="d-block text-muted lh-sm">${c.label}</small>`).join('');
                let childData = children.map(() => `<small class="d-block text-muted">—</small>`).join('');
                headHtml += `<th class="text-nowrap small py-1 px-2">${col.label}${childHead}</th>`;
                dataHtml += `<td class="small py-1 px-2">—${childData}</td>`;
            });
            // Actions column
            headHtml += '<th class="small py-1 px-2" style="width:50px;"></th>';
            dataHtml += '<td class="small py-1 px-2"><i class="bi bi-three-dots-vertical text-muted"></i></td>';

            $('#csPreviewRow').html(headHtml);
            $('#csPreviewDataRow').html(dataHtml);
        },

        // ── Save / Reset ───────────────────────────────────────────────────────
        save() {
            const token = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/column-settings/' + this.page,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({_token: token, columns: this._state}),
                success: (res) => {
                    this._cache = null; // bust cache so next fetch is fresh
                    toastr.success(res.message || 'Saved.');
                    bootstrap.Modal.getInstance(document.getElementById('columnSettingsModal')).hide();
                    QUOTATION.list.dataTable();
                },
                error: () => toastr.error('Could not save column settings.'),
            });
        },

        reset() {
            if (!confirm('Reset columns to default settings?')) return;
            const token = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/column-settings/' + this.page,
                method: 'DELETE',
                data: {_token: token},
                success: (res) => {
                    this._cache = null;
                    this._state = JSON.parse(JSON.stringify(res.columns));
                    this.renderFieldList();
                    this.renderColumnOrder();
                    this.renderPreview();
                    toastr.success(res.message || 'Reset to defaults.');
                },
                error: () => toastr.error('Could not reset column settings.'),
            });
        },
    },

    // ─── List ──────────────────────────────────────────────────────────────────
    list: {
        load(activeTab) {
            QUOTATION.list.dataTable(activeTab);
        },

        // Special cell renderers keyed by field key
        renderers: {
            row_no(data) {
                return `<span class="fw-semibold">${data ?? ''}</span>`;
            },
            status(data) {
                const map = {
                    'pending': ['Pending', 'warning'],
                    'accepted': ['Accepted', 'success'],
                    'converted': ['Converted', 'info'],
                    'cancelled': ['Cancelled', 'danger'],
                    'expired': ['Expired', 'secondary'],
                    'draft': ['Draft', 'secondary'],
                    'sent': ['Sent', 'primary'],
                    'rejected': ['Rejected', 'danger'],
                    'confirmed': ['Confirmed', 'success'],
                };
                const key = (data ?? '').toString().toLowerCase();
                const [label, color] = map[key] ?? ['—', 'secondary'];
                return `<span class="badge bg-${color} text-capitalize">${label}</span>`;
            },
            services(data) {
                if (!data) return '';
                const items = Array.isArray(data) ? data : data.toString().split(',');
                return items.filter(Boolean).map(s =>
                    `<span class="badge bg-light text-dark border me-1">${s.trim()}</span>`
                ).join('');
            },
        },

        /** Build a DataTable column definition from a column_json entry. */
        _buildDtColumn(colDef, fields) {
            const children = colDef.children || [];
            const renderer = QUOTATION.list.renderers[colDef.key] || null;

            if (!children.length) {
                // Simple column
                return {
                    data: colDef.key,
                    defaultContent: '',
                    render: renderer ? (data) => renderer(data) : undefined,
                };
            }

            // Parent with children stacked in one cell
            return {
                data: colDef.key,
                defaultContent: '',
                render(data, type, row) {
                    const parentVal = renderer ? renderer(data) : (data ?? '');
                    const childHtml = children.map(child => {
                        const r = QUOTATION.list.renderers[child.key] || null;
                        const val = row[child.key] ?? '';
                        return `<small class="d-block text-muted lh-sm">${r ? r(val) : val}</small>`;
                    }).join('');
                    return parentVal + childHtml;
                },
            };
        },

        /** Build the dynamic <thead><tr> HTML from column_json. */
        _buildThead(columns, fields) {
            const fieldMap = {};
            fields.forEach(f => {
                fieldMap[f.key] = f;
            });

            let html = '';
            columns.forEach(col => {
                const meta = fieldMap[col.key] || {};
                const minWidth = meta.min_width ? `min-width:${meta.min_width}px;` : '';
                const children = col.children || [];
                const childHtml = children.map(c =>
                    `<small class="d-block text-muted lh-sm" style="font-weight:400;">${c.label}</small>`
                ).join('');
                html += `<th style="${minWidth}">${col.label}${childHtml}</th>`;
            });
            html += '<th style="min-width:50px;"></th>';
            return html;
        },

        dataTable(activeTab = null) {
            activeTab = (activeTab && (typeof activeTab !== 'object'))
                ? activeTab
                : $("#listTabs").find('li button.active').attr('id');

            // Fetch column settings first, then init DataTable.
            // destroyDataTable() is intentionally INSIDE the callback so that
            // rapid double-calls (from startup.js timeout + window.load) don't
            // race: each callback destroys whatever is there right before it
            // creates the new table, avoiding a DataTables double-init error.
            QUOTATION.columnSettings.fetch((settings) => {
                $('#dtFooter').empty();   // clear relocated pagination before destroy
                GLOBAL_FN.destroyDataTable();

                const columns = settings.columns;
                const fields = settings.fields;
                const fieldMap = {};
                fields.forEach(f => {
                    fieldMap[f.key] = f;
                });

                // Rebuild thead
                $('#dataTable thead tr').html(QUOTATION.list._buildThead(columns, fields));

                // Build DataTable columns array
                const dtColumns = columns.map(col => QUOTATION.list._buildDtColumn(col, fields));

                // Orderable / non-orderable column indices
                const noSort = dtColumns.map((_, i) => {
                    const key = columns[i].key;
                    const meta = fieldMap[key] || {};
                    return meta.orderable ? null : i;
                }).filter(i => i !== null);
                // Always disable last column (actions)
                noSort.push(dtColumns.length);

                // Default sort on first orderable column (or 0)
                const firstOrderable = dtColumns.findIndex((_, i) => {
                    const key = columns[i].key;
                    const meta = fieldMap[key] || {};
                    return !!meta.orderable;
                });
                const defaultOrder = firstOrderable >= 0 ? [[firstOrderable, 'desc']] : [[0, 'desc']];

                const actionBtn = GLOBAL_FN.dataTable.optionButton();
                actionBtn.className = (actionBtn.className ? actionBtn.className + ' ' : '') + 'text-center';

                let table = $('#dataTable').DataTable({
                    processing: false,
                    serverSide: true,
                    autoWidth: false,
                    lengthChange: false,
                    pageLength: 25,
                    dom: 'rtip',
                    order: defaultOrder,
                    ajax: {
                        url: GLOBAL_FN.buildUrl('sales/quotation/data'),
                        type: 'POST',
                        data(d) {
                            d.tab = activeTab;
                            d.filterData = QUOTATION.filter.default();
                        },
                        dataSrc(json) {
                            $('#dataTable tbody').find('.loading-row').remove();
                            GLOBAL_FN.setStatusCounts(json.statusCounts);
                            return json.data;
                        }
                    },
                    columnDefs: [
                        {targets: noSort, orderable: false, searchable: false},
                    ],
                    columns: [...dtColumns, actionBtn],
                    language: {
                        search: '',
                        emptyTable: ' ',
                        zeroRecords: ' ',
                    },
                    deferLoading: 0,

                    drawCallback() {
                        const info = this.api().page.info();
                        const noData = info.recordsTotal === 0;
                        const noResults = !noData && info.recordsDisplay === 0;
                        const hasRows = info.recordsDisplay > 0;

                        $('#tableWrapper').toggleClass('d-none', !hasRows);
                        $('#dtFooter').toggleClass('d-none', !hasRows);
                        $('#quotationEmptyState').toggleClass('d-none', hasRows);
                        $('#emptyStateNoData').toggleClass('d-none', !noData);
                        $('#emptyStateNoResults').toggleClass('d-none', !noResults);
                    },

                    initComplete() {
                        QUOTATION.form.open();
                        webDataTable.actions.menu();

                        // Move info and pagination outside #tableWrapper so they
                        // don't scroll sideways with the table content.
                        const $wrap = $('#dataTable').closest('.dataTables_wrapper');
                        $('#dtFooter').append($wrap.find('.dataTables_info, .dataTables_paginate'));
                    }
                });

                $('#customSearch').on('keyup', function () {
                    table.search(this.value).draw();
                });
                webDataTable.loader(table);
                webDataTable.search(table);
            });
        },

        extraActions(row) {
            QUOTATION.list.actions.statusChange(row);
            QUOTATION.list.actions.view(row);
            QUOTATION.list.actions.email(row);
        },
        actions: {
            statusChange(row) {
                $('#row_pending,#row_accepted,#row_rejected,#row_convert_to_job').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('sales/quotation/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: fd,
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                })
            },
            view(row) {
                $('#row_view').off().on('click', function () {
                    let customerId = row.attr('data-id');
                    let drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
                    drawer.show();
                    $('#moduleOverview').html('<p>Loading...</p>');
                    $.get('/sales/quotation/' + customerId + '/overview', function (data) {
                        $('#moduleOverview').html(data);
                    });
                });
            },
            email(row) {
                $('#row_email').off().on('click', function () {
                    let quotationId = row.attr('data-id');
                    $.get('/sales/quotation/' + quotationId + '/email-data', function (data) {
                        $('#emailTo').val(data.to);
                        $('#emailCc').val(data.cc);
                        $('#emailSubject').val('Quotation #' + data.id);
                        let drawer = new bootstrap.Offcanvas(document.getElementById('sendEmailDrawer'));
                        drawer.show();
                        $('#sendEmailForm').off('submit').on('submit', function (e) {
                            e.preventDefault();
                            let formData = new FormData(this);
                            const submitBtn = $(this).find('button[type="submit"]');
                            const originalBtnText = submitBtn.html();
                            submitBtn.html('<span class="spinner-border spinner-border-sm"></span> Sending...');
                            submitBtn.prop('disabled', true);
                            $.ajax({
                                url: '/sales/quotation/send-email',
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                success(response) {
                                    bootstrap.Offcanvas.getInstance(document.getElementById('sendEmailDrawer')).hide();
                                    toastr.success(response.message);
                                    $('#sendEmailForm')[0].reset();
                                },
                                error(xhr) {
                                    toastr.error(xhr.responseJSON?.message || 'An error occurred while sending the email.');
                                },
                                complete() {
                                    submitBtn.html(originalBtnText);
                                    submitBtn.prop('disabled', false);
                                }
                            });
                        });
                    });
                });
            }
        }
    },

    // ─── Form ──────────────────────────────────────────────────────────────────
    form: {
        load() {
            QUOTATION.form.open();
            let enquiryId = localStorage.getItem('convert-enquiry');
            if (enquiryId) {
                webModal.openGlobalModal({
                    title: 'New Quotation',
                    url: GLOBAL_FN.buildUrl('sales/quotation/create'),
                    size: 'xxl',
                    scroll: false,
                    minHeight: '700px',
                    content: {enquiryId}
                });
                localStorage.removeItem('convert-enquiry');
            }
        },
        open() {
            $('#new,#new-first').off().on('click', function () {
                let dataTableData = $('#dataTable');
                let modelSize = dataTableData.data('model-size');
                let minHeight = dataTableData.data('min-height');
                webModal.openGlobalModal({
                    title: 'New Quotation',
                    url: GLOBAL_FN.buildUrl('sales/quotation/create'),
                    content: null,
                    size: modelSize || 'lg',
                    scroll: false,
                    minHeight: minHeight,
                });
            });
        },
        openCallback() {
            QUOTATION.form.addContainer();
            QUOTATION.form.addPackage();
            QUOTATION.form.removeRow();
            QUOTATION.form.initCharges();
            QUOTATION.form.shipmentMode();
            setTimeout(function () {
                QUOTATION.form.customerProspectToggle();
                QUOTATION.form.customerAddressFetch();
            });
            GLOBAL_FN.activity.activityChange();
            QUOTATION.form.polPodLoad();
        },
        customerProspectToggle() {
            $('#customer').on('change', function () {
                const customerValue = $(this).val();
                const prospectSelect = document.querySelector('#prospect');
                if (customerValue && customerValue !== '') {
                    if (prospectSelect?.tomselect) prospectSelect.tomselect.disable();
                } else {
                    if (prospectSelect?.tomselect) prospectSelect.tomselect.enable();
                }
            });

            $('#prospect').on('change', function () {
                const prospectValue = $(this).val();
                const customerSelect = document.querySelector('#customer');
                if (prospectValue && prospectValue !== '') {
                    if (customerSelect?.tomselect) customerSelect.tomselect.disable();
                } else {
                    if (customerSelect?.tomselect) customerSelect.tomselect.enable();
                }
            });

            const customerValue = $('#customer').val();
            const prospectValue = $('#prospect').val();
            const customerSelect = document.querySelector('#customer');
            const prospectSelect = document.querySelector('#prospect');
            const isEditMode = $('#data-id').val() && $('#prospect').length > 0;
            const hasProspectId = $('#prospect').data('has-prospect') === true ||
                $('[name="prospect"]').find('option:selected').val() !== '';

            if (customerValue && customerValue !== '') {
                if (prospectSelect?.tomselect) prospectSelect.tomselect.disable();
            } else if ((prospectValue && prospectValue !== '') || (isEditMode && hasProspectId)) {
                if (customerSelect?.tomselect) customerSelect.tomselect.disable();
            }
        },
        customerAddressFetch() {
            function loadAddress(encodedId) {
                if (!encodedId || encodedId === '__new__') {
                    $('#customerAddressWrapper').hide();
                    $('#customerAddress').val('');
                    return;
                }
                $.get('/customer/' + encodedId + '/address', function (res) {
                    $('#customerAddress').val(res.address || '');
                    $('#customerAddressWrapper').toggle(!!res.address);
                });
            }

            const initial = $('#customer').val();
            if (initial) loadAddress(initial);

            $('#customer').on('change', function () {
                loadAddress($(this).val());
            });
        },
        shipmentMode(destroy = null) {
            if (destroy) {
                document.querySelector('#pol').tomselect.destroy();
                document.querySelector('#pod').tomselect.destroy();
                QUOTATION.form.polPodLoad(true);
            }
        },
        polPodLoad(preLoad = null) {
            let port = $('#activity-id-hidden').val();
            initTomSelectSearch('#pol', port, 50, preLoad);
            initTomSelectSearch('#pod', port, 50, preLoad);
            initTomSelectSearch('#carrier', port + 'Lines', 50, preLoad);
        },
        addContainer() {
            $('#addContainerRow').off().on('click', function () {
                let $list = $('#containerList');
                let $newCard = $list.find('.container-card:first').clone();
                $newCard.find('input, textarea').val('');
                $newCard.find('select').val('').removeClass('tomselected ts-hidden-accessible');
                $newCard.find('div.ts-wrapper').remove();
                initTomSelectForm($newCard);
                $list.append($newCard);
                QUOTATION.form.removeRow();
            });
        },
        addPackage() {
            $('#addPackageRow').off().on('click', function () {
                let $table = $('#packageTable tbody');
                let $newRow = $table.find('tr:first').clone();
                $newRow.find('input, select').val('');
                $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
                $newRow.find('div.ts-wrapper').remove();
                initTomSelectForm($newRow);
                $table.append($newRow);
                QUOTATION.form.removeRow();
            });
        },
        removeRow() {
            // Container cards
            $('#containerList').off('click', '.remove-row').on('click', '.remove-row', function () {
                let $list = $('#containerList');
                let $card = $(this).closest('.container-card');
                if ($list.find('.container-card').length > 1) {
                    $card.remove();
                } else {
                    $card.find('input, textarea').val('');
                    $card.find('select').each(function () {
                        if (this.tomselect) {
                            this.tomselect.clear();
                        } else {
                            $(this).val('');
                        }
                    });
                }
            });
            // Package table rows
            $('#packageTable').off('click', '.remove-row').on('click', '.remove-row', function () {
                let $tbody = $(this).closest('tbody');
                const $tr = $(this).closest('tr');
                if ($tbody.find('tr').length > 1) {
                    $tr.remove();
                } else {
                    $tr.find('input').val('');
                    $tr.find('select').each(function () {
                        $(this).val('');
                        if ($(this).hasClass('selectpicker')) {
                            $(this).selectpicker('destroy').addClass('selectpicker');
                            selectPicker('#' + $(this).closest('table').attr('id'));
                        }
                    });
                }
            });
        },

        initCharges() {
            const $tbody = $('#chargesBody');

            /* ── helpers ── */
            function calcRow($row) {
                const qty    = parseFloat($row.find('.chg-qty').val())     || 0;
                const exRate = parseFloat($row.find('.chg-ex-rate').val()) || 1;
                const amtQty = parseFloat($row.find('.chg-amt-qty').val()) || 0;

                const fcy   = amtQty ? (qty * amtQty) : 0;
                const local = fcy    ? (fcy * exRate) : 0;

                $row.find('.chg-fcy-amount').val(fcy   ? fcy.toFixed(2)   : '');
                $row.find('.chg-local-amount').val(local ? local.toFixed(2) : '');
                calcFooterTotals();
            }

            function calcFooterTotals() {
                let tf = 0, tl = 0;
                $tbody.find('.charge-row').each(function () {
                    tf += parseFloat($(this).find('.chg-fcy-amount').val())   || 0;
                    tl += parseFloat($(this).find('.chg-local-amount').val()) || 0;
                });
                $('#chgGrandFcy').text(tf.toFixed(2));
                $('#chgGrandLocal').text(tl.toFixed(2));
            }

            function renumberRows() {
                $tbody.find('.charge-row').each(function (i) {
                    $(this).find('.chg-line-no').text(i + 1);
                });
            }

            function cloneRow($src, clearValues) {
                let $newRow = $src.clone();
                if (clearValues) {
                    $newRow.find('input:not([readonly])').val('');
                    $newRow.find('select').prop('selectedIndex', 0);
                    $newRow.find('.chg-qty').val(1);
                    $newRow.find('.chg-ex-rate').val(1);
                }
                $newRow.find('.chg-fcy-amount, .chg-local-amount').val('');
                bindRowEvents($newRow);
                return $newRow;
            }

            function bindRowEvents($row) {
                /* auto-calc on change */
                $row.find('.chg-qty, .chg-ex-rate, .chg-amt-qty').off('input.chg').on('input.chg', function () {
                    calcRow($row);
                });

                /* Clone this row */
                $row.find('.chg-clone-row').off('click.chg').on('click.chg', function () {
                    let $clone = cloneRow($row, false);
                    $row.after($clone);
                    renumberRows();
                    calcFooterTotals();
                });

                /* Delete row */
                $row.find('.chg-remove-row').off('click.chg').on('click.chg', function () {
                    if ($tbody.find('.charge-row').length > 1) {
                        $row.remove();
                    } else {
                        $row.find('input:not([readonly])').val('');
                        $row.find('select').prop('selectedIndex', 0);
                        $row.find('.chg-qty').val(1);
                        $row.find('.chg-ex-rate').val(1);
                        $row.find('.chg-fcy-amount, .chg-local-amount').val('');
                    }
                    renumberRows();
                    calcFooterTotals();
                });
            }

            /* bind events to existing rows */
            $tbody.find('.charge-row').each(function () {
                bindRowEvents($(this));
            });

            /* initial totals */
            $tbody.find('.charge-row').each(function () {
                calcRow($(this));
            });

            /* Add new empty row */
            $('#addChargeRow').off('click.chg').on('click.chg', function () {
                let $first = $tbody.find('.charge-row:first');
                let $newRow = cloneRow($first, true);
                $tbody.append($newRow);
                renumberRows();
            });
        }
    },
}
