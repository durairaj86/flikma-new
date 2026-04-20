USER = {
    title: 'User',
    baseUrl: 'masters/user',
    actionUrl: 'masters/user',
    load() {
        USER.form.open();
        USER.profile.save();
    },
    list: {
        load(activeTab) {
            USER.list.dataTable(activeTab);
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
                    url: '/masters/user/data',
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
                        data: 'name', render: function (data, type, row) {
                            return row.name;
                        }
                    },
                    {
                        data: 'email', render: function (data, type, row) {
                            return row.email + '<br>' + row.phone;
                        }
                    },
                    {
                        data: 'department.name', render: function (data, type, row) {
                            return row.department.name + '<br>' + row.role;
                        }
                    },
                    {data: 'last_login'},
                    {data: 'created_at', name: 'created_at'},
                    // Actions column
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {
                    search: "" // removes "Search:" label
                },
                deferLoading: 0, // don't load immediately

                initComplete: function () {
                    // Add custom class to default search input
                    USER.form.open();
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
            USER.list.actions.statusChange(row);
            USER.list.actions.view(row);
        },
        actions: {
            statusChange(row) {
                /* $('#row_pending,#row_confirm,#row_blocked,#row_rejected').off().on('click', function () {
                     //GLOBAL_FN.ajaxData.sendData(GLOBAL_FN.buildUrl('customer/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')),'datatable',{})
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

                    let drawer = new bootstrap.Offcanvas(document.getElementById('viewDrawer'));
                    drawer.show();

                    // Load Overview
                    $('#customerOverview').html('<p>Loading...</p>');
                    $.get('/' + USER.baseUrl + '/' + customerId + '/overview', function (data) {
                        $('#customerOverview').html(data);
                    });

                    // Clear other tabs
                    $('#customerInvoices').html('');
                    $('#customerTransactions').html('');
                });
            },
        }
    },
    form: {
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'Add User',
                    url: GLOBAL_FN.buildUrl('masters/user/create'),
                    content: null,
                    size: 'xl',
                    callBack: null
                });
            })
        },
    },
    profile:{
        save(){
            $('#submit').off().on('click', function (e) {
                e.preventDefault();
                const form = $('#profile-form');
                const action = form.attr('action');
                const method = form.attr('method') || 'POST';

                if (form.valid()) {

                    // Build form data
                    const formData = new FormData(form[0]);
                    // const formData = new FormData(this);

                    //const submitBtn = form.find('button[type="submit"]');
                    //submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Submitting');

                    $.ajax({
                        url: action,
                        type: method,
                        data: formData,
                        //context: modal,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            console.log(response);
                            if (response.status === 'success') {
                                toastr.success(response.message);

                                if (callback) callback(response);
                            } else {
                                if (response.status === 'error') {
                                    toastr.error(response.message);
                                }
                            }
                        },
                        error: function (xhr) {
                            let errors = xhr.responseJSON.errors;
                            if (errors) {
                                $.each(errors, function (key, value) {
                                    toastr.error(value[0]);
                                });
                            } else {
                                toastr.error("Something went wrong!");
                            }
                        },
                        /*error: function(xhr){
                            $('#globalModalBody').html(xhr.responseText);
                            setupModalFooter(); // Re-add footer buttons after validation errors
                        },*/
                        complete: function () {
                            submitBtn.prop('disabled', false).html('Submit');
                        }
                    });
                }
            })
        }
    }
}
