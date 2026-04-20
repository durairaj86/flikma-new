ASSET = {
    title: 'Asset',
    baseUrl: 'finance/asset',
    actionUrl: 'finance/asset',
    load() {
        ASSET.form.load();
        ASSET.filter.load();
        ASSET.list.load();
        //ASSET.events && ASSET.events.bind && ASSET.events.bind();
        datepicker();
    },
    /*events: {
        bind() {
            // Delegate click for Generate Schedule button even when content is injected via AJAX
            $(document).off('click', '#gen-schedule').on('click', '#gen-schedule', function (e) {
                e.preventDefault();
                const btn = $(this);
                const assetId = btn.data('id') || $('#moduleOverview').find('[data-asset-id]').data('asset-id') || $('[data-asset-id]').data('asset-id');
                // Fallback: try to extract from URL inside print/download buttons
                let id = assetId;
                if (!id) {
                    const printBtn = $(document).find('[onclick^="ASSET.printPreview("]');
                    if (printBtn.length) {
                        const m = printBtn.attr('onclick').match(/printPreview\('?(\d+)'?\)/);
                        if (m) id = m[1];
                    }
                }
                if (!id) {
                    console.error('Asset ID not found for generating schedule');
                    toastr && toastr.error('Asset not identified');
                    return;
                }
                const url = '/finance/asset/' + id + '/generate-schedule';
                const token = document.querySelector('meta[name="csrf-token"]')?.content;
                btn.prop('disabled', true).addClass('disabled');
                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: token ? {'X-CSRF-TOKEN': token} : {},
                    success: function (res) {
                        toastr && toastr.success(res?.message || 'Schedule generated');
                        // If drawer overview is open, reload its content; otherwise refresh page/list
                        if ($('#moduleOverview').length) {
                            $.get('/finance/asset/' + id + '/overview', function (data) {
                                $('#moduleOverview').html(data);
                            });
                        } else {
                            location.reload();
                        }
                    },
                    error: function (xhr) {
                        const msg = xhr?.responseJSON?.message || 'Failed to generate schedule';
                        toastr && toastr.error(msg);
                    },
                    complete: function () {
                        btn.prop('disabled', false).removeClass('disabled');
                    }
                });
            });
        }
    },*/
    filter: {
        load() {
            // Apply filter and search
            $('#apply-filter').off().on('click', function () {
                ASSET.list.dataTable();
                FILTER && FILTER.filteredColumn && FILTER.filteredColumn();
            });
            $('#customSearch').off('keyup').on('keyup', function () {
                ASSET.list.dataTable();
            });
        },
        default() {
            let data = {};
            let params = new URLSearchParams($('#list-filter').serialize());
            params.forEach((value, key) => {
                if (data[key]) {
                    data[key] = [].concat(data[key], value);
                } else {
                    data[key] = value;
                }
            });
            data['limit'] = 25;
            data['customSearch'] = $('#customSearch').val();
            return data;
        }
    },
    printPreview(printId) {
        const iframe = document.getElementById('print-frame');
        if (!iframe) {
            const newFrame = document.createElement('iframe');
            newFrame.id = 'print-frame';
            newFrame.style.display = 'none';
            document.body.appendChild(newFrame);
        }
        const frame = document.getElementById('print-frame');
        frame.onload = function () {
            try {
                frame.contentWindow.focus();
                frame.contentWindow.print();
            } catch (e) {
                console.error('Print failed', e);
            }
        };
        frame.src = '/' + ASSET.baseUrl + '/' + printId + '/print';
    },
    downloadPDF(printId) {
        fetch('/' + ASSET.baseUrl + '/' + printId + '/print')
            .then(res => res.text())
            .then(html => {
                const container = document.createElement('div');
                container.id = 'html-pdf';
                container.className = 'px-4 pt-4';
                container.innerHTML = html;
                const opt = {
                    margin: 0.2,
                    filename: `asset-${printId}.pdf`,
                };
                html2pdf().set(opt).from(container).save();
            });
    },
    list: {
        load(activeTab) {
            ASSET.list.dataTable(activeTab);
        },
        dataTable(activeTab = null) {
            GLOBAL_FN.destroyDataTable && GLOBAL_FN.destroyDataTable();
            activeTab = (activeTab && (typeof activeTab !== 'object')) ? activeTab : $("#listTabs").find('li button.active').attr('id');
            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                orderable: false,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                ajax: {
                    url: GLOBAL_FN.buildUrl(ASSET.baseUrl + '/data'),
                    type: 'POST',
                    data: function (d) {
                        d.filterData = ASSET.filter.default();
                    },
                    dataSrc: function (json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: [8, 9, 10], searchable: false},
                    {targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11], orderable: false},
                ],
                columns: [
                    {
                        data: 'row_no', render: function (data, type, row) {
                            return '<strong>' + (row.row_no ?? row.id) + '</strong>';
                        }
                    },
                    {
                        data: 'name_en', render: function (d, t, r) {
                            return r.name_en ?? '-';
                        }
                    },
                    {data: 'category_name', defaultContent: '-'},
                    {data: 'acquisition_date', defaultContent: '-'},
                    {data: 'supplier_name', defaultContent: '-'},
                    {data: 'invoice_number', defaultContent: '-'},
                    {data: 'invoice_date', defaultContent: '-'},
                    {data: 'status_badge', defaultContent: '-', className: 'text-center'},
                    {
                        data: 'cost', className: 'text-end', render: function (d) {
                            return Number(d ?? 0).toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {data: 'accumulated', className: 'text-end'},
                    {data: 'book_value', className: 'text-end'},
                    GLOBAL_FN.dataTable.optionButton()
                ],
                initComplete: function () {
                    ASSET.form.open();
                    webDataTable.actions.menu();
                }
            });
            $('#customSearch').on('keyup', function () {
                table.search(this.value).draw();
            });
            $('#dataTable_filter').closest('div.row').remove();
            webDataTable.loader(table);
            webDataTable.search(table);
        },
        extraActions(row) {
            ASSET.list.actions.view(row);
            ASSET.list.actions.generateSchedule(row);
        },
        actions: {
            view(row) {
                $('#row_view').off().on('click', function () {
                    let assetId = row.attr('data-id');

                    // Open drawer
                    let drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
                    drawer.show();

                    // Load Overview
                    $('#moduleOverview').html('<p>Loading...</p>');
                    $.get('/finance/asset/' + assetId + '/overview', function (data) {
                        $('#moduleOverview').html(data);
                    });
                });
            },
            generateSchedule(row) {
                $('#row_generate_schedule').off().on('click', function () {
                    const id = row.attr('data-id');
                    if (!id) {
                        toastr && toastr.error('Asset not identified');
                        return;
                    }
                    const url = '/finance/asset/' + id + '/generate-schedule';
                    const token = document.querySelector('meta[name="csrf-token"]')?.content;
                    $.ajax({
                        url: url,
                        type: 'POST',
                        headers: token ? {'X-CSRF-TOKEN': token} : {},
                        success: function (res) {
                            toastr && toastr.success(res?.message || 'Schedule generated');
                            // Refresh table and, if drawer open for same asset, reload overview
                            if ($.fn.DataTable && $('#dataTable').length) {
                                $('#dataTable').DataTable().ajax.reload(null, false);
                            }
                            if ($('#moduleOverview').length) {
                                $.get('/finance/asset/' + id + '/overview', function (data) {
                                    $('#moduleOverview').html(data);
                                });
                            }
                        },
                        error: function (xhr) {
                            const msg = xhr?.responseJSON?.message || 'Failed to generate schedule';
                            toastr && toastr.error(msg);
                        }
                    });
                });
            }
        }
    },
    form: {
        load() {
            ASSET.form.open();
        },
        open() {
            $('#new').off().on('click', function () {
                webModal.openGlobalModal({
                    title: 'New Asset',
                    url: GLOBAL_FN.buildUrl(ASSET.baseUrl + '/create'),
                    content: null,
                    size: 'md',
                    scroll: false,
                });
            })
        },
    },
};
