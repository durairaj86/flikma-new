const appIsLocal = document.querySelector('meta[name="app-js"]').getAttribute('content') === 'local';
// Get the current module from meta tag (uppercase) or empty string
var MODULE = $("body").attr("data-module");
MODULE = MODULE ? MODULE.toUpperCase() : "";
// DOM elements
const navTabs = document.getElementById('nav-tabs');
// App version
const VERSION = 1;
// Reference to global window object
const WINDOW = window;

const hostName = window.location.origin;

let dataTableId = $('#dataTable');

/*document.addEventListener("turbo:load", () => {
    console.log("Turbo page loaded:", window.location.pathname);
    window[MODULE].load();
    // run page-specific initializations here
    // e.g. re-bind DataTables, tooltips, etc.
});*/

// Fires when only a <turbo-frame> loads
document.addEventListener("turbo:load", () => {
    //console.log(MODULE);

    // Call global functions
    /*if (typeof globalFunction !== 'undefined') {
        globalFunction.load();
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
        globalFunction.load();
    }, 100)
});

/*document.addEventListener("DOMContentLoaded", function() {
    globalFunction.load();
    console.log("ffff")
});*/

function hostUrl() {
    return hostName + '/';
}

function fullUrl() {
    return window.location.href;
}

let globalFunction = {
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
        globalFunction.dataTableLoad();
    },
    dataTableLoad() {
        /*setTimeout(function(){
            window[MODULE].dataTable.load();
        },1000)*/
        window[MODULE].dataTable.load();
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
    }
}


function ensureBackdrop() {
    $('.modal-backdrop').remove();
    $('body').append('<div class="modal-backdrop fade show"></div>');
}

document.getElementById('globalModal').addEventListener('keydown', function (e) {
    switch (e.key) {
        case "F1":
            e.preventDefault();
            console.log("New triggered");
            break;
        case "F2":
            e.preventDefault();
            console.log("Edit triggered");
            break;
        case "F3":
            e.preventDefault();
            console.log("View triggered");
            break;
    }
});


function setupModalEscBootstrapConfirm(modalEl, modalSelector, message = "Are you sure you want to close the modal?") {
    /*const modalEl = document.querySelector(modalSelector);

    // Disable default ESC close
    $(modalEl).modal({
        keyboard: false,
        backdrop: 'static'
    });*/

    // Attach listener only once

    /*let lastFocusedInput = null;

    modalEl.addEventListener('focusin', function (e) {
        if (e.target.matches('input, textarea, select')) {
            lastFocusedInput = e.target;
            console.log(lastFocusedInput.id);
        }
    });

    modalEl.addEventListener('shown.bs.modal', function () {
        const firstInput = modalEl.querySelector('.modal-dialog .modal-content input:not([type="hidden"]), .modal-dialog .modal-content textarea, .modal-dialog .modal-content select');

        if (firstInput) {
            if (!lastFocusedInput) {
                firstInput.focus();
            }

            console.log("First input ID:", firstInput.id || "(no id)");
        }
    });*/

    $('#globalModal a.btn-close').off().on('click', function () {
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
        if (e.target.matches('input:not([type="hidden"]), textarea, select')) {
            lastFocusedInput = e.target;
        }
    });

    modalEl.addEventListener('shown.bs.modal', function () {
        const focusable = modalEl.querySelectorAll(focusableSelector);
        const focusableArr = Array.from(focusable).filter(el => !el.disabled && el.offsetParent !== null);
        if (focusableArr.length) {
            firstEl = focusableArr[0];
            lastEl = focusableArr[focusableArr.length - 1];
            if(!lastFocusedInput) firstEl.focus();
            /*if (lastFocusedInput) {
                $('#' + lastFocusedInput.id).focus();
            } else {
                firstEl.focus();
            }*/
        }
        //cleanExtraBackdrops();
    });
    /*confirmModalEl.addEventListener('shown.bs.modal', function () {
        cleanExtraBackdrops();
    });*/

    /*modalEl.addEventListener('hidden.bs.modal', function () {
        cleanExtraBackdrops();
    });*/

    /*confirmModalEl.addEventListener('hidden.bs.modal', function () {
        cleanExtraBackdrops();
    });*/

    /*confirmModalEl.addEventListener('keydown', function (e) {
        if (['Tab', 'ArrowRight', 'ArrowLeft'].includes(e.key)) {
            e.preventDefault();

            const confirmFocusable = confirmModalEl.querySelectorAll(focusableSelector);
            const confirmFocusableArr = Array.from(confirmFocusable).filter(el => !el.disabled && el.offsetParent !== null);

            let confirmIndex = confirmFocusable.indexOf(document.activeElement);
            if (confirmIndex === -1) return;

            // Determine direction
            let moveForward = (e.key === 'Tab' && !e.shiftKey) || e.key === 'ArrowRight';
            let moveBackward = (e.key === 'Tab' && e.shiftKey) || e.key === 'ArrowLeft';

            if (moveForward) {
                let confirmNextIndex = (confirmIndex + 1) % confirmFocusableArr.length;
                confirmFocusableArr[confirmNextIndex].focus();
            } else if (moveBackward) {
                let confirmPrevIndex = (confirmIndex - 1 + confirmFocusableArr.length) % confirmFocusableArr.length;
                confirmFocusableArr[confirmPrevIndex].focus();
            }
        }

    });*/

    /*if (e.key === 'Tab' || e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
        if ((e.shiftKey || e.key === 'ArrowRight') && document.activeElement === firstEl) {
            e.preventDefault();
            lastEl.focus();
        } else if ((!e.shiftKey || e.key === 'ArrowLeft') && document.activeElement === lastEl) {
            e.preventDefault();
            firstEl.focus();
        }
    }*/

    modalEl.addEventListener('keydown', function (e) {
        if (['Tab', 'ArrowRight', 'ArrowLeft'].includes(e.key)) {
            e.preventDefault();

            const focusable = modalEl.querySelectorAll(focusableSelector);
            const focusableArr = Array.from(focusable).filter(el => !el.disabled && el.offsetParent !== null);

            // Use lastFocusedInput if set, else document.activeElement
            const currentEl = lastFocusedInput || document.activeElement;
            let index = focusableArr.indexOf(currentEl);
            if (index === -1) return;

            // Determine direction
            let moveForward = (e.key === 'Tab' && !e.shiftKey) || e.key === 'ArrowRight';
            let moveBackward = (e.key === 'Tab' && e.shiftKey) || e.key === 'ArrowLeft';

            if (moveForward) {
                let nextIndex = (index + 1) % focusableArr.length;
                focusableArr[nextIndex].focus();
            } else if (moveBackward) {
                let prevIndex = (index - 1 + focusableArr.length) % focusableArr.length;
                focusableArr[prevIndex].focus();
            }
        } else if (e.key === 'Escape') {
            e.preventDefault();
            e.stopPropagation();

            document.getElementById('confirmMessage').textContent = message;

            const parentModal = bootstrap.Modal.getInstance(modalEl);
            if (parentModal) parentModal.hide();

            // Always clean before showing confirm modal
            //cleanExtraBackdrops();

            confirmModal.show();
            ensureBackdrop();

            yesBtn.addEventListener('click', function yesHandler() {
                confirmModal.hide();
                $('.modal-backdrop').remove();
            }, {once: true});

            cancelBtn.addEventListener('click', function cancelHandler() {
                confirmModal.hide();
                if (parentModal) parentModal.show();
                ensureBackdrop();

                if (lastFocusedInput) lastFocusedInput.focus();
            }, {once: true});
        }
    });

    /*modalEl.addEventListener('keydown', function (e) {
        if (e.key === 'Tab' || e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
            if ((e.shiftKey || e.key === 'ArrowRight') && document.activeElement === firstEl) {
                e.preventDefault();
                lastEl.focus();
            } else if ((!e.shiftKey || e.key === 'ArrowLeft') && document.activeElement === lastEl) {
                e.preventDefault();
                firstEl.focus();
            }
        }
        if (['Tab', 'ArrowRight', 'ArrowLeft'].includes(e.key)) {
            e.preventDefault();

            const focusable = modalEl.querySelectorAll(focusableSelector);
            const focusableArr = Array.from(focusable).filter(el => !el.disabled && el.offsetParent !== null);

            let index = focusableArr.indexOf(document.activeElement);
            if (index === -1) return;

            // Determine direction
            let moveForward = (e.key === 'Tab' && !e.shiftKey) || e.key === 'ArrowRight';
            let moveBackward = (e.key === 'Tab' && e.shiftKey) || e.key === 'ArrowLeft';

            if (moveForward) {
                let nextIndex = (index + 1) % focusableArr.length;
                focusableArr[nextIndex].focus();
            } else if (moveBackward) {
                let prevIndex = (index - 1 + focusableArr.length) % focusableArr.length;
                focusableArr[prevIndex].focus();
            }
        }
        else if (e.key === 'Escape') {
            e.preventDefault();
            e.stopPropagation();

            document.getElementById('confirmMessage').textContent = message;

            const parentModal = bootstrap.Modal.getInstance(modalEl);
            if (parentModal) parentModal.hide();

            // Always clean before showing confirm modal
            //cleanExtraBackdrops();

            confirmModal.show();
            ensureBackdrop();

            yesBtn.addEventListener('click', function yesHandler() {
                confirmModal.hide();
                $('.modal-backdrop').remove();
            }, {once: true});

            cancelBtn.addEventListener('click', function cancelHandler() {
                confirmModal.hide();
                if (parentModal) parentModal.show();
                ensureBackdrop();

                if (lastFocusedInput) lastFocusedInput.focus();
            }, {once: true});
        }
    });*/


}


// Handle AJAX form submit
let webModal = {
    openGlobalModal(title = 'Modal', url = null, content = null, size = 'md', scrollable = true, callback = null) {
        const modalEl = document.getElementById('globalModal');

// Remove existing keydown handler if any
        modalEl.onkeydown = null;

// Add keydown listener to prevent ESC
        /*modalEl.addEventListener('keydown', function(e) {
            if (modalEl.classList.contains('show') && e.key === 'Escape') {
                e.preventDefault();
                e.stopPropagation();
                /!*if (confirm("Are you sure you want to close the modal?")) {
                    $('#globalModal').modal('hide'); // close if confirmed
                }*!/
                console.log('ESC prevented');
            }
        });*/


        //setupModalEscBootstrapConfirm('#subModal', "Close sub modal?");


        const modalDialog = $('#globalModalDialog');

        // Remove previous size & scrollable classes
        modalDialog.removeClass('modal-sm modal-md modal-lg modal-xl modal-fullscreen modal-dialog-scrollable');

        // Add size class
        switch (size) {
            case 'sm':
                modalDialog.addClass('modal-sm');
                break;
            case 'md':
                modalDialog.addClass('modal-md');
                break;
            case 'lg':
                modalDialog.addClass('modal-lg');
                break;
            case 'xl':
                modalDialog.addClass('modal-xl');
                break;
            case 'fullscreen':
                modalDialog.addClass('modal-fullscreen');
                break;
            default:
                modalDialog.addClass('modal-md');
        }

        // Add scrollable body
        if (scrollable) modalDialog.addClass('modal-dialog-scrollable');

        // Set title
        $('#globalModalTitle').text(title);

        // Show initial loader
        $('#globalModalBody').html(`
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2">Loading...</div>
        </div>
    `);

        const modal = new bootstrap.Modal(modalEl, {
            keyboard: false,   // Disable ESC
            backdrop: 'static' // Optional: disable click outside
        });
        modal.show();

        /*$(document).on('keydown', function (event) {//ESC button prevent
            if (event.key === "Escape") {
                // If main modal is visible
                if ($('#globalModal').hasClass('show')) {
                    event.preventDefault();
                    event.stopPropagation();
                }
            }
        });*/

// Example usage
        setupModalEscBootstrapConfirm(modalEl, '#globalModal', "Close main modal?");


        // Load content via AJAX or inline
        if (url) {
            $.get(url, function (data) {
                $('#globalModalBody').html(data);
                webModal.buttons();
            });
        } else if (content) {
            $('#globalModalBody').html(content);
            webModal.buttons();
        }
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
                        if (response.status === 'success') {
                            toastr.success(response.message);

                            // Reload DataTable
                            $('#dataTable').DataTable().ajax.reload(null, false);

                            // Close modal
                            modal.hide();

                            // Reset form
                            $('#customerForm')[0].reset();
                            if (callback) callback(response);
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
    buttons() {//set button dynamically
        const form = $('#globalModalBody').find('form');
        if (form.length) {
            const formId = form.attr('id');
            $('#globalModalFooter').html(`
                <div class="d-flex justify-content-end gap-2 mt-3">
  <button
    type="button"
    class="btn btn-outline-secondary"
    id="modalClearBtn"
    aria-label="Clear the form"
  >
    <i class="bi bi-x-circle me-1"></i> Clear
  </button>

  <button
    type="submit"
    class="btn btn-primary"
    form="${formId}"
    aria-label="Submit the form"
  >
    <i class="bi bi-check-circle me-1"></i> Submit
  </button>
</div>

            `).show();

            $('#modalClearBtn').off('click').on('click', function () {
                form[0].reset();
            });
        } else {
            $('#globalModalFooter').hide();
        }
    }
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
                url: `/customer/${rowId}/actions`,
                type: "GET",
                context: row,
                success: function (actions) {
                    // Remove loader
                    dropdownDiv.find(".loader-item").remove();

                    let menu = '';
                    actions.forEach(item => {
                        if (item.type === 'item') {
                            // simple menu item
                            menu += `<li>
            <a class="dropdown-item ${item.class}"
               id="${item.id}"
               data-id="${item['data-id']}"><i class="fas fa-edit"></i>
               ${item.label}
            </a>
        </li>`;
                        } else if (item.type === 'submenu') {
                            // submenu
                            menu += `<li class="dropdown-submenu">
            <a class="dropdown-item dropdown-toggle J-N-JX BS" href="#"><i class="fas fa-eye"></i> ${item.label}</a>
            <ul class="dropdown-menu">`;

                            item.items.forEach(sub => {
                                menu += `<li>
                <a class="dropdown-item ${sub.class}"
                   id="${sub.id}"
                   data-id="${sub['data-id']}"><i class="fas fa-edit"></i>
                   ${sub.label}
                </a>
            </li>`;
                            });

                            menu += `</ul></li>`;
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
        default(row) {
            console.log(row);
            let actions = webDataTable.actions;
            actions.edit(row);
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
                    let rowNoAppend, id = row.attr('data-id');
                    rowNoAppend = row.find('span.rowNo').text();
                    webModal.openGlobalModal(
                        window[MODULE].title + rowNoAppend,
                        globalFunction.buildUrl(window[MODULE].baseUrl + '/' + id + '/create'), null, 'xl', false);

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
            globalFunction.load();
        });
    },

    addScripts() {
        const appendContainer = document.getElementById('dynamic-scripts');

        // Load main module JS
        if (MODULE) {
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
