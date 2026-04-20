BASIC_SALARY = {
    title: 'Basic Salary',
    baseUrl: 'payroll/basic/salary',
    actionUrl: 'payroll/basic/salary',
    load() {
        BASIC_SALARY.form.load();
        BASIC_SALARY.filter.load();
        datepicker();
    },
    filter: {
        load: function () {
            BASIC_SALARY.filter.filterBox();
            BASIC_SALARY.filter.searchBox();
            /*BASIC_SALARY.filter.dateRangePreset();
            BASIC_SALARY.filter.togglePanel();*/
        },
        filterBox: function () {
            $('#apply-filter').off().on({
                click: function () {
                    BASIC_SALARY.list.dataTable();
                }
            });
        },
        searchBox: function () {
            let searchTimeout;
            $('#customSearch').off().on({
                keyup: function (e) {
                    // If Enter key is pressed, search immediately
                    if (e.key === 'Enter') {
                        clearTimeout(searchTimeout);
                        BASIC_SALARY.list.dataTable();
                        return;
                    }

                    // Otherwise, debounce the search to avoid too many requests
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function () {
                        BASIC_SALARY.list.dataTable();
                    }, 500); // Wait 500ms after user stops typing
                }
            });
        },
        /*togglePanel: function() {
            $('#filter-box').off().on('click', function () {
                $('#filterPanel').toggleClass('d-none');
            });
        },
        dateRangePreset: function() {
            $('#presetDateRange').off().on('change', function() {
                let preset = $(this).val();
                let fromDate, toDate;
                const today = new Date();

                switch(preset) {
                    case 'today':
                        fromDate = toDate = BASIC_SALARY.utils.formatDate(today);
                        break;
                    case 'yesterday':
                        const yesterday = new Date(today);
                        yesterday.setDate(yesterday.getDate() - 1);
                        fromDate = toDate = BASIC_SALARY.utils.formatDate(yesterday);
                        break;
                    case 'thisMonth':
                        fromDate = BASIC_SALARY.utils.formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
                        toDate = BASIC_SALARY.utils.formatDate(today);
                        break;
                    case 'lastMonth':
                        fromDate = BASIC_SALARY.utils.formatDate(new Date(today.getFullYear(), today.getMonth() - 1, 1));
                        toDate = BASIC_SALARY.utils.formatDate(new Date(today.getFullYear(), today.getMonth(), 0));
                        break;
                    case 'thisQuarter':
                        const quarterMonth = Math.floor(today.getMonth() / 3) * 3;
                        fromDate = BASIC_SALARY.utils.formatDate(new Date(today.getFullYear(), quarterMonth, 1));
                        toDate = BASIC_SALARY.utils.formatDate(today);
                        break;
                    case 'lastQuarter':
                        const lastQuarterMonth = Math.floor((today.getMonth() - 3) / 3) * 3;
                        fromDate = BASIC_SALARY.utils.formatDate(new Date(today.getFullYear(), lastQuarterMonth, 1));
                        toDate = BASIC_SALARY.utils.formatDate(new Date(today.getFullYear(), lastQuarterMonth + 3, 0));
                        break;
                    case 'thisYear':
                        fromDate = BASIC_SALARY.utils.formatDate(new Date(today.getFullYear(), 0, 1));
                        toDate = BASIC_SALARY.utils.formatDate(today);
                        break;
                    case 'lastYear':
                        fromDate = BASIC_SALARY.utils.formatDate(new Date(today.getFullYear() - 1, 0, 1));
                        toDate = BASIC_SALARY.utils.formatDate(new Date(today.getFullYear() - 1, 11, 31));
                        break;
                    default:
                        return; // Do nothing if no preset is selected
                }

                $('#filter-from-date').val(fromDate);
                $('#filter-to-date').val(toDate);
            });
        },*/
        default: function () {
            let data = {};
            data['employee_id'] = $('#filter-employee').val();
            data['from_date'] = $('#filter-from-date').val();
            data['to_date'] = $('#filter-to-date').val();
            data['customSearch'] = $('#customSearch').val();
            return data;
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
        iframe.src = '/' + BASIC_SALARY.baseUrl + '/' + printId + '/print';
    },
    list: {
        load() {
            BASIC_SALARY.list.dataTable();
        },
        dataTable() {
            GLOBAL_FN.destroyDataTable();
            let table = $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: GLOBAL_FN.buildUrl('payroll/basic/salary/data'),
                    type: 'POST',
                    data: function (d) {
                        d.filterData = BASIC_SALARY.filter.default();
                    }
                },
                columns: [
                    {data: 'employee_name', name: 'employee_name'},
                    {
                        data: 'basic_salary',
                        name: 'basic_salary',
                        render: function (data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'housing_allowance',
                        name: 'housing_allowance',
                        render: function (data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'transportation_allowance',
                        name: 'transportation_allowance',
                        render: function (data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'food_allowance',
                        name: 'food_allowance',
                        render: function (data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'phone_allowance',
                        name: 'phone_allowance',
                        render: function (data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'other_allowance',
                        name: 'other_allowance',
                        render: function (data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: 'total_salary',
                        name: 'total_salary',
                        render: function (data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {data: 'effective_date', name: 'effective_date'},
                    {
                        data: 'status',
                        name: 'status',
                        render: function (data) {
                            let statusClass = data === 'active' ? 'status-active' : 'status-inactive';
                            return `<span class="status-pill ${statusClass}">${data.toUpperCase()}</span>`;
                        }
                    },
                    GLOBAL_FN.dataTable.optionButton()
                ],
                order: [[8, 'desc']], // Order by effective_date descending
                responsive: true,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pageLength: 25,
                initComplete: function () {
                    BASIC_SALARY.form.open();
                    webDataTable.actions.menu();
                }
            });

            $('#customSearch').on('keyup', function () {
                table.search(this.value).draw();
            });
        },
        extraActions(row) {
            BASIC_SALARY.list.actions.statusChange(row);
            BASIC_SALARY.list.actions.view(row);
            BASIC_SALARY.list.actions.delete(row);
        },
        actions: {
            statusChange(row) {
                $('#row_pending,#row_delivered,#row_cancelled,#row_in_transit').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('payroll/basic/salary/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                        method: 'POST',
                        data: fd,
                        callBack: 'datatable'
                    }, $(this).attr('data-value'));
                })
            },
            view(row) {
                $('#row_view').off().on('click', function () {
                    let id = row.attr('data-id');

                    // Open drawer
                    let drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
                    drawer.show();

                    // Load Overview
                    $('#moduleOverview').html('<p>Loading...</p>');
                    $.get('/payroll/basic/salary/' + id + '/overview', function (data) {
                        $('#moduleOverview').html(data);
                    });
                });
            },
            print(row) {
                $('#row_print').off().on('click', function () {
                    let id = row.attr('data-id');
                    BASIC_SALARY.printPreview(id);
                });
            }
            /*edit() {
                $(document).off('click', '.edit-record').on('click', '.edit-record', function () {
                    let id = $(this).data('id');
                    $.ajax({
                        url: GLOBAL_FN.buildUrl(`payroll/basic/salary/${id}/edit`),
                        type: 'GET',
                        success: function (response) {

                        }
                    });
                });
            },
            delete() {
                $(document).off('click', '.delete-record').on('click', '.delete-record', function () {
                    let id = $(this).data('id');
                    if (confirm('Are you sure you want to delete this basic salary record?')) {
                        $.ajax({
                            url: GLOBAL_FN.buildUrl(`payroll/basic/salary/${id}/delete`),
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                if (response.success) {
                                    toastr.success(response.message);
                                    BASIC_SALARY.list.dataTable();
                                } else {
                                    toastr.error(response.message);
                                }
                            },
                            error: function (xhr) {
                                toastr.error('An error occurred while deleting the record.');
                            }
                        });
                    }
                });
            }*/
        }
    },
    form: {
        load() {
            BASIC_SALARY.form.open();
        },
        open() {
            $('#new').off().on('click', function () {
                BASIC_SALARY.form.openBasicSalaryModal();
            });
        },
        openBasicSalaryModal: function (id = null) {
            webModal.openGlobalModal({
                title: 'New Attendance',
                url: GLOBAL_FN.buildUrl(id ? 'payroll/basic/salary/' + id + '/create' : 'payroll/basic/salary/create'),
                content: {
                    id: id
                },
                size: 'md',
                scroll: false,
            });
        },
    }
};
