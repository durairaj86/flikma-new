CRM_PIPELINE = {
    baseUrl: 'sales/pipeline',
    current: null,   // card open in the drawer {type, id}

    load() {
        const reload = window.debounceSearch ? window.debounceSearch(() => CRM_PIPELINE.board.load()) : () => CRM_PIPELINE.board.load();
        initTomSelectForm($('#crmFilters'));
        $('#crmSalesperson, #crmDays').off().on('change', () => CRM_PIPELINE.board.load());
        $('#crmSearch').off().on('input', reload);
        $('#new').off().on('click', () => CRM.logActivity(null, null, () => CRM_PIPELINE.board.load()));
        // after any modal save on this page (activity form opened from the drawer, edit ...): refresh the board and the drawer
        window.afterModalSave = function () {
            CRM_PIPELINE.board.load();
            CRM_PIPELINE.drawer.refresh();
        };
        CRM_PIPELINE.board.keyboard();
        CRM_PIPELINE.board.load();
    },

    esc(v) {
        return $('<span>').text(v == null ? '' : String(v)).html();
    },

    money(v, cur) {
        return Number(v || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + (cur ? ' ' + cur : '');
    },

    board: {
        load() {
            $.ajax({
                url: GLOBAL_FN.buildUrl('sales/pipeline/data'),
                type: 'POST',
                data: {
                    salesperson_id: $('#crmSalesperson').val() || '',
                    days: $('#crmDays').val(),
                    q: $('#crmSearch').val() || ''
                },
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function (res) {
                    const focused = $(document.activeElement).closest('.crm-card');
                    const keep = focused.length ? focused.data('key') : null;
                    Object.keys(res.columns).forEach(key => CRM_PIPELINE.board.column(key, res.columns[key], res.currency));
                    $('#kpiQuoted').text(res.columns.quoted.count + ' · ' + CRM_PIPELINE.money(res.columns.quoted.value, res.currency));
                    $('#kpiWon').text(res.columns.won.count + ' · ' + CRM_PIPELINE.money(res.columns.won.value, res.currency));
                    $('#kpiWinRate').text(res.win_rate === null ? '—' : res.win_rate + '%');
                    if (keep) $('.crm-card[data-key="' + keep + '"]').trigger('focus');
                },
                error: function (xhr) {
                    toastr.error((xhr.responseJSON && xhr.responseJSON.message) || trans('Could not load the pipeline.'));
                }
            });
        },

        column(key, col, currency) {
            const $col = $('.crm-col[data-col="' + key + '"]');
            $col.find('.crm-col-count').text(col.count);
            $col.find('.crm-col-value').text(col.value > 0 ? CRM_PIPELINE.money(col.value, currency) + ' ·' : '');
            const $body = $col.find('.crm-col-body').empty();
            if (!col.cards.length) {
                $body.append('<div class="crm-empty">' + trans('Nothing here') + '</div>');
                return;
            }
            col.cards.forEach(c => $body.append(CRM_PIPELINE.board.card(c, key)));
            if (col.count > col.cards.length) {
                $body.append('<div class="crm-more">' + trans(':n more — narrow the search').replace(':n', col.count - col.cards.length) + '</div>');
            }
        },

        card(c, col) {
            const e = CRM_PIPELINE.esc;
            let chip;
            if (col === 'lost') {
                chip = '<span class="crm-chip lost">' + e(c.lost_reason || c.stage_label) + '</span>';
            } else if (c.next_follow_up) {
                chip = '<span class="crm-chip ' + c.follow_up_state + '"><i class="bi bi-alarm me-1"></i>' + e(c.next_follow_up.substring(0, 10)) + '</span>';
            } else if (col !== 'won') {
                chip = '<span class="crm-chip none">' + trans('No follow-up') + '</span>';
            } else {
                chip = '';
            }
            const value = c.type === 'quotation' && c.doc_value ? '<span class="fw-semibold">' + CRM_PIPELINE.money(c.doc_value, c.doc_currency) + '</span>' : '';
            const expiry = col === 'quoted' && c.expires_in !== null && c.expires_in <= 3
                ? '<span class="crm-chip ' + (c.expires_in < 0 ? 'overdue' : 'today') + '">' + (c.expires_in < 0 ? trans('Expired') : trans('Expires in :n d').replace(':n', c.expires_in)) + '</span>' : '';
            // the customer's own answer from the accept / reject link in the quotation email
            const answer = c.customer_status === 1 ? '<span class="crm-chip" style="background:#dcfce7;color:#166534">' + trans('Customer accepted') + '</span>'
                : (c.customer_status === 2 ? '<span class="crm-chip overdue">' + trans('Customer rejected') + '</span>' : '');
            const stage = (c.type === 'prospect' && col === 'lead') || col === 'enquiry' || col === 'won'
                ? '<span class="crm-chip">' + e(c.stage_label) + '</span>' : '';
            return '<div class="crm-card" tabindex="0" role="button" data-key="' + c.type + '-' + c.id + '" data-type="' + c.type + '" data-id="' + c.id + '">' +
                '<div class="d-flex justify-content-between gap-2"><span class="crm-card-name text-truncate">' + e(c.name) + '</span><span class="crm-card-no">' + e(c.no) + '</span></div>' +
                (c.sub ? '<div class="text-muted text-truncate">' + e(c.sub) + '</div>' : '') +
                '<div class="d-flex justify-content-between align-items-center gap-1 mt-1 flex-wrap">' + value + '<span class="d-flex gap-1 flex-wrap ms-auto">' + answer + stage + expiry + chip + '</span></div>' +
                '<div class="d-flex justify-content-between text-muted mt-1" style="font-size:.72rem"><span>' + e(c.salesperson || '') + '</span><span>' + trans(':n d old').replace(':n', c.age) + '</span></div>' +
                '</div>';
        },

        /** Keyboard-first: arrows move between cards (left/right between columns), Enter opens the card. */
        keyboard() {
            $('#crmBoard').off('click.crm keydown.crm')
                .on('click.crm', '.crm-card', function () {
                    CRM_PIPELINE.drawer.open($(this).data('type'), $(this).data('id'));
                })
                .on('keydown.crm', '.crm-card', function (ev) {
                    const $card = $(this);
                    if (ev.key === 'Enter' || ev.key === ' ') {
                        ev.preventDefault();
                        CRM_PIPELINE.drawer.open($card.data('type'), $card.data('id'));
                        return;
                    }
                    let $target = null;
                    if (ev.key === 'ArrowDown') $target = $card.nextAll('.crm-card').first();
                    if (ev.key === 'ArrowUp') $target = $card.prevAll('.crm-card').first();
                    if (ev.key === 'ArrowRight' || ev.key === 'ArrowLeft') {
                        const rtl = document.documentElement.dir === 'rtl';
                        const forward = (ev.key === 'ArrowRight') !== rtl;
                        let $col = $card.closest('.crm-col');
                        const index = $card.index('.crm-col[data-col="' + $col.data('col') + '"] .crm-card');
                        do {
                            $col = forward ? $col.next('.crm-col') : $col.prev('.crm-col');
                        } while ($col.length && !$col.find('.crm-card').length);
                        if ($col.length) {
                            const cards = $col.find('.crm-card');
                            $target = cards.eq(Math.min(index, cards.length - 1));
                        }
                    }
                    if ($target && $target.length) {
                        ev.preventDefault();
                        $target.trigger('focus');
                    }
                });
            // Down from the filters goes into the board
            $('#crmSearch').off('keydown.crm').on('keydown.crm', function (ev) {
                if (ev.key === 'ArrowDown') {
                    ev.preventDefault();
                    $('#crmBoard .crm-card').first().trigger('focus');
                }
            });
        }
    },

    drawer: {
        open(type, id) {
            CRM_PIPELINE.current = {type, id};
            $('#crmDrawerTitle').text('');
            $('#crmDrawerFacts').html('<div class="text-muted">' + trans('Loading...') + '</div>');
            $('#crmDrawerActions, #crmDrawerTimeline').empty();
            const el = document.getElementById('crmDrawer');
            bootstrap.Offcanvas.getOrCreateInstance(el).show();
            // when the drawer closes, the card the user came from gets the focus back
            $(el).off('hidden.bs.offcanvas.crm').on('hidden.bs.offcanvas.crm', function () {
                $('.crm-card[data-key="' + type + '-' + id + '"]').trigger('focus');
            });
            // the offcanvas takes the focus itself once it has slid in: then hand it to the first action (keyboard-first)
            $(el).off('shown.bs.offcanvas.crm').on('shown.bs.offcanvas.crm', function () {
                $('#crmDrawerActions button').first().trigger('focus');
            });
            CRM_PIPELINE.drawer.refresh();
        },

        refresh() {
            const cur = CRM_PIPELINE.current;
            if (!cur || !$('#crmDrawer').hasClass('show') && !$('#crmDrawer').hasClass('showing')) return;
            $.get(GLOBAL_FN.buildUrl('sales/pipeline/card/' + cur.type + '/' + cur.id), function (c) {
                CRM_PIPELINE.drawer.render(c);
            }).fail(function () {
                $('#crmDrawerFacts').html('<div class="alert alert-warning">' + trans('This record is no longer available.') + '</div>');
            });
            $.get(GLOBAL_FN.buildUrl('sales/activity/timeline/' + cur.type + '/' + cur.id), function (html) {
                $('#crmDrawerTimeline').html(html);
            });
        },

        render(c) {
            const e = CRM_PIPELINE.esc;
            const kinds = {prospect: trans('Prospect'), enquiry: trans('Enquiry'), quotation: trans('Quotation')};
            $('#crmDrawerKind').text(kinds[c.type] + ' · ' + (c.no || ''));
            $('#crmDrawerTitle').text(c.name || '');
            const fact = (label, value) => value ? '<div class="d-flex justify-content-between border-bottom py-1"><span class="text-muted">' + label + '</span><span class="text-end">' + value + '</span></div>' : '';
            $('#crmDrawerFacts').html(
                fact(trans('Status'), e(c.stage_label)) +
                fact(trans('Route'), e(c.sub && c.type !== 'prospect' ? c.sub : '')) +
                fact(trans('Company'), e(c.type === 'prospect' ? c.sub : '')) +
                fact(trans('Phone'), e(c.phone)) +
                fact(trans('Email'), e(c.email)) +
                fact(trans('Amount'), c.doc_value ? CRM_PIPELINE.money(c.doc_value, c.doc_currency) : '') +
                fact(trans('Valid Until'), e(c.valid_until)) +
                fact(trans('Salesperson'), e(c.salesperson)) +
                fact(trans('Next follow-up'), e(c.next_follow_up)) +
                fact(trans('Last activity'), e(c.last_activity)) +
                fact(trans('Lost reason'), e([c.lost_reason, c.lost_note].filter(Boolean).join(' — ')))
            );

            const $a = $('#crmDrawerActions').empty();
            const btn = (cls, icon, label, action) => $('<button type="button" class="btn btn-sm rounded-pill px-3 ' + cls + '"><i class="bi ' + icon + ' me-1"></i>' + label + '</button>').on('click', action);
            const done = () => { CRM_PIPELINE.board.load(); CRM_PIPELINE.drawer.refresh(); };

            $a.append(btn('btn-primary', 'bi-plus-lg', trans('Log Activity'), () => CRM.logActivity(c.type, c.id)));
            if (c.type === 'prospect' && c.stage !== 'lost') {
                if (c.stage === 'new') $a.append(btn('btn-outline-primary', 'bi-telephone', trans('Mark as Contacted'), () => CRM_PIPELINE.drawer.prospectStage(c.id, 'contacted', done)));
                if (c.stage !== 'qualified') $a.append(btn('btn-outline-primary', 'bi-check2-circle', trans('Mark as Qualified'), () => CRM_PIPELINE.drawer.prospectStage(c.id, 'qualified', done)));
                $a.append(btn('btn-outline-danger', 'bi-x-circle', trans('Mark as lost'), () => CRM.lost({
                    url: GLOBAL_FN.buildUrl('prospect/' + c.id + '/status/lost'), onDone: done
                })));
            } else if (c.type === 'prospect') {
                $a.append(btn('btn-outline-secondary', 'bi-arrow-counterclockwise', trans('Reopen'), () => CRM_PIPELINE.drawer.prospectStage(c.id, 'new', done)));
            }
            if (c.type === 'enquiry' && [1, 2].includes(c.stage)) {
                $a.append(btn('btn-outline-danger', 'bi-x-circle', trans('Mark as lost'), () => CRM.lost({
                    url: GLOBAL_FN.buildUrl('sales/enquiry/' + c.id + '/status/4'), message: trans('The enquiry will be cancelled.'), onDone: done
                })));
            }
            if (c.type === 'quotation' && [1, 4].includes(c.stage)) {
                // a new version opens in the quotation form, where the rates are changed
                $a.append(btn('btn-outline-primary', 'bi-arrow-clockwise', trans('Revise'), () => CRM.revise(c.id, res => {
                    window.location.href = GLOBAL_FN.buildUrl('sales/quotations?edit=' + res.id);
                })));
            }
            if (c.type === 'quotation' && c.stage === 1) {
                $a.append(btn('btn-outline-danger', 'bi-x-circle', trans('Mark as lost'), () => CRM.lost({
                    url: GLOBAL_FN.buildUrl('sales/quotation/' + c.id + '/status/3'), message: trans('The quotation will be cancelled.'), onDone: done
                })));
            }
            $a.append($('<a class="btn btn-sm rounded-pill px-3 btn-outline-secondary"><i class="bi bi-box-arrow-up-right me-1"></i>' + trans('Open') + '</a>').attr('href', CRM.openUrl(c.type, c.id)));

            $('#crmDrawerTimeline').off('click.crm').on('click.crm', '.crm-tl-done', function () {
                CRM.markDone($(this).data('id'), done);
            });
            // a refresh while the drawer is already open (after a save) puts the focus back on the first action
            if ($('#crmDrawer').hasClass('show') && !$('#crmDrawer').hasClass('showing') && !$('#globalModal').hasClass('show')) {
                $a.find('button').first().trigger('focus');
            }
        },

        prospectStage(id, status, done) {
            $.ajax({
                url: GLOBAL_FN.buildUrl('prospect/' + id + '/status/' + status),
                type: 'POST',
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                success: function (res) { toastr.success(res.message); done(); },
                error: function (xhr) { toastr.error((xhr.responseJSON && xhr.responseJSON.message) || trans('Something went wrong.')); }
            });
        }
    }
}
