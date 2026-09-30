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
                                    <th>{{ __('Account') }}</th>
                                    <th>{{ __('Entity Type') }}</th>
                                    <th>{{ __('Entity') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Debit') }}</th>
                                    <th>{{ __('Credit') }}</th>
                                    <th>{{ __('Tax Account') }}</th>
                                    <th>{{ __('Tax Amount') }}</th>
                                    <th width="50">{{ __('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody id="entries-body">
                                @if($journalVoucher->id)
                                    @foreach($journalVoucher->journalVoucherItems as $index => $item)
                                        <tr class="entry-row">
                                            <td>
                                                <select class="form-select account-select" name="account_ids[]" required>
                                                    <option value="">{{ __('Select Account') }}</option>
                                                    @foreach($accounts as $account)
                                                        <option value="{{ $account->id }}" @selected($item->account_id == $account->id)>
                                                            {{ $account->code }} - {{ $account->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-select entity-type-select" name="entity_types[]">
                                                    <option value="">{{ __('None') }}</option>
                                                    <option value="customer" @selected($item->entity_type == 'customer')>{{ __('Customer') }}</option>
                                                    <option value="supplier" @selected($item->entity_type == 'supplier')>{{ __('Supplier') }}</option>
                                                    <option value="job" @selected($item->entity_type == 'job')>{{ __('Job') }}</option>
                                                    <option value="tax" @selected($item->entity_type == 'tax')>{{ __('Tax') }}</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-select entity-select" name="entity_ids[]" data-entity-type="{{ $item->entity_type }}">
                                                    <option value="">{{ __('Select Entity') }}</option>
                                                    @if($item->entity_type == 'customer')
                                                        @foreach($customers as $customer)
                                                            <option value="{{ $customer->id }}" @selected($item->entity_id == $customer->id)>
                                                                {{ $customer->name }}
                                                            </option>
                                                        @endforeach
                                                    @elseif($item->entity_type == 'supplier')
                                                        @foreach($suppliers as $supplier)
                                                            <option value="{{ $supplier->id }}" @selected($item->entity_id == $supplier->id)>
                                                                {{ $supplier->name }}
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
                                                <input type="number" step="0.01" class="form-control debit-amount" name="debit_amounts[]" value="{{ $item->debit_amount }}" min="0">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control credit-amount" name="credit_amounts[]" value="{{ $item->credit_amount }}" min="0">
                                            </td>
                                            <td>
                                                <select class="form-select tax-account-select" name="tax_ids[]">
                                                    <option value="">{{ __('Select Tax Account') }}</option>
                                                    @foreach($accounts as $account)
                                                        <option value="{{ $account->id }}" @selected($item->tax_id == $account->id)>
                                                            {{ $account->code }} - {{ $account->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control tax-amount" name="tax_amounts[]" value="{{ $item->tax_amount }}" min="0">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger remove-entry-row">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr class="entry-row">
                                        <td>
                                            <select class="form-select account-select" name="account_ids[]" required>
                                                <option value="">{{ __('Select Account') }}</option>
                                                @foreach($accounts as $account)
                                                    <option value="{{ $account->id }}">
                                                        {{ $account->code }} - {{ $account->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-select entity-type-select" name="entity_types[]">
                                                <option value="">{{ __('None') }}</option>
                                                <option value="customer">{{ __('Customer') }}</option>
                                                <option value="supplier">{{ __('Supplier') }}</option>
                                                <option value="job">{{ __('Job') }}</option>
                                                <option value="tax">{{ __('Tax') }}</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-select entity-select" name="entity_ids[]" data-entity-type="">
                                                <option value="">{{ __('Select Entity') }}</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="descriptions[]">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="form-control debit-amount" name="debit_amounts[]" value="0" min="0">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="form-control credit-amount" name="credit_amounts[]" value="0" min="0">
                                        </td>
                                        <td>
                                            <select class="form-select tax-account-select" name="tax_ids[]">
                                                <option value="">{{ __('Select Tax Account') }}</option>
                                                @foreach($accounts as $account)
                                                    <option value="{{ $account->id }}">
                                                        {{ $account->code }} - {{ $account->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="form-control tax-amount" name="tax_amounts[]" value="0" min="0">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-entry-row">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
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

<script>
    $(document).ready(function() {
        // Initialize select2 for selects
        $('.account-select').select2({
            placeholder: "{{ __('Select Account') }}",
            width: '100%'
        });

        $('.entity-select').select2({
            placeholder: "{{ __('Select Entity') }}",
            width: '100%'
        });

        $('.tax-account-select').select2({
            placeholder: "{{ __('Select Tax Account') }}",
            width: '100%'
        });

        // Initialize entity type change handlers
        $('.entity-type-select').each(function() {
            $(this).trigger('change');
        });

        // Add new entry row
        $('#add-entry-row').on('click', function() {
            const newRow = `
                <tr class="entry-row">
                    <td>
                        <select class="form-select account-select" name="account_ids[]" required>
                            <option value="">{{ __('Select Account') }}</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <select class="form-select entity-type-select" name="entity_types[]">
                            <option value="">{{ __('None') }}</option>
                            <option value="customer">{{ __('Customer') }}</option>
                            <option value="supplier">{{ __('Supplier') }}</option>
                            <option value="job">{{ __('Job') }}</option>
                            <option value="tax">{{ __('Tax') }}</option>
                        </select>
                    </td>
                    <td>
                        <select class="form-select entity-select" name="entity_ids[]" data-entity-type="">
                            <option value="">{{ __('Select Entity') }}</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control" name="descriptions[]">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control debit-amount" name="debit_amounts[]" value="0" min="0">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control credit-amount" name="credit_amounts[]" value="0" min="0">
                    </td>
                    <td>
                        <select class="form-select tax-account-select" name="tax_ids[]">
                            <option value="">{{ __('Select Tax Account') }}</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">
                                    {{ $account->code }} - {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control tax-amount" name="tax_amounts[]" value="0" min="0">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-entry-row">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#entries-body').append(newRow);

            // Initialize select2 for the new row
            $('#entries-body tr:last-child .account-select').select2({
                placeholder: "{{ __('Select Account') }}",
                width: '100%'
            });

            $('#entries-body tr:last-child .entity-select').select2({
                placeholder: "{{ __('Select Entity') }}",
                width: '100%'
            });

            $('#entries-body tr:last-child .tax-account-select').select2({
                placeholder: "{{ __('Select Tax Account') }}",
                width: '100%'
            });

            // Attach event handlers to the new row
            attachEventHandlers();
        });

        // Remove entry row
        $(document).on('click', '.remove-entry-row', function() {
            if ($('#entries-body tr').length > 1) {
                $(this).closest('tr').remove();
                calculateTotals();
            } else {
                toastr.error("{{ __('At least one entry is required') }}");
            }
        });

        // Calculate totals when amounts change and handle entity type changes
        function attachEventHandlers() {
            $('.debit-amount, .credit-amount, .tax-amount').off('input').on('input', function() {
                calculateTotals();
            });

            // Handle entity type changes
            $('.entity-type-select').off('change').on('change', function() {
                const entityType = $(this).val();
                const entitySelect = $(this).closest('tr').find('.entity-select');

                // Clear current options and add placeholder
                entitySelect.empty().append('<option value="">{{ __('Select Entity') }}</option>');
                entitySelect.attr('data-entity-type', entityType);

                // Populate based on entity type
                if (entityType === 'customer') {
                    @foreach($customers as $customer)
                        entitySelect.append(`<option value="{{ $customer->id }}">{{ $customer->name }}</option>`);
                    @endforeach
                } else if (entityType === 'supplier') {
                    @foreach($suppliers as $supplier)
                        entitySelect.append(`<option value="{{ $supplier->id }}">{{ $supplier->name }}</option>`);
                    @endforeach
                } else if (entityType === 'job') {
                    @foreach($jobs as $job)
                        entitySelect.append(`<option value="{{ $job->id }}">{{ $job->row_no }}</option>`);
                    @endforeach
                }

                // Refresh select2
                entitySelect.select2({
                    placeholder: "{{ __('Select Entity') }}",
                    width: '100%'
                });
            });
        }

        // Calculate totals
        function calculateTotals() {
            let totalDebit = 0;
            let totalCredit = 0;
            let totalTax = 0;

            $('.debit-amount').each(function() {
                totalDebit += parseFloat($(this).val() || 0);
            });

            $('.credit-amount').each(function() {
                totalCredit += parseFloat($(this).val() || 0);
            });

            $('.tax-amount').each(function() {
                totalTax += parseFloat($(this).val() || 0);
            });

            $('#total-debit').text(formatNumber(totalDebit));
            $('#total-credit').text(formatNumber(totalCredit));

            // Update total tax
            $('#total-tax').text(formatNumber(totalTax));

            const balance = Math.abs(totalDebit - totalCredit);
            $('#balance').text(formatNumber(balance));

            if (balance > 0.01) {
                $('#balance-row').removeClass('table-success').addClass('table-danger');
            } else {
                $('#balance-row').removeClass('table-danger').addClass('table-success');
            }
        }

        // Format number with commas
        function formatNumber(num) {
            return parseFloat(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        // Initial setup
        attachEventHandlers();
    });
</script>
