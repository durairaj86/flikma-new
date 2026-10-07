ACCOUNT = {
    title: 'Accounts',
    baseUrl: 'finance/account',
    actionUrl: 'finance/accounts',
    load() {
        //ACCOUNT.form.load();
        this.list.bindTabs();
        ACCOUNT.filter.load();
        //this.list.dataTable('asset');
    },
    filter: {
        load() {
            $('#apply-filter').off().on('click', function () {
                ACCOUNT.list.dataTable();
                FILTER.filteredColumn();
            });
        },
        default() {
            return {
                status: $('#filter-status').val() || 'all',
                parent: $('#filter-parent').val() || '',
            };
        },
    },
    list: {
        load(activeTab) {
            ACCOUNT.list.dataTable(activeTab);
        },
        bindTabs() {
            $('#listTabs button').on('click', (e) => {
                const tab = $(e.target).attr('id');
                this.dataTable(tab);
            });
        },
        dataTable(activeTab = null) {
            GLOBAL_FN.destroyDataTable();
            activeTab = (activeTab && (typeof activeTab !== 'object')) ? activeTab : $("#listTabs").find('li button.active').attr('id');
            const table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 500,
                ordering: false,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                ajax: {
                    url: GLOBAL_FN.buildUrl('finance/account/data'),
                    type: 'POST',
                    data: function (d) {
                        d.type = activeTab;
                        d.filterData = ACCOUNT.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody .loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        return json.data;
                    }
                },
                columns: [
                    //{data: 'DT_RowIndex', class: 'hide-tooltip fav-index'},
                    {
                        data: 'name', render: function (data, type, row) {
                            if (type !== 'display') return data;
                            const indent = (row.depth || 0) * 22;
                            const caret = row.has_children
                                ? '<span class="acc-caret" data-id="' + row.id + '"><i class="bi bi-caret-down-fill small"></i></span>'
                                : '<span class="acc-caret-spacer"></span>';
                            return '<div class="d-flex align-items-center" style="padding-left:' + indent + 'px;">' + caret
                                + '<span class="acc-name' + (row.has_children ? ' is-parent' : '') + '">' + data + '</span></div>';
                        }
                    },
                    {data: 'code'},
                    {data: 'account_number'},
                    {
                        data: 'is_active', render: function (data, type, row) {
                            return '<div class="form-check form-switch"><input class="form-check-input is_active" type="checkbox" data-old-value="' + row.is_active + '" value="1" ' + (row.is_active ? "checked" : "") + '></div>';
                        }
                    },
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {
                    search: "" // removes "Search:" label
                },

                //deferLoading: 0, // don't load immediately
                initComplete: function () {
                    ACCOUNT.form.open();
                    webDataTable.actions.menu();
                    ACCOUNT.list.actions.statusChange();
                },
            });
            // Collapse / expand a parent: hides every row whose ancestors include it (re-showing only rows whose own ancestors are all open).
            const collapsed = new Set();
            const applyTree = () => {
                $('#dataTable tbody tr.row-item').each(function () {
                    const anc = ($(this).attr('data-ancestors') || '').split(',').filter(Boolean);
                    $(this).toggle(!anc.some(id => collapsed.has(id)));
                });
            };
            $('#dataTable tbody').off('click', '.acc-caret').on('click', '.acc-caret', function (e) {
                e.stopPropagation();
                const id = String($(this).data('id'));
                collapsed.has(id) ? collapsed.delete(id) : collapsed.add(id);
                $(this).toggleClass('collapsed', collapsed.has(id));
                applyTree();
            });
            $('#acc-expand-all').off().on('click', function () { collapsed.clear(); $('.acc-caret').removeClass('collapsed'); applyTree(); });
            $('#acc-collapse-all').off().on('click', function () {
                $('#dataTable tbody .acc-caret').each(function () { collapsed.add(String($(this).data('id'))); }).addClass('collapsed');
                applyTree();
            });

            $('#customSearch').on('keyup input', window.debounceSearch(function () {
                table.search(this.value).draw();
            }));
            //webDataTable.loader(table);
            webDataTable.search(table);
        },
        extraActions(row) {

        },
        actions: {
            statusChange() {
                $(document).off('change', '.is_active').on('change', '.is_active', function () {
                    let row = $(this).closest('tr');
                    let fd = new FormData();

                    changeCustomerStatus(
                        GLOBAL_FN.buildUrl('finance/accounts/' + row.attr('data-id') + '/status/' + $(this).is(':checked')),
                        {
                            method: 'POST',
                            data: fd,
                            callBack: 'datatable'
                        },
                        $(this).attr('data-value'),
                        $(this),
                    );
                });
            },
        }
    },
    form: {
        load() {
            ACCOUNT.form.open();
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'New Account',
                    url: GLOBAL_FN.buildUrl('finance/account/create'),
                    content: null,
                    size: 'md',
                });
            })
        },
    },
}
