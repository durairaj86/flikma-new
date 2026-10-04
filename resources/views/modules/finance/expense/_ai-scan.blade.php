{{-- "Scan Receipt with AI" card for new expenses: same flow as the supplier-invoice scan. --}}
<style>
    .btn-indigo { background-color: #4f46e5; border-color: #4f46e5; color: #fff; }
    .btn-indigo:hover { background-color: #4338ca; border-color: #4338ca; color: #fff; }
    .ai-scan-card { background: linear-gradient(135deg, #f5f3ff 0%, #eef2ff 100%); border: 1px solid #e0e7ff !important; }
    .ai-scan-icon { width: 34px; height: 34px; border-radius: 50%; background: #4f46e5; color: #fff; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .extra-small { font-size: .72rem; }
    .ai-reveal-row { animation: aiRevealFadeIn .35s ease-out; border-bottom: 1px solid #f3f4f6; }
    .ai-reveal-row:last-child { border-bottom: none; }
    @keyframes aiRevealFadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="px-4 mt-3">
    <div class="card border-0 shadow-sm ai-scan-card">
        <div class="card-body py-3 px-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="ai-scan-icon"><i class="bi bi-stars"></i></span>
                    <div>
                        <div class="fw-bold small">{{ __('Scan Receipt with AI') }}</div>
                        <div class="text-muted extra-small">{{ __("Upload a photo or PDF of the receipt and we'll fill in the details below.") }}</div>
                    </div>
                </div>
                <label class="btn btn-indigo btn-sm shadow-sm mb-0" id="aiScanBillLabel">
                    <i class="bi bi-camera me-1"></i> <span id="aiScanBillLabelText">{{ __('Upload Receipt') }}</span>
                    <input type="file" id="aiScanBillInput" accept="image/jpeg,image/png,image/webp,application/pdf" class="d-none">
                </label>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="aiScanBillModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <span class="ai-scan-icon" style="width:26px;height:26px;font-size:.8rem;"><i class="bi bi-stars"></i></span>
                    {{ __('Scanning Receipt') }}
                </h6>
                <button type="button" class="btn-close" id="aiScanBillModalCloseX" data-bs-dismiss="modal" aria-label="{{ __('Close') }}" style="display:none;"></button>
            </div>
            <div class="modal-body pt-2">
                <div id="aiScanBillModalLoading" class="text-center py-4">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <div class="fw-semibold" id="aiScanBillModalStatusText">{{ __('Scanning your receipt…') }}</div>
                    <div class="text-muted small mt-1">{{ __('This usually takes a few seconds') }}</div>
                </div>
                <ul class="list-unstyled mb-0" id="aiScanBillModalList" style="display:none;"></ul>
                <div class="alert alert-danger border-0 mb-0" id="aiScanBillModalError" style="display:none;"></div>
            </div>
            <div class="modal-footer border-0 pt-0" id="aiScanBillModalFooter" style="display:none;">
                <button type="button" class="btn btn-indigo btn-sm px-4" data-bs-dismiss="modal">
                    <i class="bi bi-check-lg me-1"></i> {{ __('Done — Review & Save') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    // The scan popup lives in <body>, not inside the expense popup, so it stacks above it.
    document.querySelectorAll('body > #aiScanBillModal').forEach(function (old) { old.remove(); });
    var modalEl = document.getElementById('aiScanBillModal');
    document.body.appendChild(modalEl);

    var fileInput = document.getElementById('aiScanBillInput');
    var labelText = document.getElementById('aiScanBillLabelText');
    var TXT = {
        upload: @json(__('Upload Receipt')),
        scanning: @json(__('Scanning...')),
        collecting: @json(__('Collecting data…')),
        failed: @json(__('Could not read the receipt. Please fill the form manually.')),
        addSupplier: @json(__('Add Supplier')),
        adding: @json(__('Adding...')),
        supplierAdded: @json(__('Supplier Added')),
        notMatched: @json(__('not in your suppliers yet')),
        matched: @json(__('matched')),
        supplier: @json(__('Supplier')),
        reference: @json(__('Reference')),
        date: @json(__('Expense Date')),
        account: @json(__('Account')),
        noAccount: @json(__('no match — please select the account manually')),
        description: @json(__('Description')),
        amount: @json(__('Amount')),
        tax: @json(__('tax'))
    };

    function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
    function $id(i) { return document.getElementById(i); }
    function bsModal() { return window.bootstrap ? bootstrap.Modal.getOrCreateInstance(modalEl) : null; }

    function resetModal() {
        $id('aiScanBillModalLoading').style.display = 'block';
        $id('aiScanBillModalStatusText').textContent = @json(__('Scanning your receipt…'));
        $id('aiScanBillModalList').style.display = 'none';
        $id('aiScanBillModalList').innerHTML = '';
        $id('aiScanBillModalError').style.display = 'none';
        $id('aiScanBillModalFooter').style.display = 'none';
        $id('aiScanBillModalCloseX').style.display = 'none';
    }

    function showError(message) {
        $id('aiScanBillModalLoading').style.display = 'none';
        var el = $id('aiScanBillModalError');
        el.style.display = 'block';
        el.textContent = message;
        $id('aiScanBillModalFooter').style.display = 'flex';
        $id('aiScanBillModalCloseX').style.display = 'block';
    }

    function setDate(name, iso) {
        var input = document.querySelector('#moduleForm input[name="' + name + '"]');
        if (!input || !iso) return;
        var p = String(iso).split('-');
        if (p.length === 3 && input._flatpickr) {
            input._flatpickr.setDate(new Date(+p[0], +p[1] - 1, +p[2]), true);
        } else {
            input.value = iso;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function setSelect(select, value) {
        if (!select || value === null || value === undefined || value === '') return false;
        if (select.tomselect) {
            if (!select.tomselect.options[String(value)]) return false;
            select.tomselect.setValue(String(value));
            return true;
        }
        select.value = String(value);
        select.dispatchEvent(new Event('change', { bubbles: true }));
        return true;
    }

    function taxCodeFor(row, rate) {
        var sel = row.querySelector('select.tax');
        if (!sel) return null;
        var best = null;
        Array.prototype.forEach.call(sel.options, function (o) {
            if (o.value === '') return;
            if (parseFloat(o.dataset.percent) === parseFloat(rate) && (best === null || o.value === 'STANDARD' || o.value === 'ZERO')) best = o.value;
        });
        return best;
    }

    function fillForm(data) {
        if (data.supplier_id) setSelect($id('supplier'), data.supplier_id);
        var ref = document.querySelector('#moduleForm input[name="reference_number"]');
        if (ref && data.reference_number) ref.value = data.reference_number;
        setDate('posted_at', data.expense_date);

        var tbody = $id('EXPENSE-tbody');
        if (!tbody) return;
        var lines = data.line_items && data.line_items.length ? data.line_items : [{ description: data.description, amount: data.subtotal, tax_rate: data.tax_rate, account_id: data.account_id }];
        lines.forEach(function (line, i) {
            if (i > 0) {
                var adder = tbody.querySelector('tr:last-child .add-row');
                if (adder && window.jQuery) window.jQuery(adder).trigger('click');
            }
            var rows = tbody.querySelectorAll('tr');
            var row = rows[i] || rows[rows.length - 1];
            setSelect(row.querySelector('select[name="account[]"]'), line.account_id);
            var comment = row.querySelector('textarea[name="comment[]"]');
            if (comment) comment.value = line.description || '';
            var q = row.querySelector('.quantity');
            if (q) { q.value = 1; q.dispatchEvent(new Event('input', { bubbles: true })); }
            var pr = row.querySelector('.unit_price');
            if (pr) { pr.value = line.amount; pr.dispatchEvent(new Event('input', { bubbles: true })); }
            setSelect(row.querySelector('select.tax'), taxCodeFor(row, line.tax_rate));
        });
        if (window.CALCULATION) { CALCULATION.finalTotals(); }
    }

    function addSupplierButton(btn, sup) {
        btn.addEventListener('click', function () {
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> ' + TXT.adding;
            var fd = new FormData();
            fd.append('_token', csrf());
            fd.append('name', sup.name);
            fd.append('email', sup.email || '');
            fd.append('phone', sup.phone || '');
            fd.append('address', sup.address || '');
            fd.append('city', sup.city || '');
            fd.append('tax_number', sup.tax_number || '');
            if (sup.currency) fd.append('currency', sup.currency);
            fetch(@json(route('expenses.ai.save-supplier')), { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
                .then(function (res) {
                    if (res.ok && res.d.status === 'success' && res.d.party) {
                        var sel = $id('supplier');
                        if (sel && sel.tomselect) {
                            if (!sel.tomselect.options[res.d.party.id]) sel.tomselect.addOption({ value: String(res.d.party.id), text: res.d.party.name });
                            sel.tomselect.setValue(String(res.d.party.id));
                        }
                        btn.className = 'btn btn-success btn-sm px-3';
                        btn.innerHTML = '<i class="bi bi-check-lg me-1"></i> ' + TXT.supplierAdded;
                    } else { throw new Error('fail'); }
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-plus-lg me-1"></i> ' + TXT.addSupplier;
                });
        });
    }

    function reveal(data) {
        var rows = [];
        if (data.vendor_name) {
            rows.push({ label: TXT.supplier, value: esc(data.vendor_name) + ' <span class="text-muted fw-normal">(' + (data.supplier_id ? TXT.matched : TXT.notMatched) + ')</span>', warn: !data.supplier_id, addSupplier: !data.supplier_id });
        }
        if (data.reference_number) rows.push({ label: TXT.reference, value: esc(data.reference_number) });
        if (data.expense_date) rows.push({ label: TXT.date, value: esc(data.expense_date) });
        var lines = data.line_items && data.line_items.length ? data.line_items : [];
        lines.forEach(function (l, i) {
            var n = lines.length > 1 ? ' ' + (i + 1) : '';
            rows.push({
                label: TXT.description + n,
                value: esc(l.description) + ' <span class="text-muted fw-normal">— ' + Number(l.amount).toFixed(2) + (l.tax_rate ? ' (' + TXT.tax + ' ' + l.tax_rate + '%)' : '') + '</span>'
            });
            rows.push({
                label: TXT.account + n,
                value: l.account_name ? esc(l.account_name) : '<span class="text-muted fw-normal">' + TXT.noAccount + '</span>',
                warn: !l.account_name
            });
        });

        var list = $id('aiScanBillModalList');
        $id('aiScanBillModalLoading').style.display = 'none';
        list.style.display = 'block';
        list.innerHTML = '';
        rows.forEach(function (row, i) {
            setTimeout(function () {
                var li = document.createElement('li');
                li.className = 'd-flex align-items-center gap-2 py-1 ai-reveal-row';
                li.innerHTML = '<i class="bi ' + (row.warn ? 'bi-exclamation-triangle-fill text-warning' : 'bi-check-circle-fill text-success') + '"></i>' +
                    '<span class="text-muted small" style="min-width:90px;">' + esc(row.label) + '</span>' +
                    '<span class="fw-semibold small flex-grow-1">' + row.value + '</span>' +
                    (row.addSupplier ? '<button type="button" class="btn btn-indigo btn-sm px-3"><i class="bi bi-plus-lg me-1"></i> ' + TXT.addSupplier + '</button>' : '');
                list.appendChild(li);
                if (row.addSupplier) {
                    addSupplierButton(li.querySelector('button'), { name: data.vendor_name, email: data.vendor_email, phone: data.vendor_phone, address: data.vendor_address, city: data.vendor_city, tax_number: data.vendor_tax_number, currency: data.currency });
                }
                if (i === rows.length - 1) {
                    setTimeout(function () {
                        $id('aiScanBillModalFooter').style.display = 'flex';
                        $id('aiScanBillModalCloseX').style.display = 'block';
                    }, 250);
                }
            }, i * 350);
        });
    }

    if (!fileInput) return;
    fileInput.addEventListener('change', function (e) {
        var file = fileInput.files && fileInput.files[0];
        if (!file) return;
        e.target.value = '';
        labelText.textContent = TXT.scanning;
        resetModal();
        var m = bsModal();
        if (m) m.show();

        var statusTimer = setTimeout(function () { $id('aiScanBillModalStatusText').textContent = TXT.collecting; }, 900);
        var fd = new FormData();
        fd.append('image', file);
        fd.append('_token', csrf());

        fetch(@json(route('expenses.ai.scan-receipt')), { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
            .then(function (res) {
                clearTimeout(statusTimer);
                labelText.textContent = TXT.upload;
                if (!res.ok) { showError(res.d.error || res.d.message || TXT.failed); return; }
                var attachInput = document.querySelector('#moduleForm input[name="attachments[]"]');
                if (attachInput && typeof DataTransfer !== 'undefined') {
                    var dt = new DataTransfer();
                    dt.items.add(file);
                    attachInput.files = dt.files;
                }
                fillForm(res.d);
                reveal(res.d);
            })
            .catch(function () {
                clearTimeout(statusTimer);
                labelText.textContent = TXT.upload;
                showError(TXT.failed);
            });
    });
})();
</script>
