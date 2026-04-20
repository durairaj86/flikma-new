EMPLOYEE_LOAN = {
    title: 'Employee Loan',
    baseUrl: 'payroll/employee/loan',
    actionUrl: 'payroll/employee/loan',
    load() {
        EMPLOYEE_LOAN.form.load();
        EMPLOYEE_LOAN.filter.load();
        datepicker();
    },
    filter: {
        load: function () {
            EMPLOYEE_LOAN.filter.filterBox();
            EMPLOYEE_LOAN.filter.searchBox();
        },
        filterBox: function () {
            $('#apply-filter').off().on({
                click: function () {
                    EMPLOYEE_LOAN.list.dataTable();
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
                        EMPLOYEE_LOAN.list.dataTable();
                        return;
                    }

                    // Otherwise, debounce the search to avoid too many requests
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function () {
                        EMPLOYEE_LOAN.list.dataTable();
                    }, 500); // Wait 500ms after user stops typing
                }
            });
        },
        default: function () {
            let data = {};
            data['employee_id'] = $('#filter-employee').val();
            data['from_date'] = $('#filter-from-date').val();
            data['to_date'] = $('#filter-to-date').val();
            data['status'] = $('#filter-status').val();
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
        iframe.src = '/' + EMPLOYEE_LOAN.baseUrl + '/' + printId + '/print';
    },
    list: {
        load() {
            EMPLOYEE_LOAN.list.dataTable();
        },
        dataTable() {
            GLOBAL_FN.destroyDataTable();
            let table = $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: GLOBAL_FN.buildUrl('payroll/employee/loan/data'),
                    type: 'POST',
                    data: function (d) {
                        d.filterData = EMPLOYEE_LOAN.filter.default();
                    }
                },
                columns: [
                    {data: 'employee_name', name: 'employee_name'},
                    {
                        data: 'loan_amount',
                        name: 'loan_amount',
                        render: function (data) {
                            return parseFloat(data).toFixed(2);
                        }
                    },
                    {
                        data: null,
                        name: 'installment',
                        render: function (data) {
                            return `${parseFloat(data.installment_amount).toFixed(2)} x ${data.number_of_installments}`;
                        }
                    },
                    {
                        data: null,
                        name: 'remaining',
                        render: function (data) {
                            return `${parseFloat(data.remaining_amount).toFixed(2)} (${data.remaining_installments} installments)`;
                        }
                    },
                    {data: 'loan_date', name: 'loan_date'},
                    {data: 'first_payment_date', name: 'first_payment_date'},
                    {
                        data: 'payment_method',
                        name: 'payment_method',
                        render: function (data) {
                            return data.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function (data) {
                            let statusClass = `status-${data}`;
                            return `<span class="status-pill ${statusClass}">${data.replace('_', ' ').toUpperCase()}</span>`;
                        }
                    },
                    GLOBAL_FN.dataTable.optionButton()
                ],
                order: [[4, 'desc']], // Order by loan_date descending
                responsive: true,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pageLength: 25,
                initComplete: function () {
                    EMPLOYEE_LOAN.form.open();
                    webDataTable.actions.menu();
                }
            });

            $('#customSearch').on('keyup', function () {
                table.search(this.value).draw();
            });
        },
        extraActions(row) {
            EMPLOYEE_LOAN.list.actions.statusChange(row);
            EMPLOYEE_LOAN.list.actions.view(row);
            EMPLOYEE_LOAN.list.actions.delete(row);
        },
        actions: {
            statusChange(row) {
                $('#row_approved,#row_paid,#row_partially_paid,#row_rejected').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(GLOBAL_FN.buildUrl('payroll/employee/loan/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
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
                    $.get('/payroll/employee/loan/' + id + '/overview', function (data) {
                        $('#moduleOverview').html(data);
                    });
                });
            },
            print(row) {
                $('#row_print').off().on('click', function () {
                    let id = row.attr('data-id');
                    EMPLOYEE_LOAN.printPreview(id);
                });
            }
        }
    },
    form: {
        load() {
            EMPLOYEE_LOAN.form.open();
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'New Attendance',
                    url: GLOBAL_FN.buildUrl('payroll/employee/loan/create'),
                    content: {
                        id: null
                    },
                    size: 'md',
                    scroll: false,
                });
            });
        },
        openCallback() {
            EMPLOYEE_LOAN.form.calculateInstallmentAmount();
        },
        calculateInstallmentAmount() {
            $('#loan_amount,#interest_rate,#number_of_installments').off().on('change', function () {
                let loanAmount = noComma($('#loan_amount').val()) || 0;
                let interestRate = noComma($('#interest_rate').val()) || 0;
                let numberOfInstallments = noComma($('#number_of_installments').val()) || 1;

                // Calculate total amount with interest
                let totalAmount = loanAmount * (1 + (interestRate / 100));

                // Calculate installment amount
                let installmentAmount = totalAmount / numberOfInstallments;

                $('#installment_amount').val(installmentAmount.toFixed(2));

                // Set initial remaining values for new loans
                if (!$('#data-id').val()) {
                    $('#remaining_amount').val(loanAmount.toFixed(2));
                    $('#remaining_installments').val(numberOfInstallments);
                }
            })
        }
    }
};
