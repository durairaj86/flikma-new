@section('page-title', __('Finance Settings'))
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.settings-navigation')
        <section class="flex-grow-1 px-4 d-flex flex-column">
            <div class="card border-0 shadow-sm rounded-3 p-4 mt-4" style="max-width: 860px;">
                <h5 class="fw-semibold mb-1">{{ __('Finance Settings') }}</h5>
                <p class="small text-muted mb-4">{{ __('How customer invoices treat credit limits, and what emailed statements carry.') }}</p>
                <form id="financeSettingsForm" class="row g-3" novalidate>
                    @csrf
                    <div class="col-md-8">
                        <label class="form-label fw-medium" for="fs_credit_check">{{ __('Credit limit check') }}</label>
                        <select name="credit_check" id="fs_credit_check" class="tom-select" required>
                            @foreach(\App\Services\Finance\FinanceSettings::CREDIT_CHECKS as $k => $label)
                                <option value="{{ $k }}" @selected($settings['credit_check'] === $k)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">{{ __('Checked when a customer invoice is approved: outstanding balance plus the invoice against the credit limit, and invoices overdue past the credit days.') }}</div>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-medium" for="fs_statement_attachment">{{ __('Statement email attachment') }}</label>
                        <select name="statement_attachment" id="fs_statement_attachment" class="tom-select" required>
                            @foreach(\App\Services\Finance\FinanceSettings::STATEMENT_ATTACHMENTS as $k => $label)
                                <option value="{{ $k }}" @selected($settings['statement_attachment'] === $k)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">{{ __('What is attached when a customer or supplier statement is sent by email.') }}</div>
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
            $('#financeSettingsForm').on('submit', function (e) {
                e.preventDefault();
                const $f = $(this);
                $f.find('.is-invalid').removeClass('is-invalid');
                $f.find('.invalid-feedback').remove();
                $('#financeSettingsSave').prop('disabled', true);
                $.post(GLOBAL_FN.buildUrl('settings/finance'), $f.serialize())
                    .done(res => toastr.success(res.message))
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
