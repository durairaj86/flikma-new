CRM_FOLLOW_UP = {
    title: trans('Activity'),
    baseUrl: 'sales/activity',
    actionUrl: 'sales/activity',
    load() {
        $('#new').off().on('click', () => CRM.logActivity(null, null));
        initTomSelectForm($('#fuFilters'));
        $('#fuOwner, #fuAction').off().on('change', () => CRM_FOLLOW_UP.list.dataTable());
    },
    list: {
        load(activeTab) {
            CRM_FOLLOW_UP.list.dataTable(activeTab);
        },
        dataTable(activeTab = null) {
            GLOBAL_FN.destroyDataTable();
            activeTab = (activeTab && (typeof activeTab !== 'object')) ? activeTab : $('#listTabs').find('li button.active').attr('id');
            const e = v => $('<span>').text(v == null ? '' : String(v)).html();
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                order: [[1, activeTab === 'done' ? 'desc' : 'asc']],
                ajax: {
                    url: GLOBAL_FN.buildUrl('sales/follow-up/data'),
                    type: 'POST',
                    data: function (d) {
                        d.tab = activeTab;
                        d.owner = $('#fuOwner').val();
                        d.action = $('#fuAction').val();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        // first visit: nothing overdue → open the first tab that has work, once (a tab the user picks stays)
                        const c = json.statusCounts || {};
                        if (!CRM_FOLLOW_UP.autoTabDone) {
                            CRM_FOLLOW_UP.autoTabDone = true;
                            const next = activeTab === 'overdue' && !+c.overdue && ['today', 'upcoming'].find(k => +c[k] > 0);
                            if (next) setTimeout(() => $('#' + next).trigger('click'), 0);
                        }
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0, 2, 3, 5, 6], orderable: false},
                    {targets: [0, 1, 2, 3, 5, 6], searchable: false},
                ],
                columns: [
                    {data: 'DT_RowIndex', class: 'hide-tooltip fav-index'},
                    {
                        data: 'follow_up_at', render: function (data, type, row) {
                            const late = row.overdue_days > 0 ? '<div class="small fu-overdue">' + trans(':n days late').replace(':n', row.overdue_days) + '</div>' : '';
                            const done = row.follow_up_done_at ? '<div class="small text-success">' + trans('Done') + ' ' + e(row.follow_up_done_at) + '</div>' : '';
                            return '<div class="' + (row.overdue_days > 0 ? 'fu-overdue' : '') + '">' + e(data) + '</div>' + late + done;
                        }
                    },
                    {
                        data: 'subject', render: function (s) {
                            return '<div class="small text-muted">' + e(s.kind) + ' · ' + e(s.no) + '</div><div class="fw-semibold">' + e(s.name) + '</div>';
                        }
                    },
                    {
                        data: 'action_name', render: function (data, type, row) {
                            return '<span class="badge rounded-pill" style="' + row.action_style + '">' + e(data) + '</span>';
                        }
                    },
                    {
                        data: 'notes', render: function (data) {
                            const text = String(data || '');
                            return text ? '<div class="small text-truncate" style="max-width:380px" title="' + e(text) + '">' + e(text) + '</div>' : '<span class="text-muted">—</span>';
                        }
                    },
                    {data: 'assignee_name'},
                    GLOBAL_FN.dataTable.optionButton(true, true)
                ],
                language: {search: ''},
                deferLoading: 0,
                initComplete: function () {
                    webDataTable.actions.menu();
                }
            });
            $('#customSearch').off('keyup input').on('keyup input', window.debounceSearch(function () {
                table.search(this.value).draw();
            }));
            webDataTable.loader(table);
            webDataTable.search(table);
        },
        extraActions(row) {
            const reload = () => $('#dataTable').DataTable().ajax.reload(null, false);
            $('#row_done').off().on('click', function () {
                // done: tick it off, then offer to log what happened (keyboard: Enter on "Log outcome")
                CRM.markDone(row.attr('data-id'), function (res) {
                    reload();
                    appConfirm({
                        title: trans('Follow-up done'),
                        message: trans('Log what happened and set the next follow-up?'),
                        button: trans('Log outcome'),
                        btnClass: 'btn-primary',
                        tone: 'success',
                        icon: 'bi-journal-plus',
                        focusConfirm: true,
                        onConfirm: () => CRM.logActivity(res.subject_type, res.subject_id)
                    });
                });
            });
            $('#row_undone').off().on('click', function () {
                CRM.markDone(row.attr('data-id'), reload, true);
            });
            $('#row_log_activity').off().on('click', function () {
                CRM.logActivity(row.attr('data-subject-type'), row.attr('data-subject-id'));
            });
            $('#row_open').off().on('click', function () {
                window.location.href = CRM.openUrl(row.attr('data-subject-type'), row.attr('data-subject-id'));
            });
            $('#row_delete').off().on('click', function () {
                deleteRecord(GLOBAL_FN.buildUrl('sales/activity/' + row.attr('data-id')), reload, {title: trans('Delete activity?')});
            });
        }
    },
    form: {
        openCallback() {
            // the edit form is opened by the shared row Edit action: wire its "With" picker once Tom Select is on
            setTimeout(CRM.bindActivityForm, 0);
        }
    }
}
