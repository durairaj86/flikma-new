@section('page-title', __('CRM Settings'))
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.settings-navigation')
        <section class="flex-grow-1 px-4 d-flex flex-column">
            <div class="card border-0 shadow-sm rounded-3 p-4 mt-4" style="max-width: 860px;">
                <h5 class="fw-semibold mb-1">{{ __('CRM Settings') }}</h5>
                <p class="small text-muted mb-4">{{ __('Used by the Customer Tracker, the CRM Dashboard and the Activity Summary.') }}</p>
                <form id="crmSettingsForm" class="row g-3" novalidate>
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label fw-medium">{{ __('Week starts on') }}</label>
                        <select name="week_start" class="tom-select" required>
                            @foreach(\App\Services\Crm\CrmSettings::WEEK_STARTS as $k => $label)
                                <option value="{{ $k }}" @selected($settings['week_start'] === $k)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">{{ __('For "This week" and the weekly summary.') }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-medium">{{ __('Stale customer after (days)') }}</label>
                        <input type="text" name="stale_days" class="form-control integer" data-max-int="3" required value="{{ $settings['stale_days'] }}">
                        <div class="form-text">{{ __('No contact for this many days turns "Days since" red.') }}</div>
                    </div>

                    <div class="col-12"><div class="border-top pt-3 fw-semibold small text-muted">{{ __('Score: suggested rank') }}</div></div>
                    @foreach([['points_touch', __('Points per outreach touch'), __('Each call, mail, WhatsApp or meeting logged')],
                              ['points_rfq', __('Points per RFQ received'), __('Each enquiry')],
                              ['points_job', __('Points per job confirmed'), __('Each job')]] as [$name, $label, $help])
                        <div class="col-md-4">
                            <label class="form-label fw-medium">{{ $label }}</label>
                            <input type="text" name="{{ $name }}" class="form-control integer" data-max-int="4" required value="{{ $settings[$name] }}">
                            <div class="form-text">{{ $help }}</div>
                        </div>
                    @endforeach
                    @foreach([['rank_a', 'A'], ['rank_b', 'B'], ['rank_c', 'C']] as [$name, $rank])
                        <div class="col-md-4">
                            <label class="form-label fw-medium">{{ __('Rank :r from score', ['r' => $rank]) }}</label>
                            <input type="text" name="{{ $name }}" class="form-control integer" data-max-int="4" required value="{{ $settings[$name] }}">
                        </div>
                    @endforeach
                    <div class="col-12 small text-muted">{{ __('Below the Rank C limit the suggested rank is D.') }}</div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary rounded-pill px-4" id="crmSettingsSave">{{ __('Save Settings') }}</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            initTomSelectForm($('#crmSettingsForm'));
            $('#crmSettingsForm').on('submit', function (e) {
                e.preventDefault();
                const $f = $(this);
                $f.find('.is-invalid').removeClass('is-invalid');
                $f.find('.invalid-feedback').remove();
                $('#crmSettingsSave').prop('disabled', true);
                $.post(GLOBAL_FN.buildUrl('settings/crm'), $f.serialize())
                    .done(res => toastr.success(res.message))
                    .fail(function (xhr) {
                        const errors = (xhr.responseJSON || {}).errors || {};
                        Object.entries(errors).forEach(([name, msgs]) => {
                            const $i = $f.find('[name="' + name + '"]').addClass('is-invalid');
                            $i.after('<div class="invalid-feedback d-block">' + $('<span>').text(msgs[0]).html() + '</div>');
                        });
                        toastr.error((xhr.responseJSON || {}).message || trans('Something went wrong.'));
                    })
                    .always(() => $('#crmSettingsSave').prop('disabled', false));
            });
        });
    </script>
</x-app-layout>
