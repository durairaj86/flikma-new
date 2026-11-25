/* Customer Tracker: one line per customer / prospect (the sales workbook's CRM sheet). */
CRM_TRACKER = {
    load() {
        initTomSelectForm($('#trkFilters'));
        $('#trkShow, #trkSalesperson, #trkRank, #trkStatus').off('change.trk').on('change.trk', () => $('#dataTable').DataTable().ajax.reload());
        $('#trkExport').off().on('click', function () {
            window.location.href = GLOBAL_FN.buildUrl('sales/tracker/export') + '?' + $.param(CRM_TRACKER.filters());
        });
    },
    filters() {
        return {
            show: $('#trkShow').val(),
            salesperson_id: $('#trkSalesperson').val() || '',
            rank: $('#trkRank').val() || '',
            status: $('#trkStatus').val() || '',
            q: $('#customSearch').val() || '',
        };
    },
    list: {
        load() {
            CRM_TRACKER.list.dataTable();
        },
        dataTable() {
            GLOBAL_FN.destroyDataTable();
            const e = v => $('<span>').text(v == null ? '' : String(v)).html();
            const dash = '<span class="text-muted">—</span>';
            const table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 50,
                ordering: false,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                ajax: {
                    url: GLOBAL_FN.buildUrl('sales/tracker/data'),
                    type: 'POST',
                    data: function (d) {
                        Object.assign(d, CRM_TRACKER.filters());
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        const c = json.counts || {};
                        $('#kpiLines').text(c.lines || 0);
                        $('#kpiStale').text(c.stale || 0);
                        $('#kpiOverdue').text(c.overdue || 0);
                        return json.data;
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', class: 'hide-tooltip fav-index'},
                    {
                        data: 'name', render: function (data, type, r) {
                            const contact = [r.contact, r.phone].filter(Boolean).map(e).join(' · ');
                            return '<a href="' + CRM.openUrl(r.type, r.id) + '" class="fw-semibold text-dark">' + e(data) + '</a>' +
                                '<div class="small text-muted">' + e(r.kind) + (r.no ? ' · ' + e(r.no) : '') + (r.location ? ' · ' + e(r.location) : '') + '</div>' +
                                (contact ? '<div class="small">' + contact + '</div>' : '');
                        }
                    },
                    {
                        data: 'rank', render: function (data, type, r) {
                            const rank = data ? '<span class="trk-rank r-' + e(data) + '">' + e(data) + '</span>' : dash;
                            const hint = r.suggested_rank && r.suggested_rank !== data
                                ? '<div class="small text-muted" title="' + trans('Suggested from the score') + '">' + trans('Suggested') + ' ' + e(r.suggested_rank) + '</div>' : '';
                            return rank + hint;
                        }
                    },
                    {
                        data: 'status_label', render: function (data, type, r) {
                            return '<span class="badge rounded-pill" style="' + (window.TRACKER_STATUS_STYLE[r.status] || '') + '">' + e(data) + '</span>';
                        }
                    },
                    {
                        data: 'route', render: function (data, type, r) {
                            const extra = [r.service, r.volume, r.incoterm].filter(Boolean).map(e).join(' · ');
                            return (data ? e(data) : dash) + (extra ? '<div class="small text-muted">' + extra + '</div>' : '');
                        }
                    },
                    {data: 'handled_by', render: d => d ? e(d) : dash},
                    {
                        data: 'selling_fmt', class: 'text-end', render: function (data, type, r) {
                            if (!data) return dash;
                            const m = r.margin_fmt ? '<div class="small ' + (r.margin < 0 ? 'text-danger' : 'text-success') + '">' + e(r.margin_fmt) +
                                (r.margin_pct !== null ? ' (' + e(r.margin_pct) + '%)' : '') + '</div>' : '';
                            return e(data) + m;
                        }
                    },
                    {
                        data: 'score', class: 'text-end', render: function (data, type, r) {
                            return '<span class="fw-semibold">' + e(data) + '</span>' +
                                '<div class="small text-muted text-nowrap" title="' + trans('Touches') + ' · ' + trans('RFQs') + ' · ' + trans('Jobs') + '">' +
                                e(r.touches) + ' · ' + e(r.rfqs) + ' · ' + e(r.jobs) + '</div>';
                        }
                    },
                    {
                        data: 'last_action', render: function (data, type, r) {
                            if (!data) return '<span class="' + (r.stale ? 'trk-late' : 'text-muted') + '">' + trans('No contact yet') + '</span>';
                            const days = r.days_since === 0 ? trans('today') : trans(':n days ago').replace(':n', r.days_since);
                            return '<span class="badge rounded-pill" style="' + r.last_style + '">' + e(data) + '</span>' +
                                '<div class="small ' + (r.stale ? 'trk-late' : 'text-muted') + '">' + e(r.last_at) + ' · ' + e(days) + '</div>';
                        }
                    },
                    {
                        data: 'next_action', render: function (data, type, r) {
                            if (!data) return dash;
                            return e(data) + '<div class="small ' + (r.next_overdue ? 'trk-late' : 'text-muted') + '">' + e(r.next_at) + '</div>';
                        }
                    },
                    {
                        data: null, class: 'text-end', render: function (data, type, r) {
                            return '<button type="button" class="btn btn-sm btn-outline-primary rounded-pill trk-log text-nowrap" data-type="' + e(r.type) + '" data-id="' + e(r.id) + '">' +
                                '<i class="bi bi-plus-lg me-1"></i>' + trans('Log') + '</button>';
                        }
                    },
                ],
                language: {search: ''},
            });
            $('#customSearch').off('keyup input').on('keyup input', window.debounceSearch(function () {
                table.ajax.reload();
            }));
            $('#dataTable').off('click.trk').on('click.trk', '.trk-log', function () {
                CRM.logActivity($(this).data('type'), $(this).data('id'), () => table.ajax.reload(null, false));
            });
            webDataTable.loader(table);
        },
    },
};
