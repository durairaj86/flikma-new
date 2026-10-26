<div class="row g-3 align-items-center border-bottom py-3 px-4 mb-1 small" style="background:#eee;">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-4">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $journalVoucher->row_no ?? __('New Journal Voucher') }}</span>
            </div>
        </div>

        <!-- Save & Next Button -->
        <div id="show-buttons"></div>
    </div>
</div>

<div class="container-fluid align-items-center px-0" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Journal Voucher') }}">

    <form id="moduleForm" novalidate action="{{ route('finance.journal_vouchers.store') }}">
        @csrf
        <input type="hidden" name="journal_voucher_id" id="journal_voucher_id" value="{{ $journalVoucher->id }}">
        <div class="mb-4 mt-3 border-0 px-4 error-border-off">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="voucher_type" class="form-label required">{{ __('Voucher Type') }} <span
                                    class="text-danger">*</span></label>
                            <select class="tom-select" id="voucher_type" name="voucher_type" required>
                                <option value="">{{ __('Select Voucher Type') }}</option>
                                @foreach($voucherTypes as $type)
                                    <option
                                        value="{{ $type->value }}" @selected($journalVoucher->voucher_type == $type->value)>
                                        {{ $type->label() }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="job_id" class="form-label">{{ __('Job') }}</label>
                            <select class="tom-select" id="job_id" name="job_id">
                                <option value="">{{ __('Select Job') }}</option>
                                @foreach($jobs as $job)
                                    <option
                                        value="{{ $job->id }}" @selected($journalVoucher->job_id == $job->id)>
                                        {{ $job->row_no }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="voucher_date" class="form-label required">{{ __('Voucher Date') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control datepicker" id="voucher_date" name="voucher_date"
                                   value="{{ $journalVoucher->voucher_date ? \Carbon\Carbon::parse($journalVoucher->voucher_date)->format('Y-m-d') : date('Y-m-d') }}"
                                   required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="reference_no" class="form-label">{{ __('Reference No') }}</label>
                            <input type="text" class="form-control" id="reference_no" name="reference_no"
                                   value="{{ $journalVoucher->reference_no }}"
                                   placeholder="{{ __('Reference Number') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="currency" class="form-label required">{{ __('Currency') }} <span
                                    class="text-danger">*</span></label>
                            <x-common.currencies-exchange :value="$journalVoucher->currency"
                                                          exchangeRate="{{ $journalVoucher->currency_rate }}"
                                                          width="auto"></x-common.currencies-exchange>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control" id="notes" name="notes"
                                      rows="1">{{ $journalVoucher->notes }}</textarea>
                        </div>
                    </div>
                </div>

                @if($journalVoucher->status == 3)
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-danger">
                                <strong>{{ __('Disapproval Reason') }}:</strong> {{ $journalVoucher->disapproval_reason }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="border-0 mb-4 px-4">
            <div class="card-body p-0">
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4>{{ __('Journal Entries') }}</h4>
                            <button type="button" class="btn btn-sm btn-primary" id="add-entry-row">
                                <i class="bi bi-plus-circle me-1"></i> {{ __('Add Entry') }}
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="entries-table">
                                <thead>
                                <tr>
                                    <th style="min-width:240px">{{ __('Account') }}</th>
                                    <th style="min-width:140px">{{ __('Entity Type') }}</th>
                                    <th style="min-width:200px">{{ __('Entity') }}</th>
                                    <th style="min-width:200px">{{ __('Description') }}</th>
                                    <th style="min-width:130px">{{ __('Debit') }}</th>
                                    <th style="min-width:130px">{{ __('Credit') }}</th>
                                    <th style="min-width:220px">{{ __('Tax Account') }}</th>
                                    <th style="min-width:120px">{{ __('Tax Amount') }}</th>
                                    <th width="50">{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody id="entries-body">
                                @if($journalVoucher->id)
                                    @foreach($journalVoucher->journalVoucherItems as $index => $item)
                                        <tr class="entry-row">
                                            <td>
                                                <select class="tom-select account-select" data-live-search="true" data-dropdown-parent="body" name="account_ids[]" required>
                                                    <option value="">{{ __('Select Account') }}</option>
                                                    @foreach($accounts as $account)
                                                        <option value="{{ $account->id }}" @selected($item->account_id == $account->id)>
                                                            {{ $account->code }} - {{ $account->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select class="tom-select entity-type-select" data-dropdown-parent="body" name="entity_types[]">
                                                    <option value="">{{ __('None') }}</option>
                                                    <option value="customer" @selected($item->entity_type == 'customer')>{{ __('Customer') }}</option>
                                                    <option value="supplier" @selected($item->entity_type == 'supplier')>{{ __('Supplier') }}</option>
                                                    <option value="job" @selected($item->entity_type == 'job')>{{ __('Job') }}</option>
                                                    <option value="tax" @selected($item->entity_type == 'tax')>{{ __('Tax') }}</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select class="tom-select entity-select" data-live-search="true" data-dropdown-parent="body" name="entity_ids[]" data-entity-type="{{ $item->entity_type }}">
                                                    <option value="">{{ __('Select Entity') }}</option>
                                                    @if($item->entity_type == 'customer')
                                                        @foreach($customers as $customer)
                                                            <option value="{{ $customer->id }}" @selected($item->entity_id == $customer->id)>
                                                                {{ $customer->name_en }}
                                                            </option>
                                                        @endforeach
                                                    @elseif($item->entity_type == 'supplier')
                                                        @foreach($suppliers as $supplier)
                                                            <option value="{{ $supplier->id }}" @selected($item->entity_id == $supplier->id)>
                                                                {{ $supplier->name_en }}
                                                            </option>
                                                        @endforeach
                                                    @elseif($item->entity_type == 'job')
                                                        @foreach($jobs as $job)
                                                            <option value="{{ $job->id }}" @selected($item->entity_id == $job->id)>
                                                                {{ $job->row_no }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="descriptions[]" value="{{ $item->description }}">
                                            </td>
                                            <td>
                                                <input type="text" step="0.01" class="decimal form-control debit-amount" name="debit_amounts[]" value="{{ $item->debit_amount }}" min="0">
                                            </td>
                                            <td>
                                                <input type="text" step="0.01" class="decimal form-control credit-amount" name="credit_amounts[]" value="{{ $item->credit_amount }}" min="0">
                                            </td>
                                            <td>
                                                <select class="tom-select tax-account-select" data-live-search="true" data-dropdown-parent="body" name="tax_ids[]">
                                                    <option value="">{{ __('Select Tax Account') }}</option>
                                                    @foreach($accounts as $account)
                                                        <option value="{{ $account->id }}" @selected($item->tax_id == $account->id)>
                                                            {{ $account->code }} - {{ $account->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" step="0.01" class="decimal form-control tax-amount" name="tax_amounts[]" value="{{ $item->tax_amount }}" min="0">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger remove-entry-row">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    @include('modules.finance.journal_voucher._entry-row')
                                @endif
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th colspan="4" class="text-end">{{ __('Total') }}:</th>
                                    <th id="total-debit">{{ number_format($journalVoucher->debit_total, 2) }}</th>
                                    <th id="total-credit">{{ number_format($journalVoucher->credit_total, 2) }}</th>
                                    <th class="text-end">{{ __('Total Tax') }}:</th>
                                    <th id="total-tax">{{ number_format($journalVoucher->journalVoucherItems->sum('tax_amount') ?? 0, 2) }}</th>
                                    <th></th>
                                </tr>
                                <tr id="balance-row" class="{{ $journalVoucher->debit_total != $journalVoucher->credit_total ? 'table-danger' : 'table-success' }}">
                                    <th colspan="4" class="text-end">{{ __('Balance') }}:</th>
                                    <th colspan="4" id="balance">{{ number_format(abs($journalVoucher->debit_total - $journalVoucher->credit_total), 2) }}</th>
                                    <th></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<template id="entry-row-template">
    @include('modules.finance.journal_voucher._entry-row')
</template>

<script>
    (function () {
        const $body = $('#entries-body');

        // the account / entity lists open on <body> (a table box would clip them); Bootstrap's modal focus trap would
        // pull focus back out of their search box and close them, so the trap is off while this form is open
        const modalEl = document.getElementById('globalModal');
        const releaseFocusTrap = () => bootstrap.Modal.getInstance(modalEl)?._focustrap?.deactivate();
        releaseFocusTrap();
        $(modalEl).one('shown.bs.modal', releaseFocusTrap);
        setTimeout(releaseFocusTrap, 400);
        const entities = {
            customer: @json($customers->map(fn ($c) => ['id' => $c->id, 'name' => $c->name_en])->values()),
            supplier: @json($suppliers->map(fn ($s) => ['id' => $s->id, 'name' => $s->name_en])->values()),
            job: @json($jobs->map(fn ($j) => ['id' => $j->id, 'name' => $j->row_no])->values()),
        };
        // .decimal boxes return the clean number from .val(); guard anyway
        const num = (el) => parseFloat(String($(el).val() || '0').replace(/,/g, '')) || 0;
        const fmt = (n) => n.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');

        function calculateTotals() {
            let debit = 0, credit = 0, tax = 0;
            $body.find('.debit-amount').each(function () { debit += num(this); });
            $body.find('.credit-amount').each(function () { credit += num(this); });
            $body.find('.tax-amount').each(function () { tax += num(this); });
            const balance = Math.abs(debit - credit);
            $('#total-debit').text(fmt(debit));
            $('#total-credit').text(fmt(credit));
            $('#total-tax').text(fmt(tax));
            $('#balance').text(fmt(balance));
            $('#balance-row').toggleClass('table-danger', balance > 0.01).toggleClass('table-success', balance <= 0.01);
        }

        // the Entity list only has something to offer for customer / supplier / job: otherwise the box is locked and says so.
        // (locked, not disabled, so the entity_ids[] array still lines up with the other columns)
        function syncEntityBox(row) {
            const type = $(row).find('.entity-type-select').val();
            const ts = $(row).find('.entity-select')[0]?.tomselect;
            if (!ts) return;
            const usable = !!entities[type];
            ts.settings.placeholder = usable ? @json(__('Select Entity')) : @json(__('Select entity type first'));
            ts.inputState();
            usable ? ts.unlock() : ts.lock();
        }

        $('#add-entry-row').on('click', function () {
            const $row = $($('#entry-row-template').html().trim());
            $body.append($row);
            initTomSelectForm($row);
            syncEntityBox($row);
            $row.find('.account-select')[0]?.tomselect?.focus();
        });

        $body.on('click', '.remove-entry-row', function () {
            if ($body.find('tr.entry-row').length > 1) {
                $(this).closest('tr').remove();
                calculateTotals();
            } else {
                toastr.error(@json(__('At least one entry is required')));
            }
        });

        setTimeout(() => $body.find('tr.entry-row').each(function () { syncEntityBox(this); }), 100);

        $body.on('input change', '.debit-amount, .credit-amount, .tax-amount', calculateTotals);

        // entity list follows the entity type
        $body.on('change', '.entity-type-select', function () {
            const select = $(this).closest('tr').find('.entity-select')[0];
            if (select.tomselect) select.tomselect.destroy();
            $(select).empty().append($('<option>', {value: '', text: entities[this.value] ? @json(__('Select Entity')) : @json(__('Select entity type first'))}));
            (entities[this.value] || []).forEach(e => $(select).append($('<option>', {value: e.id, text: e.name})));
            select.setAttribute('data-entity-type', this.value);
            initTomSelectForm(null, false, select);
            syncEntityBox($(select).closest('tr'));
        });

    })();
</script>
