QUOTATION_TERM = {
    title: 'Quotation Term',
    baseUrl: 'masters/quotation-term',
    actionUrl: 'masters/quotation-term',
    load() {
        QUOTATION_TERM.form.open();
    },
    list: {
        load() {
            QUOTATION_TERM.list.dataTable();
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
                    url: '/masters/quotation-term/data',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [0], searchable: false},
                    {targets: [0], orderable: false},
                ],
                columns: [
                    {data: 'DT_RowIndex', class: 'text-center hide-tooltip fav-index'},
                    {data: 'title', render: (data, type, row) => row.title},
                    {data: 'is_general', render: (data, type, row) => row.is_general},
                    {data: 'terms', render: (data, type, row) => `<span class="text-muted small">${row.terms}</span>`},
                    GLOBAL_FN.dataTable.optionButton()
                ],
                language: {
                    search: ""
                },
                deferLoading: 0,
                initComplete: function () {
                    QUOTATION_TERM.form.open();
                    webDataTable.actions.menu();
                }
            });
            $('#customSearch').on('keyup', function () {
                table.search(this.value).draw();
            });
            webDataTable.loader(table);
            webDataTable.search(table);
        },
        extraActions(row) {
            QUOTATION_TERM.list.actions.delete(row);
        },
        actions: {
            delete(row) {
                $('#row_delete').off().on('click', function () {
                    $.confirm({
                        title: 'Confirm Delete',
                        content: 'Are you sure you want to delete this quotation term?',
                        type: 'red',
                        buttons: {
                            cancel: function () {
                            },
                            delete: {
                                text: 'Delete',
                                btnClass: 'btn-red',
                                action: function () {
                                    $.ajax({
                                        url: GLOBAL_FN.buildUrl('masters/quotation-term/' + row.attr('data-id')),
                                        type: 'DELETE',
                                        dataType: 'json',
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        success: function () {
                                            $('#dataTable').DataTable().ajax.reload(null, false);
                                        },
                                        error: function () {
                                            $.alert('Failed to delete this quotation term.');
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
                    title: 'New Quotation Term',
                    url: GLOBAL_FN.buildUrl('masters/quotation-term/create'),
                    content: null,
                    size: 'md',
                    callBack: null
                });
            })
        },
        // Toggle the Activity field based on the General switch — general
        // terms apply to every activity, so there's nothing to pick.
        openCallback() {
            function syncActivityVisibility() {
                let isGeneral = $('#is_general').is(':checked');
                $('#activityFieldWrapper').toggle(!isGeneral);
                $('#activity_id').prop('required', !isGeneral);
            }

            $('#is_general').off('change').on('change', syncActivityVisibility);
            syncActivityVisibility();

            QUOTATION_TERM.form.initEditor();
        },
        // Quill has no name attribute of its own, so the modal's generic
        // FormData(form) submit (see startup.js submitForm()) would never
        // pick up what was typed — #terms-hidden is the real submitted
        // field, kept in sync on every Quill edit and again right before
        // submit (before.submit, below) as a final safety net.
        initEditor() {
            let hidden = document.getElementById('terms-hidden');
            let container = document.getElementById('terms-editor');
            if (!hidden || !container) return;

            let quill = new Quill(container, {
                theme: 'snow',
                modules: {
                    toolbar: [
                        [{header: [2, 3, 4, false]}],
                        ['bold', 'italic'],
                        [{align: []}],
                    ],
                },
            });

            if (hidden.value) {
                quill.clipboard.dangerouslyPasteHTML(hidden.value);
            }

            quill.on('text-change', function () {
                hidden.value = quill.root.innerHTML;
            });

            QUOTATION_TERM._quill = quill;
        },
    },
    before: {
        submit() {
            let quill = QUOTATION_TERM._quill;
            if (quill) {
                document.getElementById('terms-hidden').value = quill.root.innerHTML;
                if (quill.getText().trim().length === 0) {
                    toastr.error('Terms & Conditions text is required.');
                    return false;
                }
            }
            return true;
        }
    },
}
