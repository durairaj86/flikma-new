QUOTATION_NEW = {
    title: 'Quotation',
    baseUrl: 'sales/quotations-new',

    load() {
        if ($('#dataTable').length) {
            QUOTATION_NEW.list.load();
            QUOTATION_NEW.filter.load();
        }
        if ($('#wizardForm').length) {
            QUOTATION_NEW.wizard.load();
        }
        datepicker();
    },

    // ─── Filter ───────────────────────────────────────────────────────────
    filter: {
        load() {
            $('#apply-filter').off().on('click', function () {
                QUOTATION_NEW.list.dataTable();
                if (typeof FILTER !== 'undefined') FILTER.filteredColumn();
            });
        },
        default(status) {
            let data = {};
            let params = new URLSearchParams($('#list-filter').serialize());
            params.forEach((value, key) => {
                if (data[key]) {
                    data[key] = [].concat(data[key], value);
                } else {
                    data[key] = value;
                }
            });
            data.tab = status ?? $('#listTabs').find('button.active').attr('id');
            data.customSearch = $('#customSearch').val();
            return data;
        }
    },

    // ─── List ─────────────────────────────────────────────────────────────
    list: {
        load(activeTab) {
            QUOTATION_NEW.list.dataTable(activeTab);
        },
        dataTable(activeTab = null) {
            GLOBAL_FN.destroyDataTable();
            activeTab = (activeTab && typeof activeTab !== 'object')
                ? activeTab
                : $('#listTabs').find('button.active').attr('id');

            // Non-orderable/non-searchable column indices (0-based)
            // 4=Status, 5=LatestComments, 11=UserName, 12=SalesPerson,
            // 15=Remarks, 16=ShipmentNo, 17=JobNo, 21=P.Sale, 22=P.Cost,
            // 23=GP, 24=GP%, 25=ShipperName, 26=ConsigneeName,
            // 27=ShipmentStatus, 28=ETD, 29=ETA, 30=OriginAgent,
            // 31=DestAgent, 32=EnquiryNo, 33=NoOfTeu, 34=ContainerType, 38=Actions
            let noSort = [4, 5, 11, 12, 15, 16, 17, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 38];

            let actionBtn = GLOBAL_FN.dataTable.optionButton();
            actionBtn.className = (actionBtn.className ? actionBtn.className + ' ' : '') + 'text-center';

            let table = $('#dataTable').DataTable({
                processing: false,
                serverSide: true,
                autoWidth: false,
                lengthChange: false,
                pageLength: 25,
                dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
                order: [[3, 'desc']],
                ajax: {
                    url: GLOBAL_FN.buildUrl('sales/quotations-new/data'),
                    type: 'POST',
                    data(d) {
                        d.tab = activeTab;
                        d.filterData = QUOTATION_NEW.filter.default();
                    },
                    dataSrc(json) {
                        $('#dataTable tbody').find('.loading-row').remove();
                        if (json.statusCounts) {
                            let counts = json.statusCounts;
                            $('#pendingCount').text(counts[1] ?? 0);
                            $('#approvedCount').text(counts[2] ?? 0);
                            $('#cancelledCount').text(counts[3] ?? 0);
                        }
                        return json.data;
                    }
                },
                columnDefs: [
                    {targets: noSort, orderable: false, searchable: false},
                ],
                columns: [
                    // 0 - Quote No (left sticky via CSS)
                    {data: 'row_no', defaultContent: '', className: 'fw-semibold'},
                    // 1 - Client
                    {data: 'client_name', defaultContent: ''},
                    // 2 - Branch
                    {data: 'branch', defaultContent: ''},
                    // 3 - Date
                    {data: 'quotation_date', defaultContent: ''},
                    // 4 - Status
                    {
                        data: 'status',
                        render(data) {
                            const map = {1: ['Pending','warning'], 2: ['Approved','success'], 3: ['Cancelled','danger']};
                            let [label, color] = map[data] ?? ['—', 'secondary'];
                            return `<span class="badge bg-${color}">${label}</span>`;
                        }
                    },
                    // 5 - Latest Comments
                    {data: null, defaultContent: ''},
                    // 6 - Operational Activity (department)
                    {data: 'department', defaultContent: ''},
                    // 7 - Origin (pol)
                    {data: 'pol', defaultContent: ''},
                    // 8 - Destination (pod)
                    {data: 'pod', defaultContent: ''},
                    // 9 - Valid From
                    {data: 'valid_from', defaultContent: ''},
                    // 10 - Valid To
                    {data: 'valid_to', defaultContent: ''},
                    // 11 - User Name
                    {data: 'user_name', defaultContent: ''},
                    // 12 - Sales Person
                    {data: null, defaultContent: ''},
                    // 13 - INCO Term
                    {data: 'inco_terms', defaultContent: ''},
                    // 14 - Carrier
                    {data: 'carrier', defaultContent: ''},
                    // 15 - Remarks
                    {data: 'remarks', defaultContent: ''},
                    // 16 - Shipment No.
                    {data: null, defaultContent: ''},
                    // 17 - Job No.
                    {data: null, defaultContent: ''},
                    // 18 - No.of Pcs
                    {data: 'no_of_pcs', defaultContent: '', className: 'text-end'},
                    // 19 - G.Weight
                    {data: 'gross_weight', defaultContent: '', className: 'text-end'},
                    // 20 - Volume
                    {data: 'volume', defaultContent: '', className: 'text-end'},
                    // 21 - P.Sale
                    {data: 'p_sale', defaultContent: '', className: 'text-end'},
                    // 22 - P.Cost
                    {data: 'p_cost', defaultContent: '', className: 'text-end'},
                    // 23 - GP
                    {data: 'gp', defaultContent: '', className: 'text-end'},
                    // 24 - GP%
                    {
                        data: 'gp_pct', defaultContent: '', className: 'text-end',
                        render(data) { return data !== '' && data !== null ? data + '%' : ''; }
                    },
                    // 25 - Shipper Name
                    {data: null, defaultContent: ''},
                    // 26 - Consignee Name
                    {data: null, defaultContent: ''},
                    // 27 - Shipment Status
                    {data: null, defaultContent: ''},
                    // 28 - ETD
                    {data: 'etd_fmt', defaultContent: ''},
                    // 29 - ETA
                    {data: 'eta_fmt', defaultContent: ''},
                    // 30 - Origin Agent
                    {data: null, defaultContent: ''},
                    // 31 - Destination Agent
                    {data: null, defaultContent: ''},
                    // 32 - Enquiry No
                    {data: null, defaultContent: ''},
                    // 33 - No Of Teu
                    {data: null, defaultContent: ''},
                    // 34 - Container Type
                    {data: null, defaultContent: ''},
                    // 35 - Vessel/Flight Name
                    {data: 'vessel_name', defaultContent: ''},
                    // 36 - Voyage/Flight No
                    {data: 'voyage_no', defaultContent: ''},
                    // 37 - Place Of Delivery
                    {data: 'place_of_delivery', defaultContent: ''},
                    // 38 - Edit (right sticky via CSS)
                    actionBtn,
                ],
                language: {search: ''},
                deferLoading: 0,
                initComplete() {
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
            QUOTATION_NEW.list.actions.statusChange(row);
            QUOTATION_NEW.list.actions.edit(row);
            QUOTATION_NEW.list.actions.view(row);
        },
        actions: {
            statusChange(row) {
                $('#row_approve,#row_pending,#row_cancel').off().on('click', function () {
                    let fd = new FormData();
                    changeCustomerStatus(
                        GLOBAL_FN.buildUrl(
                            'sales/quotations-new/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')
                        ),
                        {method: 'POST', data: fd, callBack: 'datatable'},
                        $(this).attr('data-value')
                    );
                });
            },
            edit(row) {
                $('#row_edit').off().on('click', function () {
                    webModal.openGlobalModal({
                        title: 'Edit Quotation',
                        url: GLOBAL_FN.buildUrl('sales/quotations-new/' + row.attr('data-id') + '/edit'),
                        size: 'xl',
                        scroll: false,
                    });
                });
            },
            view(row) {
                $('#row_view').off().on('click', function () {
                    window.location.href = GLOBAL_FN.buildUrl(
                        'sales/quotations-new/' + row.attr('data-id') + '/step5'
                    );
                });
            }
        }
    },

    // ─── Wizard ───────────────────────────────────────────────────────────
    wizard: {
        currentStep: 1,
        quotationId: null,

        load() {
            // Detect current step from URL
            let path = window.location.pathname;
            let stepMatch = path.match(/step(\d+)/);
            QUOTATION_NEW.wizard.currentStep = stepMatch ? parseInt(stepMatch[1]) : 1;
            QUOTATION_NEW.wizard.quotationId = $('[name="quotation_id"]').val() || null;

            QUOTATION_NEW.wizard.bindNext();
            QUOTATION_NEW.wizard.bindFinalise();
            QUOTATION_NEW.wizard.bindPortSelects();
            QUOTATION_NEW.wizard.bindCarrierSelect();
            QUOTATION_NEW.wizard.bindChargesTable();
            QUOTATION_NEW.wizard.bindChargeCalculations();
            QUOTATION_NEW.wizard.bindClientAddress();
        },

        bindNext() {
            $('#btn-next').off().on('click', function () {
                let step = QUOTATION_NEW.wizard.currentStep;
                let storeUrl = GLOBAL_FN.buildUrl('sales/quotations-new/step' + step + '/store');
                if (QUOTATION_NEW.wizard.quotationId) {
                    storeUrl = GLOBAL_FN.buildUrl('sales/quotations-new/' + QUOTATION_NEW.wizard.quotationId + '/step' + step + '/store');
                }

                let fd = new FormData($('#wizardForm')[0]);

                $.ajax({
                    url: storeUrl,
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success(res) {
                        if (res.status === 'success') {
                            QUOTATION_NEW.wizard.quotationId = res.id;
                            window.location.href = GLOBAL_FN.buildUrl(
                                'sales/quotations-new/' + res.id + '/step' + res.next
                            );
                        }
                    },
                    error(xhr) {
                        let msg = xhr.responseJSON?.message || 'Validation failed. Please check required fields.';
                        toastr.error(msg);
                        if (xhr.responseJSON?.errors) {
                            Object.values(xhr.responseJSON.errors).forEach(e => toastr.warning(e[0]));
                        }
                    }
                });
            });
        },

        bindFinalise() {
            $('#btn-finalise').off().on('click', function () {
                let id = $('[name="quotation_id"]').val();
                $.ajax({
                    url: GLOBAL_FN.buildUrl('sales/quotations-new/' + id + '/finalise'),
                    type: 'POST',
                    data: {_token: $('meta[name="csrf-token"]').attr('content')},
                    success(res) {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            setTimeout(() => {
                                window.location.href = GLOBAL_FN.buildUrl('sales/quotations-new');
                            }, 1200);
                        }
                    },
                    error() {
                        toastr.error('Failed to finalise quotation.');
                    }
                });
            });
        },

        bindPortSelects() {
            // Init tom-select-search for port selects in wizard steps
            ['#qn_origin','#qn_destination'].forEach(sel => {
                if ($(sel).length) {
                    initTomSelectSearch(sel, 'sea', 50, null);
                }
            });
        },

        bindClientAddress() {
            // Auto-populate address field when client is selected
            $('#customer').on('change', function () {
                let selected = $(this).find('option:selected');
                // Try to use data-address if available, otherwise leave blank for user to fill
                let addr = selected.data('address') || selected.data('subtext') || '';
                if (addr && $('#qn_client_address').length) {
                    $('#qn_client_address').val(addr);
                }
            });
        },

        bindCarrierSelect() {
            if ($('#qn_carrier').length) {
                initTomSelectSearch('#qn_carrier', 'seaLines', 50, null);
            }
        },

        bindChargesTable() {
            // Add row
            $('#addChargeRow').off().on('click', function () {
                $.get(GLOBAL_FN.buildUrl('sales/quotations-new/charge-row-template'), function (html) {
                    $('#chargesBody').append(html);
                    QUOTATION_NEW.wizard.bindChargeCalculations();
                    QUOTATION_NEW.wizard.recalcTotals();
                });
            });

            // Delete selected rows
            $('#deleteSelectedCharges').off().on('click', function () {
                let $checked = $('#chargesBody .charge-select:checked');
                if ($checked.length === 0) {
                    toastr.warning('Please select at least one row to delete.');
                    return;
                }
                $checked.each(function () {
                    let $tr = $(this).closest('tr');
                    if ($('#chargesBody tr').length > 1) {
                        $tr.remove();
                    } else {
                        $tr.find('input[type=text],input[type=number]').val('');
                        $(this).prop('checked', false);
                    }
                });
                $('#selectAllCharges').prop('checked', false);
                QUOTATION_NEW.wizard.recalcTotals();
            });

            // Select all
            $('#selectAllCharges').off().on('change', function () {
                $('.charge-select').prop('checked', $(this).is(':checked'));
            });
        },

        bindRemoveRow() {
            // kept for backwards compatibility (not used in table — no delete btn in rows)
        },

        bindChargeCalculations() {
            // FCY Amount = qty_amount * ex_rate (defaulted 1)
            // Amount INR = FCY Amount
            $(document).off('input.chargeCalc', '.charge-qty-amount, .charge-qty').on('input.chargeCalc', '.charge-qty-amount, .charge-qty', function () {
                let $row = $(this).closest('tr');
                let qty    = parseFloat($row.find('.charge-qty').val()) || 1;
                let amount = parseFloat($row.find('.charge-qty-amount').val()) || 0;
                let total  = qty * amount;
                $row.find('.charge-fcy').val('INR 1 * ' + amount.toFixed(0));
                $row.find('.charge-amount-inr').val(total.toFixed(2));
                QUOTATION_NEW.wizard.recalcTotals();
            });

            $(document).off('input.taxCalc', '.charge-tax').on('input.taxCalc', '.charge-tax', function () {
                QUOTATION_NEW.wizard.recalcTotals();
            });
        },

        recalcTotals() {
            let subTotal = 0, totalTax = 0;
            $('#chargesBody tr').each(function () {
                subTotal += parseFloat($(this).find('.charge-amount-inr').val()) || 0;
                totalTax += parseFloat($(this).find('.charge-tax').val()) || 0;
            });
            $('#subTotalDisplay').text(subTotal.toFixed(2));
            $('#totalTaxDisplay').text(totalTax.toFixed(2));
            $('#grandTotalDisplay').text((subTotal + totalTax).toFixed(2));
        }
    },

    // ─── Edit Modal ───────────────────────────────────────────────────────
    editModal: {
        load() {
            // Init port selects in edit modal
            ['#qn_por','#qn_pol','#qn_pod','#qn_pof'].forEach(sel => {
                if ($(sel).length) initTomSelectSearch(sel, 'sea', 50, true);
            });
            if ($('#qn_edit_carrier').length) {
                initTomSelectSearch('#qn_edit_carrier', 'seaLines', 50, true);
            }

            // Save
            $('#btn-modal-save').off().on('click', function () {
                let form = $('#moduleForm');
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success(res) {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            webModal.closeGlobalModal();
                            QUOTATION_NEW.list.dataTable();
                        }
                    },
                    error(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Save failed.');
                    }
                });
            });
        }
    },

    // ─── Costing Modal ────────────────────────────────────────────────────
    costing: {
        load(quotationId, chargeId = null) {
            let url = GLOBAL_FN.buildUrl('sales/quotations-new/' + quotationId + '/costing');
            if (chargeId) url += '/' + chargeId;

            webModal.openGlobalModal({
                title: 'Costing',
                url: url,
                size: 'xl',
                scroll: true,
            });
        },

        bindEvents() {
            QUOTATION_NEW.costing.bindCalculations();

            $('#btn-save-close-costing, #btn-save-stay-costing').off().on('click', function () {
                let closeAfter = $(this).attr('id') === 'btn-save-close-costing';
                QUOTATION_NEW.costing.save(closeAfter);
            });

            $('#btn-delete-costing').off().on('click', function () {
                let chargeId = $(this).data('charge-id');
                if (confirm('Delete this charge?')) {
                    $.ajax({
                        url: GLOBAL_FN.buildUrl('sales/quotations-new/charge/' + chargeId + '/delete'),
                        type: 'DELETE',
                        data: {_token: $('meta[name="csrf-token"]').attr('content')},
                        success() {
                            webModal.closeGlobalModal();
                            toastr.success('Charge deleted.');
                        }
                    });
                }
            });

            $('#btn-cancel-costing').off().on('click', function () {
                webModal.closeGlobalModal();
            });
        },

        bindCalculations() {
            // Amount INR = qty_amount * ex_rate
            $(document).off('input.costing').on('input.costing', '.costing-qty-amount, .costing-ex-rate', function () {
                let qty    = parseFloat($('.costing-qty-amount').val()) || 0;
                let rate   = parseFloat($('.costing-ex-rate').val()) || 1;
                let amtINR = qty * rate;
                $('.costing-fcy').val(qty.toFixed(2));
                $('.costing-amount-inr').val(amtINR.toFixed(2));
                $('.costing-taxable').val(amtINR.toFixed(2));
                QUOTATION_NEW.costing.recalcProfit();
            });

            $(document).off('input.costingCost').on('input.costingCost', '.costing-cost-amount', function () {
                QUOTATION_NEW.costing.recalcProfit();
            });
        },

        recalcProfit() {
            let sale = parseFloat($('.costing-amount-inr').val()) || 0;
            let cost = parseFloat($('.costing-cost-amount').val()) || 0;
            $('#grossProfitDisplay').text((sale - cost).toFixed(2));
        },

        save(closeAfter) {
            let quotationId = $('[name="quotation_new_id"]').val();
            $.ajax({
                url: GLOBAL_FN.buildUrl('sales/quotations-new/' + quotationId + '/costing/store'),
                type: 'POST',
                data: $('#costingForm').serialize(),
                success(res) {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        if (closeAfter) webModal.closeGlobalModal();
                    }
                },
                error(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Failed to save charge.');
                }
            });
        }
    }
};
