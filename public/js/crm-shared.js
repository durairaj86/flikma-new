/* CRM helpers used on several pages (pipeline, follow-ups, enquiries, quotations): "Mark as lost" with a reason, and "Log activity". */
window.CRM = {
    _reasons: null,

    /** Lost reasons for the dialog, fetched once per page. */
    reasons(done) {
        if (CRM._reasons) return done(CRM._reasons);
        $.get(GLOBAL_FN.buildUrl('lookup/lost-reasons'), function (rows) {
            CRM._reasons = rows || [];
            done(CRM._reasons);
        }).fail(function () {
            toastr.error(trans('Could not load the lost reasons.'));
        });
    },

    /**
     * Ask why the deal was lost, then POST the status change with lost_reason_id + lost_note.
     * opts: {url, title, message, button, onDone}
     */
    lost(opts) {
        CRM.reasons(function (reasons) {
            // an open drawer (offcanvas) keeps pulling the focus back to itself: pause its focus trap while this dialog is open
            const traps = [...document.querySelectorAll('.offcanvas.show')]
                .map(el => bootstrap.Offcanvas.getInstance(el)?._focustrap).filter(Boolean);
            traps.forEach(t => t.deactivate());
            const options = reasons.map(r => '<option value="' + r.id + '">' + $('<span>').text(r.name).html() + '</option>').join('');
            const dlg = $.confirm({
                title: opts.title || trans('Mark as lost'),
                columnClass: 'col-md-6 col-md-offset-3',
                content: '<div class="crm-lost-form text-start">' +
                    (opts.message ? '<p class="mb-2">' + opts.message + '</p>' : '') +
                    '<label class="form-label">' + trans('Why was it lost?') + ' <sup class="text-danger">*</sup></label>' +
                    '<select class="tom-select" id="crmLostReason" data-live-search="true" data-dropdown-parent="body"><option value="">' + trans('-- Select --') + '</option>' + options + '</select>' +
                    '<div class="invalid-feedback" id="crmLostReasonError"></div>' +
                    '<label class="form-label mt-2">' + trans('Note') + '</label>' +
                    '<textarea class="form-control" id="crmLostNote" rows="2" maxlength="500" placeholder="' + trans('e.g. Competitor quoted 15% lower') + '"></textarea>' +
                    '</div>',
                buttons: {
                    cancel: {
                        text: trans('No'),
                        btnClass: 'btn-secondary',
                        keys: ['esc'],
                    },
                    confirm: {
                        text: opts.button || trans('Mark as lost'),
                        btnClass: 'btn-danger',
                        action: function () {
                            const $reason = this.$content.find('#crmLostReason');
                            if (!$reason.val()) {
                                $reason.addClass('is-invalid');
                                $reason[0].tomselect ? $reason[0].tomselect.focus() : $reason.trigger('focus');
                                this.$content.find('#crmLostReasonError').text(trans('Choose why this was lost.'));
                                return false;
                            }
                            const fd = new FormData();
                            fd.append('lost_reason_id', $reason.val());
                            fd.append('lost_note', this.$content.find('#crmLostNote').val());
                            const box = this;
                            box.buttons.confirm.disable();
                            $.ajax({
                                url: opts.url, type: 'POST', data: fd, processData: false, contentType: false,
                                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                                success: function (res) {
                                    toastr.success((res && res.message) || trans('Saved'));
                                    box.close();
                                    if (typeof opts.onDone === 'function') opts.onDone(res);
                                },
                                error: function (xhr) {
                                    box.buttons.confirm.enable();
                                    const r = xhr.responseJSON || {};
                                    const first = r.errors ? Object.values(r.errors)[0] : null;
                                    toastr.error((first && first[0]) || r.message || trans('Something went wrong.'));
                                }
                            });
                            return false;
                        }
                    }
                },
                onDestroy: function () {
                    traps.forEach(t => t.activate());
                },
                onContentReady: function () {
                    const $c = this.$content;
                    initTomSelectForm(null, false, $c.find('#crmLostReason'));
                    const ts = $c.find('#crmLostReason')[0].tomselect;
                    // the list opens on <body> (the dialog's scrolling content would clip it), so it must sit above the dialog
                    ts.dropdown.style.zIndex = 100000000;
                    setTimeout(() => ts.focus(), 50);
                    // keyboard-first: picking a reason (Enter) goes on to the note, Ctrl+Enter saves
                    $c.find('#crmLostReason').on('change', function () {
                        $(this).removeClass('is-invalid');
                        $c.find('#crmLostReasonError').text('');
                        if (this.value) setTimeout(() => $c.find('#crmLostNote').trigger('focus'), 0);
                    });
                    $c.find('#crmLostNote').on('keydown', function (e) {
                        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); dlg.buttons.confirm.el.trigger('click'); }
                    });
                }
            });
        });
    },

    /** Make a new version (-R1, -R2 ...) of a quotation after asking why; onDone(res) gets the new quotation's id. */
    revise(quotationId, onDone) {
        const traps = [...document.querySelectorAll('.offcanvas.show')]
            .map(el => bootstrap.Offcanvas.getInstance(el)?._focustrap).filter(Boolean);
        traps.forEach(t => t.deactivate());
        const dlg = $.confirm({
            title: trans('Revise quotation'),
            columnClass: 'col-md-6 col-md-offset-3',
            content: '<div class="text-start"><p class="mb-2 small text-muted">' + trans('A new version is made with the same lines; this one is kept as history.') + '</p>' +
                '<label class="form-label">' + trans('Why revise?') + ' <sup class="text-danger">*</sup></label>' +
                '<textarea class="form-control" id="crmReviseNote" rows="2" maxlength="255" placeholder="' + trans('e.g. Customer asked for a lower rate') + '"></textarea>' +
                '<div class="invalid-feedback" id="crmReviseError"></div></div>',
            buttons: {
                cancel: {text: trans('No'), btnClass: 'btn-secondary', keys: ['esc']},
                confirm: {
                    text: trans('Revise'),
                    btnClass: 'btn-primary',
                    action: function () {
                        const $note = this.$content.find('#crmReviseNote');
                        if (!$note.val().trim()) {
                            $note.addClass('is-invalid').trigger('focus');
                            this.$content.find('#crmReviseError').text(trans('Write why it is revised.'));
                            return false;
                        }
                        const box = this;
                        box.buttons.confirm.disable();
                        $.ajax({
                            url: GLOBAL_FN.buildUrl('sales/quotation/' + quotationId + '/revise'),
                            type: 'POST',
                            data: {revision_notes: $note.val().trim()},
                            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                            success: function (res) {
                                toastr.success(res.message);
                                box.close();
                                if (typeof onDone === 'function') onDone(res);
                            },
                            error: function (xhr) {
                                box.buttons.confirm.enable();
                                const r = xhr.responseJSON || {};
                                const first = r.errors ? Object.values(r.errors)[0] : null;
                                toastr.error((first && first[0]) || r.message || trans('Something went wrong.'));
                            }
                        });
                        return false;
                    }
                }
            },
            onDestroy: function () {
                traps.forEach(t => t.activate());
            },
            onContentReady: function () {
                const $c = this.$content;
                setTimeout(() => $c.find('#crmReviseNote').trigger('focus'), 50);
                $c.find('#crmReviseNote').on('input', function () {
                    $(this).removeClass('is-invalid');
                    $c.find('#crmReviseError').text('');
                }).on('keydown', function (e) {
                    if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); dlg.buttons.confirm.el.trigger('click'); }
                });
            }
        });
    },

    /** Open the activity form for a record (the "With" field is fixed to it). onSaved runs after the save instead of the list reload. */
    logActivity(subjectType, subjectId, onSaved) {
        const previous = window.afterModalSave;
        if (typeof onSaved === 'function') {
            window.afterModalSave = function (res) {
                window.afterModalSave = previous;
                onSaved(res);
            };
            $('#globalModal').one('hidden.bs.modal', function () { window.afterModalSave = previous; });
        }
        webModal.openGlobalModal({
            title: trans('Log Activity'),
            url: GLOBAL_FN.buildUrl('sales/activity/create'),
            content: subjectType ? {subject_type: subjectType, subject_id: subjectId} : null,
            size: 'md',
            scroll: true,
            minHeight: 'min-height:0;',
            callBack: CRM.bindActivityForm
        });
    },

    /** The "With" picker of the activity form: changing the kind reloads the record list. */
    bindActivityForm() {
        const typeEl = document.getElementById('crm_subject_type');
        const idEl = document.getElementById('crm_subject_id');
        if (!idEl) {
            // the record is fixed: start in "What happened" (the first fields are a dropdown and a date, which never take focus by themselves)
            const notes = document.querySelector('#globalModal textarea[name=notes]');
            const focus = () => setTimeout(() => notes && notes.focus(), 0);
            $('#globalModal').hasClass('show') ? focus() : $('#globalModal').one('shown.bs.modal', focus);
            return;
        }
        if (!typeEl || !idEl.tomselect) return;
        const load = function (keep) {
            const ts = idEl.tomselect;
            $.get(GLOBAL_FN.buildUrl('sales/activity/subjects'), {type: typeEl.value}, function (rows) {
                const current = keep ? ts.getValue() : '';
                ts.clearOptions();
                rows.forEach(r => ts.addOption({value: String(r.id), text: r.text}));
                if (current) ts.setValue(current, true); else ts.clear(true);
                ts.refreshOptions(false);
            });
        };
        $(typeEl).off('change.crm').on('change.crm', function () { load(false); });
        load(true);
    },

    /** Tick a follow-up off. */
    markDone(activityId, onDone, undo = false) {
        $.ajax({
            url: GLOBAL_FN.buildUrl('sales/activity/' + activityId + '/done'),
            type: 'POST',
            data: undo ? {undo: 1} : {},
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            success: function (res) {
                toastr.success(res.message);
                if (typeof onDone === 'function') onDone(res);
            },
            error: function (xhr) {
                toastr.error((xhr.responseJSON && xhr.responseJSON.message) || trans('Something went wrong.'));
            }
        });
    },

    /** Where a record is opened in its own module (the list opens its drawer from ?open=). */
    openUrl(type, id) {
        return GLOBAL_FN.buildUrl({
            prospect: 'prospects',
            customer: 'customers/' + id,
            enquiry: 'sales/enquiries',
            quotation: 'sales/quotations'
        }[type] || 'sales/pipeline') + (type === 'enquiry' || type === 'quotation' ? '?open=' + id : '');
    }
};
