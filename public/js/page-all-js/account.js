ACCOUNT = {
    title: 'Accounts',
    baseUrl: 'finance/account',
    actionUrl: 'finance/accounts',
    load() {
        //ACCOUNT.form.load();
        this.list.bindTabs();
        //this.list.dataTable('asset');
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
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                ajax: {
                    url: GLOBAL_FN.buildUrl('finance/account/data'),
                    type: 'POST',
                    data: {type: activeTab},
                    dataSrc: function (json) {
                        $('#dataTable tbody .loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        return json.data;
                    }
                },
                columns: [
                    //{data: 'DT_RowIndex', class: 'hide-tooltip fav-index'},
                    {data: 'name'},
                    {data: 'code'},
                    {data: 'parent_id'},
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
            $('#customSearch').on('keyup', function () {
                table.search(this.value).draw();
            });
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
