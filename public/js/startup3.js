const appIsLocal = document.querySelector('meta[name="app-js"]').getAttribute('content') === 'local';
// Get the current module from meta tag (uppercase) or empty string
var MODULE = $("body").attr("data-module");
MODULE = MODULE ? MODULE.toUpperCase() : "";
// DOM elements
const listNavTabs = $('#listTabs');
// App version
const VERSION = 1;
// Reference to global window object
const WINDOW = window;
const baseCurrency = 'SAR';

const hostName = window.location.origin;

let dataTableId = $('#dataTable');

/*document.addEventListener("turbo:load", () => {
    console.log("Turbo page loaded:", window.location.pathname);
    window[MODULE].load();
    // run page-specific initializations here
    // e.g. re-bind DataTables, tooltips, etc.
});*/
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
/*$(document).on("keydown", function (e) {
    if (e.key === "F1" || e.key === "F3") {
        e.preventDefault();
    } else if (e.altKey && e.key === 'n') {
        e.preventDefault();
        $('#new').click();
    } else if (e.ctrlKey && e.key === '/') {
        e.preventDefault();
        $('#shortcutPanel').toggleClass('d-none');
    }
});*/

/*document.addEventListener("DOMContentLoaded", function () {
    const tabs = document.querySelectorAll("#modalTabs .nav-link, #listTabs .nav-link");
    const indicator = document.querySelector(".tab-indicator");

    function updateIndicator(el) {
        const tabRect = el.getBoundingClientRect();
        const parentRect = el.parentElement.parentElement.getBoundingClientRect();

        indicator.style.width = tabRect.width + "px";
        indicator.style.left = (tabRect.left - parentRect.left) + "px";
    }

    // Init on load
    const activeTab = document.querySelector("#modalTabs .nav-link.active,#listTabs .nav-link.active");
    if (activeTab) updateIndicator(activeTab);

    // Update on click
    tabs.forEach(tab => {
        tab.addEventListener("shown.bs.tab", function (e) {
            updateIndicator(e.target);
        });
    });
});*/

// Fires when only a <turbo-frame> loads
document.addEventListener("turbo:load", () => {
    //console.log(MODULE);

    // Call global functions
    /*if (typeof GLOBAL_FN !== 'undefined') {
        GLOBAL_FN.load();
    }*/

    // Call the current page/module load
    /*if (MODULE && window[MODULE] && typeof window[MODULE].load === 'function') {
        window[MODULE].load();
    }*/

    MODULE = $("body").attr("data-module");
    MODULE = MODULE ? MODULE.toUpperCase() : "";
    PAGE.init();

    setTimeout(function () {
        loadJs();
        GLOBAL_FN.load();


    }, 100)
    //window.turboHasError = false;
});
//document.addEventListener('turbo:before-cache', GLOBAL_FN.destroyDataTable);
/*document.addEventListener('turbo:before-cache', function() {
    // Check if the DataTable exists and destroy it
    if ($.fn.DataTable) {
        // Get the DataTables API instance for the table
        const tableApi = $('#dataTable').DataTable();

        // Check if the API found any settings (meaning the table is initialized)
        if (tableApi.settings().length > 0) {
            // Destroy the instance to prevent conflicts on next Turbo load
            tableApi.destroy();
        }
    }
});*/



/*window.addEventListener("error", function (event) {
    console.error("⚠️ Turbo Error Detected:", event.message);
    window.turboHasError = true;
}, true);

document.addEventListener("turbo:click", (event) => {
    if (window.turboHasError) {
        console.warn("🔁 Reloading due to previous Turbo error...");
        event.preventDefault();
        window.location.href = event.target.closest("a").href;
    }
});

window.addEventListener("popstate", (event) => {
    if (window.turboHasError) {
        console.warn("🔁 Reloading due to previous Turbo error on back/forward...");
        window.location.reload();
    }
});*/

/*document.addEventListener("DOMContentLoaded", function() {
    GLOBAL_FN.load();
    console.log("ffff")
});*/

function hostUrl() {
    return hostName + '/';
}

function fullUrl() {
    return window.location.href;
}


let GLOBAL_FN = {
    /*buildUrl(url, parameter = {}, returnURL = true, target = '_blank') {
        let objectParameter = Object.keys(parameter), newParameter = '', newURL, key;
        for (let p = 0; p < objectParameter.length; p++) {
            key = objectParameter[p];
            newParameter += newParameter ? '&' : '';
            newParameter += key + '=' + parameter[key];
        }
        newURL = hostUrl() + url.replace(hostUrl(), '');
        if (newParameter) {
            newURL = newURL + '?' + newParameter;
        }
        if (returnURL) {
            return newURL;
        }
        window.open(newURL, target);//for same window use _self
    }*/
    load() {
        GLOBAL_FN.dataTable.load();
        GLOBAL_FN.turboBreadCrumbLoad();
        GLOBAL_FN.turboMasterNavLoad();
        GLOBAL_FN.listNavTabs();
        initTomSelectForm($('#content-wrapper'));
    },
    listNavTabs() {
        $('#listTabs').find('li button').off().on({
            click() {
                let activeTab = $(this).attr('id');
                loadJs('list.dataTable', activeTab);
            },
        });
        /*$(document).on('click', '#listTabs li button', function () {
            // Destroy if already initialized
            if ($.fn.DataTable.isDataTable("#dataTable")) {
                $("#dataTable").DataTable().destroy();
                $("#dataTable tbody").empty();
            }
            let activeTab = $(this).attr('id');
            loadJs('list.dataTable', activeTab);
        });*/
    },
    destroyDataTable() {
        /*if ($.fn.DataTable.isDataTable("#dataTable")) {
            $('#dataTable').DataTable().destroy();
            $("#dataTable tbody").empty();
        }*/
        const tableElement = $('#dataTable');

        // ⚠️ Check if the core DataTables plugin is loaded AND the element exists
        if (tableElement.length && $.fn.DataTable) {
            const tableApi = tableElement.DataTable();

            // Check if the API found any settings (initialized)
            if (tableApi.settings().length > 0) {
                // Safely destroy the initialized instance
                tableApi.destroy();
            }
        }
    },
    dataTable: {
        load() {
            loadJs('list.dataTable', '', {}, MODULE);
        },
        optionButton() {
            return {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function (data, type, row) {
                    if (row.company_id) {
                        return `<div class="dropdown">
        <a class="btn btn-outline-secondary btn-sm rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-three-dots-vertical"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-end"></ul></div>`;
                    } else {
                        return '';
                    }
                }
            }
        },
    },

    setStatusCounts(counts = null) {
        for (const [status, count] of Object.entries(counts)) {
            // Example: update badge in each tab
            $('#' + status.toLowerCase() + 'Count').text(count);
        }
    },
    buildUrl(path, params = {}, returnURL = true, target = '_blank') {
        const base = hostUrl();
        const cleanPath = path.replace(base, ''); // remove duplicate host if present
        const query = new URLSearchParams(params).toString();
        const newURL = base + cleanPath + (query ? `?${query}` : '');

        if (returnURL) {
            return newURL;
        }

        window.open(newURL, target);
    },
    turboBreadCrumbLoad() {
        $('ol.breadcrumb li.breadcrumb-item').off().on('click', function (e) {
            e.preventDefault(); // stop default behavior

            let breadcrumbUrl = $(this).data('url');
            if (breadcrumbUrl) {
                // Navigate using Turbo
                Turbo.visit(breadcrumbUrl);
            }
        });
    },
    turboMasterNavLoad() {
        $('#master-navigation li').off().on('click', function (e) {
            e.preventDefault(); // stop default behavior

            let masterNavUrl = $(this).data('url');
            if (masterNavUrl) {
                // Navigate using Turbo
                Turbo.visit(masterNavUrl);
            }
        });
    },
    ajaxData: {
        sendData(url, callBack = null, options) {
            let defaults = {
                method: 'POST',
                data: '',
            };
            let settings = $.extend({}, defaults, options); // merge options
            $.ajax({
                url: url,
                type: settings.method,
                data: settings.data,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.status === 'success') {
                        toastr.success(response.message);
                        if (callBack) {
                            if (callBack === 'datatable') {
                                window[MODULE].list.dataTable();
                            } else {
                                callBack(response);
                            }
                        }
                        ;
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
            });
        }
    },
    activity: {
        activityChange() {
            $('#activity-id').on('change', function () {
                const shipmentType = this.selectedOptions[0].dataset.shipmentType;
                let prevValue = $('#activity-id-hidden').val();
                if (prevValue != shipmentType) {
                    $('#activity-id-hidden').val(shipmentType);
                    loadJs('form.shipmentMode', true);
                }
            });
        }
    }
}

CALCULATION = {
    exchangeRate: parseFloat($('#sarRate').text()) || 3.75, // default fallback
    load() {
        CALCULATION.start();
    },
    start() {
        $('#' + MODULE + '-tbody').on('input change', '.quantity, .unit_price, .tax', function () {
            let $row = $(this).closest('tr');
            CALCULATION.rowTotal($row);
            CALCULATION.finalTotals();
        });
    },
    rowTotal($row) {
        let qty = parseFloat($row.find('.quantity').val()) || 0;
        let price = parseFloat($row.find('.unit_price').val()) || 0;
        let tax = parseFloat($row.find('.tax').val()) || 0;

        let baseAmount = qty * price;
        $row.find('.row-total').val(baseAmount.toFixed(2));
    },
    finalTotals() {
        let subtotal = 0;
        let totalTax = 0;
        let grandTotal = 0;

        $('#' + MODULE + '-tbody tr').each(function () {
            const $row = $(this);
            let amount = parseFloat($row.find('.row-total').val()) || 0;
            const selectedOption = $row.find('.tax').find('option:selected');
            const taxPercent = parseFloat(selectedOption.data('percent')) || 0;

            let taxAmt = (amount * taxPercent) / 100;
            subtotal += amount;
            totalTax += taxAmt;
            grandTotal += amount + taxAmt;
        });

        $('#subTotal').text(subtotal.toFixed(2));
        $('#totalTax').text(totalTax.toFixed(2));
        $('#grandNet').text(grandTotal.toFixed(2));

        // SAR equivalent if exists
        if ($('#sarRate').length) {
            let sarValue = grandTotal * this.exchangeRate;
            $('#sarEquivalent').text(sarValue.toFixed(2));
        }
    }
}

function changeCustomerStatus(url, settings, newStatus, clickElement = null) {
    let message = "Are you sure you want to change status?";
    let requireReason = false;

    if (newStatus === '2') {
        message = "Are you sure you want to convert this customer to Confirmed?";
    } else if (newStatus === '5') {
        message = "Why do you want to reject this customer?";
        requireReason = true;
    } else if (newStatus === '4') {
        message = "Why do you want to block this customer?";
        requireReason = true;
    }
    let input, inputType, oldValue, row;
    if (clickElement) {
        input = clickElement;
        inputType = input.attr('type');
        row = input.closest('tr');
        oldValue = input.attr('data-old-value');
    }

    $.confirm({
        title: 'Confirm!',
        content: requireReason
            ? '<div class="form-group">' +
            '<label>' + message + '</label>' +
            '<textarea id="reasonInput" class="form-control mt-2" placeholder="Enter reason..."></textarea>' +
            '</div>'
            : message,
        /*type: requireReason ? 'red' : 'blue',*/
        buttons: {
            cancel: {
                text: 'No',
                btnClass: 'btn-secondary',
                action: function () {
                    // Revert based on input type
                    if (inputType === 'checkbox') {
                        input.prop('checked', oldValue);
                    } else if (inputType === 'radio') {
                        $('input[name="' + input.attr('name') + '"][value="' + oldValue + '"]').prop('checked', true);
                    }
                }
            },
            confirm: {
                text: 'Yes',
                btnClass: 'btn-primary',
                action: function () {
                    let reason = null;
                    if (requireReason) {
                        reason = this.$content.find('#reasonInput').val();
                        if (!reason) {
                            $.alert("Please provide a reason before proceeding.");
                            return false; // prevent closing
                        }
                        settings.data.append('reason', reason);
                    }

                    // Perform AJAX
                    $.ajax({
                        url: url,
                        type: settings.method,
                        data: settings.data,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            if (response.status === 'success') {
                                toastr.success(response.message);
                                if (settings.callBack) {
                                    if (settings.callBack === 'datatable') {
                                        window[MODULE].list.dataTable();
                                    } else {
                                        settings.callBack(response);
                                    }
                                }
                            }
                        },
                        error: function (xhr) {
                            let errors = xhr.responseJSON.errors;
                            if (errors) {
                                $.each(errors, function (key, value) {
                                    toastr.error(value[0]);
                                    // Revert on AJAX error
                                    if (inputType === 'checkbox') {
                                        input.prop('checked', oldValue);
                                    } else if (inputType === 'radio') {
                                        $('input[name="' + input.attr('name') + '"][value="' + oldValue + '"]').prop('checked', true);
                                    }
                                });
                            } else {
                                toastr.error("Something went wrong!");
                                // Revert on AJAX error
                                if (inputType === 'checkbox') {
                                    input.prop('checked', oldValue);
                                } else if (inputType === 'radio') {
                                    $('input[name="' + input.attr('name') + '"][value="' + oldValue + '"]').prop('checked', true);
                                }
                            }
                        }
                    });
                }
            }
        }
    });
}

function deleteFn(url, settings) {
    let message = "Are you sure you want to delete?";

    $.confirm({
        title: 'Confirm!',
        buttons: {
            cancel: {
                text: 'No',
                btnClass: 'btn-secondary'
            },
            confirm: {
                text: 'Yes',
                btnClass: 'btn-primary',
                action: function () {
                    $.ajax({
                        url: url,
                        type: settings.method,
                        data: settings.data,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            if (response.status === 'success') {
                                toastr.success(response.message);
                                if (settings.callBack) {
                                    if (settings.callBack === 'datatable') {
                                        window[MODULE].list.dataTable();
                                    } else {
                                        settings.callBack(response);
                                    }
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
                        }
                    });
                }
            }
        }
    });
}


// Handle AJAX form submit
let webModal = {
    openGlobalModal(options) {
        let defaults = {
            title: 'Modal',
            content: null,
            size: 'xl',
            scroll: true,
            callBack: null,
            maxHeight: 'max-height:75vh;',
            minHeight: '650px',
        };
        let settings = $.extend({}, defaults, options); // merge options

        const $modalEl = $('#globalModal');
        const $modalDialog = $('#globalModalDialog');

        // Remove existing ESC handler
        $modalEl.off('keydown');

        // Remove previous size & scrollable classes
        $modalDialog.removeClass('modal-sm modal-md modal-lg modal-xl modal-xxl modal-fullscreen modal-full-wrapper modal-dialog-scrollable');

        // Add size class
        switch (settings.size) {
            case 'sm':
                $modalDialog.addClass('modal-sm');
                break;
            case 'md':
                $modalDialog.addClass('modal-md');
                break;
            case 'lg':
                $modalDialog.addClass('modal-lg');
                break;
            case 'xl':
                $modalDialog.addClass('modal-xl');
                break;
            case 'xxl':
                $modalDialog.addClass('modal-xxl');
                break;
            case '3xl':
                $modalDialog.addClass('modal-3xl');
                break;
            case '4xl':
                $modalDialog.addClass('modal-4xl');
                break;
            case 'fullscreen':
                $modalDialog.addClass('modal-fullscreen');
                break;
            case 'full-wrapper':
                $('#globalModal').attr("style", "position:absolute");
                $modalDialog.addClass('modal-full-wrapper');
                break;
            default:
                $modalDialog.addClass('modal-md');
        }

        // Add scrollable body
        if (settings.scroll) $modalDialog.addClass('modal-dialog-scrollable');

        // Set title
        $('#globalModalTitle').text(settings.title);

        // Show initial loader
        $('#globalModalBody').html(`
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2">Loading...</div>
        </div>
    `);

        // Init Bootstrap modal
        const modal = new bootstrap.Modal($modalEl[0], {
            keyboard: false,   // Disable ESC
            backdrop: 'static', // Optional: disable click outside
            //backdrop: false,
            focus: true
        });

        modal.show();

        // Load content via AJAX or inline
        if (settings.url) {
            $.ajax({
                url: settings.url,
                type: 'GET', // or 'POST' if needed
                dataType: 'html', // expect HTML by default
                data: settings.content,
                success: function (data, textStatus, xhr) {
                    // Check if response is JSON (logout scenario)
                    const contentType = xhr.getResponseHeader('Content-Type') || '';
                    if (contentType.includes('application/json')) {
                        let jsonData;
                        try {
                            jsonData = typeof data === 'string' ? JSON.parse(data) : data;
                            if (jsonData.status === 'logout' && jsonData.redirect) {
                                window.location.href = jsonData.redirect;
                                return;
                            }
                        } catch (e) {
                            // Not valid JSON, continue
                        }
                    }

                    // Insert content into modal
                    $('#globalModalBody').html(data);
                    webModal.buttons('globalModal');
                    loadJs('form.openCallback');
                    //selectPicker('#moduleForm');
                    initTomSelectForm();
                    datepicker();
                    CURRENCY.currencyRate();
                    INPUT.load();

                    $('#moduleForm').attr('style', 'min-height:' + settings.minHeight + ';' + settings.maxHeight + 'overflow-y: auto; overflow-x: hidden');

                    // Run callback if provided
                    if (typeof settings.callBack === 'function') {
                        settings.callBack(data);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Modal load failed:', error);
                }
            });
        } else if (settings.content) {
            $('#globalModalBody').html(settings.content);
            webModal.buttons('globalModal');
        }

// Setup ESC confirm handler
        webModal.modelHandler($modalEl[0], '#globalModal', "Close main modal?");
        webModal.openChildModal.load();
        this.submitForm(modal);
    },
    submitForm(modal) {
        $('#globalModal').off('submit', 'form').on('submit', 'form', function (e) {
            e.preventDefault();
            const form = $(this);
            const action = form.attr('action');
            const method = form.attr('method') || 'POST';
            // Reset validation
            /*form.removeClass("was-validated");

            if (!form[0].checkValidity()) {
                e.stopPropagation();
                form.addClass("was-validated");
                return;
            }*/
            if ($(this).valid()) {

                // Build form data
                const formData = new FormData(this);
                // const formData = new FormData(this);

                const submitBtn = form.find('button[type="submit"]');
                submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Submitting');

                $.ajax({
                    url: action,
                    type: method,
                    data: formData,
                    context: modal,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        console.log(response);
                        if (response.status === 'success') {
                            toastr.success(response.message);

                            // Reload DataTable
                            $('#dataTable').DataTable().ajax.reload(null, false);

                            // Close modal
                            modal.hide();

                            // Reset form
                            $('#customerForm')[0].reset();
                            console.log(response, callback);
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
        });
    },
    buttons(modalId) {
        const modalEl = $(`#${modalId}`);
        const form = modalEl.find('form');
        //const footer = modalEl.find('.modal-footer');
        const showButtons = modalEl.find('#show-buttons');
        const buttons = $('#modal-buttons');
        const buttonsConfig = buttons.data('buttons')?.split(',') || [];

        let html = '<div class="d-flex justify-content-end gap-2">';
        if (buttonsConfig.includes('cancel')) {
            html += `
            <button type="button" class="btn btn-outline-secondary" id="btn-cancel">
                <i class="bi bi-x-circle me-1"></i> Cancel
            </button>`;
        }
        if (buttonsConfig.includes('saveNew')) {
            html += `
            <button type="button" class="btn btn-outline-secondary" id="modalSaveNewBtn">
                <i class="bi bi-plus-circle me-1"></i> Save & New
            </button>`;
        }
        if (buttonsConfig.includes('saveDraft')) {
            html += `
            <button type="button" class="btn btn-warning" id="modalSaveAsDraftBtn">
                <i class="bi bi-eye me-1"></i> Save as Draft
            </button>`;
        }
        if (buttonsConfig.includes('save') && form.length) {
            html += `
            <button type="submit" class="btn btn-primary" form="${form.attr('id')}" id="modalSaveBtn">
                <i class="bi bi-save me-1"></i> ${buttons.data('button-save') ?? 'Save'}
            </button>`;
        }


        html += '</div>';
        //footer.html(html).show();
        showButtons.html(html).show();
    },
    quickModal: {
        open(options) {
            let defaults = {
                title: 'Modal',
                content: null,
                size: 'xl',
                scroll: true,
                callBack: null,
            };
            let settings = $.extend({}, defaults, options); // merge options

            const $modalEl = $('#quickModal');
            const $modalDialog = $('#quickModalDialog');


            $('#globalModal').css({'opacity': 0});
            $('#globalModal .modal-dialog')
                .removeClass('global-modal-normal')
                .addClass('global-modal-shrink');

            const quickEl = document.getElementById('quickModal');

            const quick = new bootstrap.Modal(quickEl, {
                backdrop: false,
                keyboard: false
            });

            // Add slide animation
            $(quickEl).find('.modal-dialog').addClass('quick-slide-in');

            quick.show();


            $.ajax({
                url: settings.url,
                type: 'GET', // or 'POST' if needed
                dataType: 'html', // expect HTML by default
                success: function (data, textStatus, xhr) {
                    // Check if response is JSON (logout scenario)
                    const contentType = xhr.getResponseHeader('Content-Type') || '';
                    if (contentType.includes('application/json')) {
                        let jsonData;
                        try {
                            jsonData = typeof data === 'string' ? JSON.parse(data) : data;
                            if (jsonData.status === 'logout' && jsonData.redirect) {
                                window.location.href = jsonData.redirect;
                                return;
                            }
                        } catch (e) {
                            // Not valid JSON, continue
                        }
                    }

                    // Insert content into modal
                    $('#quickModalBody').html(data);
                    webModal.buttons('globalModal');

                    // Run callback if provided
                    if (typeof settings.callBack === 'function') {
                        settings.callBack(data);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Modal load failed:', error);
                }
            });


            webModal.quickModal.close(quickEl);
            webModal.quickModal.submit(quick, settings.module);
        },
        close(quickEl) {
            $('#quickModal').one('hidden.bs.modal', function () {
                $('#globalModal').css({'opacity': 1});
                // Remove slide animation
                $(quickEl).find('.modal-dialog').removeClass('quick-slide-in');

                // Restore the main modal layout
                $('#globalModal .modal-dialog')
                    .removeClass('global-modal-shrink')
                    .addClass('global-modal-normal');

                // Restore focus trap for bootstrap
                /*$(document).on(
                    'focusin.bs.modal',
                    bootstrap.Modal.prototype._handleFocusin.bind()
                );*/
            });
            /*$('#quickModal').on('hidden.bs.modal', function () {
                $('#customer').val('');
                $('#customer').selectpicker('destroy').addClass('selectpicker');
                $('#globalModal').css('z-index', '');
                selectPicker('#customer-select');
            });*/
        },
        submit(modal, module) {
            $('#quickModal').off('submit', 'form').on('submit', 'form', function (e) {
                e.preventDefault();
                const form = $(this);
                const action = form.attr('action');
                const method = form.attr('method') || 'POST';
                // Reset validation
                /*form.removeClass("was-validated");

                if (!form[0].checkValidity()) {
                    e.stopPropagation();
                    form.addClass("was-validated");
                    return;
                }*/
                if ($(this).valid()) {

                    // Build form data
                    const formData = new FormData(this);
                    // const formData = new FormData(this);

                    const submitBtn = form.find('button[type="submit"]');
                    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Submitting');

                    $.ajax({
                        url: action,
                        type: method,
                        data: formData,
                        context: modal,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            console.log(response);
                            if (response.status === 'success') {
                                toastr.success(response.message);

                                // Reload DataTable
                                //$('#dataTable').DataTable().ajax.reload(null, false);

                                // Close modal
                                modal.hide();

                                // Reset form
                                /*$('#customerForm')[0].reset();*/
                                if (module) loadJs('form.quick.after.save', response, '', module);
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
            });
        },
    },
    buttons222() {//set button dynamically
        const form = $('#globalModalBody').find('form');
        if (form.length) {
            const formId = form.attr('id');
            $('#globalModalFooter').html(`
                <div class="d-flex justify-content-end gap-2 mt-3">
  <button
    type="button"
    class="btn btn-outline-secondary"
    id="modalSaveNewBtn"
    aria-label="Save and open new form"
  >
    <i class="bi bi-x-circle me-1"></i> Save & New
  </button>

  <button
    type="submit"
    class="btn btn-primary"
    form="${formId}"
    aria-label="Submit the form"
  >
    <i class="bi bi-check-circle me-1"></i> Save
  </button>
</div>

            `).show();

            $('#modalClearBtn').off('click').on('click', function () {
                form[0].reset();
            });
        } else {
            $('#globalModalFooter').hide();
        }
    },
    /*shortCut: {
        closeSubModal(modalID) {
            const modalCl = document.getElementById(modalID);
            const modalInstance = bootstrap.Modal.getInstance(modalCl)
                || new bootstrap.Modal(modalCl);
            modalInstance.hide();
        }
    },*/
    modelHandler(modalEl, modalSelector, message = "Are you sure you want to close the modal?") {
        /*$('#globalModal a.btn-close').off().on('click', function () {
            $('.modal-backdrop').remove();
        });*/

        let lastFocusedInput = null;
        //const focusableSelector = 'input:not([type="hidden"]), textarea, select, button, a[href]';
        const focusableSelector = 'input:not([type="hidden"]),textarea,select,button:not(.nav-link),a[href]:not(.nav-link),[tabindex]:not([tabindex="-1"]):not(.nav-link)'
        let firstEl = null;
        let lastEl = null;

        // Track last focused input
        modalEl.addEventListener('focusin', function (e) {
            if (e.target.matches(focusableSelector)) {
                lastFocusedInput = e.target;
            }
        });

        // Focus first or last focused input when modal opens
        modalEl.addEventListener('shown.bs.modal', function () {
            const focusable = modalEl.querySelectorAll(focusableSelector);
            const focusableArr = Array.from(focusable).filter(el => !el.disabled && el.offsetParent !== null);

            if (focusableArr.length) {
                firstEl = focusableArr[0];
                lastEl = focusableArr[focusableArr.length - 1];
                if (lastFocusedInput) {
                    lastFocusedInput.focus();
                } else {
                    firstEl.focus();
                }
            }
        });

        setTimeout(function () {
            $('#globalModal .btn-close, #globalModal #btn-cancel').off('click').on('click', function (e) {
                e.preventDefault(); // stop main modal from closing
                confirmDialog();
            });
        }, 400)

        function confirmDialog() {
            const parentModal = bootstrap.Modal.getInstance(modalEl);

            $.confirm({
                title: 'Are you sure you want to exit?',
                content: 'Some information has been modified. Please save your changes or continue without saving.',
                autoClose: false,
                backgroundDismiss: false,
                escapeKey: false,
                buttons: {
                    cancel: {
                        text: 'Exit',
                        btnClass: '',
                        action: function () {
                            if (lastFocusedInput) {
                                lastFocusedInput.focus();
                            } else if (firstEl) {
                                firstEl.focus();
                            }
                        }
                    },
                    confirm: {
                        text: 'Confirm',
                        btnClass: 'btn-primary',
                        id: 'confirmYes',
                        action: function () {
                            if (parentModal) parentModal.hide();
                            modalEl.removeEventListener('keydown', handler);
                        }
                    }
                },
                onDestroy: function () {
                    setTimeout(function () {
                        lastFocusedInput.focus();
                    }, 450);
                },
                onClose: function () {
                    lastFocusedInput.focus();
                },

                onOpen: function () { // regular function, NOT arrow
                    // Use jQuery to get the buttons directly from the modal element
                    const $modal = $(this.$el); // $el is the modal container in jQuery Confirm
                    const buttons = $modal.find('.jconfirm-buttons button');
                    // Disable Bootstrap modal ESC
                    $modal.on('keydown.preventESC,keydown.preventTAB', function (e) {
                        if (e.key === 'Escape' || e.key === 'Tab') {
                            e.preventDefault();
                            e.stopPropagation();
                            console.log('ESC pressed — modal will not close');
                        }
                    });


                    if (buttons.length >= 2) {
                        buttons.eq(0).attr('id', 'confirmCancel');
                        buttons.eq(1).attr('id', 'confirmYes');

                        // Focus first button
                        buttons.eq(0).focus();
                    }

                    /*if (buttons.length >= 2) {
                        $(buttons[0]).attr('id', 'confirmCancel').removeClass('btn-default').addClass('btn-danger');  // first button
                        $(buttons[1]).attr('id', 'confirmYes').removeClass('btn-default').addClass('btn-primary'); // second button
                    }

                    // Focus first button initially
                    $(buttons[0]).focus();*/

                    // Arrow key navigation
                    let index = 0;
                    const keyHandler = function (e) {
                        if (e.key === 'ArrowRight') {
                            e.preventDefault();
                            index = (index + 1) % buttons.length;
                            $(buttons[index]).focus();
                        } else if (e.key === 'ArrowLeft') {
                            e.preventDefault();
                            index = (index - 1 + buttons.length) % buttons.length;
                            $(buttons[index]).focus();
                        }
                    };

                    $(document).on('keydown.jconfirm', keyHandler);

                    // Remove handler on close
                    $modal.on('hidden.bs.modal', () => {
                        $(document).off('keydown.jconfirm', keyHandler);
                    });
                },
            });
        }

        // Keydown handler
        const handler = function (e) {
            if (['Tab', 'ArrowRight', 'ArrowLeft'].includes(e.key)) {
                //const focusableSelector = 'input:not([type="hidden"]),textarea,select,button:not(.nav-link),a[href]:not(.nav-link),[tabindex]:not([tabindex="-1"]):not(.nav-link)';

                const focusable = Array.from(modalEl.querySelectorAll(focusableSelector))
                    .filter(el => !el.disabled && el.offsetParent !== null); // only visible & enabled
                const firstEl = focusable[0];
                const lastEl = focusable[focusable.length - 1];
                const currentIndex = focusable.indexOf(document.activeElement);

                if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    if (currentIndex === -1 || currentIndex === focusable.length - 1) {
                        firstEl.focus(); // wrap to first
                    } else {
                        focusable[currentIndex + 1].focus();
                    }
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    if (currentIndex <= 0) {
                        lastEl.focus(); // wrap to last
                    } else {
                        focusable[currentIndex - 1].focus();
                    }
                } else if (e.key === 'Tab') {
                    if (e.shiftKey) { // Shift+Tab
                        if (document.activeElement === firstEl) {
                            e.preventDefault();
                            lastEl.focus();
                        }
                    } else { // Tab
                        if (document.activeElement === lastEl) {
                            e.preventDefault();
                            firstEl.focus();
                        }
                    }
                }
            } else if (e.key === 'Escape') {
                e.preventDefault();
                e.stopPropagation();
                confirmDialog();

            } else if (e.key === 'Enter') {
                let target = $(e.target);

                if (target.is('button, button[type="submit"], input[type="submit"],textarea')) {
                    return true; // allow submit
                } else if (target.is('input[type="radio"]')) {
                    e.preventDefault(); // stop form submission
                    target.prop('checked', true).trigger('change'); // toggle radio
                    return false;
                } else {
                    e.preventDefault();
                    return false;
                }
            } else if (e.altKey && e.key.toLowerCase() === 's') {
                e.preventDefault();
                let saveBtn = $('#globalModal').find('button[type="submit"]');
                saveBtn.focus();
                if (saveBtn.length) {
                    saveBtn.click();
                }
            } else if (e.key === "F3") {
                console.log("F3 pressed")

                let $tabs = $('#globalModal .nav-link');
                let $active = $tabs.filter('.active');

                let currentIndex = $tabs.index($active);
                let nextIndex = (currentIndex + 1) % $tabs.length; // loop back to first

                $tabs.eq(nextIndex).tab('show');
                $tabs.eq(nextIndex).focus(); // optional: move keyboard focus
            }
        };

        // Attach keydown (make sure no duplicates)

        modalEl.addEventListener('keydown', handler);

        // Cleanup when modal closes
        /*modalEl.addEventListener('hidden.bs.modal', function () {
            modalEl.removeEventListener('keydown', handler);
        });*/
    },
    modelHandler2222(modalEl, modalSelector, message = "Are you sure you want to close the modal?") {
        $('#globalModal a.btn-close, #globalModal #btn-cancel').off().on('click', function () {
            $('.modal-backdrop').remove();
        });


        const confirmModalEl = document.getElementById('confirmModal');
        const confirmModal = new bootstrap.Modal(confirmModalEl, {
            backdrop: 'static',
            keyboard: false
        });

        const yesBtn = document.getElementById('confirmYes');
        const cancelBtn = document.getElementById('confirmCancel');

        let lastFocusedInput = null;
        const focusableSelector = 'input:not([type="hidden"]), textarea, select, button, a[href]';
        let firstEl = null;
        let lastEl = null;

        modalEl.addEventListener('focusin', function (e) {
            if (e.target.matches(focusableSelector)) {
                lastFocusedInput = e.target;
            }
        });

        modalEl.addEventListener('shown.bs.modal', function () {
            const focusable = modalEl.querySelectorAll(focusableSelector);
            const focusableArr = Array.from(focusable).filter(el => !el.disabled && el.offsetParent !== null);

            if (focusableArr.length) {
                firstEl = focusableArr[0];
                lastEl = focusableArr[focusableArr.length - 1];
                if (lastFocusedInput) {
                    lastFocusedInput.focus();
                } else {
                    firstEl.focus();
                }
            }
        });

        const cancelHandler = function (e) {
            if (['ArrowRight', 'ArrowLeft'].includes(e.key)) {
                console.log(e.key);
                if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    $('#confirmYes').focus();
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    $('#confirmCancel').focus();
                }
            }
        };

        confirmModalEl.addEventListener('keydown', cancelHandler);

        const handler = function (e) {
            if ([/*'Tab', */'ArrowRight', 'ArrowLeft'].includes(e.key)) {

                const focusableSelector = 'input, select, textarea, button, a[href], [tabindex]:not([tabindex="-1"])';
                const focusable = Array.from(document.querySelectorAll(focusableSelector))
                    .filter(el => !el.disabled && el.offsetParent !== null); // only visible & enabled

                const index = focusable.indexOf(document.activeElement);

                /*if (e.shiftKey && document.activeElement === firstEl) {
                    e.preventDefault();
                    lastEl.focus();
                } else if (!e.shiftKey && document.activeElement === lastEl) {
                    e.preventDefault();
                    firstEl.focus();
                } else */
                if (e.key === 'ArrowRight') {
                    e.preventDefault(); // stop default cursor move
                    if (index > -1 && index < focusable.length - 1) {
                        focusable[index + 1].focus(); // same as Tab
                    }
                } else if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    if (index > 0) {
                        focusable[index - 1].focus(); // same as Shift+Tab
                    }
                }
            } else if (e.key === 'Escape') {
                e.preventDefault();
                e.stopPropagation();

                document.getElementById('confirmMessage').textContent = message;

                const parentModal = bootstrap.Modal.getInstance(modalEl);
                if (parentModal) parentModal.hide();

                confirmModal.show();
                webModal.ensureBackdrop();

                yesBtn.addEventListener('click', function yesHandler() {
                    confirmModal.hide();
                    $('.modal-backdrop').remove();
                    confirmModalEl.removeEventListener('keydown', cancelHandler);
                    modalEl.removeEventListener('keydown', handler);
                }, {once: true});

                cancelBtn.addEventListener('click', function cancelHandler() {
                    confirmModal.hide();
                    if (parentModal) parentModal.show();
                    webModal.ensureBackdrop();
                }, {once: true});
            } else if (e.key === 'Enter') {
                let target = $(e.target);

                // Only allow submit if focus is on a submit button
                if (target.is('button[type="submit"], input[type="submit"]')) {
                    // Let it submit naturally
                    return true;
                } else {
                    // Block Enter everywhere else
                    e.preventDefault();
                    return false;
                }
            } else if (e.altKey && e.key.toLowerCase() === 's') {
                e.preventDefault(); // prevent browser default (like focus on menu)

                // Find your save button (adjust selector as needed)
                let saveBtn = $('#globalModal').find('button[type="submit"], .btn-save').first();

                if (saveBtn.length) {
                    saveBtn.trigger('click');
                }
            }
        };

        modalEl.addEventListener('keydown', handler);
    },
    ensureBackdrop() {
        $('.modal-backdrop').remove();
        $('body').append('<div class="modal-backdrop fade show"></div>');
        console.log('added');
    },
    openChildModal: {
        load() {
            $("#globalModal").on("click keydown", function (e) {
                const target = e.target; // the element clicked or typed in

                if ($(target).hasClass('modal-option')) { // check the actual element
                    if (e.type === "click" || e.type === "keydown" && e.key === 'Enter') {
                        e.preventDefault();
                        let currentInputId = $(target).attr('data-open-modal');
                        webModal.openChildModal.open(currentInputId);
                    }
                }

            });
        },
        open(fieldPrefix) {
            let currentIndex = -1,
                parentField = fieldPrefix + "-field",
                childModal = fieldPrefix + "Modal",
                childSearch = fieldPrefix + "-search",
                childList = fieldPrefix + "List";

            const childModalObj = $("#" + childModal);
            const childSearchObj = $("#" + childSearch);
            const parentFieldObj = $("#" + parentField);

            // Show modal and reset
            childModalObj.modal("show");
            childSearchObj.val("").focus();
            currentIndex = -1;
            $("#" + childList + " .list-group-item").removeClass("active");

            // Filter list on input
            childSearchObj.on("input", function () {
                const value = $(this).val().toLowerCase();
                $("#" + childList + " li").filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
                currentIndex = -1;
            });

            // Keyboard navigation in search input
            childSearchObj.on("keydown", function (e) {
                const items = $("#" + childList + " .list-group-item:visible");
                if (e.key === "ArrowDown") {
                    e.preventDefault();
                    currentIndex++;
                    highlightItem(items);
                } else if (e.key === "ArrowUp") {
                    e.preventDefault();
                    currentIndex--;
                    highlightItem(items);
                } else if (e.key === "Enter") {
                    e.preventDefault();
                    if (currentIndex >= 0) {
                        selectChildModalValue(items.eq(currentIndex));
                    }
                }
            });

            // Click select
            $("#" + childList).on("click", ".list-group-item", function () {
                selectChildModalValue($(this));
            });

            // Restore focus to parent field after modal closes
            childModalObj.on("hidden.bs.modal", function () {
                parentFieldObj.focus();
            });

            // Handle F1/F2 shortcuts on modal and search input
            childModalObj.add(childSearchObj).on("keydown", function (e) {
                if (e.key === "F1") {
                    console.log("F1 pressed → close modal");
                    webModal.openChildModal.close(childModal); // your custom close function
                }
                /*if (e.key === "F2") {
                    e.preventDefault();
                    console.log("F2 pressed → do something else");
                }*/
            });

            // Highlight function
            function highlightItem(items) {
                if (items.length === 0) return;
                if (currentIndex < 0) currentIndex = 0;
                if (currentIndex >= items.length) currentIndex = items.length - 1;
                items.removeClass("active");
                items.eq(currentIndex).addClass("active").focus();
            }

            // Select item function
            function selectChildModalValue(el) {
                const code = el.data("code");
                parentFieldObj.val(code);
                childModalObj.modal("hide");
            }
        },
        close(modalID) {
            const modalCl = document.getElementById(modalID);
            const modalInstance = bootstrap.Modal.getInstance(modalCl)
                || new bootstrap.Modal(modalCl);
            modalInstance.hide();
        }
    },
}

webDataTable = {
    search(table) {
        $('#dataTable_filter input').off();
        $('#dataTable_filter input').addClass('my-custom-search');
        $('#dataTable_filter input').attr('placeholder', 'Type to search...');
        // Add custom debounce search
        let searchTimeout;
        $('#dataTable_filter input').on('keyup', function () {
            clearTimeout(searchTimeout);
            let value = this.value;

            searchTimeout = setTimeout(() => {
                table.search(value).draw();
            }, 600); // wait 600ms after last keyup
        });
    },
    loader(table) {
        // 1️⃣ show loader on first load
        showTableLoader(table, '#dataTable');

        // 2️⃣ show loader again on every new request
        /*$('#dataTable').on('preXhr.dt', function () {
            showTableLoader(table, '#dataTable');
        });*/

        // 3️⃣ trigger initial AJAX after 1s
        setTimeout(() => {
            table.ajax.reload();
        }, 600);
    },
    /*actions(table) {
        this.edit(table);
    },*/
    actions: {
        menu() {
            $('#dataTable').off('click', ".dropdown a.btn").on('click', ".dropdown a.btn", function (e) {
                e.preventDefault();
                e.stopPropagation(); // prevent bubbling
                //$(".dropdown .dropdown-menu").removeClass('show').removeAttr('style');
                //$('ul.dropdown-menu').removeClass('show');
                let row = $(this).closest('tr');
                let dropdownDiv = row.find("ul.dropdown-menu");
                // 🔹 Close other dropdowns completely
                $(".dropdown .dropdown-menu").not(dropdownDiv).removeClass("show").empty();

                webDataTable.actions.menuCallBack(row);
            })

        },
        menuCallBack(row) {
            let dropdownDiv = row.find('ul.dropdown-menu');
            let rowId = row.data("id");
            // Remove existing menus (close others)
            dropdownDiv.find('li').remove();

            // Add loader item
            dropdownDiv.append(`
        <li class="loader-item text-center p-2 pt-5 pb-5">
            <div class="spinner-border spinner-border-sm text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </li>
    `);

            $.ajax({
                url: GLOBAL_FN.buildUrl(window[MODULE].actionUrl + '/' + rowId + '/actions'),
                type: "GET",
                context: row,
                success: function (actions) {
                    // Remove loader
                    dropdownDiv.find(".loader-item").remove();

                    let menu = '';
                    actions.forEach(item => {
                        if (item.type === 'item') {
                            // simple menu item
                            if (item.separator === 'before') {
                                menu += `<li class="separator"></li>`;
                            }
                            menu += `<li>
            <a class="dropdown-item ${item.class ?? ''}"
               id="${item.id}" ${item.onclick ? "onclick=" + item.onclick : ""}
               data-id="${item['data-id']}" ${item['data-value'] ? "data-value=" + item['data-value'] : ""}><i class="${webDataTable.actions.icons(item.icon)}"></i>
               ${item.label}
            </a>
        </li>`;
                            if (item.separator === 'after') {
                                menu += `<li class="separator"></li>`;
                            }
                        } else if (item.type === 'submenu') {
                            // submenu
                            menu += `<li class="dropdown-submenu">
            <a class="dropdown-item dropdown-toggle J-N-JX BS"><i class="${webDataTable.actions.icons(item.icon)}"></i> ${item.label}</a>
            <ul class="dropdown-menu">`;

                            item.items.forEach(sub => {
                                menu += `<li>
                <a class="dropdown-item ${sub.class ?? ''}"
                   id="${sub.id}"
                   data-id="${sub['data-id']}" data-value="${sub['data-value']}"><i class="${webDataTable.actions.icons(sub.icon)}"></i>
                   ${sub.label}
                </a>
            </li>`;
                            });

                            menu += `</ul></li>`;
                            if (item.separator === 'after') {
                                menu += `<li class="separator"></li>`;
                            }
                        }
                    });

                    dropdownDiv.append(menu);
                    webDataTable.actions.default(this);
                },
                error: function () {
                    // Remove loader
                    dropdownDiv.find(".loader-item").remove();
                    toastr.error("Failed to load actions");
                }
            });
        },
        icons(key) {
            const map = {
                view: "bi bi-eye",           // 👁 outline eye
                edit: "bi bi-pencil",        // ✏️ outline pencil
                delete: "bi bi-trash",       // 🗑 outline trash
                confirmed: "bi bi-check-circle", // ✅ outlined circle check
                rejected: "bi bi-x-circle",      // ❌ outlined circle X
                blocked: "bi bi-slash-circle",   // 🚫 outlined circle slash
                unblocked: "bi bi-unlock",       // 🔓 outlined unlock
                pending: "bi bi-hourglass",      // ⏳ outline hourglass
                verified: "bi bi-shield-check",  // 🛡 outline shield check
                add: "fa fa-plus-circle",
                save: "fa fa-save",
                cancel: "fa fa-times",
                refresh: "fa fa-sync",
                download: "fa fa-download",
                upload: "fa fa-upload",
                search: "fa fa-search",
                move_to: "bi bi-arrow-right-circle", // ⬅️➡️ Move To
                email: "bi bi-envelope",
                statement: 'bi bi-file-text', // <-- your statement icon
                action: 'bi bi-gear', // <-- your statement icon
                quotation: 'bi bi-file-earmark-text',
                print: 'bi bi-printer',
                comments: 'bi bi-chat-dots',
                convert: 'bi bi-arrow-repeat',
            };
            return map[key] || "fa fa-question-circle"; // fallback icon
        },
        default(row) {
            let actions = webDataTable.actions;
            actions.edit(row);
            loadJs('list.extraActions', row)
        },
        edit(row) {
            $('#row_edit').off().on({
                click() {
                    /*let defaults = {
                        size: 'large',
                        id: row.attr('data-id'),
                        rowNo: row.find('span.rowNo').text(),
                        append_url: '/edit',
                        callBack: window[MODULE].model.load,
                        // callback: DEFAULT.list.view.callBack,
                        data: '',
                        buttons: true,
                        beforeEdit: true
                    }, rowNoAppend;*/
                    //options = loadJs('before.edit', row);
                    let partyName, id = row.attr('data-id');
                    let title = $('#dataTable').data('title');
                    webModal.openGlobalModal({
                        title: 'Edit ' + title,
                        url: GLOBAL_FN.buildUrl(window[MODULE].baseUrl + '/' + id + '/create'),
                        content: null,
                        size: 'xxl',
                        scroll: true,
                        callBack: null
                    });


                    /*jQuery.extend(defaults, options);
                    if (defaults.beforeEdit) {
                        rowNoAppend = defaults.rowNo ? ' - ' + defaults.rowNo : '';
                        MODEL.options({
                            type: 'modal',
                            url: DEFAULT.initUrl(window[MODULE].baseUrl + row.attr('data-id') + '/edit'),
                            callBack: defaults.callBack,
                            options: {
                                buttons: defaults.buttons,
                                size: defaults.size,
                                data: defaults.data
                            },
                            title: window[MODULE].title + rowNoAppend
                        });

                    }*/
                }
            });
        },
    },

    edit2(table, data) {
        table.on('click', '.edit-btn', function (e) {
            e.preventDefault();

            let id = $(this).data('id');
            webModal.openGlobalModal(
                data.title,
                data.url,
                data.content ? data.content : null,
                data.size ? data.size : 'xl',
                data.scroll ? data.scroll : false,
                data.callback ? data.callback : '');
        });
    }
}


function loadJs(methodName = null, payload = '', config = {}, parentModuleName = null) {
    if ((MODULE && typeof MODULE !== 'undefined') || parentModuleName) {
        let loadModule = parentModuleName ?? MODULE;

        const moduleRoot = window[loadModule];
        const resolvedMethod = methodName ?? loadModule + '.load';
        const methodParts = resolvedMethod.split('.');

        let currentContext;
        let parentContext;

        for (let i = 0; i < methodParts.length; i++) {
            parentContext = currentContext ?? moduleRoot;

            if (typeof parentContext[methodParts[i]] === 'object') {
                currentContext = parentContext[methodParts[i]];
            } else if (
                typeof moduleRoot === 'object' &&
                typeof parentContext[methodParts[i]] === 'function'
            ) {
                return parentContext[methodParts[i]](payload, config);
            }
        }

        return true;
    }
}

const PAGE = {
    init() {
        if (appIsLocal) {
            this.addScripts();
        }
        window.addEventListener('load', () => {
            loadJs();
            GLOBAL_FN.load();
        });
    },

    addScripts() {
        const appendContainer = document.getElementById('dynamic-scripts');

        // Load main module JS
        if (MODULE) {
            console.log(MODULE);
            appendContainer.appendChild(this.createScript(MODULE));
        }

        // Load extra JS files
        const extraJsValue = document.getElementById('extra-js')?.value;
        if (extraJsValue) {
            extraJsValue.split(',').forEach(jsFile => {
                appendContainer.appendChild(this.createScript(jsFile));
            });
        }
    },

    createScript(fileName) {
        const script = document.createElement('script');
        script.src = hostUrl() + 'js/page-all-js/' + fileName.toLowerCase() + '.js?v=' + VERSION;
        return script;
    },
};

// Initialize
PAGE.init();

function showTableLoader(table, tableSelector) {
    let cols = $(`${tableSelector} thead th`).length;
    let pageLen = 10;
    let loaderRows = '';

    for (let i = 0; i < pageLen; i++) {
        loaderRows += '<tr class="loading-row">';
        for (let j = 0; j < cols; j++) {
            loaderRows += `<td><div class="loader-td"></div></td>`;
        }
        loaderRows += '</tr>';
    }

    $(`${tableSelector} tbody`).html(loaderRows);
}

function selectPicker(form, destroy) {
    let selectpicker = $(form).find("select.selectpicker").not('.with-ajax'), g, cls;
    if (destroy) {
        selectpicker.selectpicker('destroy');
    }
    if (selectpicker.length > 0) {
        /*selectpicker.not('[multiple],[required]').each(function () {
            if ($(this).find('option:first').text() !== 'Select') {
                $(this).prepend('<option value="">Select</option>');
            }
        });*/
        //$.fn.selectpicker.Constructor.BootstrapVersion = '5';
        if ($.fn.selectpicker) {
            selectpicker.selectpicker({
                size: 10,
                showSubtext: true,
            });
        } else {
            console.warn('⚠️ bootstrap-select is not loaded!');
        }

        selectpicker.each(function () {
            const maxWidth = $(this).data('max-width') || 400;
            $(this).parent('.bootstrap-select').find('.dropdown-menu').css('max-width', maxWidth + 'px');
        });

        selectpicker.on('changed.bs.select', function () {
            if ($(this).val() !== '') {
                g = $(this).closest('.form-group');
                g.find('label.is-invalid').remove();
                g.removeClass('has-danger');
                g.removeClass('is-invalid').addClass('validate');
            }
        });
        selectpicker.on('hide.bs.select', function () {
            $(this).trigger("focusout");
        });
        /*selectpicker.on('show.bs.select', function () {
            g = $(this).closest('.form-group');
            cls = g.attr('class');
            g.addClass('filter-error');
        });*/
        $('.bootstrap-select').find('button').on('keydown', function (e) {
            if (e.which === 13) {
                $(this).click();
            }
        });
    }
}

function datepicker() {
    $("input.datepicker").each(function () {
        let defaultDateAttr = $(this).data("default-date"); // read attribute
        let inputValue = $(this).val();
        let defaultDate = inputValue ?? null; // default: no date
        $(this).attr("placeholder", "dd-mm-yyyy");
        if (!inputValue && defaultDateAttr) {
            defaultDate = new Date();
        }

        flatpickr(this, {
            dateFormat: "d-m-Y",
            minDate: "01-01-2025",
            maxDate: "31-12-2027",
            altInput: true,
            allowInput: true,
            altFormat: "d-m-Y",
            defaultDate: defaultDate, // null keeps it empty
            disableMobile: true,
        });
    });

}

function initTomSelectForm(form = null, destroy = false, individualId = null) {
    if (individualId) {
        form = $(individualId);
    } else {
        form = form ? form.find('select.tom-select') : $('#moduleForm').find('select.tom-select');
    }

    form.each(function () {

        let el = this;
        if (!el) return;

        // destroy safely
        if (el.tomselect && typeof el.tomselect.destroy === "function") {
            el.tomselect.destroy();
        }

        const $el = $(el);
        const placeholder = $el.data('placeholder') ?? $el.attr('placeholder') ?? '';
        const isMultiple = $el.prop('multiple');
        const liveSearch = $el.data('live-search') === true || $el.data('live-search') === 'true';
        const create = $el.data('create') === true || $el.data('create') === 'true';
        const maxItemsAttr = parseInt($el.data('max-items'), 10);

        const ts = new TomSelect(el, {
            //create: create,
            openOnFocus: true,
            allowEmptyOption: true,
            maxItems: isMultiple ? null : 1,
            placeholder: placeholder,
            searchField: liveSearch ? ['text'] : [],
            hideSelected: false,

            plugins: [
                'dropdown_input',
                isMultiple ? 'remove_button' : null
            ].filter(Boolean),
            /*dropdownParent: 'body',*/
            onInitialize() {
                // Handle is-invalid class for validation
                if ($el.hasClass('is-invalid')) {
                    this.wrapper.classList.add('is-invalid');
                }

                // Watch for changes to the is-invalid class on the original select
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.attributeName === 'class') {
                            if ($el.hasClass('is-invalid')) {
                                this.wrapper.classList.add('is-invalid');
                            } else {
                                this.wrapper.classList.remove('is-invalid');
                            }
                        }
                    });
                });
                observer.observe(el, {attributes: true});

                // Hide search input when required
                if (!liveSearch && !isMultiple && this.control_input) {
                    this.control_input.classList.add('d-none');
                }

                if (isMultiple && this.items.length > 0 && this.control_input) {
                    this.control_input.setAttribute('placeholder', '');
                }

                const maxWidth = parseInt($el.data('max-width'), 10);
                const maxDropdownWidth = parseInt($el.data('dropdown-width'), 10);

                if (!isNaN(maxWidth)) {
                    this.wrapper.style.width = maxWidth + 'px';
                    this.control.style.width = maxWidth + 'px';
                    this.dropdown.style.width = (maxDropdownWidth ?? maxWidth) + 'px';
                }

                // --- Toggle-on-click behaviour for multiple select ---
                if (isMultiple) {
                    const dropdownContent = this.dropdown_content;

                    dropdownContent.addEventListener('click', (e) => {
                        const opt = e.target.closest('[data-value]');
                        if (!opt) return;

                        const val = opt.getAttribute('data-value');
                        if (!val) return;

                        // Already selected → remove
                        if (this.items.indexOf(val) !== -1) {

                            this.removeItem(val);

                            // 🔥 FIX: update UI instantly
                            opt.classList.remove('selected');

                            // re-run option refresh
                            this.refreshOptions(false);

                            e.stopPropagation();
                            e.preventDefault();
                            return;
                        }

                        // Adding new → let TomSelect handle
                        setTimeout(() => {
                            this.refreshOptions(false);  // update UI after select
                        }, 10);
                    }, {passive: false});
                }
            },

            render: {
                option: function (data, escape) {

                    if (data.divider === true || data.value === '__divider__') {
                        return '<div class="ts-divider">──────────</div>';
                    }

                    const subtext = data.subtext
                        ? `<div class="ts-subtext">${escape(data.subtext)}</div>`
                        : '';

                    // IMPORTANT: MUST INCLUDE class="option"
                    return `
                        <div class="option ts-option-item ts-light-divider" data-value="${escape(data.value)}">
                            ${escape(data.text)}${subtext}
                        </div>
                    `;
                },

                item: function (data, escape) {
                    return `<div class="item">${escape(data.text)}</div>`;
                }
            },

            onItemAdd(value, data) {
                if (value === "__new__") {
                    const self = this;
                    const option = self.input.querySelector(`option[value="${value}"]`);
                    const module = option ? option.getAttribute('data-module') : null;

                    setTimeout(() => {
                        self.clear(true);

                        if (module) {
                            loadJs('form.quick.open', '', '', module);
                        } else {
                            console.error('Module not found!');
                        }
                    }, 10);
                }
            },
            create: create ? function (input, callback) {
                $.ajax({
                    url: "/items/store",
                    type: "POST",
                    data: {
                        name: input,
                        _token: $('meta[name="csrf-token"]').attr("content")
                    },
                    success: function (response) {

                        let newItem = {
                            id: response.id,
                            name: response.name,
                            subtext: response.subtext
                        };

                        // send item back to TS internal process
                        callback({
                            value: newItem.id,
                            text: newItem.name,
                            subtext: newItem.subtext
                        });

                        // ADD item to dropdown list
                        this.addOption({
                            value: newItem.id,
                            text: newItem.name
                        });

                        // SELECT new item
                        this.addItem(newItem.id);

                        // CLOSE dropdown
                        this.close();
                    }.bind(this), // 🔥 IMPORTANT - bind TomSelect instance

                    error: function () {
                        callback();
                    }
                });
            } : false

        });

    });
}


function initTomSelectSearch(selector, dbName, maxLength = 100, preLoad = null) {
    const $selector = $(selector);
    const placeholder = $selector.data('placeholder') ?? $selector.attr('placeholder') ?? '';
    new TomSelect(selector, {
        //valueField: 'id',
        valueField: 'name',
        labelField: 'name',
        searchField: 'name',
        create: false,               // disable creating new entries
        placeholder: placeholder,
        maxOptions: 50,
        openOnFocus: true,
        maxItems: 1,
        allowEmptyOption: true,      // allows placeholder selection
        preload: preLoad ? 'focus' : false,
        hideSelected: false,
        plugins: [
            'dropdown_input', null
        ].filter(Boolean),
        //dropdownParent: 'body',
        /*onInitialize() {
            if (this.control_input) {
                this.control_input.classList.add('d-none'); // hide built-in search input
            }
        },*/
        onType: function (str) {
            if (str.length > maxLength) {
                this.clear();        // optional: prevent exceeding maxlength
            }
        },
        load: function (query, callback) {
            let finalQuery = query;

            if (preLoad) {
                // When preload enabled → always load all data
                if (!query || query.length === 0) {
                    finalQuery = '';
                }
            }

            if (!finalQuery.length && !preLoad) return callback();

            fetch(`/dropdown/search?query=${encodeURIComponent(finalQuery)}&db=${dbName}`)
                .then(res => res.json())
                .then(json => {
                    const data = json.map(item => ({
                        id: item.id,
                        name: item.name
                    }));
                    callback(data);
                })
                .catch(() => callback());
        },
        onDropdownClose() {
            // restore input if it was moved
            if (this.control_input && this.control) {
                this.control.appendChild(this.control_input);
            }
        },

    });
}

CURRENCY = {
    currencyRate() {
        $('#currency-code').on('change', function () {
            const currency = $(this).val();
            const loader = $('#rate-loader');

            // Show loader
            loader.removeClass('d-none');
            $('#currency-rate').val('');
            $.ajax({
                url: '/currency/rate/' + currency + '/' + baseCurrency,
                type: 'GET',
                data: {currency: currency},
                success: function (response) {
                    $('#currency-rate').val(response.conversion_rate);
                },
                error: function () {
                    alert('Failed to fetch exchange rate');
                },
                complete: function () {
                    loader.addClass('d-none');
                }
            });
        })
    }
}
const INPUT = {
    decimalDigits: 2,
    load() {
        // Initialize defaults immediately when the page loads
        INPUT.setDefaultZeros();

        // Also handle dynamically added inputs (like in tables)
        const observer = new MutationObserver(() => INPUT.setDefaultZeros());
        observer.observe(document.body, {childList: true, subtree: true});

        document.addEventListener('keydown', function (e) {
            const el = e.target;
            if (!(el.classList.contains('integer') || el.classList.contains('float'))) return;

            const key = e.key;
            const value = el.value;
            const allowNegative = el.classList.contains('negative');

            if (["Backspace", "Delete", "ArrowLeft", "ArrowRight", "Tab", "Home", "End"].includes(key)) return;

            if (allowNegative && key === '-' && el.selectionStart === 0 && !value.includes('-')) return;

            if (el.classList.contains('integer') && !/^\d$/.test(key)) e.preventDefault();

            if (el.classList.contains('float')) {
                if (key === '.' && !value.includes('.')) return;
                if (/^\d$/.test(key)) {
                    const dotIndex = value.indexOf('.');
                    if (dotIndex !== -1 && el.selectionStart > dotIndex) {
                        const decimals = value.split('.')[1] || '';
                        if (decimals.length >= INPUT.decimalDigits) e.preventDefault();
                    }
                    return;
                }
                e.preventDefault();
            }
        });

        // Input & paste: sanitize
        document.addEventListener('input', function (e) {
            const el = e.target;
            if (el.classList.contains('integer') || el.classList.contains('float')) {
                INPUT.sanitizeValue(el);
            }
        });

        // Blur: format the value + set zero default
        document.addEventListener('blur', function (e) {
            const el = e.target;
            if (el.classList.contains('integer') || el.classList.contains('float')) {
                INPUT.ensureDefaultZero(el);
                INPUT.formatValue(el);
            }
        }, true);

        // Focus: select all
        document.addEventListener('focus', function (e) {
            const el = e.target;
            if (el.classList.contains('integer') || el.classList.contains('float')) {
                el.select();
            }
        }, true);
    },

    // Set all inputs with class integer/float to default values if empty
    setDefaultZeros() {
        document.querySelectorAll('input.integer, input.float').forEach(el => {
            if (!el.value.trim()) {
                if (el.classList.contains('float')) {
                    el.value = '0.' + '0'.repeat(INPUT.decimalDigits);
                } else {
                    el.value = '0';
                }
            }
        });
    },

    sanitizeValue(el) {
        let val = el.value;
        const isInteger = el.classList.contains('integer');
        const isFloat = el.classList.contains('float');
        const allowNegative = el.classList.contains('negative');

        let negative = '';
        if (allowNegative && val.startsWith('-')) {
            negative = '-';
            val = val.slice(1);
        }

        if (isInteger) val = val.replace(/\D/g, '');
        if (isFloat) {
            val = val
                .replace(/[^0-9.]/g, '')
                .replace(/(\..*)\./g, '$1');
            const match = val.match(/^(\d+)(\.\d{0,2})?.*$/);
            if (match) val = match[1] + (match[2] || '');
        }

        el.value = negative + val;
    },

    ensureDefaultZero(el) {
        let val = el.value.trim();
        if (!val || val === '-' || val === '.' || val === '-.') {
            // default based on type
            if (el.classList.contains('float')) {
                el.value = '0.' + '0'.repeat(INPUT.decimalDigits);
            } else {
                el.value = '0';
            }
        }
    },
    formatValue(el) {
        let val = el.value;
        if (!val) return;

        let negative = '';
        if (val.startsWith('-')) {
            negative = '-';
            val = val.slice(1);
        }

        val = val.replace(/,/g, ''); // remove commas

        if (el.classList.contains('float')) {
            if (!val.includes('.')) val += '.00';
            let [intPart, decPart = ''] = val.split('.');
            intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            decPart = decPart.padEnd(INPUT.decimalDigits, '0').slice(0, INPUT.decimalDigits);
            el.value = negative + intPart + '.' + decPart;
        } else if (el.classList.contains('integer')) {
            let intPart = val.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            el.value = negative + intPart;
        }
    }
};

function amountFormat(number) {
    // Assuming the currency is SAR and you want 2 decimal places
    return new Intl.NumberFormat('en-SA', {
        style: 'decimal',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(number);
}
