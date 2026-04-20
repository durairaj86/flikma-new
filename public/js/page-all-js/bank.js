BANK = {
    baseUrl: 'masters/bank',
    actionUrl: 'masters/bank',
    load() {
        BANK.form.open();
        BANK.shortcutFn();
    },
    shortcutFn() {
        //webModal.optionsChildModal('currency');
        /*document.addEventListener('keydown', function (e) {
            if (e.key === 'F1') {
                e.preventDefault(); // stop browser help from opening
                webModal.shortCut.closeSubModal('currencyModal');
            }
        });*/

    },
    list: {
        load() {
            BANK.list.dataTable();
        },
        dataTable() {
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                order: [[1, 'desc']],
                ajax: {
                    url: '/masters/bank/data',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataSrc: function (json) {
                        // Remove loader rows when data arrives
                        $('#dataTable tbody').find('.loading-row').remove();
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0], searchable: false},
                    {targets: [0], orderable: false},
                    /*{targets: [1], class: 'hide', visible: false},*/
                ],
                columns: [
                    {data: 'DT_RowIndex', class: 'text-center hide-tooltip fav-index'},
                    {
                        data: 'bank_name', render: function (data, type, row) {
                            return '<div>' + row.bank_name + '</div><small class="text-muted">' + row.branch_name + '</small>';
                        }
                    },
                    {data: 'account_number'},
                    {data: 'account_holder'},
                    {data: 'currency'},
                    {data: 'iban_code'},
                    {data: 'swift_code'},
                    {data: 'bank_address'},
                    {data: 'sort', class: 'text-end'},
                    // Actions column
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {
                    search: "" // removes "Search:" label
                },
                deferLoading: 0, // don't load immediately

                initComplete: function () {
                    BANK.form.open();
                    webDataTable.actions.menu();
                }
            });
            $('#customSearch').on('keyup', function () {
                table.search(this.value).draw();
            });
            webDataTable.loader(table);
            webDataTable.search(table);
            //webDataTable.actions.menu();
        },
        extraActions(row) {
            BANK.list.actions.statusChange(row);
            BANK.list.actions.view(row);
        },
        actions: {
            statusChange(row) {
                /*$('#row_pending,#row_confirm,#row_blocked,#row_rejected').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('customer/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: fd,
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                })*/
            },
            view(row) {
                $('#row_view').off().on('click', function () {
                    let customerId = row.attr('data-id');

                    // Open drawer

                    let drawer = new bootstrap.Offcanvas(document.getElementById('bankDrawer'));
                    drawer.show();

                    // Load Overview
                    $('#customerOverview').html('<p>Loading...</p>');
                    $.get('/masters/bank/' + customerId + '/overview', function (data) {
                        $('#customerOverview').html(data);
                    });
                });
            },
        },
    },
    form: {
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'Add Bank',
                    url: GLOBAL_FN.buildUrl('masters/bank/create'),
                    content: null,
                    size: 'lg',
                    callBack: null
                });
            })
        },
    },
}
