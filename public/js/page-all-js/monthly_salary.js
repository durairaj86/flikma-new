MONTHLY_SALARY = {
    title: 'Monthly Salary',
    baseUrl: 'payroll/monthly/salary',
    actionUrl: 'payroll/monthly/salary',
    load() {
        MONTHLY_SALARY.form.load();
        MONTHLY_SALARY.filter.load();
        datepicker();
    },
    filter: {
        load: function () {
            MONTHLY_SALARY.filter.filterBox();
            MONTHLY_SALARY.filter.searchBox();
        },
        filterBox: function () {
            $('#apply-filter').off().on({
                click: function () {
                    MONTHLY_SALARY.list.dataTable();
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
                        MONTHLY_SALARY.list.dataTable();
                        return;
                    }

                    // Otherwise, debounce the search to avoid too many requests
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function () {
                        MONTHLY_SALARY.list.dataTable();
                    }, 500); // Wait 500ms after user stops typing
                }
            });
        },
        default: function () {
            let data = {};
            data['employee_id'] = $('#filter-employee').val();
            data['from_date'] = $('#filter-from-date').val();
            data['to_date'] = $('#filter-to-date').val();
            data['month'] = $('#filter-month').val();
            data['year'] = $('#filter-year').val();
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
        iframe.src = '/' + MONTHLY_SALARY.baseUrl + '/' + printId + '/print';
    },
    list: {
        load() {
            MONTHLY_SALARY.list.dataTable();
        },
        dataTable() {
            GLOBAL_FN.destroyDataTable();
            let table = $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: GLOBAL_FN.buildUrl('payroll/monthly/salary/data'),
                    type: 'POST',
                    data: function (d) {
                        d.filterData = MONTHLY_SALARY.filter.default();
                    }
                },
                columns: [
                    {data: 'employee_name', name: 'employee_name'},
                    {data: 'month_year', name: 'month_year'},
                    {
                        data: 'basic_salary',
                        name: 'basic_salary',
                        render: function (data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: null,
                        name: 'allowances',
                        render: function (data) {
                            const allowances = parseFloat(data.housing_allowance) +
                                parseFloat(data.transportation_allowance) +
                                parseFloat(data.food_allowance) +
                                parseFloat(data.phone_allowance) +
                                parseFloat(data.other_allowance);
                            return allowances.toFixed(2);
                        }
                    },
                    {
                        data: null,
                        name: 'overtime',
                        render: function (data) {
                            return `${parseFloat(data.overtime_hours).toFixed(2)} hrs / ${parseFloat(data.overtime_amount).toFixed(2)}`;
                        }
                    },
                    {
                        data: 'bonus',
                        name: 'bonus',
                        render: function (data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: null,
                        name: 'deductions',
                        render: function (data) {
                            const deductions = parseFloat(data.deductions) + parseFloat(data.loan_deduction);
                            return deductions.toFixed(2);
                        }
                    },
                    {
                        data: 'total_salary',
                        name: 'total_salary',
                        render: function (data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {data: 'payment_date', name: 'payment_date'},
                    {
                        data: 'status',
                        name: 'status',
                        render: function (data) {
                            let statusClass = '';
                            switch (data) {
                                case 'pending':
                                    statusClass = 'status-pending';
                                    break;
                                case 'paid':
                                    statusClass = 'status-paid';
                                    break;
                                case 'cancelled':
                                    statusClass = 'status-cancelled';
                                    break;
                            }
                            return `<span class="status-pill ${statusClass}">${data.toUpperCase()}</span>`;
                        }
                    },
                    GLOBAL_FN.dataTable.optionButton()
                ],
                order: [[8, 'desc'], [1, 'desc']], // Order by payment_date and month_year descending
                responsive: true,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pageLength: 25,
                initComplete: function () {
                    MONTHLY_SALARY.form.open();
                    webDataTable.actions.menu();
                }
            });

            $('#customSearch').on('keyup', function () {
                table.search(this.value).draw();
            });
        },
        extraActions(row) {
            MONTHLY_SALARY.list.actions.statusChange(row);
            MONTHLY_SALARY.list.actions.view(row);
            MONTHLY_SALARY.list.actions.delete(row);
        },
        actions: {
            statusChange(row) {
                $('#row_paid,#row_cancelled').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('payroll/monthly/salary/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
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
                    $.get('/payroll/monthly/salary/' + id + '/overview', function (data) {
                        $('#moduleOverview').html(data);
                    });
                });
            },
            print(row) {
                $('#row_print').off().on('click', function () {
                    let id = row.attr('data-id');
                    MONTHLY_SALARY.printPreview(id);
                });
            }
        },
    },
    form: {
        load() {
            MONTHLY_SALARY.form.open();
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'New Attendance',
                    url: GLOBAL_FN.buildUrl('payroll/monthly/salary/create'),
                    content: {
                        id: null
                    },
                    size: 'md',
                    scroll: false,
                });
            });
        },
        openCallback() {
            MONTHLY_SALARY.form.calculateTotalSalary();
            MONTHLY_SALARY.form.fetchBasicSalary();
        },
        calculateTotalSalary() {
            $('#basic_salary,.allowance,.addition,.deduction').off().on('change', function () {
                let basicSalary = noComma($('#basic_salary').val()) || 0;

                // Sum all allowances
                let allowances = 0;
                $('.allowance').each((i, el) => {
                    allowances += noComma($(el).val()) || 0;
                });

                // Sum all additions
                let additions = 0;
                $('.addition').each((i, el) => {
                    additions += noComma($(el).val()) || 0;
                })

                // Sum all deductions
                let deductions = 0;
                $('.deduction').each((i, el) => {
                    deductions += noComma($(el).val()) || 0;
                })

                // Calculate total
                let total = basicSalary + allowances + additions - deductions;
                $('#total_salary').val(total.toFixed(2));
            });
        }, fetchBasicSalary() {
            $('#fetch-basic-salary').off().on('click', function () {
                let employeeId = $('#employee_id').val();
                if (!employeeId) {
                    toastr.error("Please select an employee");
                }
                GLOBAL_FN.ajaxData.sendData(
                    '/payroll/monthly/salary/get-employee-basic-salary',
                    MONTHLY_SALARY.form.setBasicSalaryData,
                    {data: {employee_id: employeeId}}
                );
            })
        },
        setBasicSalaryData(data) {
            console.log(data)
            $('#basic_salary').val(data.data.basic_salary);
            $('#housing_allowance').val(data.data.housing_allowance);
            $('#transportation_allowance').val(data.data.transportation_allowance);
            $('#food_allowance').val(data.data.food_allowance);
            $('#phone_allowance').val(data.data.phone_allowance);
            $('#other_allowance').val(data.data.other_allowance);
        }
    }
};
