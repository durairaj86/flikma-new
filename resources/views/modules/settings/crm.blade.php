@section('page-title', __('CRM Settings'))
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.settings-navigation')
        <section class="flex-grow-1 px-4 d-flex flex-column">
            <div class="card border-0 shadow-sm rounded-3 p-4 mt-4" style="max-width: 1180px;">
                <h5 class="fw-semibold mb-1">{{ __('CRM Settings') }}</h5>
                <p class="small text-muted mb-4">{{ __('Used by the Customer Tracker, the CRM Dashboard and the Activity Summary.') }}</p>
                <div class="row g-4">
                <div class="col-xl-7">
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
                <div class="col-xl-5">
                    <div class="border rounded-3 p-3 bg-light position-sticky" style="top: 1rem;" id="crmExample" aria-live="polite">
                        <div class="fw-semibold mb-1">{{ __('Example') }}</div>
                        <p class="small text-muted mb-3">{{ __('A sample customer. It changes as you change the settings on the left; nothing is saved until you press Save.') }}</p>
                        <table class="table table-sm table-borderless small mb-3">
                            <tr><td class="text-muted">{{ __('Outreach touches') }}</td><td class="text-end">6 &times; <span data-ex="points_touch"></span> = <b data-ex="s_touch"></b></td></tr>
                            <tr><td class="text-muted">{{ __('RFQs received') }}</td><td class="text-end">2 &times; <span data-ex="points_rfq"></span> = <b data-ex="s_rfq"></b></td></tr>
                            <tr><td class="text-muted">{{ __('Jobs confirmed') }}</td><td class="text-end">1 &times; <span data-ex="points_job"></span> = <b data-ex="s_job"></b></td></tr>
                            <tr class="border-top"><td class="fw-semibold">{{ __('Score') }}</td><td class="text-end fw-semibold" data-ex="score"></td></tr>
                        </table>
                        <div class="small mb-3">{{ __('Suggested rank') }}: <span class="badge text-bg-primary fs-6" data-ex="rank"></span>
                            <span class="text-muted ms-1" data-ex="rank_why"></span></div>
                        <div class="small mb-3">{{ __('Last contact 10 days ago') }}:
                            <span data-ex="stale_ok" class="text-success">{{ __('Days since') }} 10 - {{ __('not stale') }}</span>
                            <span data-ex="stale_bad" class="text-danger fw-semibold">{{ __('Days since') }} 10 - {{ __('shown in red (stale)') }}</span>
                        </div>
                        <div class="small">{{ __('"This week" runs') }}: <b data-ex="week"></b></div>
                    </div>
                </div>
                </div>
            </div>
        </section>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            initTomSelectForm($('#crmSettingsForm'));
            const dayNames = {sunday: trans('Sunday'), monday: trans('Monday'), saturday: trans('Saturday')};
            const endDay = {sunday: 'saturday', monday: 'sunday', saturday: 'friday'};
            const val = n => parseInt($('#crmSettingsForm [name="' + n + '"]').val(), 10) || 0;
            const showExample = () => {
                const set = (k, v) => $('#crmExample [data-ex="' + k + '"]').text(v);
                const pt = val('points_touch'), pr = val('points_rfq'), pj = val('points_job');
                const score = 6 * pt + 2 * pr + pj, a = val('rank_a'), b = val('rank_b'), c = val('rank_c');
                const rank = score >= a ? 'A' : score >= b ? 'B' : score >= c ? 'C' : 'D';
                const from = {A: a, B: b, C: c}[rank];
                set('points_touch', pt); set('points_rfq', pr); set('points_job', pj);
                set('s_touch', 6 * pt); set('s_rfq', 2 * pr); set('s_job', pj); set('score', score);
                set('rank', rank);
                set('rank_why', rank === 'D' ? trans('score is below :n').replace(':n', c) : trans('score :s reaches :n').replace(':s', score).replace(':n', from));
                const stale = 10 >= val('stale_days');
                $('#crmExample [data-ex="stale_bad"]').toggle(stale);
                $('#crmExample [data-ex="stale_ok"]').toggle(!stale);
                const ws = $('#crmSettingsForm [name="week_start"]').val() || 'sunday';
                set('week', (dayNames[ws] || ws) + ' ' + trans('to') + ' ' + (dayNames[endDay[ws]] || endDay[ws]));
            };
            $('#crmSettingsForm').on('input change', 'input, select', showExample);
            showExample();
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
