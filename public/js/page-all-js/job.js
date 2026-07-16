JOB = {
    title: 'Job',
    baseUrl: 'operation/job',
    actionUrl: 'operation/job',
    load() {
        JOB.columnSettings.bindBtn();
        JOB.form.load();
        JOB.filter.load();
        datepicker();
    },

    // ─── Column Settings ───────────────────────────────────────────────────────
    columnSettings: {
        page: 'job',
        _cache: null,
        _state: [],
        _fields: [],
        _dragAbort: null,

        // Mirrors App\Helpers\ModuleDefaultColumns::job() — used only if the
        // /column-settings/job request fails, so the list/modal never renders empty.
        _fallback: {
            fields: [
                {key: 'row_no', label: 'Job No', category: 'General', min_width: 130, fixed: true},
                {key: 'customer_name', label: 'Customer', category: 'General', min_width: 180},
                {key: 'status', label: 'Status', category: 'General', min_width: 90},
                {key: 'services', label: 'Services', category: 'General', min_width: 140},
                {key: 'activity_name', label: 'Activity', category: 'General', min_width: 150},
                {key: 'shipment_mode', label: 'Shipment Mode', category: 'General', min_width: 120},
                {key: 'posted_at', label: 'Job Date', category: 'General', min_width: 100, orderable: true},
                {key: 'created_at', label: 'Created At', category: 'General', min_width: 130, orderable: true},
                {key: 'pol', label: 'Origin (POL)', category: 'Routing', min_width: 130},
                {key: 'pod', label: 'Destination (POD)', category: 'Routing', min_width: 160},
                {key: 'carrier', label: 'Carrier', category: 'Vessel', min_width: 140},
                {key: 'etd', label: 'ETD', category: 'Vessel', min_width: 100, orderable: true},
                {key: 'eta', label: 'ETA', category: 'Vessel', min_width: 100, orderable: true},
            ],
            columns: [
                {key: 'row_no', label: 'Job No', type: 'parent', children: [{key: 'services', label: 'Services'}]},
                {key: 'customer_name', label: 'Customer', type: 'parent', children: []},
                {key: 'pol', label: 'Origin', type: 'parent', children: [{key: 'pod', label: 'Destination'}]},
                {key: 'carrier', label: 'Carrier', type: 'parent', children: [{key: 'etd', label: 'ETD'}, {key: 'eta', label: 'ETA'}]},
                {key: 'status', label: 'Status', type: 'parent', children: []},
                {key: 'posted_at', label: 'Job Date', type: 'parent', children: []},
            ],
            is_custom: false,
        },

        bindBtn() {
            // Move modal to body to avoid stacking-context/overflow clipping issues
            const $modal = $('#columnSettingsModal');
            if ($modal.length && $modal.parent()[0] !== document.body) {
                $modal.appendTo(document.body);
            }

            // Use event delegation so handlers survive DOM refresh
            $(document).off('click.cs-job-btn', '#columnSettingsBtn')
                       .on('click.cs-job-btn', '#columnSettingsBtn', () => JOB.columnSettings.openModal());
            $(document).off('click.cs-job-save', '#csSaveBtn')
                       .on('click.cs-job-save', '#csSaveBtn', () => JOB.columnSettings.save());
            $(document).off('click.cs-job-reset', '#csResetBtn')
                       .on('click.cs-job-reset', '#csResetBtn', () => JOB.columnSettings.reset());
        },

        fetch(callback) {
            if (this._cache) { callback(this._cache); return; }
            $.get('/column-settings/' + this.page)
                .done((res) => {
                    const hasColumns = res && Array.isArray(res.columns) && res.columns.length > 0;
                    this._cache = hasColumns ? res : this._fallback;
                    callback(this._cache);
                })
                .fail(() => {
                    console.warn('Column settings fetch failed – using built-in defaults.');
                    callback(this._fallback);
                });
        },

        openModal() {
            this.fetch((res) => {
                this._state  = JSON.parse(JSON.stringify(Array.isArray(res.columns) ? res.columns : []));
                this._fields = Array.isArray(res.fields) ? res.fields : [];
                this.renderFieldList();
                this.renderColumnOrder();
                this.renderPreview();
                const el = document.getElementById('columnSettingsModal');
                if (el) new bootstrap.Modal(el).show();
            });
        },

        _selectedKeys() {
            const keys = new Set();
            this._state.forEach(col => {
                keys.add(col.key);
                (col.children || []).forEach(c => keys.add(c.key));
            });
            return keys;
        },

        _fieldMeta(key) { return this._fields.find(f => f.key === key) || {}; },

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
                    const isChild  = !isParent && selected.has(f.key);
                    const isFixed  = f.fixed;
                    let badgeHtml  = '';
                    if (isFixed)  badgeHtml += `<span class="badge bg-secondary" style="font-size:0.6rem;">Fixed</span>`;
                    if (isParent) badgeHtml += `<span class="badge bg-primary"   style="font-size:0.6rem;">Column</span>`;
                    if (isChild)  badgeHtml += `<span class="badge bg-info text-dark" style="font-size:0.6rem;">Sub</span>`;
                    const checked  = selected.has(f.key) ? 'checked' : '';
                    const disabled = isFixed ? 'disabled' : '';
                    html += `<div class="d-flex align-items-center gap-2 px-2 py-1 cs-field-item rounded hover-bg"
                                  style="cursor:pointer;" data-key="${f.key}" data-label="${f.label.toLowerCase()}">
                        <input type="checkbox" class="form-check-input cs-field-check flex-shrink-0 mt-0"
                               id="csf_${f.key}" data-key="${f.key}" ${checked} ${disabled}>
                        <label class="mb-0 small" for="csf_${f.key}" style="cursor:pointer;flex:1;">${f.label}</label>
                        <div class="flex-shrink-0 d-flex gap-1">${badgeHtml}</div>
                    </div>`;
                });
                html += '</div>';
            });
            const $list = $('#csFieldList').html(html);
            const applySearch = () => {
                const q = $('#csFieldSearch').val().trim().toLowerCase();
                $list.find('.cs-field-item').each(function () {
                    const matches = q === '' || ($(this).attr('data-label') || '').includes(q);
                    $(this).toggle(matches).attr('data-matches', matches ? '1' : '0');
                });
                $list.find('.cs-field-group').each(function () {
                    $(this).toggle(q === '' || $(this).find('.cs-field-item[data-matches="1"]').length > 0);
                });
            };
            if ($('#csFieldSearch').val().trim() !== '') applySearch();
            $('#csFieldSearch').off('input.cs').on('input.cs', applySearch);
            $list.find('.cs-field-check').off('change').on('change', (e) => {
                const key = $(e.currentTarget).data('key');
                if ($(e.currentTarget).is(':checked')) this._addAsParent(key);
                else this._removeField(key);
                this.renderFieldList();
                this.renderColumnOrder();
                this.renderPreview();
            });
        },

        _addAsParent(key) {
            const meta = this._fieldMeta(key);
            this._state.forEach(col => { col.children = (col.children || []).filter(c => c.key !== key); });
            if (!this._state.some(c => c.key === key))
                this._state.push({key, label: meta.label || key, type: 'parent', children: []});
        },

        _removeField(key) {
            this._state = this._state.filter(c => c.key !== key);
            this._state.forEach(col => { col.children = (col.children || []).filter(c => c.key !== key); });
        },

        renderColumnOrder() {
            const selected = this._selectedKeys();
            let html = '';
            this._state.forEach((col, idx) => {
                const meta    = this._fieldMeta(col.key);
                const isFixed = meta.fixed;
                html += `<div class="cs-col-item border rounded mb-2 bg-white shadow-sm" draggable="false"
                              data-idx="${idx}" data-fixed="${isFixed ? '1' : '0'}" style="user-select:none;">
                    <div class="d-flex align-items-center gap-2 px-3 py-2">
                        <i class="bi bi-grip-vertical text-muted cs-drag-handle" style="cursor:${isFixed ? 'default' : 'grab'};"></i>
                        <input type="text" class="form-control form-control-sm border-0 p-0 fw-semibold cs-col-label"
                               data-idx="${idx}" value="${col.label}" style="outline:none;background:transparent;min-width:60px;">
                        <span class="badge bg-light text-muted border ms-auto" style="font-size:0.65rem;">${meta.category || ''}</span>
                        ${isFixed ? '' : `<button type="button" class="btn btn-sm p-0 text-muted cs-remove-col" data-idx="${idx}" title="Remove">
                            <i class="bi bi-x-lg" style="font-size:0.75rem;"></i></button>`}
                    </div>`;
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
                                <i class="bi bi-x" style="font-size:0.75rem;"></i></button>
                        </div>`;
                    });
                    html += '</div>';
                }
                const available = this._fields.filter(f => !selected.has(f.key));
                if (available.length) {
                    html += `<div class="border-top mx-3 mb-2 pt-2">
                        <select class="form-select form-select-sm cs-add-child" data-idx="${idx}" style="font-size:0.78rem;">
                            <option value="">+ Add sub-column…</option>
                            ${available.map(f => `<option value="${f.key}" data-label="${f.label}">${f.label}</option>`).join('')}
                        </select></div>`;
                }
                html += '</div>';
            });
            const $list = $('#csColumnList').html(html || '<p class="text-muted small text-center pt-4">No columns selected. Click fields on the left to add them.</p>');
            $list.find('.cs-col-label').off('change').on('change', (e) => {
                this._state[+$(e.currentTarget).data('idx')].label = $(e.currentTarget).val();
                this.renderPreview();
            });
            $list.find('.cs-child-label').off('change').on('change', (e) => {
                this._state[+$(e.currentTarget).data('idx')].children[+$(e.currentTarget).data('cidx')].label = $(e.currentTarget).val();
                this.renderPreview();
            });
            $list.find('.cs-remove-col').off('click').on('click', (e) => {
                this._state.splice(+$(e.currentTarget).data('idx'), 1);
                this.renderFieldList(); this.renderColumnOrder(); this.renderPreview();
            });
            $list.find('.cs-remove-child').off('click').on('click', (e) => {
                this._state[+$(e.currentTarget).data('idx')].children.splice(+$(e.currentTarget).data('cidx'), 1);
                this.renderFieldList(); this.renderColumnOrder(); this.renderPreview();
            });
            $list.find('.cs-add-child').off('change').on('change', (e) => {
                const idx   = +$(e.currentTarget).data('idx');
                const key   = $(e.currentTarget).val();
                if (!key) return;
                const label = $(e.currentTarget).find('option:selected').data('label') || key;
                this._state[idx].children.push({key, label});
                this.renderFieldList(); this.renderColumnOrder(); this.renderPreview();
            });
            this._initDrag($list[0]);
        },

        _initDrag(container) {
            if (this._dragAbort) this._dragAbort.abort();
            this._dragAbort = new AbortController();
            const signal = this._dragAbort.signal;
            let dragSrcIdx = null;
            container.addEventListener('mousedown', (e) => {
                const item = e.target.closest('.cs-col-item');
                if (!item) return;
                item.setAttribute('draggable', (!!e.target.closest('.cs-drag-handle') && item.dataset.fixed !== '1') ? 'true' : 'false');
            }, {signal});
            container.addEventListener('dragstart', (e) => {
                const item = e.target.closest('.cs-col-item[draggable="true"]');
                if (!item) { e.preventDefault(); return; }
                dragSrcIdx = +item.dataset.idx;
                item.style.opacity = '0.45';
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', String(dragSrcIdx));
            }, {signal});
            container.addEventListener('dragend', () => {
                container.querySelectorAll('.cs-col-item').forEach(el => {
                    el.style.opacity = ''; el.classList.remove('cs-drag-over'); el.setAttribute('draggable', 'false');
                });
                dragSrcIdx = null;
            }, {signal});
            container.addEventListener('dragover', (e) => {
                e.preventDefault(); e.dataTransfer.dropEffect = 'move';
                const item = e.target.closest('.cs-col-item');
                container.querySelectorAll('.cs-col-item').forEach(el => el.classList.remove('cs-drag-over'));
                if (item && item.dataset.fixed !== '1') item.classList.add('cs-drag-over');
            }, {signal});
            container.addEventListener('dragleave', (e) => {
                if (!container.contains(e.relatedTarget))
                    container.querySelectorAll('.cs-col-item').forEach(el => el.classList.remove('cs-drag-over'));
            }, {signal});
            container.addEventListener('drop', (e) => {
                e.preventDefault(); e.stopPropagation();
                container.querySelectorAll('.cs-col-item').forEach(el => {
                    el.classList.remove('cs-drag-over'); el.setAttribute('draggable', 'false');
                });
                const item = e.target.closest('.cs-col-item');
                if (!item || dragSrcIdx === null) return;
                const targetIdx = +item.dataset.idx;
                if (dragSrcIdx === targetIdx || item.dataset.fixed === '1') return;
                const moved = this._state.splice(dragSrcIdx, 1)[0];
                this._state.splice(targetIdx, 0, moved);
                dragSrcIdx = null;
                this.renderColumnOrder(); this.renderPreview();
            }, {signal});
        },

        renderPreview() {
            let headHtml = '', dataHtml = '';
            this._state.forEach(col => {
                const children = col.children || [];
                const childHead = children.map(c => `<small class="d-block text-muted lh-sm">${c.label}</small>`).join('');
                const childData = children.map(() => `<small class="d-block text-muted">—</small>`).join('');
                headHtml += `<th class="text-nowrap small py-1 px-2">${col.label}${childHead}</th>`;
                dataHtml += `<td class="small py-1 px-2">—${childData}</td>`;
            });
            headHtml += '<th class="small py-1 px-2" style="width:50px;"></th>';
            dataHtml += '<td class="small py-1 px-2"><i class="bi bi-three-dots-vertical text-muted"></i></td>';
            $('#csPreviewRow').html(headHtml);
            $('#csPreviewDataRow').html(dataHtml);
        },

        save() {
            const token = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                url: '/column-settings/' + this.page,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({_token: token, columns: this._state}),
                success: (res) => {
                    this._cache = null;
                    toastr.success(res.message || 'Saved.');
                    bootstrap.Modal.getInstance(document.getElementById('columnSettingsModal')).hide();
                    JOB.list.dataTable();
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
                    this.renderFieldList(); this.renderColumnOrder(); this.renderPreview();
                    toastr.success(res.message || 'Reset to defaults.');
                },
                error: () => toastr.error('Could not reset column settings.'),
            });
        },
    },
    filter: {
        load: function () {
            JOB.filter.filterBox();
            JOB.filter.shipmentMode();
            JOB.filter.polPodLoad();
        },
        filterBox: function () {
            $('#apply-filter').off().on({
                click: function () {
                    JOB.list.dataTable();
                    /*setFilterCount();*/
                    FILTER.filteredColumn();
                }
            });
        },
        default: function (status = 0) {
            let data = {}, tab = status ?? $("#listTabs").find('li button.active').attr('id');
            //let filterData = $('#list-filter').formSerialize() + '&' + $('#individual-filter').formSerialize() + '&tab=' + tab + "&limit=25&dummy=" + $('#dataTable_length').val();
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
            //JOB.list.dataTable(tab, data);
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
            let port = $('input[name=shipment_mode]:checked').val();
            initTomSelectSearch('#filter-pol', port, 100, preLoad);
            initTomSelectSearch('#filter-pod', port, 100, preLoad);
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
        iframe.src = '/' + JOB.baseUrl + '/' + printId + '/print';
    },
    downloadPDF(printId) {
        fetch('/' + JOB.baseUrl + '/' + printId + '/print')
            .then(res => res.text())
            .then(html => {
                const container = document.createElement('div');
                //container.style.display = 'none';
                container.id = 'html-pdf';
                container.className = 'px-4';
                container.innerHTML = html;
                console.log(container);
                //document.body.appendChild(container);
                const opt = {
                    margin: 0.2,
                    filename: `job-${printId}.pdf`,
                };
                html2pdf().set(opt).from(container).save().finally(() => {
                    //document.body.removeChild(container);
                });
            });
    },
    list: {
        load(activeTab) {
            JOB.list.dataTable(activeTab);
        },

        // Field-level renderers (plain data → HTML)
        renderers: {
            row_no(data) {
                return `<span class="fw-bold text-dark">${data ?? ''}</span>`;
            },
            status(data) {
                const map = {
                    'pending':   ['Pending',   'warning'],
                    'active':    ['Active',    'primary'],
                    'completed': ['Completed', 'success'],
                    'cancelled': ['Cancelled', 'danger'],
                    'trashed':   ['Trashed',   'secondary'],
                };
                const key = (data ?? '').toString().toLowerCase();
                const [label, color] = map[key] ?? [data ?? '—', 'secondary'];
                return `<span class="badge bg-${color} text-capitalize">${label}</span>`;
            },
            services(data) {
                if (!data) return '';
                const items = Array.isArray(data) ? data : data.toString().split(',');
                return items.filter(Boolean).map(s =>
                    `<span class="badge bg-light text-dark border me-1">${s.trim()}</span>`
                ).join('');
            },
            clearance_status(data) {
                if (!data) return '';
                return `<span class="badge bg-info-subtle text-info border border-info-subtle">${data}</span>`;
            },
        },

        /** Build a DataTable column definition from a column_json entry. */
        _buildDtColumn(colDef) {
            const children = colDef.children || [];
            const renderer = JOB.list.renderers[colDef.key] || null;
            if (!children.length) {
                return {data: colDef.key, defaultContent: '', render: renderer ? (data) => renderer(data) : undefined};
            }
            return {
                data: colDef.key,
                defaultContent: '',
                render(data, type, row) {
                    const parentVal = renderer ? renderer(data) : (data ?? '');
                    const childHtml = children.map(child => {
                        const r   = JOB.list.renderers[child.key] || null;
                        const val = row[child.key] ?? '';
                        return `<small class="d-block text-muted lh-sm">${r ? r(val) : val}</small>`;
                    }).join('');
                    return parentVal + childHtml;
                },
            };
        },

        /** Build the dynamic <thead><tr> HTML. */
        _buildThead(columns, fields) {
            const fieldMap = {};
            fields.forEach(f => { fieldMap[f.key] = f; });
            let html = '';
            columns.forEach(col => {
                const meta     = fieldMap[col.key] || {};
                const minWidth = meta.min_width ? `min-width:${meta.min_width}px;` : '';
                const childHtml = (col.children || []).map(c =>
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

            JOB.columnSettings.fetch((settings) => {
                $('#dtFooter').empty();   // clear relocated pagination before destroy
                GLOBAL_FN.destroyDataTable();

                const columns  = settings.columns;
                const fields   = settings.fields;
                const fieldMap = {};
                fields.forEach(f => { fieldMap[f.key] = f; });

                // Rebuild thead
                $('#dataTable thead tr').html(JOB.list._buildThead(columns, fields));

                const dtColumns = columns.map(col => JOB.list._buildDtColumn(col, fields));

                // Orderable column indices
                const noSort = dtColumns.map((_, i) => {
                    const meta = fieldMap[columns[i].key] || {};
                    return meta.orderable ? null : i;
                }).filter(i => i !== null);
                noSort.push(dtColumns.length); // always disable action column

                const defaultOrder = (() => {
                    const first = dtColumns.findIndex((_, i) => (fieldMap[columns[i].key] || {}).orderable);
                    return first >= 0 ? [[first, 'desc']] : [[0, 'desc']];
                })();

                const actionBtn = GLOBAL_FN.dataTable.optionButton(activeTab !== 'trashed');

                let table = $('#dataTable').DataTable({
                    processing: false,
                    serverSide: true,
                    autoWidth: false,
                    lengthChange: false,
                    pageLength: parseInt($('#pageLength').val(), 10) || 25,
                    dom: 'rtip',
                    order: defaultOrder,
                    ajax: {
                        url: GLOBAL_FN.buildUrl('operation/job/data'),
                        type: 'POST',
                        data(d) {
                            d.tab = activeTab;
                            d.filterData = JOB.filter.default();
                        },
                        dataSrc(json) {
                            $('#dataTable tbody').find('.loading-row').remove();
                            GLOBAL_FN.setStatusCounts(json.statusCounts);
                            return json.data;
                        }
                    },
                    columnDefs: [{targets: noSort, orderable: false, searchable: false}],
                    columns: [...dtColumns, actionBtn],
                    language: {search: '', emptyTable: ' ', zeroRecords: ' '},
                    deferLoading: 0,
                    drawCallback() {
                        const info = this.api().page.info();
                        const noData = info.recordsTotal === 0;
                        const noResults = !noData && info.recordsDisplay === 0;
                        const hasRows = info.recordsDisplay > 0;

                        $('#tableWrapper').toggleClass('d-none', !hasRows);
                        $('#dtFooter').toggleClass('d-none', !hasRows);
                        $('#jobEmptyState').toggleClass('d-none', hasRows);
                        $('#emptyStateNoData').toggleClass('d-none', !noData);
                        $('#emptyStateNoResults').toggleClass('d-none', !noResults);
                    },
                    initComplete() {
                        JOB.form.open();
                        webDataTable.actions.menu();
                        const $wrap = $('#dataTable').closest('.dataTables_wrapper');
                        $('#dtFooter').append($wrap.find('.dataTables_info, .dataTables_paginate'));
                    }
                });

                $('#customSearch').on('keyup', function () {
                    table.search(this.value).draw();
                });
                $('#pageLength').off('change').on('change', function () {
                    table.page.len(parseInt(this.value, 10) || 25).draw();
                });
                webDataTable.loader(table);
                webDataTable.search(table);
            });
        },
        templates: {
            rowInfo: (data, row) => {
                const services = row.services ? row.services.split(',') : [];

                const badges = services.map(service => {
                    const s = service.trim().toLowerCase();
                    let colorClass = 'bg-primary-subtle text-primary border-primary'; // Default
                    style = 'font-size: 0.6rem;';

                    // Define color logic
                    if (s === 'transportation') {
                        colorClass = 'bg-info-subtle text-info border-info';
                    } else if (s === 'freight forwarding') {
                        colorClass = 'bg-purple-subtle text-purple border-purple'; // Ensure your CSS has .text-purple
                        style = 'font-size: 0.65rem; background-color: #f3ebff; color: #6610f2;';
                    } else if (s === 'warehousing') {
                        colorClass = 'bg-success-subtle text-success border-success';
                    }

                    return `
                <span class="badge ${colorClass} border border-opacity-10"
                      style="${style}">
                    ${service.trim()}
                </span>`;
                }).join('');

                return `<div class="fw-semibold">
        <div class="fw-bold text-dark">${data}</div>
        <div class="d-flex flex-wrap gap-1 mt-1">
        ${badges}
        </div>
        <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Mode: <i class="bi bi-airplane-engines-fill"></i> ${row.activity_id}</small>
        </div>
        <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Cust: ${row.customer.name_en}</small>`
            },

            polPod: (row) => `<div class="d-flex align-items-center gap-2"><div class="lh-1"><span class="fw-bold d-block">${row.polCode}</span><small class="text-muted" style="font-size: 0.65rem;">${row.etd}</small></div>${row.etd || row.polCode ? '<i class="bi bi-chevron-right text-muted small"></i>' : ''}<div class="lh-1"><span class="fw-bold d-block">${row.podCode}</span><small class="text-muted" style="font-size: 0.65rem;">${row.eta}</small></div></div><div class="mt-1 x-small text-secondary">${row.carrier ? '<i class="bi bi-info-circle me-1"></i>Flight: ' + row.carrier : ''}</div>`,
            payload: (row) => `<div class="small fw-bold text-dark">${row.weight ?? ''}</div><div class="text-muted" style="font-size: 0.65rem;">Vol: ${row.volume ?? '-'} ${row.no_of_pieces ? ' | ' + row.no_of_pieces : ''}</div><div class="text-muted" style="font-size: 0.65rem;">Commodity: ${row.commodity ?? '-'}</div>`,
            tracking: (row) => `<div style="font-size: 0.7rem;"><span class="text-muted text-uppercase">AWB:</span> ${row.awb_no ?? '-'}</div><div style="font-size: 0.7rem;"><div style="font-size: 0.7rem;"><span class="text-muted">Bayan:</span> ${row.clearance?.bayan_no ?? '-'}</div>`,
            consignee: (row) => `<div class="lh-1"><span class="small fw-bold d-block text-truncate" style="max-width: 150px;">${row.shipper ?? ''}</span>${row.shipper ? '<i class="bi bi-arrow-down text-muted" style="font-size: 0.7rem;"></i>' : ''}<span class="small fw-bold d-block text-truncate text-primary" style="max-width: 150px;">${row.consignee ?? ''}</span></div>`,
            invoices: (data) => `<div class="text-end"><span class="text-muted">Draft : ${data.draft}</span></div><div class="text-end"><span class="text-success">Approved : ${data.approved}</span></div>`,

            //<div class="text-info fw-medium" style="font-size: 0.7rem;"><i class="bi bi-person-check me-1"></i>Ops: Anil S.</div>
        },
        extraActions(row) {
            JOB.list.actions.statusChange(row);
            JOB.list.actions.view(row);
            JOB.list.actions.email(row);
            JOB.list.actions.delete(row);
        },
        actions: {
            statusChange(row) {
                $('#row_pending,#row_completed,#row_rejected,#row_trashed').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('operation/job/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: fd,
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                })
                $('#customer_invoice,#supplier_invoice,#proforma_invoice').off().on('click', function () {
                    location.href = $(this).attr('data-value');
                })
            },
            view(row) {
                $('#row_view').off().on('click', function () {
                    let jobId = row.attr('data-id');

                    // Open drawer

                    let drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
                    drawer.show();

                    // Load Overview
                    $('#moduleOverview').html('<p>Loading...</p>');
                    $.get('/operation/job/' + jobId + '/overview', function (data) {
                        $('#moduleOverview').html(data);
                    });
                });
            },
            delete(row) {
                $('#row_delete').off().on('click', function () {
                    let jobId = row.attr('data-id');
                    deleteFn(GLOBAL_FN.buildUrl('operation/job/' + jobId + '/delete'), {
                        method: 'GET',
                        callBack: 'datatable'
                    });
                });
            },
            email(row) {
                $('#row_email').off().on('click', function () {
                    let drawer = new bootstrap.Offcanvas(document.getElementById('sendEmailDrawer'));
                    drawer.show();
                });
            }
        }
    },
    form: {
        load() {
            JOB.form.open();
            /*let quotationId = localStorage.getItem('convert-quotation');
            if (quotationId) {
                webModal.openGlobalModal({
                    title: 'New Job',
                    url: GLOBAL_FN.buildUrl('operation/job/create'),
                    size: 'xxl',
                    content: {
                        quotationId: quotationId
                    }
                });
                localStorage.removeItem('convert-quotation');
            }*/
        },
        open() {
            $('#new,#new-first').off().on('click', function () {
                let dataTableData = $('#dataTable');
                let modelSize = dataTableData.data('model-size');
                let minHeight = dataTableData.data('min-height');
                webModal.openGlobalModal({
                    title: 'New Job',
                    url: GLOBAL_FN.buildUrl('operation/job/create'),
                    content: null,
                    minHeight: minHeight,
                    size: modelSize,
                });
            })
        },
        openCallback() {
            JOB.form.addContainer();
            JOB.form.addPackage();
            //JOB.form.removeRow();
            //JOB.form.shipmentMode();
            GLOBAL_FN.activity.activityChange();
            JOB.form.polPodLoad();
            //JOB.form.calculation.package();
        },
        /*calculation: {
            package() {
                $('#tab-packages .quantity,#tab-packages .length,#tab-packages .width,#tab-packages .height,#tab-packages .weight').off().on('change', function () {
                    let element = $(this).closest('tr');
                    let quantity = element.find('.quantity').val();
                    console.log(quantity);
                    let length = element.find('.length').val();
                    let width = element.find('.width').val();
                    let height = element.find('.height').val();
                    let weight = element.find('.weight').val();
                    let total = element.find('.total').val();
                    let volume = ((quantity * (length * width * height)) / 1000000).toFixed(8);
                    let total_weight = (quantity * weight).toFixed(3);
                    let v_weight = ((quantity * (length * width * height)) / 6000).toFixed(3);
                    let chargeable_weight = Math.max(total_weight,v_weight);
                    element.find('.volume').val(volume);
                    element.find('.total_weight').val(total_weight);
                    element.find('.chargeable_weight').val(chargeable_weight);
                })
            }
        },*/
        shipmentMode() {
            //$('#shipment_mode').off().on('change', function () {
            let jobPol = document.querySelector('#pol');
            let jobPod = document.querySelector('#pod');
            //let jobCarrier = document.querySelector('#carrier');

            // If already initialized, destroy first

            jobPol.tomselect.destroy();
            jobPod.tomselect.destroy();
            //jobCarrier.tomselect.destroy();

            JOB.form.polPodLoad();
            //})
        },
        polPodLoad(preLoad = null) {
            let port = $('#activity-id-hidden').val();
            initTomSelectSearch('#pol', port, 100, preLoad);
            initTomSelectSearch('#pod', port, 100, preLoad);
            /*initTomSelectSearch('#carrier', port + 'Lines', 50, preLoad);*/
        },
        addContainer() {
            // Add Container Row
            $('#containerTable').off('click', '.addContainerRow').on('click', '.addContainerRow', function () {
                let $tbody = $(this).closest('tbody');
                let $newRow = $tbody.find('tr:first').clone();

                // Clear values in cloned row
                $newRow.find('input, select, textarea').val('');
                $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
                $newRow.find('div.ts-wrapper').remove();
                initTomSelectForm($newRow);

                $tbody.append($newRow);
            });
        },
        addPackage() {
            // Add Package Row
            $('#packageTable').off('click', '.addPackageRow').on('click', '.addPackageRow', function () {
                let $tbody = $(this).closest('tbody');
                let $newRow = $tbody.find('tr:first').clone();

                // Clear values in cloned row
                $newRow.find('input, select, textarea').val('');
                $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
                $newRow.find('div.ts-wrapper').remove();
                initTomSelectForm($newRow);

                $tbody.append($newRow);
            });
        },
        /*removeRow() {
            // Remove Row (for both tables)
            $('#containerTable,#packageTable').off('click', '.remove-row').on('click', '.remove-row', function () {
                let $tbody = $(this).closest('tbody');
                const $tr = $(this).closest('tr');
                if ($tbody.find('tr').length > 1) {
                    $tr.remove();
                } else {
                    // If only one row left, just clear it
                    // $(this).closest('tr').find('input, select').val('');
                    $tr.find('input').val('');
                    $tr.find('select').each(function () {
                        $(this).val('');
                        if ($(this).hasClass('selectpicker')) {
                            $(this).selectpicker('destroy').addClass('selectpicker');
                            selectPicker('#' + $(this).closest('table').attr('id'));
                        }
                    });
                }
            })
        }*/
    },
}
