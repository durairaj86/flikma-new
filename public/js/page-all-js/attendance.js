/**
 * Attendance Module JavaScript
 */
ATTENDANCE = {
    title: 'Attendance',
    baseUrl: 'payroll/attendance',
    actionUrl: 'payroll/attendance',
    load() {
        ATTENDANCE.form.load();
        ATTENDANCE.filter.load();
        //ATTENDANCE.utils.initializeTomSelect();
        ATTENDANCE.calendar.load();
        initTomSelectForm($('#content-wrapper'));//due to livewire delay load. it use here
    },
    filter: {
        load: function () {
            ATTENDANCE.filter.filterBox();
            //ATTENDANCE.filter.togglePanel();
        },
        filterBox: function () {
            $('#apply-filter').off().on({
                click: function () {
                    ATTENDANCE.list.dataTable();
                }
            });
        },
        /*togglePanel: function() {
            $('#filter-box').off().on('click', function () {
                $('#filterPanel').toggleClass('d-none');
            });
        },*/
        default: function () {
            let data = {};
            data['month'] = $('#filter-month').val();
            data['year'] = $('#filter-year').val();
            data['employee_id'] = $('#filter-employee').val();
            //data['_token'] = $('meta[name="csrf-token"]').attr('content');
            return data;
        }
    },
    utils: {
        /*initializeTomSelect: function () {
            document.querySelectorAll('.tom-select').forEach(function (el) {
                new TomSelect(el, {
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    }
                });
            });
        },*/
        openAttendanceModal: function (id = null, employeeId = null, date = null) {
            webModal.openGlobalModal({
                title: 'New Attendance',
                url: GLOBAL_FN.buildUrl(id ? 'payroll/attendance/' + id + '/create' : 'payroll/attendance/create'),
                content: {
                    id: id
                },
                size: 'md',
                scroll: false,
            });


            /*let url = GLOBAL_FN.buildUrl('payroll/attendance/create');

            if (id) {
                url = GLOBAL_FN.buildUrl(`payroll/attendance/${id}/edit`);
            }

            $.ajax({
                url: url,
                type: 'GET',
                success: function (response) {
                    $('#modal-default .modal-content').html(response);
                    $('#modal-default').modal('show');

                    // If creating new record with pre-filled data
                    if (!id && employeeId) {
                        $('#employee_id').val(employeeId);
                    }

                    if (!id && date) {
                        $('#date').val(date);
                    }

                    // Initialize datepicker if needed
                    if ($('.datepicker').length) {
                        $('.datepicker').datepicker({
                            format: 'dd-mm-yyyy',
                            autoclose: true
                        });
                    }
                },
                error: function () {
                    Swal.fire(
                        'Error!',
                        'Failed to load the form.',
                        'error'
                    );
                }
            });*/
        }
    },
    list: {
        load() {
            ATTENDANCE.list.dataTable();
        },
        dataTable() {
            GLOBAL_FN.destroyDataTable();
            let table = $('#dataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: GLOBAL_FN.buildUrl('payroll/attendance/data'),
                    type: 'POST',
                    data: function (d) {
                        d.filterData = ATTENDANCE.filter.default();
                    }
                },
                columns: [
                    {data: 'employee_name', name: 'employee_name'},
                    {data: 'formatted_date', name: 'date'},
                    {data: 'day_of_week', name: 'day_of_week'},
                    {
                        data: 'check_in',
                        name: 'check_in',
                        render: function (data) {
                            return data ? data : 'N/A';
                        }
                    },
                    {
                        data: 'check_out',
                        name: 'check_out',
                        render: function (data) {
                            return data ? data : 'N/A';
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function (data) {
                            let statusClass = 'status-' + data;
                            let statusText = data.charAt(0).toUpperCase() + data.slice(1);
                            return `<span class="badge ${statusClass}">${statusText}</span>`;
                        }
                    },
                    {
                        data: 'remarks',
                        name: 'remarks',
                        render: function (data) {
                            return data ? data : '';
                        }
                    },
                    {data: 'actions', name: 'actions', orderable: false, searchable: false}
                ],
                order: [[1, 'desc']],
                initComplete: function () {
                    //ATTENDANCE.form.open();
                    ATTENDANCE.list.actions.edit();
                    ATTENDANCE.list.actions.delete();
                    webDataTable.actions.menu();
                }
            });
        },
        actions: {
            edit() {
                $(document).off('click', '.edit-record').on('click', '.edit-record', function () {
                    const id = $(this).data('id');
                    ATTENDANCE.utils.openAttendanceModal(id);
                });
            },
            delete() {
                $(document).off('click', '.delete-record').on('click', '.delete-record', function () {
                    const id = $(this).data('id');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: GLOBAL_FN.buildUrl(`payroll/attendance/${id}/delete`),
                                type: 'DELETE',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function (response) {
                                    if (response.success) {
                                        Swal.fire(
                                            'Deleted!',
                                            response.message,
                                            'success'
                                        );
                                        ATTENDANCE.list.dataTable();
                                        ATTENDANCE.calendar.update();
                                    } else {
                                        Swal.fire(
                                            'Error!',
                                            response.message,
                                            'error'
                                        );
                                    }
                                },
                                error: function (xhr) {
                                    Swal.fire(
                                        'Error!',
                                        'Something went wrong.',
                                        'error'
                                    );
                                }
                            });
                        }
                    });
                });
            },
            /*save() {
                $(document).off('click', '#save-attendance').on('click', '#save-attendance', function () {
                    const form = $('#attendance-form');

                    // Validate form
                    if (!form[0].checkValidity()) {
                        form.addClass('was-validated');
                        return;
                    }

                    const formData = form.serialize();
                    const id = $('input[name="data-id"]').val();
                    const url = id ?
                        GLOBAL_FN.buildUrl(`payroll/attendance/${id}/create`) :
                        GLOBAL_FN.buildUrl('payroll/attendance/create');

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: formData,
                        success: function (response) {
                            if (response.success) {
                                $('#modal-default').modal('hide');
                                Swal.fire(
                                    'Success!',
                                    response.message,
                                    'success'
                                );
                                ATTENDANCE.list.dataTable();
                                ATTENDANCE.calendar.update();
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function (xhr) {
                            let errorMessage = 'Something went wrong.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            }
                            Swal.fire(
                                'Error!',
                                errorMessage,
                                'error'
                            );
                        }
                    });
                });
            }*/
        }
    },
    form: {
        load() {
            ATTENDANCE.form.open();
        },
        open() {
            $('#new').off().on('click', function () {
                ATTENDANCE.utils.openAttendanceModal();
            });
        }
    },
    calendar: {
        load() {
            ATTENDANCE.calendar.cellClick();
        },
        update() {
            // This function is called to update the calendar after changes
            // Calendar functionality is handled by Livewire
            if (typeof updateCalendar === 'function') {
                updateCalendar();
            }
        },
        cellClick() {
            $(document).off('click', '.attendance-cell').on('click', '.attendance-cell', function () {
                const attendanceId = $(this).data('attendance-id');
                const employeeId = $(this).data('employee-id');
                const date = $(this).data('date');

                if (attendanceId) {
                    // Edit existing attendance
                    ATTENDANCE.utils.openAttendanceModal(attendanceId);
                } else {
                    // Create new attendance for this date and employee
                    ATTENDANCE.utils.openAttendanceModal(null, employeeId, date);
                }
            });
        }
    }
};
