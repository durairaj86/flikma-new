PROSPECT = {
    title: 'Prospect',
    baseUrl: 'prospect',
    actionUrl: 'prospect',
    load() {
        PROSPECT.form.open();
    },
    list: {
        load(activeTab) {
            PROSPECT.list.dataTable(activeTab);
        },
        dataTable(activeTab = null) {
            GLOBAL_FN.destroyDataTable();
            activeTab = (activeTab && (typeof activeTab !== 'object')) ? activeTab : $("#listTabs").find('li button.active').attr('id');
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                order: [[1, 'desc']],
                ajax: {
                    url: GLOBAL_FN.buildUrl(PROSPECT.baseUrl + '/data'),
                    type: 'POST',
                    data: {
                        'tab': activeTab
                    },
                    dataSrc: function (json) {
                        // Remove loader rows when data arrives
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0], searchable: false},
                    {targets: [0], orderable: false},
                    /*{targets: [1], class: 'hide', visible: false},*/
                ],
                columns: [
                    {data: 'DT_RowIndex', class: 'hide-tooltip fav-index'},
                    {
                        data: 'name_en', render: function (data, type, row) {
                            return row.name_en;
                        }
                    },
                    {
                        data: 'email', render: function (data, type, row) {
                            return row.email;
                        }
                    },
                    {
                        data: 'phone', render: function (data, type, row) {
                            return row.phone;
                        }
                    },
                    {data: 'salesperson.name'},
                    {data: 'created_at', name: 'created_at'},
                    // Actions column
                    GLOBAL_FN.dataTable.optionButton()

                ],
                language: {
                    search: "" // removes "Search:" label
                },

                deferLoading: 0, // don't load immediately
                /*preDrawCallback: function (settings) {
                    let api = this.api();
                    let cols = api.columns().nodes().length;
                    let pageLen = api.page.len(); // rows per page

                    // clear old rows & insert loader rows
                    let loaderRows = '';
                    for (let i = 0; i < pageLen; i++) {
                        loaderRows += '<tr class="loading-row">';
                        for (let j = 0; j < cols; j++) {
                            loaderRows += `<td><div class="loader-td"></div></td>`;
                        }
                        loaderRows += '</tr>';
                    }
                    $('#dataTable tbody').html(loaderRows);
                }*/

                initComplete: function () {
                    PROSPECT.form.open();
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
            PROSPECT.list.actions.delete(row);
        },
        actions: {
            delete(row) {
                $('#row_delete').off().on('click', function () {

                    $.confirm({
                        title: 'Confirm Delete',
                        content: 'Are you sure you want to delete this record?',
                        type: 'red',
                        buttons: {
                            cancel: function () {
                            },

                            delete: {
                                text: 'Delete',
                                btnClass: 'btn-red',
                                action: function () {

                                    $.ajax({
                                        url: '/prospect/delete/' + row.attr('data-id'),
                                        type: 'DELETE',
                                        dataType: 'json',
                                        success: function (response) {

                                            if (response.status === 'success') {
                                                toastr.success(response.message);
                                                //row.remove(); // remove row if needed
                                                loadJs('list.load');
                                            } else if (response.status === 'warning') {
                                                toastr.warning(response.message);
                                            } else {
                                                toastr.error('Error deleting record.');
                                            }
                                        },
                                        error: function () {
                                            toastr.error('Server error');
                                        }
                                    });

                                }
                            }
                        }
                    });

                });

            }
        }
    },
    form: {
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'Add Prospect',
                    url: GLOBAL_FN.buildUrl('prospect/create'),
                    content: null,
                    size: 'md',
                    callBack: null,
                    minHeight: '300'
                });
            })
        },
        quick: {
            open() {
                webModal.quickModal.open({
                    title: 'Add Quick Prospect',
                    url: GLOBAL_FN.buildUrl('prospect/create/quick'),
                    content: null,
                    size: 'md',
                    callBack: null,
                    module: 'PROSPECT'
                });
            },
            after: {
                save(data) {
                    let ts = document.querySelector('#prospect').tomselect;

                    // If TomSelect already exists → add dynamically
                    if (ts) {
                        ts.addOption({
                            value: data.id,
                            text: data.name,
                            subtext: data.code
                        });
                        ts.addItem(data.id); // select it
                    } else {
                        // If not initialized yet
                        $('#prospect').prepend(
                            `<option value="${data.id}" data-subtext="${data.code}" selected>${data.name}</option>`
                        );
                    }
                }
            }
        }
    },
}
