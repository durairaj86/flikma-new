@php
    $types = \App\Models\Crm\CrmActivity::TYPES;
    $subjects = array_keys(\App\Models\Crm\CrmActivity::SUBJECTS);
@endphp
<div class="container px-4 py-3 align-items-center" id="modal-buttons" data-buttons="cancel,save"
     data-button-save="{{ __('Save Activity') }}">
    <div class="row g-3 align-items-center bg-white border-bottom py-2 mb-3 small">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div class="module-info">
                <span class="fw-semibold fs-5">{{ $activity->id ? __('Edit Activity') : __('Log Activity') }}</span>
                @if($lockedSubject)
                    <span class="text-muted ms-2">{{ $lockedSubject['kind'] }} · {{ $lockedSubject['no'] }} {{ $lockedSubject['name'] }}</span>
                @endif
            </div>
            <div id="show-buttons"></div>
        </div>
    </div>
    <div class="row">
        <form id="moduleForm" novalidate action="{{ request()->url() }}">
            @csrf
            <input type="hidden" name="data-id" value="{{ $activity->id ?? '' }}">
            <div class="model-form-tab-div">
                <div class="row g-3">
                    @if($lockedSubject)
                        <input type="hidden" name="subject_type" value="{{ $activity->subject_type }}">
                        <input type="hidden" name="subject_id" value="{{ $activity->subject_id }}">
                    @else
                        <div class="col-4 form-group">
                            <label class="form-label required">{{ __('With') }} <sup class="text-danger">*</sup></label>
                            <select name="subject_type" id="crm_subject_type" class="tom-select" required>
                                @foreach($subjects as $s)
                                    <option value="{{ $s }}" @selected(($activity->subject_type ?: 'prospect') === $s)>{{ \App\Models\Crm\CrmActivity::subjectLabel($s) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-8 form-group">
                            <label class="form-label required">&nbsp;</label>
                            <select name="subject_id" id="crm_subject_id" class="tom-select" required data-live-search="true" data-placeholder="{{ __('Search...') }}">
                                <option value="">{{ __('Search...') }}</option>
                                @foreach($subjectOptions as $sid => $s)
                                    <option value="{{ $sid }}" selected>{{ trim(($s['no'] ?? '') . ' - ' . ($s['name'] ?? ''), ' -') }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-4 form-group">
                        <label class="form-label required">{{ __('Activity') }} <sup class="text-danger">*</sup></label>
                        <select name="type" class="tom-select" required>
                            @foreach($types as $key => $t)
                                <option value="{{ $key }}" @selected($activity->type === $key)>{{ __($t['label']) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4 form-group">
                        <label class="form-label required">{{ __('Date') }} <sup class="text-danger">*</sup></label>
                        <input type="text" name="activity_date" class="form-control datepicker" required
                               data-min-date="01-01-2020" data-max-date="31-12-2099"
                               value="{{ $activity->activity_at?->format('d-m-Y') }}">
                    </div>
                    <div class="col-4 form-group">
                        <label class="form-label">{{ __('Time') }}</label>
                        <input type="text" name="activity_time" class="form-control timepicker" placeholder="hh:mm"
                               value="{{ $activity->activity_at?->format('H:i') }}">
                    </div>
                    <div class="col-12 form-group">
                        <label class="form-label required">{{ __('What happened') }} <sup class="text-danger">*</sup></label>
                        <textarea name="notes" class="form-control" rows="3" required maxlength="5000"
                                  placeholder="{{ __('e.g. Called the customer, they want a rate for 2 x 40HC to Jebel Ali') }}">{{ $activity->notes }}</textarea>
                    </div>

                    <div class="col-12"><div class="border-top pt-2 small fw-semibold text-muted">{{ __('Next follow-up') }}</div></div>
                    <div class="col-3 form-group">
                        <label class="form-label">{{ __('Follow-up date') }}</label>
                        <input type="text" name="follow_up_date" class="form-control datepicker"
                               data-min-date="01-01-2020" data-max-date="31-12-2099"
                               value="{{ $activity->follow_up_at?->format('d-m-Y') }}">
                    </div>
                    <div class="col-3 form-group">
                        <label class="form-label">{{ __('Time') }}</label>
                        <input type="text" name="follow_up_time" class="form-control timepicker" placeholder="hh:mm"
                               value="{{ $activity->follow_up_at?->format('H:i') }}">
                    </div>
                    <div class="col-6 form-group">
                        <label class="form-label">{{ __('Assigned to') }}</label>
                        <select name="assigned_to" class="tom-select" data-live-search="true">
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" @selected((int) ($activity->assigned_to ?: auth()->id()) === (int) $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 form-group">
                        <label class="form-label">{{ __('Follow-up note') }}</label>
                        <input type="text" name="follow_up_note" class="form-control" maxlength="255"
                               placeholder="{{ __('e.g. Send the revised rate') }}"
                               value="{{ $activity->follow_up_note }}">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
