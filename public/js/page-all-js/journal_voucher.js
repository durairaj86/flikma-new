JOURNAL_VOUCHER = {
    title: 'Journal Voucher',
    baseUrl: 'finance/journal-vouchers',
    actionUrl: 'finance/journal-vouchers',
    currentTab: 'all',
    load() {
        JOURNAL_VOUCHER.form.load();
    },

    list: {
        load(activeTab) {
            JOURNAL_VOUCHER.list.dataTable(activeTab);

            // Tab change event
            /*$('#journalVoucherTabs button').off().on('click', function(e) {
                JOURNAL_VOUCHER.currentTab = $(this).attr('id').replace('-tab', '');
                JOURNAL_VOUCHER.list.dataTable(JOURNAL_VOUCHER.currentTab);
            });*/
        },

        dataTable(activeTab = null) {
            GLOBAL_FN.destroyDataTable();
            activeTab = (activeTab && (typeof activeTab !== 'object')) ? activeTab : $("#listTabs").find('li button.active').attr('id');
            console.log(activeTab);
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                /*order: [[1, 'desc']],*/
                ajax: {
                    url: GLOBAL_FN.buildUrl('finance/journal-vouchers/data'),
                    type: 'POST',
                    data: {
                        'tab': activeTab
                    },
                    dataSrc: function (json) {
                        // Update status counts
                        $('#dataTable tbody').find('.loading-row').remove();
                        GLOBAL_FN.setStatusCounts(json.statusCounts);
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0], searchable: false},
                    {targets: [0], orderable: false},
                ],
                columns: [
                    {data: 'DT_RowIndex', class: 'hide-tooltip fav-index'},
                    {data: 'row_no', name: 'row_no'},
                    {data: 'voucher_type', name: 'voucher_type'},
                    {data: 'job_no', name: 'job_no'},
                    {data: 'voucher_date', name: 'voucher_date'},
                    {data: 'reference_no', name: 'reference_no'},
                    {data: 'currency', name: 'currency'},
                    {data: 'debit_total', name: 'debit_total'},
                    {data: 'credit_total', name: 'credit_total'},
                    {data: 'status', name: 'status'},
                    // Actions column
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {
                    search: "" // removes "Search:" label
                },
                deferLoading: 0, // don't load immediately
                initComplete: function () {
                    JOURNAL_VOUCHER.form.open();
                    webDataTable.actions.menu();
                },
                createdRow: function (row, data, dataIndex) {
                    $(row).addClass('row-item');
                    $(row).attr('data-id', data.id);
                    $(row).attr('data-name', 'Journal Voucher #' + data.row_no);
                    $(row).attr('id', 'journal-voucher-' + data.id);
                }
            });

            $('#customSearch').off('keyup').on('keyup', function () {
                table.search(this.value).draw();
            });

            // Initialize table utilities
            webDataTable.loader(table);
            webDataTable.search(table);
        },

        extraActions(row) {
            JOURNAL_VOUCHER.list.actions.statusChange(row);
            JOURNAL_VOUCHER.list.actions.view(row);
            JOURNAL_VOUCHER.list.actions.print(row);
            JOURNAL_VOUCHER.list.actions.download(row);
        },

        actions: {
            statusChange(row) {
                $('#row_approved, #row_draft, #row_disapproved').off().on('click', function () {
                    const id = row.attr('data-id');
                    const status = $(this).attr('data-value');

                    if ($(this).attr('id') === 'row_disapproved') {
                        $('#journal_voucher_id').val(id);
                        $('#disapprovalReasonModal').modal('show');
                    } else {
                        JOURNAL_VOUCHER.actions.updateStatus(id, status);
                    }
                });
            },

            view(row) {
                $('#row_view').off().on('click', function () {
                    const id = row.attr('data-id');
                    window.location.href = `/${JOURNAL_VOUCHER.baseUrl}/${id}`;
                });
            },

            edit(row) {
                $('#row_edit').off().on('click', function () {
                    const id = row.attr('data-id');
                    JOURNAL_VOUCHER.form.edit(id);
                });
            },

            delete(row) {
                $('#row_delete').off().on('click', function () {
                    const id = row.attr('data-id');
                    const name = row.attr('data-name');

                    if (confirm(`Are you sure you want to delete ${name}?`)) {
                        $.ajax({
                            url: GLOBAL_FN.buildUrl(`${JOURNAL_VOUCHER.baseUrl}/${id}`),
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (response) {
                                toastr.success(response.message);
                                GLOBAL_FN.refreshDataTable();
                            },
                            error: function (xhr) {
                                toastr.error(xhr.responseJSON?.message || 'Error deleting journal voucher');
                            }
                        });
                    }
                });
            },

            print(row) {
                $('#row_print').off().on('click', function () {
                    const id = row.attr('data-id');
                    JOURNAL_VOUCHER.printPreview(id);
                });
            },

            download(row) {
                $('#row_download').off().on('click', function () {
                    const id = row.attr('data-id');
                    JOURNAL_VOUCHER.downloadPDF(id);
                });
            }
        }
    },

    actions: {
        updateStatus: function (id, status) {
            $.ajax({
                url: GLOBAL_FN.buildUrl(`${JOURNAL_VOUCHER.baseUrl}/${id}/status/${status}`),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    toastr.success(response.message);
                    GLOBAL_FN.refreshDataTable();
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error updating status');
                }
            });
        },

        submitDisapprovalReason: function () {
            const id = $('#journal_voucher_id').val();
            const reason = $('#reason').val();

            if (!reason) {
                toastr.error('Please provide a reason for disapproval');
                return;
            }

            $.ajax({
                url: GLOBAL_FN.buildUrl(`${JOURNAL_VOUCHER.baseUrl}/${id}/disapprove`),
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    reason: reason
                },
                success: function (response) {
                    $('#disapprovalReasonModal').modal('hide');
                    $('#reason').val('');
                    toastr.success(response.message);
                    GLOBAL_FN.refreshDataTable();
                },
                error: function (xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error disapproving journal voucher');
                }
            });
        }
    },
    before: {
        submit() {
            // Add any validation logic here
            return true;
        }
    },

    form: {
        load() {
            JOURNAL_VOUCHER.form.open();
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'Create Journal Voucher',
                    url: GLOBAL_FN.buildUrl(`${JOURNAL_VOUCHER.baseUrl}/create`),
                    content: null,
                    size: 'xl',
                    scroll: true,
                    minHeight: 'min-height:70vh;',
                });
            });
        },

        edit(id) {
            webModal.openGlobalModal({
                title: 'Edit Journal Voucher',
                url: GLOBAL_FN.buildUrl(`${JOURNAL_VOUCHER.baseUrl}/${id}/edit`),
                content: null,
                size: 'xl',
                scroll: true,
                minHeight: 'min-height:70vh;',
            });
        },

        openCallback() {
            // Initialize form elements and event handlers

            // Form submission
            $('#moduleForm').off('submit').on('submit', function (e) {
                return JOURNAL_VOUCHER.form.prepareFormData($(this));
            });
        },

        prepareFormData($form) {
            // Add any form data preparation logic here
            return true;
        },

        formatNumber(num) {
            return parseFloat(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }
    },

    printPreview: function (id) {
        window.open(`/${JOURNAL_VOUCHER.baseUrl}/${id}/print`, '_blank');
    },

    downloadPDF: function (id) {
        window.open(`/${JOURNAL_VOUCHER.baseUrl}/${id}/download`, '_blank');
    }
};

// Initialize when document is ready
$(document).ready(function() {
    // Initialize the submitDisapprovalReason button click event
    $('#submitDisapprovalReason').off().on('click', function() {
        JOURNAL_VOUCHER.actions.submitDisapprovalReason();
    });
});
