@section('page-title', __('Finance Settings'))
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.settings-navigation')
        <section class="flex-grow-1 px-4 d-flex flex-column">
            <div class="card border-0 shadow-sm rounded-3 p-4 mt-4" style="max-width: 1100px;">
                <h5 class="fw-semibold mb-1">{{ __('Finance Settings') }}</h5>
                <p class="small text-muted mb-4">{{ __('How customer invoices treat credit limits, and what emailed statements carry.') }}</p>
                <form id="financeSettingsForm" class="row g-3" novalidate>
                    @csrf
                    <div class="col-lg-5">
                        <label class="form-label fw-medium" for="fs_credit_check">{{ __('Credit limit check') }}</label>
                        <select name="credit_check" id="fs_credit_check" class="tom-select" required>
                            @foreach(\App\Services\Finance\FinanceSettings::CREDIT_CHECKS as $k => $label)
                                <option value="{{ $k }}" @selected($settings['credit_check'] === $k)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">{{ __('Checked when a customer invoice is approved: outstanding balance plus the invoice against the credit limit, and invoices overdue past the credit days.') }}</div>
                    </div>
                    {{-- what the chosen setting does, on a sample customer; the messages are the ones CreditCheck and the approval really give --}}
                    @php
                        $dec = decimals();
                        $ex = ['limit' => 50000, 'out' => 40000, 'amount' => 15000];
                        $exMsg = __(':customer:', ['customer' => __('Sample Customer')]) . ' '
                            . __('Credit limit :limit exceeded: outstanding :out + this invoice :amount = :total.', [
                                'limit' => number_format($ex['limit'], $dec), 'out' => number_format($ex['out'], $dec),
                                'amount' => number_format($ex['amount'], $dec), 'total' => number_format($ex['out'] + $ex['amount'], $dec)])
                            . ' ' . __('Overdue invoices: :list.', ['list' => __(':no (:days days)', ['no' => 'IN/26/0010', 'days' => 12])]);
                    @endphp
                    <div class="col-lg-7">
                        <div class="border rounded-3 p-3 bg-light-subtle h-100" id="fsCreditExample" aria-live="polite">
                            <div class="small fw-semibold text-uppercase text-muted mb-2"><i class="bi bi-lightbulb me-1"></i>{{ __('Example') }}</div>
                            <table class="table table-sm table-borderless small mb-2 w-auto">
                                <tr><td class="text-muted pe-4 py-0">{{ __('Customer') }}</td><td class="py-0 fw-medium">{{ __('Sample Customer') }}</td></tr>
                                <tr><td class="text-muted pe-4 py-0">{{ __('Credit limit') }}</td><td class="py-0 text-end">{{ number_format($ex['limit'], $dec) }}</td></tr>
                                <tr><td class="text-muted pe-4 py-0">{{ __('Outstanding balance') }}</td><td class="py-0 text-end">{{ number_format($ex['out'], $dec) }}</td></tr>
                                <tr><td class="text-muted pe-4 py-0">{{ __('This invoice') }}</td><td class="py-0 text-end">{{ number_format($ex['amount'], $dec) }}</td></tr>
                                <tr class="border-top"><td class="text-muted pe-4 py-0 pt-1">{{ __('Total after approval') }}</td><td class="py-0 pt-1 text-end fw-semibold text-danger">{{ number_format($ex['out'] + $ex['amount'], $dec) }}</td></tr>
                                <tr><td class="text-muted pe-4 py-0">{{ __('Overdue invoice') }}</td><td class="py-0 text-end">IN/26/0010 · {{ __(':days days', ['days' => 12]) }}</td></tr>
                            </table>
                            <div class="small fw-semibold mb-1">{{ __('When this invoice is approved:') }}</div>
                            <div class="fs-example" data-mode="off">
                                <div class="small text-success"><i class="bi bi-check-circle me-1"></i>{{ __('Nothing is checked. The invoice is approved, even over the limit and with overdue invoices.') }}</div>
                            </div>
                            <div class="fs-example" data-mode="warn">
                                <div class="alert alert-warning small py-2 px-3 mb-1">{{ $exMsg }}</div>
                                <div class="small text-muted">{{ __('The invoice is still approved: the approver only sees this warning.') }}</div>
                            </div>
                            <div class="fs-example" data-mode="block">
                                <div class="alert alert-danger small py-2 px-3 mb-1">{{ $exMsg }} {{ __('Request a higher limit on Finances › Credit Approval, or ask a user with the Credit override right.') }}</div>
                                <div class="small text-muted">{{ __('The invoice stays in draft. A user with the Credit Limit Override right (and the Super User) can still approve it.') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <label class="form-label fw-medium" for="fs_statement_attachment">{{ __('Statement email attachment') }}</label>
                        <select name="statement_attachment" id="fs_statement_attachment" class="tom-select" required>
                            @foreach(\App\Services\Finance\FinanceSettings::STATEMENT_ATTACHMENTS as $k => $label)
                                <option value="{{ $k }}" @selected($settings['statement_attachment'] === $k)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">{{ __('What is attached when a customer or supplier statement is sent by email.') }}</div>
                    </div>
                    <div class="col-lg-5">
                        <label class="form-label fw-medium" for="fs_cost_sheet">{{ __('Cost sheet') }}</label>
                        <select name="cost_sheet" id="fs_cost_sheet" class="tom-select" required data-saved="{{ $settings['cost_sheet'] }}">
                            @foreach(\App\Services\Finance\FinanceSettings::COST_SHEET as $k => $label)
                                <option value="{{ $k }}" @selected($settings['cost_sheet'] === $k)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">{{ __('On: each job has a cost sheet with the expected sale and cost per charge, and the customer invoice is created from it. The cost sheet is locked once invoiced; a full credit note opens it again.') }}</div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary rounded-pill px-4" id="financeSettingsSave">{{ __('Save Settings') }}</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            initTomSelectForm($('#financeSettingsForm'));
            // the example on the right follows the chosen credit check
            const showExample = () => {
                const mode = $('#fs_credit_check').val();
                $('#fsCreditExample .fs-example').each(function () { $(this).toggle($(this).data('mode') === mode); });
            };
            $('#fs_credit_check').on('change', showExample);
            showExample();
            $('#financeSettingsForm').on('submit', function (e) {
                e.preventDefault();
                const $f = $(this);
                // switching the cost sheet off: say what happens before saving
                const cs = $('#fs_cost_sheet');
                if (cs.data('saved') === 'on' && cs.val() === 'off'
                    && !confirm(trans('Cost sheets will be hidden. Existing ones are kept and nothing in your accounts changes. Invoices made from a cost sheet keep their lines locked.'))) return;
                $f.find('.is-invalid').removeClass('is-invalid');
                $f.find('.invalid-feedback').remove();
                $('#financeSettingsSave').prop('disabled', true);
                $.post(GLOBAL_FN.buildUrl('settings/finance'), $f.serialize())
                    .done(res => { toastr.success(res.message); cs.data('saved', cs.val()); })
                    .fail(function (xhr) {
                        const errors = (xhr.responseJSON || {}).errors || {};
                        Object.entries(errors).forEach(([name, msgs]) => {
                            const $i = $f.find('[name="' + name + '"]').addClass('is-invalid');
                            $i.after('<div class="invalid-feedback d-block">' + $('<span>').text(msgs[0]).html() + '</div>');
                        });
                        toastr.error((xhr.responseJSON || {}).message || trans('Something went wrong.'));
                    })
                    .always(() => $('#financeSettingsSave').prop('disabled', false));
            });
        });
    </script>
</x-app-layout>
