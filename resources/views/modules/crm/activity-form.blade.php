@php
    $subjects = array_keys(\App\Models\Crm\CrmActivity::SUBJECTS);
    $categories = \App\Models\Crm\CrmAction::CATEGORIES;
    $byCategory = $actions->groupBy('category');
    $planned = $byCategory[\App\Models\Crm\CrmAction::PLANNED] ?? collect();
    // a planned activity's date is its due date
    $isPlanned = $activity->id && $activity->follow_up_at;
    $at = $isPlanned ? $activity->follow_up_at : $activity->activity_at;
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

                    <div class="col-6 form-group">
                        <label class="form-label required">{{ __('Action') }} <sup class="text-danger">*</sup></label>
                        <select name="action_id" id="crm_action_id" class="tom-select" required data-live-search="true" data-placeholder="{{ __('Choose an action') }}">
                            <option value="">{{ __('Choose an action') }}</option>
                            @foreach($categories as $key => $c)
                                @if(($byCategory[$key] ?? collect())->isNotEmpty())
                                    <optgroup label="{{ __($c['label']) }}">
                                        @foreach($byCategory[$key] as $a)
                                            <option value="{{ $a->id }}" data-planned="{{ $a->category === \App\Models\Crm\CrmAction::PLANNED ? 1 : 0 }}"
                                                @selected((int) $activity->action_id === (int) $a->id)>{{ $a->label() }}</option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-3 form-group">
                        <label class="form-label required"><span class="crm-date-done">{{ __('Date') }}</span><span class="crm-date-planned d-none">{{ __('Due date') }}</span> <sup class="text-danger">*</sup></label>
                        <input type="text" name="activity_date" class="form-control datepicker" required
                               data-min-date="01-01-2020" data-max-date="31-12-2099"
                               value="{{ $at?->format('d-m-Y') }}">
                    </div>
                    <div class="col-3 form-group">
                        <label class="form-label">{{ __('Time') }}</label>
                        <input type="text" name="activity_time" class="form-control timepicker" placeholder="hh:mm"
                               value="{{ $at && $at->format('H:i') !== '00:00' ? $at->format('H:i') : '' }}">
                    </div>
                    <div class="col-12 form-group">
                        <label class="form-label">{{ __('Note') }}</label>
                        <textarea name="notes" class="form-control" rows="2" maxlength="5000"
                                  placeholder="{{ __('e.g. They want a rate for 2 x 40HC to Jebel Ali') }}">{{ $activity->notes }}</textarea>
                    </div>
                    {{-- a planned action is somebody's to-do --}}
                    <div class="col-6 form-group crm-when-planned d-none">
                        <label class="form-label">{{ __('Assigned to') }}</label>
                        <select name="assigned_to" class="tom-select" data-live-search="true">
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" @selected((int) ($activity->assigned_to ?: auth()->id()) === (int) $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- after something was done: plan the next step in the same save (a new activity) --}}
                    @if(!$activity->id && $planned->isNotEmpty())
                        <div class="col-12 crm-when-done"><div class="border-top pt-2 small fw-semibold text-muted">{{ __('Plan the next step (optional)') }}</div></div>
                        <div class="col-4 form-group crm-when-done">
                            <label class="form-label">{{ __('Next action') }}</label>
                            <select name="next_action_id" id="crm_next_action_id" class="tom-select" data-placeholder="{{ __('None') }}">
                                <option value="">{{ __('None') }}</option>
                                @foreach($planned as $a)
                                    <option value="{{ $a->id }}">{{ $a->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-3 form-group crm-when-done">
                            <label class="form-label">{{ __('Due date') }}</label>
                            <input type="text" name="next_date" class="form-control datepicker"
                                   data-min-date="01-01-2020" data-max-date="31-12-2099">
                        </div>
                        <div class="col-2 form-group crm-when-done">
                            <label class="form-label">{{ __('Time') }}</label>
                            <input type="text" name="next_time" class="form-control timepicker" placeholder="hh:mm">
                        </div>
                        <div class="col-3 form-group crm-when-done">
                            <label class="form-label">{{ __('Assigned to') }}</label>
                            <select name="next_assigned_to" class="tom-select" data-live-search="true">
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" @selected((int) auth()->id() === (int) $u->id)>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>
