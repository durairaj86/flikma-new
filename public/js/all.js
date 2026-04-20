/******/ (() => { // webpackBootstrap
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!******************************************!*\
  !*** ./public/js/page-all-js/account.js ***!
  \******************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
ACCOUNT = {
  title: 'Accounts',
  baseUrl: 'finance/account',
  actionUrl: 'finance/accounts',
  load: function load() {
    //ACCOUNT.form.load();
    this.list.bindTabs();
    //this.list.dataTable('asset');
  },
  list: {
    load: function load(activeTab) {
      ACCOUNT.list.dataTable(activeTab);
    },
    bindTabs: function bindTabs() {
      var _this = this;
      $('#listTabs button').on('click', function (e) {
        var tab = $(e.target).attr('id');
        _this.dataTable(tab);
      });
    },
    dataTable: function dataTable() {
      var activeTab = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      GLOBAL_FN.destroyDataTable();
      activeTab = activeTab && _typeof(activeTab) !== 'object' ? activeTab : $("#listTabs").find('li button.active').attr('id');
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        ajax: {
          url: GLOBAL_FN.buildUrl('finance/account/data'),
          type: 'POST',
          data: {
            type: activeTab
          },
          dataSrc: function dataSrc(json) {
            $('#dataTable tbody .loading-row').remove();
            GLOBAL_FN.setStatusCounts(json.statusCounts);
            return json.data;
          }
        },
        columns: [{
          data: 'name'
        }, {
          data: 'code'
        }, {
          data: 'parent_id'
        }, {
          data: 'account_number'
        }, {
          data: 'is_active',
          render: function render(data, type, row) {
            return '<div class="form-check form-switch"><input class="form-check-input is_active" type="checkbox" data-old-value="' + row.is_active + '" value="1" ' + (row.is_active ? "checked" : "") + '></div>';
          }
        }, GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: "" // removes "Search:" label
        },
        //deferLoading: 0, // don't load immediately
        initComplete: function initComplete() {
          ACCOUNT.form.open();
          webDataTable.actions.menu();
          ACCOUNT.list.actions.statusChange();
        }
      });
      $('#customSearch').on('keyup', function () {
        table.search(this.value).draw();
      });
      //webDataTable.loader(table);
      webDataTable.search(table);
    },
    extraActions: function extraActions(row) {},
    actions: {
      statusChange: function statusChange() {
        $(document).off('change', '.is_active').on('change', '.is_active', function () {
          var row = $(this).closest('tr');
          var fd = new FormData();
          changeCustomerStatus(GLOBAL_FN.buildUrl('finance/accounts/' + row.attr('data-id') + '/status/' + $(this).is(':checked')), {
            method: 'POST',
            data: fd,
            callBack: 'datatable'
          }, $(this).attr('data-value'), $(this));
        });
      }
    }
  },
  form: {
    load: function load() {
      ACCOUNT.form.open();
    },
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'New Account',
          url: GLOBAL_FN.buildUrl('finance/account/create'),
          content: null,
          size: 'lg'
        });
      });
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!******************************************!*\
  !*** ./public/js/page-all-js/airport.js ***!
  \******************************************/
AIRPORT = {
  baseUrl: 'masters/transport/directories/airport',
  actionUrl: 'masters/transport/directories/airport',
  load: function load() {
    AIRPORT.form.open();
  },
  list: {
    load: function load() {
      AIRPORT.list.dataTable();
    },
    dataTable: function dataTable() {
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        order: [[1, 'desc']],
        ajax: {
          url: '/masters/transport/directories/airport/data',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          dataSrc: function dataSrc(json) {
            // Remove loader rows when data arrives
            $('#dataTable tbody').find('.loading-row').remove();
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0],
          orderable: false
        }
        /*{targets: [1], class: 'hide', visible: false},*/],
        columns: [{
          data: 'DT_RowIndex',
          "class": 'text-center hide-tooltip fav-index'
        }, {
          data: 'name',
          render: function render(data, type, row) {
            return row.name;
          }
        }, {
          data: 'code',
          render: function render(data, type, row) {
            return row.code;
          }
        }, {
          data: 'country_name',
          render: function render(data, type, row) {
            return row.country_name;
          }
        },
        // Actions column
        GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: "" // removes "Search:" label
        },
        deferLoading: 0,
        // don't load immediately

        initComplete: function initComplete() {
          // Add custom class to default search input
          AIRPORT.form.open();
          webDataTable.actions.menu();
        }
      });
      $('#customSearch').on('keyup', function () {
        table.search(this.value).draw();
      });
      webDataTable.loader(table);
      webDataTable.search(table);
      //webDataTable.actions.menu();
    }
  },
  form: {
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'Add Airport',
          url: GLOBAL_FN.buildUrl('masters/transport/directories/airport/create'),
          content: null,
          size: 'xl',
          callBack: null
        });
      });
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!***************************************!*\
  !*** ./public/js/page-all-js/bank.js ***!
  \***************************************/
BANK = {
  baseUrl: 'masters/bank',
  actionUrl: 'masters/bank',
  load: function load() {
    BANK.form.open();
    BANK.shortcutFn();
  },
  shortcutFn: function shortcutFn() {
    //webModal.optionsChildModal('currency');
    /*document.addEventListener('keydown', function (e) {
        if (e.key === 'F1') {
            e.preventDefault(); // stop browser help from opening
            webModal.shortCut.closeSubModal('currencyModal');
        }
    });*/
  },
  list: {
    load: function load() {
      BANK.list.dataTable();
    },
    dataTable: function dataTable() {
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        order: [[1, 'desc']],
        ajax: {
          url: '/masters/bank/data',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          dataSrc: function dataSrc(json) {
            // Remove loader rows when data arrives
            $('#dataTable tbody').find('.loading-row').remove();
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0],
          orderable: false
        }
        /*{targets: [1], class: 'hide', visible: false},*/],
        columns: [{
          data: 'DT_RowIndex',
          "class": 'text-center hide-tooltip fav-index'
        }, {
          data: 'bank_name',
          render: function render(data, type, row) {
            return '<div>' + row.bank_name + '</div><small class="text-muted">' + row.branch_name + '</small>';
          }
        }, {
          data: 'account_number'
        }, {
          data: 'account_holder'
        }, {
          data: 'currency'
        }, {
          data: 'iban_code'
        }, {
          data: 'swift_code'
        }, {
          data: 'bank_address'
        }, {
          data: 'sort',
          "class": 'text-end'
        },
        // Actions column
        GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: "" // removes "Search:" label
        },
        deferLoading: 0,
        // don't load immediately

        initComplete: function initComplete() {
          BANK.form.open();
          webDataTable.actions.menu();
        }
      });
      $('#customSearch').on('keyup', function () {
        table.search(this.value).draw();
      });
      webDataTable.loader(table);
      webDataTable.search(table);
      //webDataTable.actions.menu();
    },
    extraActions: function extraActions(row) {
      BANK.list.actions.statusChange(row);
      BANK.list.actions.view(row);
    },
    actions: {
      statusChange: function statusChange(row) {
        /*$('#row_pending,#row_confirm,#row_blocked,#row_rejected').off().on('click', function () {
            let fd = new FormData();
            changeCustomerStatus(GLOBAL_FN.buildUrl('customer/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                method: 'POST',
                data: fd,
                callBack: 'datatable'
            }, $(this).attr('data-value'));
        })*/
      },
      view: function view(row) {
        $('#row_view').off().on('click', function () {
          var customerId = row.attr('data-id');

          // Open drawer

          var drawer = new bootstrap.Offcanvas(document.getElementById('bankDrawer'));
          drawer.show();

          // Load Overview
          $('#customerOverview').html('<p>Loading...</p>');
          $.get('/masters/bank/' + customerId + '/overview', function (data) {
            $('#customerOverview').html(data);
          });
        });
      }
    }
  },
  form: {
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'Add Bank',
          url: GLOBAL_FN.buildUrl('masters/bank/create'),
          content: null,
          size: 'xl',
          callBack: null
        });
      });
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!******************************************!*\
  !*** ./public/js/page-all-js/company.js ***!
  \******************************************/
COMPANY = {
  title: 'Company',
  baseUrl: 'settings/company',
  actionUrl: 'settings/company',
  load: function load() {
    var logoInput = document.getElementById('logoInput');
    var signatureInput = document.getElementById('signatureInput');
    $('#logoUploadBox').on('click', function () {
      logoInput.click();
    });
    $('#signatureUploadBox').on('click', function () {
      signatureInput.click();
    });
    $(logoInput).on('change', function (event) {
      var file = event.target.files[0];
      if (file) {
        var reader = new FileReader();
        reader.onload = function (e) {
          $('#logoPreview').attr("src", e.target.result);
          $('#logoPreview').removeClass('d-none');
          $('#logoUploadBox .upload-text').addClass('d-none');
        };
        reader.readAsDataURL(file);
      }
    });
    $(signatureInput).on('change', function (event) {
      var file = event.target.files[0];
      if (file) {
        var reader = new FileReader();
        reader.onload = function (e) {
          $('#signaturePreview').attr("src", e.target.result);
          $('#signaturePreview').removeClass('d-none');
          $('#signatureUploadBox .upload-text').addClass('d-none');
        };
        reader.readAsDataURL(file);
      }
    });
    function toggleVatFields() {
      var complianceGroup = $('.vat-compliance-group');
      if ($('#vat_status').val() === '1') {
        complianceGroup.slideDown(200);
        $('#vatNumber').attr('required', true);
        $('#crNumber').attr('required', true);
      } else {
        complianceGroup.slideUp(200);
        $('#vatNumber').removeAttr('required');
        $('#crNumber').removeAttr('required');
      }
    }
    $('#vat_status').on('change', toggleVatFields);
    $('#submit').off().on('click', function (e) {
      e.preventDefault();
      var form = $('#company-form');
      var action = form.attr('action');
      var method = form.attr('method') || 'POST';
      // Reset validation
      /*form.removeClass("was-validated");
       if (!form[0].checkValidity()) {
          e.stopPropagation();
          form.addClass("was-validated");
          return;
      }*/
      if (form.valid()) {
        // Build form data
        var formData = new FormData(form[0]);
        // const formData = new FormData(this);

        var submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Submitting');
        $.ajax({
          url: action,
          type: method,
          data: formData,
          //context: modal,
          processData: false,
          contentType: false,
          success: function success(response) {
            console.log(response);
            if (response.status === 'success') {
              toastr.success(response.message);

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
          error: function error(xhr) {
            var errors = xhr.responseJSON.errors;
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
          complete: function complete() {
            submitBtn.prop('disabled', false).html('Submit');
          }
        });
      }
    });
  },
  list: {
    load: function load() {}
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!*************************************************!*\
  !*** ./public/js/page-all-js/container_type.js ***!
  \*************************************************/
CONTAINER_TYPE = {
  baseUrl: 'masters/services',
  load: function load() {},
  list: {
    load: function load() {
      CONTAINER_TYPE.list.dataTable();
    },
    dataTable: function dataTable() {
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        order: [[1, 'desc']],
        ajax: {
          url: '/masters/container/type/data',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          dataSrc: function dataSrc(json) {
            // Remove loader rows when data arrives
            $('#dataTable tbody').find('.loading-row').remove();
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0],
          orderable: false
        }
        /*{targets: [1], class: 'hide', visible: false},*/],
        columns: [{
          data: 'DT_RowIndex',
          "class": 'text-center hide-tooltip fav-index'
        }, {
          data: 'code',
          render: function render(data, type, row) {
            return row.code;
          }
        }, {
          data: 'name',
          render: function render(data, type, row) {
            return row.name;
          }
        }, {
          data: 'description',
          render: function render(data, type, row) {
            return row.description;
          }
        }],
        language: {
          search: "" // removes "Search:" label
        },
        deferLoading: 0,
        // don't load immediately

        initComplete: function initComplete() {
          // Add custom class to default search input
          CONTAINER_TYPE.openModel();
          webDataTable.actions.menu();
        }
      });
      $('#customSearch').on('keyup', function () {
        table.search(this.value).draw();
      });
      webDataTable.loader(table);
      webDataTable.search(table);
      //webDataTable.actions.menu();
    }
  },
  openModel: function openModel() {
    $('#new').click(function () {
      webModal.openGlobalModal('Add Service', globalFunction.buildUrl('masters/services/create'), null, 'xl', true);
    });
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!**********************************************!*\
  !*** ./public/js/page-all-js/credit_note.js ***!
  \**********************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
CREDIT_NOTE = {
  title: 'Credit Note',
  baseUrl: 'adjustment/credit-note',
  actionUrl: 'adjustment/credit-note',
  load: function load() {
    CREDIT_NOTE.form.load();
  },
  printPreview: function printPreview(printId) {
    var iframe = document.getElementById('print-frame');
    iframe.onload = function () {
      try {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        var doc = iframe.contentDocument || iframe.contentWindow.document;
        iframe.style.height = doc.body.scrollHeight + 'px';
      } catch (e) {
        console.error('Cannot print iframe content. Cross-origin issue?', e);
      }
    };
    iframe.src = '/' + CREDIT_NOTE.baseUrl + '/' + printId + '/print';
  },
  downloadPDF: function downloadPDF(printId) {
    fetch('/adjustment/credit-note/' + printId + '/print').then(function (res) {
      return res.text();
    }).then(function (html) {
      var container = document.createElement('div');
      //container.style.display = 'none';
      container.id = 'html-pdf';
      container.className = 'px-4 pt-4';
      container.innerHTML = html;
      console.log(container);
      //document.body.appendChild(container);
      var opt = {
        margin: 0.2,
        filename: "creditNote-".concat(printId, ".pdf")
      };
      html2pdf().set(opt).from(container).save()["finally"](function () {
        //document.body.removeChild(container);
      });
    });
  },
  list: {
    load: function load(activeTab) {
      CREDIT_NOTE.list.dataTable(activeTab);
    },
    dataTable: function dataTable() {
      var activeTab = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      GLOBAL_FN.destroyDataTable();
      activeTab = activeTab && _typeof(activeTab) !== 'object' ? activeTab : $("#listTabs").find('li button.active').attr('id');
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        orderable: false,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        /*order: [[1, 'desc']],*/
        ajax: {
          url: GLOBAL_FN.buildUrl('adjustment/credit-note/data'),
          type: 'POST',
          data: {
            'tab': activeTab
          },
          dataSrc: function dataSrc(json) {
            $('#dataTable tbody').find('.loading-row').remove();
            GLOBAL_FN.setStatusCounts(json.statusCounts);
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0, 1, 2, 3, 4, 5, 6, 7, 8],
          orderable: false
        }],
        columns: [{
          data: 'row_no',
          render: function render(data, type, row) {
            return '<strong>' + row.row_no + '</strong>';
          }
        }, {
          data: 'job_no'
        }, {
          data: 'customer.name_en',
          render: function render(data, type, row) {
            return '<div>' + row.customer.name_en + '</div><div class="small text-muted">Code: ' + row.customer.row_no + '</div>';
          }
        },
        /*{
            data: 'currency', render: function (data, type, row) {
                if (row.currency == baseCurrency) {
                    return '<div>' + row.currency + '</div>';
                } else {
                    return '<div>' + row.currency + ' → SAR</div><small class="text-muted">1 ' + row.currency + ' = ' + row.currency_rate + ' ' + baseCurrency + '</small>';
                }
            }
        },*/
        {
          data: 'base_total',
          render: function render(data, type, row) {
            return '<div class="text-end text-secondary">' + row.base_total + '</div><div class="text-end"><small class="text-muted">' + baseCurrency + '</small></div>';
          }
        }, {
          data: 'grand_total',
          render: function render(data, type, row) {
            return '<div class="text-end fw-semibold">' + row.grand_total + '</div><div class="text-end"><small>' + row.currency + '</small></div>';
          }
        }, {
          data: 'balance',
          "class": 'text-end'
        }, {
          data: 'invoice_date'
        }, {
          data: 'due_at'
        }, GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: ""
        },
        deferLoading: 0,
        initComplete: function initComplete() {
          CREDIT_NOTE.form.open();
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
    extraActions: function extraActions(row) {
      CREDIT_NOTE.list.actions.statusChange(row);
      CREDIT_NOTE.list.actions.view(row);
      CREDIT_NOTE.list.actions.email(row);
    },
    actions: {
      statusChange: function statusChange(row) {
        $('#row_pending,#row_approved,#row_rejected').off().on('click', function () {
          var fd = new FormData();
          changeCustomerStatus(GLOBAL_FN.buildUrl('adjustment/credit-note/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
            method: 'POST',
            data: fd,
            callBack: 'datatable'
          }, $(this).attr('data-value'));
        });
        $('#row_converted').off().on('click', function () {
          alert("convert to invoice");
        });
      },
      view: function view(row) {
        $('#row_view').off().on('click', function () {
          var customerId = row.attr('data-id');

          // Open drawer

          var drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
          drawer.show();

          // Load Overview
          $('#moduleOverview').html('<p>Loading...</p>');
          $.get('/adjustment/credit-note/' + customerId + '/overview', function (data) {
            $('#moduleOverview').html(data);
          });
        });
      },
      email: function email(row) {
        $('#row_email').off().on('click', function () {
          var drawer = new bootstrap.Offcanvas(document.getElementById('sendEmailDrawer'));
          drawer.show();
        });
      }
    }
  },
  form: {
    load: function load() {
      CREDIT_NOTE.form.open();
    },
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'New Credit Note',
          url: GLOBAL_FN.buildUrl('adjustment/credit-note/create'),
          content: null,
          size: 'xxl',
          scroll: false
        });
      });
    },
    openCallback: function openCallback() {
      CREDIT_NOTE.form.addRow();
      CREDIT_NOTE.form.removeRow();
      CALCULATION.load();
      CALCULATION.finalTotals();
    },
    addRow: function addRow() {
      $('#' + MODULE + '-tbody').off('click', '.add-row').on('click', '.add-row', function () {
        var $tbody = $(this).closest('tbody');
        var $newRow = $tbody.find('tr:first').clone();

        // Clear values in cloned row
        $newRow.find('input, select, textarea').val('');
        $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
        $newRow.find('div.ts-wrapper').remove();
        initTomSelectForm($newRow);
        $tbody.append($newRow);
        //PROFORMA_INVOICE.form.removeRow();
      });
    },
    removeRow: function removeRow() {
      $('#' + MODULE + '-tbody').off('click', '.remove-row').on('click', '.remove-row', function () {
        var $tbody = $(this).closest('tbody');
        var $tr = $(this).closest('tr');
        if ($tbody.find('tr').length > 1) {
          $tr.remove();
        } else {
          // If only one row left, just clear it
          // $(this).closest('tr').find('input, select').val('');
          $tr.find('input,textarea').val('');
          $tr.find('select').each(function () {
            $(this).val('');
            if ($(this).hasClass('selectpicker')) {
              $(this).selectpicker('destroy').addClass('selectpicker');
              console.log($(this).attr('id'));
              selectPicker('#' + $(this).closest('table').attr('id'));
            }
          });
        }
        CALCULATION.finalTotals();
      });
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!*******************************************!*\
  !*** ./public/js/page-all-js/currency.js ***!
  \*******************************************/
CURRENCY = {
  baseUrl: 'masters/services',
  load: function load() {},
  list: {
    load: function load() {
      CURRENCY.list.dataTable();
    },
    dataTable: function dataTable() {
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        order: [[1, 'desc']],
        ajax: {
          url: '/masters/currency/data',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          dataSrc: function dataSrc(json) {
            // Remove loader rows when data arrives
            $('#dataTable tbody').find('.loading-row').remove();
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0],
          orderable: false
        }
        /*{targets: [1], class: 'hide', visible: false},*/],
        columns: [{
          data: 'DT_RowIndex',
          "class": 'text-center hide-tooltip fav-index'
        }, {
          data: 'code',
          render: function render(data, type, row) {
            return row.code;
          }
        }, {
          data: 'name',
          render: function render(data, type, row) {
            return row.name;
          }
        }, {
          data: 'country',
          render: function render(data, type, row) {
            return row.country;
          }
        }],
        deferLoading: 0 // don't load immediately
      });
      $('#customSearch').on('keyup', function () {
        table.search(this.value).draw();
      });
      webDataTable.loader(table);
      webDataTable.search(table);
      //webDataTable.actions.menu();
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!***************************************************!*\
  !*** ./public/js/page-all-js/customer_invoice.js ***!
  \***************************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
CUSTOMER_INVOICE = {
  title: 'Customer Invoice',
  baseUrl: 'invoice/customer',
  actionUrl: 'invoice/customer',
  load: function load() {
    CUSTOMER_INVOICE.form.load();
  },
  printPreview: function printPreview(printId) {
    var iframe = document.getElementById('print-frame');
    iframe.onload = function () {
      try {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        var doc = iframe.contentDocument || iframe.contentWindow.document;
        iframe.style.height = doc.body.scrollHeight + 'px';
      } catch (e) {
        console.error('Cannot print iframe content. Cross-origin issue?', e);
      }
    };
    iframe.src = '/' + CUSTOMER_INVOICE.baseUrl + '/' + printId + '/print';
  },
  downloadPDF: function downloadPDF(printId) {
    fetch('/invoice/customer/' + printId + '/print').then(function (res) {
      return res.text();
    }).then(function (html) {
      var container = document.createElement('div');
      //container.style.display = 'none';
      container.id = 'html-pdf';
      container.className = 'px-4 pt-4';
      container.innerHTML = html;
      console.log(container);
      //document.body.appendChild(container);
      var opt = {
        margin: 0.2,
        filename: "customerInvoice-".concat(printId, ".pdf")
      };
      html2pdf().set(opt).from(container).save()["finally"](function () {
        //document.body.removeChild(container);
      });
    });
  },
  list: {
    load: function load() {
      var activeTab = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      CUSTOMER_INVOICE.list.dataTable(activeTab);
    },
    dataTable: function dataTable() {
      var activeTab = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      GLOBAL_FN.destroyDataTable();
      activeTab = activeTab && _typeof(activeTab) !== 'object' ? activeTab : $("#listTabs").find('li button.active').attr('id');
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        orderable: false,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        /*order: [[1, 'desc']],*/
        ajax: {
          url: GLOBAL_FN.buildUrl('invoice/customer/data'),
          type: 'POST',
          data: {
            'tab': activeTab
          },
          dataSrc: function dataSrc(json) {
            $('#dataTable tbody').find('.loading-row').remove();
            GLOBAL_FN.setStatusCounts(json.statusCounts);
            CUSTOMER_INVOICE.list.cardSummary(json.salesSummary);
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
          orderable: false
        }, {
          targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
          "class": 'px-3 py-2 text-nowrap'
        }],
        columns: [{
          data: 'row_no',
          render: function render(data, type, row) {
            var text = '<span class="badge bg-danger-subtle text-muted me-2 text-xsmall">Cancelled</span>';
            if (row.status == 1) {
              $class = 'text-muted';
              text = '';
            } else if (row.status == 3) {
              $class = 'main-text fw-bold';
              text = '<span class="badge bg-primary-subtle text-success  me-2 text-xsmall">Approved</span>';
            } else {
              $class = 'text-danger';
            }
            if (row.tax_submit_status == 4) {
              text = '<span class="badge bg-success-subtle text-success me-2 text-xsmall">Cleared</span>';
            } else if (row.tax_submit_status == 5) {
              text = '<span class="badge bg-warning-subtle text-success me-2 text-xsmall">Reported</span>';
            }
            return '<div class="' + $class + ' pb-1">' + row.row_no + '</div>' + text + '<!--<span class="badge rounded-pill bg-success-subtle text-success text-xsmall">Paid</span>-->';
          }
        }, {
          data: 'customer_name',
          render: function render(data, type, row) {
            return '<div>' + row.customer_name + '</div>';
          }
        }, {
          data: 'customer.name_en',
          render: function render(data, type, row) {
            return '<div class="fw-bold">' + row.job_no + '</div><div class="text-secondary small">' + row.job_activity + '</div>';
          }
        }, {
          data: 'job.pol',
          render: function render(data, type, row) {
            var pol_pod = '';
            if (row.job.pol) {
              pol_pod = '<span class="text-secondary small me-1">POL:</span><span>' + row.job.pol + '</span>';
            }
            if (row.job.pod) {
              if (pol_pod) {
                pol_pod += '<br>';
              }
              return pol_pod + '<span class="text-secondary small me-1">POD:</span><span>' + row.job.pod + '</span>';
            }
            return pol_pod;
          }
        },
        /*{
            data: 'currency', render: function (data, type, row) {
                if (row.currency == baseCurrency) {
                    return '<div>' + row.currency + '</div>';
                } else {
                    return '<div>' + row.currency + ' → SAR</div><small class="text-muted">1 ' + row.currency + ' = ' + row.currency_rate + ' ' + baseCurrency + '</small>';
                }
            }
        },*/
        /*{
            data: 'base_total', render: function (data, type, row) {
                return '<div class="text-end text-secondary">' + row.base_total + '</div><div class="text-end"><small class="text-muted">' + baseCurrency + '</small></div>';
            }
        },*/
        {
          data: 'sub_total',
          render: function render(data, type, row) {
            return '<div class="text-end"><span class="fw-bold">' + row.sub_total + '</span></div>';
          }
        }, {
          data: 'tax_total',
          render: function render(data, type, row) {
            return '<div class="text-end"><span class="fw-bold">' + row.tax_total + '</span></div>';
          }
        }, {
          data: 'balance',
          "class": 'text-end'
        }, {
          data: 'invoice_date'
        }, {
          data: 'due_at'
        },
        /*{
            data: 'due_status', render: function (data, type, row) {
                if (row.status !== 'unpaid') {
                    return '<div class="text-sm text-gray-500">Due: 21-09-2025</div><small class="text-xs font-bold text-red-600 block">74 days overdue</small>';
                }
                return '<div class="text-sm text-gray-500">Due: 21-12-2025</div><small class="text-xs font-bold text-green-600 block">On Time</small>';
            }
        },*/
        GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: ""
        },
        deferLoading: 0,
        initComplete: function initComplete() {
          CUSTOMER_INVOICE.form.open();
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
    cardSummary: function cardSummary(data) {
      $('#overall_sales').text(amountFormat(data.overall_sales));
      $('#total_draft_grand').text(amountFormat(data.total_draft_grand));
      $('#total_draft_sub').text(amountFormat(data.total_draft_sub));
      $('#total_draft_tax').text(amountFormat(data.total_draft_tax));
      $('#total_approved_grand').text(amountFormat(data.total_approved_grand));
      $('#total_approved_sub').text(amountFormat(data.total_approved_sub));
      $('#total_approved_tax').text(amountFormat(data.total_approved_tax));
    },
    extraActions: function extraActions(row) {
      CUSTOMER_INVOICE.list.actions.statusChange(row);
      CUSTOMER_INVOICE.list.actions.view(row);
      CUSTOMER_INVOICE.list.actions.email(row);
    },
    actions: {
      statusChange: function statusChange(row) {
        $('#row_pending,#row_approved,#row_rejected').off().on('click', function () {
          var fd = new FormData();
          changeCustomerStatus(GLOBAL_FN.buildUrl('invoice/customer/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
            method: 'POST',
            data: fd,
            callBack: 'datatable'
          }, $(this).attr('data-value'));
        });
        $('#row_converted').off().on('click', function () {
          alert("convert to invoice");
        });
      },
      view: function view(row) {
        $('#row_view').off().on('click', function () {
          var customerId = row.attr('data-id');

          // Open drawer

          var drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
          drawer.show();

          // Load Overview
          $('#moduleOverview').html('<p>Loading...</p>');
          $.get('/invoice/customer/' + customerId + '/overview', function (data) {
            $('#moduleOverview').html(data);
          });
        });
      },
      email: function email(row) {
        $('#row_email').off().on('click', function () {
          var drawer = new bootstrap.Offcanvas(document.getElementById('sendEmailDrawer'));
          drawer.show();
        });
      }
    }
  },
  form: {
    load: function load() {
      CUSTOMER_INVOICE.form.open();
    },
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'New Customer Invoice',
          url: GLOBAL_FN.buildUrl('invoice/customer/create'),
          content: null,
          size: '4xl',
          scroll: false
        });
      });
    },
    openCallback: function openCallback() {
      CUSTOMER_INVOICE.form.addRow();
      CUSTOMER_INVOICE.form.removeRow();
      CALCULATION.load();
      CALCULATION.finalTotals();
    },
    addRow: function addRow() {
      $('#' + MODULE + '-tbody').off('click', '.add-row').on('click', '.add-row', function () {
        var $tbody = $(this).closest('tbody');
        var $newRow = $tbody.find('tr:first').clone();

        // Clear values in cloned row
        $newRow.find('input, select, textarea').val('');
        $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
        $newRow.find('div.ts-wrapper').remove();
        initTomSelectForm($newRow);
        $tbody.append($newRow);
        //PROFORMA_INVOICE.form.removeRow();
      });
    },
    removeRow: function removeRow() {
      $('#' + MODULE + '-tbody').off('click', '.remove-row').on('click', '.remove-row', function () {
        var $tbody = $(this).closest('tbody');
        var $tr = $(this).closest('tr');
        if ($tbody.find('tr').length > 1) {
          $tr.remove();
        } else {
          // If only one row left, just clear it
          // $(this).closest('tr').find('input, select').val('');
          $tr.find('input,textarea').val('');
          $tr.find('select').each(function () {
            $(this).val('');
            if ($(this).hasClass('selectpicker')) {
              $(this).selectpicker('destroy').addClass('selectpicker');
              console.log($(this).attr('id'));
              selectPicker('#' + $(this).closest('table').attr('id'));
            }
          });
        }
        CALCULATION.finalTotals();
      });
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!*******************************************!*\
  !*** ./public/js/page-all-js/customer.js ***!
  \*******************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
CUSTOMER = {
  title: 'Customer',
  baseUrl: 'customer',
  actionUrl: 'customer',
  load: function load() {
    CUSTOMER.form.open();
  },
  list: {
    load: function load(activeTab) {
      CUSTOMER.list.dataTable(activeTab);
    },
    dataTable: function dataTable() {
      var activeTab = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      // ⚠️ CRUCIAL: Destroy the old instance before creating a new one
      GLOBAL_FN.destroyDataTable();
      activeTab = activeTab && _typeof(activeTab) !== 'object' ? activeTab : $("#listTabs").find('li button.active').attr('id');

      // ⚠️ CRUCIAL: Wrap the DataTables creation in a try...catch
      // If the DataTables library failed to load, this prevents the script from crashing.
      try {
        var table = $('#dataTable').DataTable({
          processing: false,
          serverSide: true,
          autoWidth: false,
          lengthChange: false,
          pageLength: 25,
          dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
          order: [[1, 'desc']],
          ajax: {
            url: GLOBAL_FN.buildUrl('customer/data'),
            type: 'POST',
            data: {
              'tab': activeTab
            },
            dataSrc: function dataSrc(json) {
              // Remove loader rows when data arrives
              $('#dataTable tbody').find('.loading-row').remove();
              GLOBAL_FN.setStatusCounts(json.statusCounts);
              return json.data;
            }
          },
          columnDefs: [{
            targets: [0],
            searchable: false
          }, {
            targets: [0],
            orderable: false
          }
          /*{targets: [1], class: 'hide', visible: false},*/],
          columns: [{
            data: 'DT_RowIndex',
            "class": 'hide-tooltip fav-index'
          }, {
            data: 'name_en',
            render: function render(data, type, row) {
              return row.name_en + '<br><small class="text-muted">' + row.row_no + '</small>';
            }
          }, {
            data: 'email',
            render: function render(data, type, row) {
              return row.email + '<br><small class="text-muted">' + row.phone + '</small>';
            }
          }, {
            data: 'country',
            render: function render(data, type, row) {
              return (row.city_en ? row.city_en + ', ' : '') + row.country;
            }
          }, {
            data: 'currency',
            name: 'currency'
          }, {
            data: 'vat_number',
            render: function render(data, type, row) {
              return row.vat_number;
            }
          }, {
            data: 'credit_limit',
            render: function render(data, type, row) {
              var _row$credit_limit;
              return ((_row$credit_limit = row.credit_limit) !== null && _row$credit_limit !== void 0 ? _row$credit_limit : '') + '<br><small class="text-muted">' + (row.credit_days ? row.credit_days + ' Days' : '') + '</small>';
            }
          }, {
            data: 'salesperson.name'
          }, {
            data: 'created_at',
            name: 'created_at'
          },
          // Actions column
          GLOBAL_FN.dataTable.optionButton()],
          language: {
            search: "" // removes "Search:" label
          },
          deferLoading: 0,
          // don't load immediately
          /*preDrawCallback: function (settings) {
              let api = this.api();
              let cols = api.columns().nodes().length;
              let pageLen = api.page.len(); // rows per page
               // clear old rows & insert loader rows
              let loaderRows = '';
              for (let i = 0; i < pageLen; i++) {
                  loaderRows += '<tr class="loading-row">';
                  for (let j = 0; j < cols; j++) {
                      loaderRows += `<td><div class="loader-td"></div></td>`;
                  }
                  loaderRows += '</tr>';
              }
              $('#dataTable tbody').html(loaderRows);
          }*/

          initComplete: function initComplete() {
            CUSTOMER.form.open();
            webDataTable.actions.menu();
          }
        });
        $('#customSearch').on('keyup', function () {
          table.search(this.value).draw();
        });
        // Assuming webDataTable object is defined elsewhere
        webDataTable.loader(table);
        webDataTable.search(table);
        //webDataTable.actions.menu();
      } catch (e) {
        console.error("DataTables Initialization Error:", e);
        // Gracefully degrade the table to a basic HTML table if the plugin fails.
      }
    },
    extraActions: function extraActions(row) {
      CUSTOMER.list.actions.statusChange(row);
      CUSTOMER.list.actions.view(row);
      CUSTOMER.list.actions.email(row);
    },
    actions: {
      statusChange: function statusChange(row) {
        $('#row_pending,#row_confirm,#row_blocked,#row_rejected').off().on('click', function () {
          //GLOBAL_FN.ajaxData.sendData(GLOBAL_FN.buildUrl('customer/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')),'datatable',{})
          var fd = new FormData();
          changeCustomerStatus(GLOBAL_FN.buildUrl('customer/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
            method: 'POST',
            data: fd,
            callBack: 'datatable'
          }, $(this).attr('data-value'));
        });
      },
      view: function view(row) {
        $('#row_view').off().on('click', function () {
          var customerId = row.attr('data-id');

          // Open drawer

          var drawer = new bootstrap.Offcanvas(document.getElementById('customerDrawer'));
          drawer.show();

          // Load Overview
          $('#customerOverview').html('<p>Loading...</p>');
          $.get('/customer/' + customerId + '/overview', function (data) {
            $('#customerOverview').html(data);
          });

          // Clear other tabs
          $('#customerInvoices').html('');
          $('#customerTransactions').html('');
        });
      },
      email: function email(row) {
        $('#row_email').off().on('click', function () {
          var drawer = new bootstrap.Offcanvas(document.getElementById('sendEmailDrawer'));
          drawer.show();
        });
      }
    }
  },
  form: {
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'Add Customer',
          url: GLOBAL_FN.buildUrl('customer/create'),
          content: null,
          size: 'xl',
          callBack: CUSTOMER.form.openCallback
        });
      });
    },
    openCallback: function openCallback() {
      $('input[name="business_type"]').on('change', function () {
        if ($(this).val() === 'registered') {
          $('#cr_number').prop('disabled', false);
          $('#vat_number').prop('disabled', false);
        } else {
          $('#cr_number').prop('disabled', true).val('');
          $('#vat_number').prop('disabled', true).val('');
        }
      });
    },
    quick: {
      open: function open() {
        webModal.quickModal.open({
          title: 'Add Quick Customer',
          url: GLOBAL_FN.buildUrl('customer/create/quick'),
          content: null,
          size: 'lg',
          callBack: null,
          module: 'CUSTOMER'
        });
      },
      after: {
        save: function save(data) {
          var ts = document.querySelector('#customer').tomselect;

          // If TomSelect already exists → add dynamically
          if (ts) {
            ts.addOption({
              value: data.id,
              text: data.name,
              subtext: data.code
            });
            ts.addItem(data.id); // select it
          } else {
            // If not initialized yet
            $('#customer').prepend("<option value=\"".concat(data.id, "\" data-subtext=\"").concat(data.code, "\" selected>").concat(data.name, "</option>"));
          }
        }
      }
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!**********************************************!*\
  !*** ./public/js/page-all-js/description.js ***!
  \**********************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
DESCRIPTION = {
  title: 'Description',
  baseUrl: 'masters/description',
  actionUrl: 'masters/description',
  load: function load() {
    DESCRIPTION.form.open();
  },
  list: {
    load: function load(activeTab) {
      DESCRIPTION.list.dataTable(activeTab);
    },
    dataTable: function dataTable() {
      var activeTab = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      GLOBAL_FN.destroyDataTable();
      activeTab = activeTab && _typeof(activeTab) !== 'object' ? activeTab : $("#listTabs").find('li button.active').attr('id');
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        order: [[1, 'desc']],
        ajax: {
          url: GLOBAL_FN.buildUrl('masters/description/data'),
          type: 'POST',
          data: {
            'tab': activeTab
          },
          dataSrc: function dataSrc(json) {
            // Remove loader rows when data arrives
            $('#dataTable tbody').find('.loading-row').remove();
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0],
          orderable: false
        }
        /*{targets: [1], class: 'hide', visible: false},*/],
        columns: [{
          data: 'DT_RowIndex',
          "class": 'hide-tooltip fav-index'
        }, {
          data: 'description',
          render: function render(data, type, row) {
            return row.description;
          }
        }, {
          data: 'description_local',
          render: function render(data, type, row) {
            return row.description_local;
          }
        }, {
          data: 'created_at',
          name: 'created_at'
        },
        // Actions column
        GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: "" // removes "Search:" label
        },
        deferLoading: 0,
        // don't load immediately
        initComplete: function initComplete() {
          DESCRIPTION.form.open();
          webDataTable.actions.menu();
        }
      });
      $('#customSearch').on('keyup', function () {
        table.search(this.value).draw();
      });
      webDataTable.loader(table);
      webDataTable.search(table);
      //webDataTable.actions.menu();
    },
    extraActions: function extraActions(row) {
      DESCRIPTION.list.actions["delete"](row);
    },
    actions: {
      "delete": function _delete(row) {
        $('#row_delete').off().on('click', function () {
          $.confirm({
            title: 'Confirm Delete',
            content: 'Are you sure you want to delete this record?',
            type: 'red',
            buttons: {
              cancel: function cancel() {},
              "delete": {
                text: 'Delete',
                btnClass: 'btn-red',
                action: function action() {
                  $.ajax({
                    url: '/masters/description/delete/' + row.attr('data-id'),
                    type: 'DELETE',
                    dataType: 'json',
                    success: function success(response) {
                      if (response.status === 'success') {
                        toastr.success(response.message);
                        //row.remove(); // remove row if needed
                        loadJs('list.load');
                      } else if (response.status === 'warning') {
                        toastr.warning(response.message);
                      } else {
                        toastr.error('Error deleting record.');
                      }
                    },
                    error: function error() {
                      toastr.error('Server error');
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
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'Add Description',
          url: GLOBAL_FN.buildUrl('masters/description/create'),
          content: null,
          size: 'md',
          callBack: null,
          minHeight: '300'
        });
      });
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!******************************************!*\
  !*** ./public/js/page-all-js/enquiry.js ***!
  \******************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
ENQUIRY = {
  title: 'Enquiry',
  baseUrl: 'sales/enquiry',
  actionUrl: 'sales/enquiry',
  load: function load() {
    ENQUIRY.form.open();
  },
  printPreview: function printPreview(printId) {
    var iframe = document.getElementById('print-frame');
    iframe.onload = function () {
      try {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        var doc = iframe.contentDocument || iframe.contentWindow.document;
        iframe.style.height = doc.body.scrollHeight + 'px';
      } catch (e) {
        console.error('Cannot print iframe content. Cross-origin issue?', e);
      }
    };
    iframe.src = '/' + ENQUIRY.baseUrl + '/' + printId + '/print';
  },
  downloadPDF: function downloadPDF(printId) {
    fetch('/' + ENQUIRY.baseUrl + '/' + printId + '/print').then(function (res) {
      return res.text();
    }).then(function (html) {
      var container = document.createElement('div');
      //container.style.display = 'none';
      container.id = 'html-pdf';
      container.className = 'px-4';
      container.innerHTML = html;
      console.log(container);
      //document.body.appendChild(container);
      var opt = {
        margin: 0.2,
        filename: "enquiry-".concat(printId, ".pdf")
      };
      html2pdf().set(opt).from(container).save()["finally"](function () {
        //document.body.removeChild(container);
      });
    });
  },
  list: {
    load: function load(activeTab) {
      ENQUIRY.list.dataTable(activeTab);
    },
    dataTable: function dataTable() {
      var activeTab = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      GLOBAL_FN.destroyDataTable();
      activeTab = activeTab && _typeof(activeTab) !== 'object' ? activeTab : $("#listTabs").find('li button.active').attr('id');
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        order: [[1, 'desc']],
        ajax: {
          url: GLOBAL_FN.buildUrl('sales/enquiry/data'),
          type: 'POST',
          data: {
            'tab': activeTab
          },
          dataSrc: function dataSrc(json) {
            // Remove loader rows when data arrives
            $('#dataTable tbody').find('.loading-row').remove();
            GLOBAL_FN.setStatusCounts(json.statusCounts);
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0],
          orderable: false
        }
        /*{targets: [1], class: 'hide', visible: false},*/],
        columns: [/*{data: 'DT_RowIndex', class: 'text-center hide-tooltip fav-index'},*/
        {
          data: 'row_no',
          "class": 'hide-tooltip fav-index'
        }, {
          data: 'name',
          render: function render(data, type, row) {
            return row.name.name + '<br><small class="text-muted">' + row.name.row_no + '</small>';
          }
        }, {
          data: 'contact',
          render: function render(data, type, row) {
            return row.contact.email + '<br><small class="text-muted">' + row.contact.phone + '</small>';
          }
        }, {
          data: 'activity.name'
        }, {
          data: 'pol'
        }, {
          data: 'pod'
        },
        /*{
            data: 'origin_city',
            render: function (data, type, row) {
                let origin = (row.origin_city || row.origin_country)
                    ? `<div class="text-capitalize">${row.origin_city ?? ''}, ${row.origin_country ?? ''}</div>`
                    : '';
                return origin;
            }
        },
        {
            data: 'destination_city',
            render: function (data, type, row) {
                let dest = (row.destination_city || row.destination_country)
                    ? `<div class="text-capitalize">${row.destination_city ?? ''}, ${row.destination_country ?? ''}</div>`
                    : '';
                return dest;
            }
        },*/
        {
          data: 'pickup_date',
          render: function render(data, type, row) {
            return row.pickup_date ? "<div>".concat(row.pickup_date, "</div>") : '';
          }
        },
        /*{
            data: 'weight',
            render: function (data, type, row) {
                let weight = row.weight ? `<div>Weight: ${row.weight} kg</div>` : '';
                let volume = row.volume ? `<small class="text-muted"><span class="fw-semibold">Volume:</span> ${row.volume} m³</small>` : '';
                return weight + volume;
            }
        },*/
        {
          data: 'expiry_date'
        }, {
          data: 'created_at'
        },
        // Actions column
        GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: "" // removes "Search:" label
        },
        deferLoading: 0,
        initComplete: function initComplete() {
          ENQUIRY.form.open();
          webDataTable.actions.menu();
        }
      });
      $('#customSearch').on('keyup', function () {
        table.search(this.value).draw();
      });
      $('#dataTable_filter').closest('div.row').remove();
      webDataTable.loader(table);
      webDataTable.search(table);
      //webDataTable.actions.menu();
    },
    extraActions: function extraActions(row) {
      ENQUIRY.list.actions.statusChange(row);
      ENQUIRY.list.actions.view(row);
      ENQUIRY.list.actions.email(row);
    },
    actions: {
      statusChange: function statusChange(row) {
        $('#row_pending,#row_confirmed,#row_rejected').off().on('click', function () {
          //GLOBAL_FN.ajaxData.sendData(GLOBAL_FN.buildUrl('ENQUIRY/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')),'datatable',{})
          var fd = new FormData();
          changeCustomerStatus(GLOBAL_FN.buildUrl('sales/enquiry/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
            method: 'POST',
            data: fd,
            callBack: 'datatable'
          }, $(this).attr('data-value'));
        });
      },
      view: function view(row) {
        $('#row_view').off().on('click', function () {
          var customerId = row.attr('data-id');

          // Open drawer

          var drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
          drawer.show();

          // Load Overview
          $('#moduleOverview').html('<p>Loading...</p>');
          $.get('/sales/enquiry/' + customerId + '/overview', function (data) {
            $('#moduleOverview').html(data);
          });
        });
      },
      email: function email(row) {
        $('#row_email').off().on('click', function () {
          var drawer = new bootstrap.Offcanvas(document.getElementById('sendEmailDrawer'));
          drawer.show();
        });
      }
    }
  },
  convertToQuotation: function convertToQuotation(enquiryId) {
    // Redirect to the create quotation from enquiry route
    localStorage.setItem('convert-enquiry', enquiryId);
    window.location.href = GLOBAL_FN.buildUrl("sales/quotations");
  },
  form: {
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'Add Enquiry',
          url: GLOBAL_FN.buildUrl('sales/enquiry/create'),
          content: null,
          size: 'xxl',
          scroll: false,
          minHeight: '650px'
        });
      });
    },
    openCallback: function openCallback() {
      ENQUIRY.form.shipmentMode();
      ENQUIRY.form.shipmentCategory();
      //ENQUIRY.form.addItem();
      //ENQUIRY.form.initRemoveRow();
      ENQUIRY.form.polPodLoad();
      setTimeout(function () {
        ENQUIRY.form.customerProspectToggle();
      });
      GLOBAL_FN.activity.activityChange();
      //CUSTOMER.form.quick.load();
    },
    customerProspectToggle: function customerProspectToggle() {
      // Handle customer select change
      $('#customer').on('change', function () {
        var customerValue = $(this).val();
        var prospectSelect = document.querySelector('#prospect');
        if (customerValue && customerValue !== '') {
          // Disable prospect select when customer is selected
          if (prospectSelect && prospectSelect.tomselect) {
            prospectSelect.tomselect.disable();
          }
        } else {
          // Enable prospect select when customer is cleared
          if (prospectSelect && prospectSelect.tomselect) {
            prospectSelect.tomselect.enable();
          }
        }
      });

      // Handle prospect select change
      $('#prospect').on('change', function () {
        var prospectValue = $(this).val();
        var customerSelect = document.querySelector('#customer');
        if (prospectValue && prospectValue !== '') {
          // Disable customer select when prospect is selected
          if (customerSelect && customerSelect.tomselect) {
            customerSelect.tomselect.disable();
          }
        } else {
          // Enable customer select when prospect is cleared
          if (customerSelect && customerSelect.tomselect) {
            customerSelect.tomselect.enable();
          }
        }
      });

      // Initial check on page load
      var customerValue = $('#customer').val();
      var prospectValue = $('#prospect').val();
      var customerSelect = document.querySelector('#customer');
      var prospectSelect = document.querySelector('#prospect');

      // Check if we're in edit mode with a prospect
      var isEditMode = $('#data-id').val() && $('#prospect').length > 0;
      var hasProspectId = $('#prospect').data('has-prospect') === true || $('[name="prospect"]').find('option:selected').val() !== '';
      if (customerValue && customerValue !== '') {
        // Disable prospect select if customer is already selected
        if (prospectSelect && prospectSelect.tomselect) {
          prospectSelect.tomselect.disable();
        }
      } else if (prospectValue && prospectValue !== '' || isEditMode && hasProspectId) {
        // Disable customer select if prospect is already selected or we're editing a prospect
        if (customerSelect && customerSelect.tomselect) {
          customerSelect.tomselect.disable();
        }
      }
    },
    shipmentMode: function shipmentMode() {
      var destroy = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      if (destroy) {
        var enquiryPol = document.querySelector('#pol');
        var enquiryPod = document.querySelector('#pod');

        // If already initialized, destroy first
        enquiryPol.tomselect.destroy();
        enquiryPod.tomselect.destroy();
        ENQUIRY.form.polPodLoad(true);
      }
    },
    shipmentCategory: function shipmentCategory() {
      $('#shipment_category').on('change', function () {
        var category = $(this).val();
        if (category === 'container') {
          $('.container-fields').removeClass('d-none');
          $('.package-fields').addClass('d-none');
        } else if (category === 'package') {
          $('.package-fields').removeClass('d-none');
          $('.container-fields').addClass('d-none');
        }
      });
    },
    polPodLoad: function polPodLoad() {
      var preLoad = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      var port = $('#activity-id-hidden').val();
      initTomSelectSearch('#pol', port, 50, preLoad);
      initTomSelectSearch('#pod', port, 50, preLoad);
    },
    addItem: function addItem() {
      $('#addItem').off().on('click', function () {
        var category = $('#shipment_category').val();
        if (!category) return alert("Select Shipment Category first!");

        // Find the first visible template row for the category
        var $template = $('#enquiry-row tr.' + category + '-fields:visible:first');

        // Clone the row (without events)
        var $row = $template.clone(false, false);
        $row.removeClass('d-none');

        // Reset input values
        $row.find('input').val('');
        $row.find('select').val('');
        $row.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
        $row.find('div.ts-wrapper').remove();
        // Reset select values and assign unique ids for TomSelect
        /*let i = 1000;
        $row.find('select').each(function () {
            const t = $(this);
            t.closest('tr').find('div.tom-select').remove();
            if (t.hasClass('tom-select')) {
                const newId = t.attr('id') + '_' + Math.floor(Math.random() * i++);
                t.attr('id', newId);
            }
        });*/

        // Initialize TomSelect on the cloned row
        initTomSelectForm($row);

        // Append the row
        $('#enquiry-row').append($row);
      });
    },
    initRemoveRow: function initRemoveRow() {
      $('#enquiry-row').off('click', '.remove-row').on('click', '.remove-row', function () {
        var $tbody = $(this).closest('#enquiry-row');
        var $tr = $(this).closest('tr');
        if ($tbody.find('tr').length > 1) {
          $tr.remove();
        } else {
          $tr.find('input').val('');
          $tr.find('select').each(function () {
            $(this).val('');
            if ($(this).hasClass('selectpicker')) {
              $(this).selectpicker('destroy').addClass('selectpicker');
              selectPicker('#enquiry-row');
            }
          });
        }
      });
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!******************************************!*\
  !*** ./public/js/page-all-js/expense.js ***!
  \******************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
EXPENSE = {
  title: 'Expense',
  baseUrl: 'finance/expense',
  actionUrl: 'finance/expense',
  load: function load() {
    EXPENSE.form.load();
  },
  printPreview: function printPreview(printId) {
    var iframe = document.getElementById('print-frame');
    iframe.onload = function () {
      try {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        var doc = iframe.contentDocument || iframe.contentWindow.document;
        iframe.style.height = doc.body.scrollHeight + 'px';
      } catch (e) {
        console.error('Cannot print iframe content. Cross-origin issue?', e);
      }
    };
    iframe.src = '/' + EXPENSE.baseUrl + '/' + printId + '/print';
  },
  downloadPDF: function downloadPDF(printId) {
    fetch('/' + EXPENSE.baseUrl + '/' + printId + '/print').then(function (res) {
      return res.text();
    }).then(function (html) {
      var container = document.createElement('div');
      container.id = 'html-pdf';
      container.className = 'px-4 pt-4';
      container.innerHTML = html;
      var opt = {
        margin: 0.2,
        filename: "expense-".concat(printId, ".pdf")
      };
      html2pdf().set(opt).from(container).save();
    });
  },
  list: {
    load: function load(activeTab) {
      EXPENSE.list.dataTable(activeTab);
    },
    dataTable: function dataTable() {
      var activeTab = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      GLOBAL_FN.destroyDataTable();
      activeTab = activeTab && _typeof(activeTab) !== 'object' ? activeTab : $("#listTabs").find('li button.active').attr('id');
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        orderable: false,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        ajax: {
          url: GLOBAL_FN.buildUrl(EXPENSE.baseUrl + '/data'),
          type: 'POST',
          data: {
            'tab': activeTab
          },
          dataSrc: function dataSrc(json) {
            $('#dataTable tbody').find('.loading-row').remove();
            GLOBAL_FN.setStatusCounts(json.statusCounts);
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0, 1, 2, 3, 4, 5, 6],
          orderable: false
        }],
        columns: [{
          data: 'row_no',
          render: function render(data, type, row) {
            return '<strong>' + row.row_no + '</strong>';
          }
        }, {
          data: 'posted_at'
        }, {
          data: 'vendor.name_en',
          render: function render(data, type, row) {
            if (row.vendor) {
              return '<div>' + row.vendor.name_en + '</div><div class="small text-muted">Code: ' + row.vendor.row_no + '</div>';
            }
            return '<div class="text-muted">-</div>';
          }
        }, {
          data: 'customer.name_en',
          render: function render(data, type, row) {
            if (row.customer) {
              return '<div>' + row.customer.name_en + '</div><div class="small text-muted">Code: ' + row.customer.row_no + '</div>';
            }
            return '<div class="text-muted">-</div>';
          }
        }, {
          data: 'base_total',
          render: function render(data, type, row) {
            return '<div class="text-end text-secondary">' + row.base_total + '</div><div class="text-end"><small class="text-muted">' + baseCurrency + '</small></div>';
          }
        }, {
          data: 'grand_total',
          render: function render(data, type, row) {
            return '<div class="text-end fw-semibold">' + row.grand_total + '</div><div class="text-end"><small>' + row.currency + '</small></div>';
          }
        }, GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: ""
        },
        deferLoading: 0,
        initComplete: function initComplete() {
          EXPENSE.form.open();
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
    extraActions: function extraActions(row) {
      EXPENSE.list.actions.statusChange(row);
      EXPENSE.list.actions.view(row);
      EXPENSE.list.actions["delete"](row);
    },
    actions: {
      statusChange: function statusChange(row) {
        $('#row_draft,#row_approved,#row_rejected').off().on('click', function () {
          var fd = new FormData();
          changeCustomerStatus(GLOBAL_FN.buildUrl(EXPENSE.baseUrl + '/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
            method: 'POST',
            data: fd,
            callBack: 'datatable'
          }, $(this).attr('data-value'));
        });
      },
      view: function view(row) {
        $('#row_view').off().on('click', function () {
          var expenseId = row.attr('data-id');

          // Open drawer
          var drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
          drawer.show();

          // Load Overview
          $('#moduleOverview').html('<p>Loading...</p>');
          $.get('/finance/expense/' + expenseId + '/overview', function (data) {
            $('#moduleOverview').html(data);
          });
        });
      },
      "delete": function _delete(row) {
        $('#row_delete').off().on('click', function () {
          if (confirm('Are you sure you want to delete this expense?')) {
            $.ajax({
              url: '/finance/expense/' + row.attr('data-id'),
              type: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              success: function success(response) {
                if (response.success) {
                  toastr.success(response.message);
                  $('#dataTable').DataTable().ajax.reload();
                } else {
                  toastr.error(response.message);
                }
              },
              error: function error(xhr) {
                toastr.error('Error deleting expense');
              }
            });
          }
        });
      }
    }
  },
  form: {
    load: function load() {
      EXPENSE.form.open();
    },
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'New Expense',
          url: GLOBAL_FN.buildUrl(EXPENSE.baseUrl + '/create'),
          content: null,
          size: '3xl',
          scroll: false
        });
      });
    },
    openCallback: function openCallback() {
      EXPENSE.form.addRow();
      EXPENSE.form.removeRow();
      CALCULATION.load();
      CALCULATION.finalTotals();
      setTimeout(function () {
        EXPENSE.form.customerProspectToggle();
      });
    },
    customerProspectToggle: function customerProspectToggle() {
      // Handle customer select change
      $('#customer').on('change', function () {
        var customerValue = $(this).val();
        var supplierSelect = document.querySelector('#supplier');
        if (customerValue && customerValue !== '') {
          // Disable prospect select when customer is selected
          if (supplierSelect && supplierSelect.tomselect) {
            supplierSelect.tomselect.disable();
          }
        } else {
          // Enable prospect select when customer is cleared
          if (supplierSelect && supplierSelect.tomselect) {
            supplierSelect.tomselect.enable();
          }
        }
      });

      // Handle supplier select change
      $('#supplier').on('change', function () {
        var supplierValue = $(this).val();
        var customerSelect = document.querySelector('#customer');
        if (supplierValue && supplierValue !== '') {
          // Disable customer select when prospect is selected
          if (customerSelect && customerSelect.tomselect) {
            customerSelect.tomselect.disable();
          }
        } else {
          // Enable customer select when prospect is cleared
          if (customerSelect && customerSelect.tomselect) {
            customerSelect.tomselect.enable();
          }
        }
      });

      // Initial check on page load
      var customerValue = $('#customer').val();
      var supplierValue = $('#supplier').val();
      var customerSelect = document.querySelector('#customer');
      var supplierSelect = document.querySelector('#supplier');

      // Check if we're in edit mode with a supplier
      var isEditMode = $('#data-id').val() && $('#supplier').length > 0;
      var hasSupplierId = $('#supplier').data('has-supplier') === true || $('[name="supplier"]').find('option:selected').val() !== '';
      if (customerValue && customerValue !== '') {
        // Disable supplier select if customer is already selected
        if (supplierSelect && supplierSelect.tomselect) {
          supplierSelect.tomselect.disable();
        }
      } else if (supplierValue && supplierValue !== '' || isEditMode && hasSupplierId) {
        // Disable customer select if supplier is already selected or we're editing a supplier
        if (customerSelect && customerSelect.tomselect) {
          customerSelect.tomselect.disable();
        }
      }
    },
    addRow: function addRow() {
      $('#' + MODULE + '-tbody').off('click', '.add-row').on('click', '.add-row', function () {
        var $tbody = $(this).closest('tbody');
        var $newRow = $tbody.find('tr:first').clone();

        // Clear values in cloned row
        $newRow.find('input, select, textarea').val('');
        $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
        $newRow.find('div.ts-wrapper').remove();
        initTomSelectForm($newRow);
        $tbody.append($newRow);
      });
    },
    removeRow: function removeRow() {
      $('#' + MODULE + '-tbody').off('click', '.remove-row').on('click', '.remove-row', function () {
        var $tbody = $(this).closest('tbody');
        var $tr = $(this).closest('tr');
        if ($tbody.find('tr').length > 1) {
          $tr.remove();
        } else {
          // If only one row left, just clear it
          $tr.find('input,textarea').val('');
          $tr.find('select').each(function () {
            $(this).val('');
            if ($(this).hasClass('selectpicker')) {
              $(this).selectpicker('destroy').addClass('selectpicker');
              selectPicker('#' + $(this).closest('table').attr('id'));
            }
          });
        }
        CALCULATION.finalTotals();
      });
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!*******************************************!*\
  !*** ./public/js/page-all-js/incoterm.js ***!
  \*******************************************/
INCOTERM = {
  baseUrl: 'masters/services',
  load: function load() {},
  list: {
    load: function load() {
      INCOTERM.list.dataTable();
    },
    dataTable: function dataTable() {
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        order: [[1, 'desc']],
        ajax: {
          url: '/masters/incoterm/data',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          dataSrc: function dataSrc(json) {
            // Remove loader rows when data arrives
            $('#dataTable tbody').find('.loading-row').remove();
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0],
          orderable: false
        }
        /*{targets: [1], class: 'hide', visible: false},*/],
        columns: [{
          data: 'DT_RowIndex',
          "class": 'text-center hide-tooltip fav-index'
        }, {
          data: 'code',
          render: function render(data, type, row) {
            return row.code;
          }
        }, {
          data: 'name',
          render: function render(data, type, row) {
            return row.name;
          }
        }, {
          data: 'description',
          render: function render(data, type, row) {
            return row.description;
          }
        }, {
          data: 'transport_mode',
          render: function render(data, type, row) {
            return row.transport_mode;
          }
        }],
        language: {
          search: "" // removes "Search:" label
        },
        deferLoading: 0,
        // don't load immediately

        initComplete: function initComplete() {
          // Add custom class to default search input
          INCOTERM.openModel();
          webDataTable.actions.menu();
        }
      });
      $('#customSearch').on('keyup', function () {
        table.search(this.value).draw();
      });
      webDataTable.loader(table);
      webDataTable.search(table);
      //webDataTable.actions.menu();
    }
  },
  openModel: function openModel() {
    $('#new').click(function () {
      webModal.openGlobalModal('Add Service', globalFunction.buildUrl('masters/services/create'), null, 'xl', true);
    });
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!**************************************!*\
  !*** ./public/js/page-all-js/job.js ***!
  \**************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
JOB = {
  title: 'Job',
  baseUrl: 'operation/job',
  actionUrl: 'operation/job',
  load: function load() {
    JOB.form.load();
  },
  printPreview: function printPreview(printId) {
    var iframe = document.getElementById('print-frame');
    iframe.onload = function () {
      try {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        var doc = iframe.contentDocument || iframe.contentWindow.document;
        iframe.style.height = doc.body.scrollHeight + 'px';
      } catch (e) {
        console.error('Cannot print iframe content. Cross-origin issue?', e);
      }
    };
    iframe.src = '/' + JOB.baseUrl + '/' + printId + '/print';
  },
  downloadPDF: function downloadPDF(printId) {
    fetch('/' + JOB.baseUrl + '/' + printId + '/print').then(function (res) {
      return res.text();
    }).then(function (html) {
      var container = document.createElement('div');
      //container.style.display = 'none';
      container.id = 'html-pdf';
      container.className = 'px-4';
      container.innerHTML = html;
      console.log(container);
      //document.body.appendChild(container);
      var opt = {
        margin: 0.2,
        filename: "job-".concat(printId, ".pdf")
      };
      html2pdf().set(opt).from(container).save()["finally"](function () {
        //document.body.removeChild(container);
      });
    });
  },
  list: {
    load: function load(activeTab) {
      JOB.list.dataTable(activeTab);
    },
    dataTable: function dataTable() {
      var activeTab = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      GLOBAL_FN.destroyDataTable();
      activeTab = activeTab && _typeof(activeTab) !== 'object' ? activeTab : $("#listTabs").find('li button.active').attr('id');
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        order: [[1, 'desc']],
        ajax: {
          url: GLOBAL_FN.buildUrl('operation/job/data'),
          type: 'POST',
          data: {
            'tab': activeTab
          },
          dataSrc: function dataSrc(json) {
            console.log(json);
            // Remove loader rows when data arrives
            $('#dataTable tbody').find('.loading-row').remove();
            GLOBAL_FN.setStatusCounts(json.statusCounts);
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0],
          orderable: false
        }
        /*{targets: [1], class: 'hide', visible: false},*/],
        columns: [{
          data: 'row_no',
          "class": 'hide-tooltip fav-index'
        }, {
          data: 'customer.name_en'
        }, {
          data: 'services'
        }, {
          data: 'pol'
        }, {
          data: 'pod'
        }, {
          data: 'carrier'
        }, {
          data: 'posted_at'
        }, GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: "" // removes "Search:" label
        },
        deferLoading: 0,
        initComplete: function initComplete() {
          JOB.form.open();
          webDataTable.actions.menu();
        }
      });
      $('#customSearch').on('keyup', function () {
        table.search(this.value).draw();
      });
      $('#dataTable_filter').closest('div.row').remove();
      webDataTable.loader(table);
      webDataTable.search(table);
      //webDataTable.actions.menu();
    },
    extraActions: function extraActions(row) {
      JOB.list.actions.statusChange(row);
      JOB.list.actions.view(row);
      JOB.list.actions.email(row);
      JOB.list.actions["delete"](row);
    },
    actions: {
      statusChange: function statusChange(row) {
        $('#row_pending,#row_completed,#row_rejected,#row_trashed').off().on('click', function () {
          var fd = new FormData();
          changeCustomerStatus(GLOBAL_FN.buildUrl('operation/job/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
            method: 'POST',
            data: fd,
            callBack: 'datatable'
          }, $(this).attr('data-value'));
        });
      },
      view: function view(row) {
        $('#row_view').off().on('click', function () {
          var jobId = row.attr('data-id');

          // Open drawer

          var drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
          drawer.show();

          // Load Overview
          $('#moduleOverview').html('<p>Loading...</p>');
          $.get('/operation/job/' + jobId + '/overview', function (data) {
            $('#moduleOverview').html(data);
          });
        });
      },
      "delete": function _delete(row) {
        $('#row_delete').off().on('click', function () {
          var jobId = row.attr('data-id');
          deleteFn(GLOBAL_FN.buildUrl('operation/job/' + jobId + '/delete'), {
            method: 'GET',
            callBack: 'datatable'
          });
        });
      },
      email: function email(row) {
        $('#row_email').off().on('click', function () {
          var drawer = new bootstrap.Offcanvas(document.getElementById('sendEmailDrawer'));
          drawer.show();
        });
      }
    }
  },
  form: {
    load: function load() {
      JOB.form.open();
      /*let quotationId = localStorage.getItem('convert-quotation');
      if (quotationId) {
          webModal.openGlobalModal({
              title: 'New Job',
              url: GLOBAL_FN.buildUrl('operation/job/create'),
              size: 'xxl',
              content: {
                  quotationId: quotationId
              }
          });
          localStorage.removeItem('convert-quotation');
      }*/
    },
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'New Job',
          url: GLOBAL_FN.buildUrl('operation/job/create'),
          content: null,
          minHeight: '750px',
          size: '4xl'
        });
      });
    },
    openCallback: function openCallback() {
      JOB.form.addContainer();
      JOB.form.addPackage();
      //JOB.form.removeRow();
      JOB.form.shipmentMode();
      JOB.form.polPodLoad();
    },
    shipmentMode: function shipmentMode() {
      $('#shipment_mode').off().on('change', function () {
        var jobPol = document.querySelector('#job_pol');
        var jobPod = document.querySelector('#job_pod');
        var jobCarrier = document.querySelector('#carrier');

        // If already initialized, destroy first
        jobPol.tomselect.destroy();
        jobPod.tomselect.destroy();
        jobCarrier.tomselect.destroy();
        JOB.form.polPodLoad();
      });
    },
    polPodLoad: function polPodLoad() {
      var preLoad = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      var port = $('#activity-id-hidden').val();
      initTomSelectSearch('#pol', port, 50, preLoad);
      initTomSelectSearch('#pod', port, 50, preLoad);
      initTomSelectSearch('#carrier', port + 'Lines', 50, preLoad);
    },
    addContainer: function addContainer() {
      // Add Container Row
      $('#containerTable').off('click', '.addContainerRow').on('click', '.addContainerRow', function () {
        var $tbody = $(this).closest('tbody');
        var $newRow = $tbody.find('tr:first').clone();

        // Clear values in cloned row
        $newRow.find('input, select, textarea').val('');
        $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
        $newRow.find('div.ts-wrapper').remove();
        initTomSelectForm($newRow);
        $tbody.append($newRow);
      });
    },
    addPackage: function addPackage() {
      // Add Package Row
      $('#packageTable').off('click', '.addPackageRow').on('click', '.addPackageRow', function () {
        var $tbody = $(this).closest('tbody');
        var $newRow = $tbody.find('tr:first').clone();

        // Clear values in cloned row
        $newRow.find('input, select, textarea').val('');
        $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
        $newRow.find('div.ts-wrapper').remove();
        initTomSelectForm($newRow);
        $tbody.append($newRow);
      });
    }
    /*removeRow() {
        // Remove Row (for both tables)
        $('#containerTable,#packageTable').off('click', '.remove-row').on('click', '.remove-row', function () {
            let $tbody = $(this).closest('tbody');
            const $tr = $(this).closest('tr');
            if ($tbody.find('tr').length > 1) {
                $tr.remove();
            } else {
                // If only one row left, just clear it
                // $(this).closest('tr').find('input, select').val('');
                $tr.find('input').val('');
                $tr.find('select').each(function () {
                    $(this).val('');
                    if ($(this).hasClass('selectpicker')) {
                        $(this).selectpicker('destroy').addClass('selectpicker');
                        selectPicker('#' + $(this).closest('table').attr('id'));
                    }
                });
            }
        })
    }*/
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!****************************************************!*\
  !*** ./public/js/page-all-js/logistics_service.js ***!
  \****************************************************/
LOGISTICS_SERVICE = {
  title: 'Services',
  baseUrl: 'masters/services',
  actionUrl: 'masters/services',
  load: function load() {
    LOGISTICS_SERVICE.form.open();
  },
  list: {
    load: function load() {
      LOGISTICS_SERVICE.list.dataTable();
    },
    dataTable: function dataTable() {
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        order: [[1, 'desc']],
        ajax: {
          url: '/masters/services/data',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          dataSrc: function dataSrc(json) {
            // Remove loader rows when data arrives
            $('#dataTable tbody').find('.loading-row').remove();
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0],
          orderable: false
        }
        /*{targets: [1], class: 'hide', visible: false},*/],
        columns: [{
          data: 'DT_RowIndex',
          "class": 'text-center hide-tooltip fav-index'
        }, {
          data: 'service_name_en',
          render: function render(data, type, row) {
            return row.service_name_en;
          }
        }, {
          data: 'service_name_ar',
          render: function render(data, type, row) {
            return row.service_name_ar;
          }
        }, {
          data: 'category',
          render: function render(data, type, row) {
            return row.category;
          }
        }, {
          data: 'code',
          render: function render(data, type, row) {
            return row.code;
          }
        }, {
          data: 'description',
          render: function render(data, type, row) {
            return row.description;
          }
        },
        // Actions column
        GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: "" // removes "Search:" label
        },
        deferLoading: 0,
        // don't load immediately

        initComplete: function initComplete() {
          // Add custom class to default search input
          LOGISTICS_SERVICE.form.open();
          webDataTable.actions.menu();
        }
      });
      $('#customSearch').on('keyup', function () {
        table.search(this.value).draw();
      });
      webDataTable.loader(table);
      webDataTable.search(table);
      //webDataTable.actions.menu();
    }
  },
  form: {
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'Add Service',
          url: GLOBAL_FN.buildUrl('masters/services/create'),
          content: null,
          size: 'lg',
          callBack: null
        });
      });
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!***********************************************!*\
  !*** ./public/js/page-all-js/package_code.js ***!
  \***********************************************/
PACKAGE_CODE = {
  title: 'Package Code',
  baseUrl: 'masters/package/code',
  actionUrl: 'masters/package/codes',
  load: function load() {
    PACKAGE_CODE.form.open();
  },
  list: {
    load: function load() {
      PACKAGE_CODE.list.dataTable();
    },
    dataTable: function dataTable() {
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        order: [[1, 'desc']],
        ajax: {
          url: '/masters/package/code/data',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          dataSrc: function dataSrc(json) {
            // Remove loader rows when data arrives
            $('#dataTable tbody').find('.loading-row').remove();
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0],
          orderable: false
        }
        /*{targets: [1], class: 'hide', visible: false},*/],
        columns: [{
          data: 'DT_RowIndex',
          "class": 'text-center hide-tooltip fav-index'
        },
        /*{
            data: 'code', render: function (data, type, row) {
                return row.code;
            }
        },*/
        {
          data: 'name',
          render: function render(data, type, row) {
            return row.name;
          }
        }, {
          data: 'description',
          render: function render(data, type, row) {
            return row.description;
          }
        },
        // Actions column
        GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: "" // removes "Search:" label
        },
        deferLoading: 0,
        // don't load immediately

        initComplete: function initComplete() {
          // Add custom class to default search input
          PACKAGE_CODE.form.open();
          webDataTable.actions.menu();
        }
      });
      $('#customSearch').on('keyup', function () {
        table.search(this.value).draw();
      });
      webDataTable.loader(table);
      webDataTable.search(table);
      //webDataTable.actions.menu();
    }
  },
  form: {
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'Add Package Code',
          url: GLOBAL_FN.buildUrl('masters/package/code/create'),
          content: null,
          size: 'lg',
          callBack: null
        });
      });
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!***************************************************!*\
  !*** ./public/js/page-all-js/proforma_invoice.js ***!
  \***************************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
PROFORMA_INVOICE = {
  title: 'Proforma Invoice',
  baseUrl: 'invoice/proforma',
  actionUrl: 'invoice/proforma',
  load: function load() {
    PROFORMA_INVOICE.form.load();
  },
  printPreview: function printPreview(printId) {
    var iframe = document.getElementById('print-frame');
    iframe.onload = function () {
      try {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        var doc = iframe.contentDocument || iframe.contentWindow.document;
        iframe.style.height = doc.body.scrollHeight + 'px';
      } catch (e) {
        console.error('Cannot print iframe content. Cross-origin issue?', e);
      }
    };
    iframe.src = '/' + PROFORMA_INVOICE.baseUrl + '/' + printId + '/print';
  },
  downloadPDF: function downloadPDF(printId) {
    fetch('/invoice/proforma/' + printId + '/print').then(function (res) {
      return res.text();
    }).then(function (html) {
      var container = document.createElement('div');
      //container.style.display = 'none';
      container.id = 'html-pdf';
      container.className = 'px-4';
      container.innerHTML = html;
      console.log(container);
      //document.body.appendChild(container);
      var opt = {
        margin: 0.2,
        filename: "proformaInvoice-".concat(printId, ".pdf")
      };
      html2pdf().set(opt).from(container).save()["finally"](function () {
        //document.body.removeChild(container);
      });
    });
  },
  list: {
    load: function load(activeTab) {
      PROFORMA_INVOICE.list.dataTable(activeTab);
    },
    dataTable: function dataTable() {
      var activeTab = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      GLOBAL_FN.destroyDataTable();
      activeTab = activeTab && _typeof(activeTab) !== 'object' ? activeTab : $("#listTabs").find('li button.active').attr('id');
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        orderable: false,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        /*order: [[1, 'desc']],*/
        ajax: {
          url: GLOBAL_FN.buildUrl('invoice/proforma/data'),
          type: 'POST',
          data: {
            'tab': activeTab
          },
          dataSrc: function dataSrc(json) {
            $('#dataTable tbody').find('.loading-row').remove();
            GLOBAL_FN.setStatusCounts(json.statusCounts);
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
          orderable: false
        }],
        columns: [{
          data: 'row_no',
          render: function render(data, type, row) {
            return '<strong>' + row.row_no + '</strong>';
          }
        }, {
          data: 'job_no'
        },
        /*{
            data: 'job_no', render: function (data, type, row) {
                return '<div>' + row.job_no + '</div><div class="small text-muted">Code: ' + row.job.shipment_mode + '</div>';
            }
        },*/
        {
          data: 'customer.name_en',
          render: function render(data, type, row) {
            return '<div>' + row.customer.name_en + '</div><div class="small text-muted">Code: ' + row.customer.row_no + '</div>';
          }
        }, {
          data: 'currency',
          render: function render(data, type, row) {
            if (row.currency == baseCurrency) {
              return '<div>' + row.currency + '</div>';
            } else {
              return '<div>' + row.currency + ' → SAR</div><small class="text-muted">1 ' + row.currency + ' = ' + row.currency_rate + ' ' + baseCurrency + '</small>';
            }
          }
        }, {
          data: 'base_sub_total',
          render: function render(data, type, row) {
            return '<div class="text-end text-secondary">' + row.base_sub_total + '</div><div class="text-end"><small class="text-muted">' + baseCurrency + '</small></div>';
          }
        }, {
          data: 'base_tax_total',
          render: function render(data, type, row) {
            return '<div class="text-end text-secondary">' + row.base_tax_total + '</div><div class="text-end"><small class="text-muted">' + baseCurrency + '</small></div>';
          }
        }, {
          data: 'sub_total',
          render: function render(data, type, row) {
            return '<div class="text-end">' + row.sub_total + '</div><div class="text-end"><small>' + row.currency + '</small></div>';
          }
        }, {
          data: 'tax_total',
          render: function render(data, type, row) {
            return '<div class="text-end">' + row.tax_total + '</div><div class="text-end"><small>' + row.currency + '</small></div>';
          }
        }, {
          data: 'grand_total',
          render: function render(data, type, row) {
            return '<div class="text-end fw-semibold">' + row.grand_total + '</div><div class="text-end"><small>' + row.currency + '</small></div>';
          }
        }, {
          data: 'posted_at'
        }, GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: ""
        },
        deferLoading: 0,
        initComplete: function initComplete() {
          PROFORMA_INVOICE.form.open();
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
    extraActions: function extraActions(row) {
      PROFORMA_INVOICE.list.actions.statusChange(row);
      PROFORMA_INVOICE.list.actions.view(row);
      PROFORMA_INVOICE.list.actions.email(row);
    },
    actions: {
      statusChange: function statusChange(row) {
        $('#row_pending,#row_approved,#row_rejected').off().on('click', function () {
          var fd = new FormData();
          changeCustomerStatus(GLOBAL_FN.buildUrl('invoice/proforma/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
            method: 'POST',
            data: fd,
            callBack: 'datatable'
          }, $(this).attr('data-value'));
        });
        $('#row_converted').off().on('click', function () {
          alert("convert to invoice");
        });
      },
      view: function view(row) {
        $('#row_view').off().on('click', function () {
          var customerId = row.attr('data-id');

          // Open drawer

          var drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
          drawer.show();

          // Load Overview
          $('#moduleOverview').html('<p>Loading...</p>');
          $.get('/invoice/proforma/' + customerId + '/overview', function (data) {
            $('#moduleOverview').html(data);
          });
        });
      },
      email: function email(row) {
        $('#row_email').off().on('click', function () {
          var drawer = new bootstrap.Offcanvas(document.getElementById('sendEmailDrawer'));
          drawer.show();
        });
      }
    }
  },
  form: {
    load: function load() {
      PROFORMA_INVOICE.form.open();
    },
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'New Proforma Invoice',
          url: GLOBAL_FN.buildUrl('invoice/proforma/create'),
          content: null,
          size: '3xl'
        });
      });
    },
    openCallback: function openCallback() {
      PROFORMA_INVOICE.form.addRow();
      PROFORMA_INVOICE.form.removeRow();
      CALCULATION.load();
      CALCULATION.finalTotals();
    },
    addRow: function addRow() {
      // Add Package Row
      $('#' + MODULE + '-tbody').off('click', '.add-row').on('click', '.add-row', function () {
        var $tbody = $(this).closest('tbody');
        var $newRow = $tbody.find('tr:first').clone();

        // Clear values in cloned row
        $newRow.find('input, select, textarea').val('');
        $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
        $newRow.find('div.ts-wrapper').remove();
        initTomSelectForm($newRow);
        $tbody.append($newRow);
        //PROFORMA_INVOICE.form.removeRow();
      });
    },
    removeRow: function removeRow() {
      $('#' + MODULE + '-tbody').off('click', '.remove-row').on('click', '.remove-row', function () {
        var $tbody = $(this).closest('tbody');
        var $tr = $(this).closest('tr');
        if ($tbody.find('tr').length > 1) {
          $tr.remove();
        } else {
          // If only one row left, just clear it
          // $(this).closest('tr').find('input, select').val('');
          $tr.find('input,textarea').val('');
          $tr.find('select').each(function () {
            $(this).val('');
            if ($(this).hasClass('selectpicker')) {
              $(this).selectpicker('destroy').addClass('selectpicker');
              console.log($(this).attr('id'));
              selectPicker('#' + $(this).closest('table').attr('id'));
            }
          });
        }
        CALCULATION.finalTotals();
      });
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!*******************************************!*\
  !*** ./public/js/page-all-js/prospect.js ***!
  \*******************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
PROSPECT = {
  title: 'Prospect',
  baseUrl: 'prospect',
  actionUrl: 'prospect',
  load: function load() {
    PROSPECT.form.open();
  },
  list: {
    load: function load(activeTab) {
      PROSPECT.list.dataTable(activeTab);
    },
    dataTable: function dataTable() {
      var activeTab = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      GLOBAL_FN.destroyDataTable();
      activeTab = activeTab && _typeof(activeTab) !== 'object' ? activeTab : $("#listTabs").find('li button.active').attr('id');
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        order: [[1, 'desc']],
        ajax: {
          url: GLOBAL_FN.buildUrl('prospect/data'),
          type: 'POST',
          data: {
            'tab': activeTab
          },
          dataSrc: function dataSrc(json) {
            // Remove loader rows when data arrives
            $('#dataTable tbody').find('.loading-row').remove();
            GLOBAL_FN.setStatusCounts(json.statusCounts);
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0],
          orderable: false
        }
        /*{targets: [1], class: 'hide', visible: false},*/],
        columns: [{
          data: 'DT_RowIndex',
          "class": 'hide-tooltip fav-index'
        }, {
          data: 'name_en',
          render: function render(data, type, row) {
            return row.name_en;
          }
        }, {
          data: 'email',
          render: function render(data, type, row) {
            return row.email;
          }
        }, {
          data: 'phone',
          render: function render(data, type, row) {
            return row.phone;
          }
        }, {
          data: 'salesperson.name'
        }, {
          data: 'created_at',
          name: 'created_at'
        },
        // Actions column
        GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: "" // removes "Search:" label
        },
        deferLoading: 0,
        // don't load immediately
        /*preDrawCallback: function (settings) {
            let api = this.api();
            let cols = api.columns().nodes().length;
            let pageLen = api.page.len(); // rows per page
             // clear old rows & insert loader rows
            let loaderRows = '';
            for (let i = 0; i < pageLen; i++) {
                loaderRows += '<tr class="loading-row">';
                for (let j = 0; j < cols; j++) {
                    loaderRows += `<td><div class="loader-td"></div></td>`;
                }
                loaderRows += '</tr>';
            }
            $('#dataTable tbody').html(loaderRows);
        }*/

        initComplete: function initComplete() {
          PROSPECT.form.open();
          webDataTable.actions.menu();
        }
      });
      $('#customSearch').on('keyup', function () {
        table.search(this.value).draw();
      });
      webDataTable.loader(table);
      webDataTable.search(table);
      //webDataTable.actions.menu();
    },
    extraActions: function extraActions(row) {
      PROSPECT.list.actions["delete"](row);
    },
    actions: {
      "delete": function _delete(row) {
        $('#row_delete').off().on('click', function () {
          $.confirm({
            title: 'Confirm Delete',
            content: 'Are you sure you want to delete this record?',
            type: 'red',
            buttons: {
              cancel: function cancel() {},
              "delete": {
                text: 'Delete',
                btnClass: 'btn-red',
                action: function action() {
                  $.ajax({
                    url: '/prospect/delete/' + row.attr('data-id'),
                    type: 'DELETE',
                    dataType: 'json',
                    success: function success(response) {
                      if (response.status === 'success') {
                        toastr.success(response.message);
                        //row.remove(); // remove row if needed
                        loadJs('list.load');
                      } else if (response.status === 'warning') {
                        toastr.warning(response.message);
                      } else {
                        toastr.error('Error deleting record.');
                      }
                    },
                    error: function error() {
                      toastr.error('Server error');
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
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'Add Prospect',
          url: GLOBAL_FN.buildUrl('prospect/create'),
          content: null,
          size: 'md',
          callBack: null,
          minHeight: '300'
        });
      });
    },
    quick: {
      open: function open() {
        webModal.quickModal.open({
          title: 'Add Quick Prospect',
          url: GLOBAL_FN.buildUrl('prospect/create/quick'),
          content: null,
          size: 'md',
          callBack: null,
          module: 'PROSPECT'
        });
      },
      after: {
        save: function save(data) {
          var ts = document.querySelector('#prospect').tomselect;

          // If TomSelect already exists → add dynamically
          if (ts) {
            ts.addOption({
              value: data.id,
              text: data.name,
              subtext: data.code
            });
            ts.addItem(data.id); // select it
          } else {
            // If not initialized yet
            $('#prospect').prepend("<option value=\"".concat(data.id, "\" data-subtext=\"").concat(data.code, "\" selected>").concat(data.name, "</option>"));
          }
        }
      }
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!********************************************!*\
  !*** ./public/js/page-all-js/quotation.js ***!
  \********************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
QUOTATION = {
  title: 'Quotation',
  baseUrl: 'sales/quotation',
  actionUrl: 'sales/quotation',
  load: function load() {
    QUOTATION.form.load();
  },
  printPreview: function printPreview(printId) {
    var iframe = document.getElementById('print-frame');
    iframe.onload = function () {
      try {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        var doc = iframe.contentDocument || iframe.contentWindow.document;
        iframe.style.height = doc.body.scrollHeight + 'px';
      } catch (e) {
        console.error('Cannot print iframe content. Cross-origin issue?', e);
      }
    };
    iframe.src = '/' + QUOTATION.baseUrl + '/' + printId + '/print';
  },
  downloadPDF: function downloadPDF(printId) {
    fetch('/' + QUOTATION.baseUrl + '/' + printId + '/print').then(function (res) {
      return res.text();
    }).then(function (html) {
      var container = document.createElement('div');
      //container.style.display = 'none';
      container.id = 'html-pdf';
      container.className = 'px-4';
      container.innerHTML = html;
      console.log(container);
      //document.body.appendChild(container);
      var opt = {
        margin: 0.2,
        filename: "quotation-".concat(printId, ".pdf")
      };
      html2pdf().set(opt).from(container).save()["finally"](function () {
        //document.body.removeChild(container);
      });
    });
  },
  list: {
    load: function load(activeTab) {
      QUOTATION.list.dataTable(activeTab);
    },
    dataTable: function dataTable() {
      var activeTab = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      GLOBAL_FN.destroyDataTable();
      activeTab = activeTab && _typeof(activeTab) !== 'object' ? activeTab : $("#listTabs").find('li button.active').attr('id');
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        order: [[1, 'desc']],
        ajax: {
          url: GLOBAL_FN.buildUrl('sales/quotation/data'),
          type: 'POST',
          data: {
            'tab': activeTab
          },
          dataSrc: function dataSrc(json) {
            // Remove loader rows when data arrives
            $('#dataTable tbody').find('.loading-row').remove();
            GLOBAL_FN.setStatusCounts(json.statusCounts);
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0],
          orderable: false
        }
        /*{targets: [1], class: 'hide', visible: false},*/],
        columns: [/*{data: 'DT_RowIndex', class: 'text-center hide-tooltip fav-index'},*/
        {
          data: 'row_no',
          "class": 'hide-tooltip fav-index'
        }, {
          data: 'name',
          render: function render(data, type, row) {
            return row.name.name + '<br><small class="text-muted">' + row.name.row_no + '</small>';
          }
        }, {
          data: 'services'
        }, {
          data: 'activity.name'
        }, {
          data: 'pol',
          render: function render(data, type, row) {
            return "<div class=\"text-capitalize\">".concat(row.pol, " -> ").concat(row.pod, "</div>");
          }
        }, {
          data: 'posted_at'
        }, {
          data: 'valid_until'
        }, {
          data: 'salesperson.name'
        },
        /*{
            data: 'shipment_type',
            render: function (data, type, row) {
                let icon = '';
                switch (row.shipment_type) {
                    case 'air':
                        icon = '<i class="bi bi-airplane text-secondary me-1"></i>'; // free Font Awesome
                        break;
                    case 'sea':
                        icon = '<i class="fa fa-ship text-secondary me-1"></i>';
                        break;
                    case 'road':
                        icon = '<i class="bi bi-truck text-secondary me-1"></i>';
                        break;
                    case 'rail':
                        icon = '<i class="bi bi-train-front text-secondary me-1"></i>';
                        break;
                }
                 let typeText = row.shipment_type ? `<div>${(row.shipment_type)}</div>` : '';
                let categoryText = row.shipment_category ? `<div>${row.shipment_category}</div>` : '';
                 return `<div class="d-flex align-items-start">
                        <div class="me-2" style="width:16px;">${icon}</div>
                        <div class="d-flex flex-column text-capitalize">
                            ${typeText}
                            ${categoryText}
                        </div>
                    </div>`;
            }
        },
        {
            data: 'origin_city',
            render: function (data, type, row) {
                let origin = (row.origin_city || row.origin_country)
                    ? `<div class="text-capitalize"><i class="fa fa-map-marker-alt text-success me-1"></i>${row.origin_city ?? ''}, ${row.origin_country ?? ''}</div>`
                    : '';
                return origin;
            }
        },
        {
            data: 'destination_city',
            render: function (data, type, row) {
                let dest = (row.destination_city || row.destination_country)
                    ? `<div class="text-capitalize"><i class="fa fa-map-marker-alt text-danger me-1"></i>${row.destination_city ?? ''}, ${row.destination_country ?? ''}</div>`
                    : '';
                return dest;
            }
        },
        {
            data: 'pickup_date',
            render: function (data, type, row) {
                let pickup = row.pickup_date ? `<div><i class="bi bi-calendar-event text-primary me-1"></i>${row.pickup_date}</div>` : '';
                let delivery = row.delivery_date ? `<div><i class="bi bi-calendar-check text-success me-1"></i>${row.delivery_date}</div>` : '';
                return pickup + delivery;
            }
        },
        {
            data: 'weight',
            render: function (data, type, row) {
                let weight = row.weight ? `<div><span class="fw-semibold">Weight:</span> ${row.weight} kg</div>` : '';
                let volume = row.volume ? `<div><span class="fw-semibold">Volume:</span> ${row.volume} m³</div>` : '';
                return weight + volume;
            }
        },
        {data: 'expiry_date'},
        {data: 'created_at'},*/
        // Actions column
        GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: "" // removes "Search:" label
        },
        deferLoading: 0,
        initComplete: function initComplete() {
          QUOTATION.form.open();
          webDataTable.actions.menu();
        }
      });
      $('#customSearch').on('keyup', function () {
        table.search(this.value).draw();
      });
      //$('#dataTable_filter').closest('div.row').remove();
      webDataTable.loader(table);
      webDataTable.search(table);
      //webDataTable.actions.menu();
    },
    extraActions: function extraActions(row) {
      QUOTATION.list.actions.statusChange(row);
      QUOTATION.list.actions.view(row);
      QUOTATION.list.actions.email(row);
      //QUOTATION.list.actions.convertToJob(row);
    },
    actions: {
      statusChange: function statusChange(row) {
        $('#row_pending,#row_accepted,#row_rejected,#row_convert_to_job').off().on('click', function () {
          var fd = new FormData();
          changeCustomerStatus(GLOBAL_FN.buildUrl('sales/quotation/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
            method: 'POST',
            data: fd,
            callBack: 'datatable'
          }, $(this).attr('data-value'));
        });
      },
      /*convertToJob(row) {
          $('#row_convert_to_job').off().on('click', function () {
              localStorage.setItem('convert-quotation', row.attr('data-id'));
              window.location.href = GLOBAL_FN.buildUrl(`operation/jobs`);
          });
      },*/
      view: function view(row) {
        $('#row_view').off().on('click', function () {
          var customerId = row.attr('data-id');

          // Open drawer

          var drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
          drawer.show();

          // Load Overview
          $('#moduleOverview').html('<p>Loading...</p>');
          $.get('/sales/quotation/' + customerId + '/overview', function (data) {
            $('#moduleOverview').html(data);
          });
        });
      },
      email: function email(row) {
        $('#row_email').off().on('click', function () {
          var quotationId = row.attr('data-id');

          // Fetch email data from server
          $.get('/sales/quotation/' + quotationId + '/email-data', function (data) {
            // Populate the email form
            $('#emailTo').val(data.to);
            $('#emailCc').val(data.cc);
            $('#emailSubject').val('Quotation #' + data.id);

            // Show the drawer
            var drawer = new bootstrap.Offcanvas(document.getElementById('sendEmailDrawer'));
            drawer.show();

            // Handle form submission
            $('#sendEmailForm').off('submit').on('submit', function (e) {
              e.preventDefault();

              // Create FormData object
              var formData = new FormData(this);

              // Show loading state
              var submitBtn = $(this).find('button[type="submit"]');
              var originalBtnText = submitBtn.html();
              submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');
              submitBtn.prop('disabled', true);

              // Send the email
              $.ajax({
                url: '/sales/quotation/send-email',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function success(response) {
                  // Close the drawer
                  bootstrap.Offcanvas.getInstance(document.getElementById('sendEmailDrawer')).hide();

                  // Show success message
                  toastr.success(response.message);

                  // Reset form
                  $('#sendEmailForm')[0].reset();
                },
                error: function error(xhr) {
                  // Show error message
                  if (xhr.responseJSON && xhr.responseJSON.message) {
                    toastr.error(xhr.responseJSON.message);
                  } else {
                    toastr.error('An error occurred while sending the email.');
                  }
                },
                complete: function complete() {
                  // Reset button state
                  submitBtn.html(originalBtnText);
                  submitBtn.prop('disabled', false);
                }
              });
            });
          });
        });
      }
    }
  },
  form: {
    load: function load() {
      QUOTATION.form.open();
      var enquiryId = localStorage.getItem('convert-enquiry');
      if (enquiryId) {
        webModal.openGlobalModal({
          title: 'New Quotation',
          url: GLOBAL_FN.buildUrl('sales/quotation/create'),
          size: 'xxl',
          scroll: false,
          minHeight: '700px',
          content: {
            enquiryId: enquiryId
          }
        });
        localStorage.removeItem('convert-enquiry');
      }
    },
    open: function open() {
      $('#new').off().on('click', function () {
        var enquiryId = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
        webModal.openGlobalModal({
          title: 'New Quotation',
          url: GLOBAL_FN.buildUrl('sales/quotation/create'),
          content: null,
          size: '3xl',
          scroll: false,
          minHeight: '700px'
        });
      });
    },
    openCallback: function openCallback() {
      QUOTATION.form.addContainer();
      QUOTATION.form.addPackage();
      QUOTATION.form.removeRow();
      QUOTATION.form.shipmentMode();
      setTimeout(function () {
        QUOTATION.form.customerProspectToggle();
      });
      GLOBAL_FN.activity.activityChange();
      QUOTATION.form.polPodLoad();
    },
    customerProspectToggle: function customerProspectToggle() {
      // Handle customer select change
      $('#customer').on('change', function () {
        var customerValue = $(this).val();
        var prospectSelect = document.querySelector('#prospect');
        if (customerValue && customerValue !== '') {
          // Disable prospect select when customer is selected
          if (prospectSelect && prospectSelect.tomselect) {
            prospectSelect.tomselect.disable();
          }
        } else {
          // Enable prospect select when customer is cleared
          if (prospectSelect && prospectSelect.tomselect) {
            prospectSelect.tomselect.enable();
          }
        }
      });

      // Handle prospect select change
      $('#prospect').on('change', function () {
        var prospectValue = $(this).val();
        var customerSelect = document.querySelector('#customer');
        if (prospectValue && prospectValue !== '') {
          // Disable customer select when prospect is selected
          if (customerSelect && customerSelect.tomselect) {
            customerSelect.tomselect.disable();
          }
        } else {
          // Enable customer select when prospect is cleared
          if (customerSelect && customerSelect.tomselect) {
            customerSelect.tomselect.enable();
          }
        }
      });

      // Initial check on page load
      var customerValue = $('#customer').val();
      var prospectValue = $('#prospect').val();
      var customerSelect = document.querySelector('#customer');
      var prospectSelect = document.querySelector('#prospect');

      // Check if we're in edit mode with a prospect
      var isEditMode = $('#data-id').val() && $('#prospect').length > 0;
      var hasProspectId = $('#prospect').data('has-prospect') === true || $('[name="prospect"]').find('option:selected').val() !== '';
      if (customerValue && customerValue !== '') {
        // Disable prospect select if customer is already selected
        if (prospectSelect && prospectSelect.tomselect) {
          prospectSelect.tomselect.disable();
        }
      } else if (prospectValue && prospectValue !== '' || isEditMode && hasProspectId) {
        // Disable customer select if prospect is already selected or we're editing a prospect
        if (customerSelect && customerSelect.tomselect) {
          customerSelect.tomselect.disable();
        }
      }
    },
    shipmentMode: function shipmentMode() {
      var destroy = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      if (destroy) {
        var quotationPol = document.querySelector('#pol');
        var quotationPod = document.querySelector('#pod');

        // If already initialized, destroy first
        quotationPol.tomselect.destroy();
        quotationPod.tomselect.destroy();
        QUOTATION.form.polPodLoad(true);
      }
    },
    polPodLoad: function polPodLoad() {
      var preLoad = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      var port = $('#activity-id-hidden').val();
      initTomSelectSearch('#pol', port, 50, preLoad);
      initTomSelectSearch('#pod', port, 50, preLoad);
      initTomSelectSearch('#carrier', port + 'Lines', 50, preLoad);
    },
    addContainer: function addContainer() {
      // Add Container Row
      $('#addContainerRow').off().on('click', function () {
        var $table = $('#containerTable tbody');
        var $newRow = $table.find('tr:first').clone();

        // Clear values in cloned row
        $newRow.find('input, select').val('');
        //$newRow.find('.bootstrap-select button').remove();
        $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
        $newRow.find('div.ts-wrapper').remove();
        initTomSelectForm($newRow);
        //selectPicker($newRow);

        $table.append($newRow);
        QUOTATION.form.removeRow();
      });
    },
    addPackage: function addPackage() {
      // Add Package Row
      $('#addPackageRow').off().on('click', function () {
        var $table = $('#packageTable tbody');
        var $newRow = $table.find('tr:first').clone();

        // Clear values in cloned row
        $newRow.find('input, select').val('');
        $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
        $newRow.find('div.ts-wrapper').remove();
        initTomSelectForm($newRow);
        $table.append($newRow);
        QUOTATION.form.removeRow();
      });
    },
    removeRow: function removeRow() {
      // Remove Row (for both tables)
      $('#containerTable,#packageTable').off('click', '.remove-row').on('click', '.remove-row', function () {
        var $tbody = $(this).closest('tbody');
        var $tr = $(this).closest('tr');
        if ($tbody.find('tr').length > 1) {
          $tr.remove();
        } else {
          // If only one row left, just clear it
          // $(this).closest('tr').find('input, select').val('');
          $tr.find('input').val('');
          $tr.find('select').each(function () {
            $(this).val('');
            if ($(this).hasClass('selectpicker')) {
              $(this).selectpicker('destroy').addClass('selectpicker');
              console.log($(this).attr('id'));
              selectPicker('#' + $(this).closest('table').attr('id'));
            }
          });
        }
      });
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!******************************************!*\
  !*** ./public/js/page-all-js/seaport.js ***!
  \******************************************/
SEAPORT = {
  baseUrl: 'masters/transport/directories/seaport',
  actionUrl: 'masters/transport/directories/seaport',
  load: function load() {
    SEAPORT.form.open();
  },
  list: {
    load: function load() {
      SEAPORT.list.dataTable();
    },
    dataTable: function dataTable() {
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        order: [[1, 'desc']],
        ajax: {
          url: '/masters/transport/directories/seaport/data',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          dataSrc: function dataSrc(json) {
            // Remove loader rows when data arrives
            $('#dataTable tbody').find('.loading-row').remove();
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0],
          orderable: false
        }
        /*{targets: [1], class: 'hide', visible: false},*/],
        columns: [{
          data: 'DT_RowIndex',
          "class": 'text-center hide-tooltip fav-index'
        }, {
          data: 'name',
          render: function render(data, type, row) {
            return row.name;
          }
        }, {
          data: 'code',
          render: function render(data, type, row) {
            return row.code;
          }
        }, {
          data: 'country_name',
          render: function render(data, type, row) {
            return row.country_name;
          }
        },
        // Actions column
        GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: "" // removes "Search:" label
        },
        deferLoading: 0,
        // don't load immediately

        initComplete: function initComplete() {
          // Add custom class to default search input
          SEAPORT.form.open();
          webDataTable.actions.menu();
        }
      });
      $('#customSearch').on('keyup', function () {
        table.search(this.value).draw();
      });
      webDataTable.loader(table);
      webDataTable.search(table);
      //webDataTable.actions.menu();
    }
  },
  form: {
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'Add Seaport',
          url: GLOBAL_FN.buildUrl('masters/transport/directories/seaport/create'),
          content: null,
          size: 'xl',
          callBack: null
        });
      });
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!***************************************************!*\
  !*** ./public/js/page-all-js/supplier_invoice.js ***!
  \***************************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
SUPPLIER_INVOICE = {
  title: 'Supplier Invoice',
  baseUrl: 'invoice/supplier',
  actionUrl: 'invoice/supplier',
  load: function load() {
    SUPPLIER_INVOICE.form.load();
  },
  printPreview: function printPreview(printId) {
    var iframe = document.getElementById('print-frame');
    iframe.onload = function () {
      try {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
        var doc = iframe.contentDocument || iframe.contentWindow.document;
        iframe.style.height = doc.body.scrollHeight + 'px';
      } catch (e) {
        console.error('Cannot print iframe content. Cross-origin issue?', e);
      }
    };
    iframe.src = '/' + SUPPLIER_INVOICE.baseUrl + '/' + printId + '/print';
  },
  downloadPDF: function downloadPDF(printId) {
    fetch('/invoice/supplier/' + printId + '/print').then(function (res) {
      return res.text();
    }).then(function (html) {
      var container = document.createElement('div');
      //container.style.display = 'none';
      container.id = 'html-pdf';
      container.className = 'px-4';
      container.innerHTML = html;
      console.log(container);
      //document.body.appendChild(container);
      var opt = {
        margin: 0.2,
        filename: "supplierInvoice-".concat(printId, ".pdf")
      };
      html2pdf().set(opt).from(container).save()["finally"](function () {
        //document.body.removeChild(container);
      });
    });
  },
  list: {
    load: function load(activeTab) {
      SUPPLIER_INVOICE.list.dataTable(activeTab);
    },
    dataTable: function dataTable() {
      var activeTab = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      GLOBAL_FN.destroyDataTable();
      activeTab = activeTab && _typeof(activeTab) !== 'object' ? activeTab : $("#listTabs").find('li button.active').attr('id');
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        orderable: false,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        /*order: [[1, 'desc']],*/
        ajax: {
          url: GLOBAL_FN.buildUrl('invoice/supplier/data'),
          type: 'POST',
          data: {
            'tab': activeTab
          },
          dataSrc: function dataSrc(json) {
            $('#dataTable tbody').find('.loading-row').remove();
            GLOBAL_FN.setStatusCounts(json.statusCounts);
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
          orderable: false
        }],
        columns: [{
          data: 'row_no',
          render: function render(data, type, row) {
            return '<strong>' + row.row_no + '</strong>';
          }
        }, {
          data: 'invoice_number'
        }, {
          data: 'job_no'
        }, {
          data: 'supplier.name_en',
          render: function render(data, type, row) {
            return '<div>' + row.supplier.name_en + '</div><div class="small text-muted">Code: ' + row.supplier.row_no + '</div>';
          }
        },
        /*{
            data: 'currency', render: function (data, type, row) {
                if (row.currency == baseCurrency) {
                    return '<div>' + row.currency + '</div>';
                } else {
                    return '<div>' + row.currency + ' → SAR</div><small class="text-muted">1 ' + row.currency + ' = ' + row.currency_rate + ' ' + baseCurrency + '</small>';
                }
            }
        },*/
        {
          data: 'base_total',
          render: function render(data, type, row) {
            return '<div class="text-end text-secondary">' + row.base_total + '</div><div class="text-end"><small class="text-muted">' + baseCurrency + '</small></div>';
          }
        }, {
          data: 'grand_total',
          render: function render(data, type, row) {
            return '<div class="text-end fw-semibold">' + row.grand_total + '</div><div class="text-end"><small>' + row.currency + '</small></div>';
          }
        }, {
          data: 'balance',
          "class": 'text-end'
        }, {
          data: 'invoice_date'
        }, {
          data: 'due_at'
        }, GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: ""
        },
        deferLoading: 0,
        initComplete: function initComplete() {
          SUPPLIER_INVOICE.form.open();
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
    extraActions: function extraActions(row) {
      SUPPLIER_INVOICE.list.actions.statusChange(row);
      SUPPLIER_INVOICE.list.actions.view(row);
      SUPPLIER_INVOICE.list.actions.email(row);
    },
    actions: {
      statusChange: function statusChange(row) {
        $('#row_pending,#row_approved,#row_rejected').off().on('click', function () {
          var fd = new FormData();
          changeCustomerStatus(GLOBAL_FN.buildUrl('invoice/supplier/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
            method: 'POST',
            data: fd,
            callBack: 'datatable'
          }, $(this).attr('data-value'));
        });
        $('#row_converted').off().on('click', function () {
          alert("convert to invoice");
        });
      },
      view: function view(row) {
        $('#row_view').off().on('click', function () {
          var customerId = row.attr('data-id');

          // Open drawer

          var drawer = new bootstrap.Offcanvas(document.getElementById('moduleDrawer'));
          drawer.show();

          // Load Overview
          $('#moduleOverview').html('<p>Loading...</p>');
          $.get('/invoice/supplier/' + customerId + '/overview', function (data) {
            $('#moduleOverview').html(data);
          });
        });
      },
      email: function email(row) {
        $('#row_email').off().on('click', function () {
          var drawer = new bootstrap.Offcanvas(document.getElementById('sendEmailDrawer'));
          drawer.show();
        });
      }
    }
  },
  form: {
    load: function load() {
      SUPPLIER_INVOICE.form.open();
    },
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'New Supplier Invoice',
          url: GLOBAL_FN.buildUrl('invoice/supplier/create'),
          content: null,
          size: '3xl'
        });
      });
    },
    openCallback: function openCallback() {
      SUPPLIER_INVOICE.form.addRow();
      SUPPLIER_INVOICE.form.removeRow();
      CALCULATION.load();
      CALCULATION.finalTotals();
    },
    addRow: function addRow() {
      // Add Package Row
      $('#' + MODULE + '-tbody').off('click', '.add-row').on('click', '.add-row', function () {
        var $tbody = $(this).closest('tbody');
        var $newRow = $tbody.find('tr:first').clone();

        // Clear values in cloned row
        $newRow.find('input, select, textarea').val('');
        $newRow.find('select').removeClass('tomselected').removeClass('ts-hidden-accessible');
        $newRow.find('div.ts-wrapper').remove();
        initTomSelectForm($newRow);
        $tbody.append($newRow);
        //PROFORMA_INVOICE.form.removeRow();
      });
    },
    removeRow: function removeRow() {
      $('#' + MODULE + '-tbody').off('click', '.remove-row').on('click', '.remove-row', function () {
        var $tbody = $(this).closest('tbody');
        var $tr = $(this).closest('tr');
        if ($tbody.find('tr').length > 1) {
          $tr.remove();
        } else {
          // If only one row left, just clear it
          // $(this).closest('tr').find('input, select').val('');
          $tr.find('input,textarea').val('');
          $tr.find('select').each(function () {
            $(this).val('');
            if ($(this).hasClass('selectpicker')) {
              $(this).selectpicker('destroy').addClass('selectpicker');
              console.log($(this).attr('id'));
              selectPicker('#' + $(this).closest('table').attr('id'));
            }
          });
        }
        CALCULATION.finalTotals();
      });
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!*******************************************!*\
  !*** ./public/js/page-all-js/supplier.js ***!
  \*******************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
SUPPLIER = {
  title: 'Supplier',
  baseUrl: 'supplier',
  actionUrl: 'supplier',
  load: function load() {
    SUPPLIER.form.open();
  },
  list: {
    load: function load(activeTab) {
      SUPPLIER.list.dataTable(activeTab);
    },
    dataTable: function dataTable() {
      var activeTab = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      GLOBAL_FN.destroyDataTable();
      activeTab = activeTab && _typeof(activeTab) !== 'object' ? activeTab : $("#listTabs").find('li button.active').attr('id');
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        order: [[1, 'desc']],
        ajax: {
          url: GLOBAL_FN.buildUrl('supplier/data'),
          type: 'POST',
          data: {
            'tab': activeTab
          },
          dataSrc: function dataSrc(json) {
            // Remove loader rows when data arrives
            $('#dataTable tbody').find('.loading-row').remove();
            GLOBAL_FN.setStatusCounts(json.statusCounts);
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0],
          orderable: false
        }
        /*{targets: [1], class: 'hide', visible: false},*/],
        columns: [{
          data: 'DT_RowIndex',
          "class": 'hide-tooltip fav-index'
        }, {
          data: 'name_en',
          render: function render(data, type, row) {
            return row.name_en + '<br><small class="text-muted">' + row.row_no + '</small>';
          }
        }, {
          data: 'email',
          render: function render(data, type, row) {
            return row.email + '<br><small class="text-muted">' + row.phone + '</small>';
          }
        }, {
          data: 'country',
          render: function render(data, type, row) {
            return (row.city_en ? row.city_en + ', ' : '') + row.country;
          }
        }, {
          data: 'currency',
          name: 'currency'
        }, {
          data: 'vat_number',
          render: function render(data, type, row) {
            return row.vat_number;
          }
        }, {
          data: 'credit_limit',
          render: function render(data, type, row) {
            var _row$credit_limit;
            return ((_row$credit_limit = row.credit_limit) !== null && _row$credit_limit !== void 0 ? _row$credit_limit : '') + '<br><small class="text-muted">' + (row.credit_days ? row.credit_days + ' Days' : '') + '</small>';
          }
        }, {
          data: 'created_at',
          name: 'created_at'
        },
        // Actions column
        GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: "" // removes "Search:" label
        },
        deferLoading: 0,
        // don't load immediately
        /*preDrawCallback: function (settings) {
            let api = this.api();
            let cols = api.columns().nodes().length;
            let pageLen = api.page.len(); // rows per page
             // clear old rows & insert loader rows
            let loaderRows = '';
            for (let i = 0; i < pageLen; i++) {
                loaderRows += '<tr class="loading-row">';
                for (let j = 0; j < cols; j++) {
                    loaderRows += `<td><div class="loader-td"></div></td>`;
                }
                loaderRows += '</tr>';
            }
            $('#dataTable tbody').html(loaderRows);
        }*/

        initComplete: function initComplete() {
          SUPPLIER.form.open();
          webDataTable.actions.menu();
        }
      });
      $('#customSearch').on('keyup', function () {
        table.search(this.value).draw();
      });
      $('#dataTable_filter').closest('div.row').remove();
      webDataTable.loader(table);
      webDataTable.search(table);
      //webDataTable.actions.menu();
    },
    extraActions: function extraActions(row) {
      SUPPLIER.list.actions.statusChange(row);
      SUPPLIER.list.actions.view(row);
    },
    actions: {
      statusChange: function statusChange(row) {
        $('#row_confirm,#row_blocked').off().on('click', function () {
          //GLOBAL_FN.ajaxData.sendData(GLOBAL_FN.buildUrl('customer/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')),'datatable',{})
          var fd = new FormData();
          changeCustomerStatus(GLOBAL_FN.buildUrl('supplier/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
            method: 'POST',
            data: fd,
            callBack: 'datatable'
          }, $(this).attr('data-value'));
        });
      },
      view: function view(row) {
        $('#row_view').off().on('click', function () {
          var customerId = row.attr('data-id');

          // Open drawer
          var drawer = new bootstrap.Offcanvas(document.getElementById('supplierDrawer'));
          drawer.show();

          // Load Overview
          $('#customerOverview').html('<p>Loading...</p>');
          $.get('/supplier/' + customerId + '/overview', function (data) {
            $('#customerOverview').html(data);
          });

          // Clear other tabs
          $('#customerInvoices').html('');
          $('#customerTransactions').html('');
        });
      }
    }
  },
  form: {
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'Add Supplier',
          url: GLOBAL_FN.buildUrl('supplier/create'),
          content: null,
          size: 'xl',
          callBack: null
        });
      });
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!***************************************!*\
  !*** ./public/js/page-all-js/user.js ***!
  \***************************************/
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
USER = {
  title: 'User',
  baseUrl: 'masters/user',
  actionUrl: 'masters/user',
  load: function load() {
    USER.form.open();
    USER.profile.save();
  },
  list: {
    load: function load(activeTab) {
      USER.list.dataTable(activeTab);
    },
    dataTable: function dataTable() {
      var activeTab = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
      GLOBAL_FN.destroyDataTable();
      activeTab = activeTab && _typeof(activeTab) !== 'object' ? activeTab : $("#listTabs").find('li button.active').attr('id');
      var table = $('#dataTable').DataTable({
        processing: false,
        serverSide: true,
        autoWidth: false,
        lengthChange: false,
        pageLength: 25,
        dom: 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7 d-flex justify-content-end"p>>',
        order: [[1, 'desc']],
        ajax: {
          url: '/masters/user/data',
          type: 'POST',
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          dataSrc: function dataSrc(json) {
            // Remove loader rows when data arrives
            $('#dataTable tbody').find('.loading-row').remove();
            return json.data;
          }
        },
        columnDefs: [{
          targets: [0],
          searchable: false
        }, {
          targets: [0],
          orderable: false
        }
        /*{targets: [1], class: 'hide', visible: false},*/],
        columns: [{
          data: 'DT_RowIndex',
          "class": 'text-center hide-tooltip fav-index'
        }, {
          data: 'name',
          render: function render(data, type, row) {
            return row.name;
          }
        }, {
          data: 'email',
          render: function render(data, type, row) {
            return row.email + '<br>' + row.phone;
          }
        }, {
          data: 'department.name',
          render: function render(data, type, row) {
            return row.department.name + '<br>' + row.role;
          }
        }, {
          data: 'last_login'
        }, {
          data: 'created_at',
          name: 'created_at'
        },
        // Actions column
        GLOBAL_FN.dataTable.optionButton()],
        language: {
          search: "" // removes "Search:" label
        },
        deferLoading: 0,
        // don't load immediately

        initComplete: function initComplete() {
          // Add custom class to default search input
          USER.form.open();
          webDataTable.actions.menu();
        }
      });
      $('#customSearch').on('keyup', function () {
        table.search(this.value).draw();
      });
      webDataTable.loader(table);
      webDataTable.search(table);
      //webDataTable.actions.menu();
    },
    extraActions: function extraActions(row) {
      USER.list.actions.statusChange(row);
      USER.list.actions.view(row);
    },
    actions: {
      statusChange: function statusChange(row) {
        /* $('#row_pending,#row_confirm,#row_blocked,#row_rejected').off().on('click', function () {
             //GLOBAL_FN.ajaxData.sendData(GLOBAL_FN.buildUrl('customer/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')),'datatable',{})
             let fd = new FormData();
             changeCustomerStatus(GLOBAL_FN.buildUrl('customer/' + row.attr('data-id') + '/status/' + $(this).attr('data-value')), {
                 method: 'POST',
                 data: fd,
                 callBack: 'datatable'
             }, $(this).attr('data-value'));
         })*/
      },
      view: function view(row) {
        $('#row_view').off().on('click', function () {
          var customerId = row.attr('data-id');

          // Open drawer

          var drawer = new bootstrap.Offcanvas(document.getElementById('viewDrawer'));
          drawer.show();

          // Load Overview
          $('#customerOverview').html('<p>Loading...</p>');
          $.get('/' + USER.baseUrl + '/' + customerId + '/overview', function (data) {
            $('#customerOverview').html(data);
          });

          // Clear other tabs
          $('#customerInvoices').html('');
          $('#customerTransactions').html('');
        });
      }
    }
  },
  form: {
    open: function open() {
      $('#new').off().on('click', function () {
        webModal.openGlobalModal({
          title: 'Add User',
          url: GLOBAL_FN.buildUrl('masters/user/create'),
          content: null,
          size: 'xl',
          callBack: null
        });
      });
    }
  },
  profile: {
    save: function save() {
      $('#submit').off().on('click', function (e) {
        e.preventDefault();
        var form = $('#profile-form');
        var action = form.attr('action');
        var method = form.attr('method') || 'POST';
        if (form.valid()) {
          // Build form data
          var formData = new FormData(form[0]);
          // const formData = new FormData(this);

          //const submitBtn = form.find('button[type="submit"]');
          //submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Submitting');

          $.ajax({
            url: action,
            type: method,
            data: formData,
            //context: modal,
            processData: false,
            contentType: false,
            success: function success(response) {
              console.log(response);
              if (response.status === 'success') {
                toastr.success(response.message);
                if (callback) callback(response);
              } else {
                if (response.status === 'error') {
                  toastr.error(response.message);
                }
              }
            },
            error: function error(xhr) {
              var errors = xhr.responseJSON.errors;
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
            complete: function complete() {
              submitBtn.prop('disabled', false).html('Submit');
            }
          });
        }
      });
    }
  }
};
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!****************************************!*\
  !*** ./public/js/page-all-js/zatca.js ***!
  \****************************************/
ZATCA = {
  load: function load() {
    var form = $("#form"),
      form2 = $('#simulation-form'),
      form3 = $('#production-form');
    /*selectPicker(form);
    formValidation(form);
    initMaxLength(form);
    formValidation(form2)
    formValidation(form3)*/
    ZATCA.submit();
    ZATCA.formLoad();
    ZATCA.registerClick();
    ZATCA.production();
    ZATCA.edit();
    ZATCA.testRun();
  },
  formLoad: function formLoad() {

    /*$('#register').on('click', function () {
        let switchStatus = '';
        if ($(this).is(':checked')) {
            switchStatus = $(this).is(':checked');
            if (switchStatus === true) {
                $('#register-details').removeClass('d-none');
                $('.save').removeClass('d-none');
            }
        } else {
            $('#register-details').addClass('d-none');
        }
    })*/
  },
  submit: function submit() {
    $("#submit-details").on('click', function () {
      if ($('#form').valid()) {
        MODEL.options({
          type: 'send',
          url: DEFAULT.initUrl('settings/register'),
          //callBack: ZATCA.register,
          callBack: ZATCA.register
        });
      }
    });
  },
  register: function register() {
    /*$('.register').removeClass('d-none');
    $('.edit').removeClass('d-none');
    $('#register-details').addClass('d-none');
    $('#register-detail').removeClass('d-none');
    $('#toggle').addClass('d-none');
    $('.save').addClass('d-none');*/
  },
  edit: function edit() {
    /*$('#edit-details').on('click', function () {
        $('#register-details').removeClass('d-none');
        $('.save').removeClass('d-none');
        $('.register').addClass('d-none');
        $('#register-detail').addClass('d-none');
        $('.edit').addClass('d-none');
    })*/
  },
  testRun: function testRun() {
    $('.test-run').on('click', function () {
      var btnId = $(this).attr('data-type');
      stickLoader('Testing', __('Testing ' + btnId.toUpperCase() + ' invoices...'));
      $('#' + btnId).text('Testing...');
      $('#label-' + btnId).empty();
      MODEL.options({
        type: 'fetch',
        url: DEFAULT.initUrl('settings/test/' + btnId + '/zatca'),
        callBack: ZATCA.testResponse,
        options: {
          data: btnId
        }
      });
    });
  },
  registerClick: function registerClick() {
    $("#submit-simulation-validate,#submit-core-validate").on('click', function () {
      var mode = 'core',
        btnId = $(this).attr('id'),
        form;
      if (btnId === 'submit-simulation-validate') {
        mode = 'simulation';
        form = $('#simulation-form');
      } else {
        form = $('#production-form');
      }
      if ($('#' + mode + '_otp').val() > 0 || mode === 'developer-portal') {
        //stickLoader('Installing', __('Installing Zatca Please wait...'));
        $('#' + btnId).text('Validating...');
        if ($('#simulation-form').valid()) {
          var loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'));
          var simulationFormBtn = document.getElementById('submit-simulation-validate');
          var simulationCardBody = document.querySelector('#simulation-card .card-body');
          var titleElement = document.getElementById('loadingModalLabel');
          var delay = 2000; // 2 seconds

          // Phase 1: Initial State
          titleElement.textContent = 'Validating OTP';

          // Phase 2: Switch to "Verifying..." after 2 seconds
          setTimeout(function () {
            titleElement.textContent = 'Verifying...';

            // Phase 3: Switch to "Installing please wait..." after another 2 seconds
            setTimeout(function () {
              titleElement.textContent = 'Installing please wait...';

              // Note: The text will remain "Installing please wait..."
              // until your actual success or error handling code changes it.
            }, delay); // 2 seconds delay for Phase 3
          }, delay); // 2 seconds delay for Phase 2
          /*MODEL.options({
              type: 'send',
              url: DEFAULT.initUrl('settings/' + mode + '/register/zatca'),
              callBack: ZATCA.response,
              options: {
                  form: 'simulation-form',
              }
          });*/
          //const formData = new FormData(this);
          var formData = new FormData(form[0]);
          $.ajax({
            url: '/settings/zatca/' + mode + '/register/zatca',
            type: 'POST',
            data: formData,
            /*context: modal,*/
            processData: false,
            contentType: false,
            beforeSend: function beforeSend() {
              loadingModal.show();
            },
            success: function success(response) {
              var success = true; // Assume success for this flow

              // 3. Hide the Loader Modal
              loadingModal.hide();
              var successContent = "\n                        <div id=\"simulation-success-message-container\" class=\"text-center p-4 rounded bg-success-subtle\">\n                            <i class=\"bi bi-check-circle-fill display-4 text-success mb-3\"></i>\n                            <h5 class=\"fw-bold text-success\">STAGE 1: ACTIVATED</h5>\n                            <p class=\"small text-success mb-0\">\n                                The Compliance CSID has been successfully issued and registered.\n                                **You may now proceed to Stage 2: Production Activation.**\n                            </p>\n                        </div>\n                    ";

              // Replace the inner content of the simulation card body
              simulationCardBody.innerHTML = "\n                        <span class=\"badge bg-warning text-dark mb-3 fs-6\">STAGE 1: SIMULATION</span>\n                        <h4 class=\"card-title fw-bold text-warning-emphasis mb-3 me-3\">Compliance Certification</h4>\n                        <p class=\"small text-muted mb-4\">\n                            This phase validates your system's technical compatibility with ZATCA's standards. Successful validation generates the **Compliance CSID**.\n                        </p>\n                        ".concat(successContent, "\n                    ");
              if (response.status === 'success') {
                toastr.success(response.message);
              } else {
                if (response.status === 'error') {
                  toastr.error(response.message);
                }
              }
            },
            error: function error(xhr) {
              loadingModal.hide();
              var error = xhr.responseJSON.message;
              if (error) {
                toastr.error(error);
              } else {
                toastr.error("Something went wrong!");
              }
            }
            /*error: function(xhr){
                $('#globalModalBody').html(xhr.responseText);
                setupModalFooter(); // Re-add footer buttons after validation errors
            },*/
            /*complete: function (xhr) {
                console.log("responsemess",xhr.responseTex);
            }*/
          });
        }
      } else {
        alert("Enter valid otp");
        //showToastr('Invalid', 'Enter valid otp', 'error');
      }
    });
  },
  production: function production() {
    $("#submit-core-validate").on('click', function () {
      var mode = 'core',
        btnId = $(this).attr('id');
      if (btnId === 'submit-simulation-validate') {
        mode = 'developer-portal';
      }
      if ($('#' + mode + '_otp').val() > 0 || mode === 'developer-portal') {
        //stickLoader('Installing', __('Installing Zatca Please wait...'));
        $('#' + btnId).text('Validating...');
        if ($('#production-form').valid()) {
          MODEL.options({
            type: 'send',
            url: DEFAULT.initUrl('settings/' + mode + '/register/zatca'),
            callBack: ZATCA.response,
            options: {
              form: 'production-form'
            }
          });
        }
      } else {
        alert("Enter valid otp");
        //showToastr('Invalid', 'Enter valid otp', 'error');
      }
    });
  },
  response: function response(data) {
    if (data.title === "Success") {
      /*$('#submit-simulation-validate,#submit-core-validate').remove();*/
      setTimeout(function () {
        location.reload();
        //pageReload()
      }, 500);
    } else {
      $('#submit-simulation-validate,#submit-core-validate').text('Validate');
    }
  },
  testResponse: function testResponse(data, btnId) {
    $('#' + btnId.data).text('Test Again');
    $('#label-' + btnId.data).html('<i class="fa fa-check-circle"></i>' + ' Sample Invoice ' + data.message);
    showToastr('Success', data.message);
  }
};
})();

/******/ })()
;