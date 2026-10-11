<script>
    // Installment amount = loan amount / installments (filled automatically, still editable).
    // On save the installments must add up to the loan amount (to within 0.01 of rounding).
    (function () {
        var form = document.querySelector('input[name="loan_amount"]').form;
        var loan = form.querySelector('[name="loan_amount"]'), n = form.querySelector('[name="total_installments"]'), inst = form.querySelector('[name="installment_amount"]');
        var hint = document.createElement('div');
        hint.className = 'form-text small';
        inst.parentNode.appendChild(hint);
        function auto() {
            var l = parseFloat(loan.value), c = parseInt(n.value, 10);
            if (l > 0 && c > 0) { inst.value = (Math.round(l / c * 100) / 100).toFixed(2); }
            check();
        }
        function check() {
            var l = parseFloat(loan.value), c = parseInt(n.value, 10), a = parseFloat(inst.value);
            if (!(l > 0 && c > 0 && a > 0)) { hint.textContent = ''; inst.setCustomValidity(''); return true; }
            var total = Math.round(a * c * 100) / 100, ok = Math.abs(total - l) < 0.011;
            hint.textContent = ok ? '' : @json(__('Installment amount × installments must equal the loan amount.')) + ' (' + total.toFixed(2) + ' ≠ ' + l.toFixed(2) + ')';
            hint.className = 'form-text small ' + (ok ? '' : 'text-danger');
            inst.setCustomValidity(ok ? '' : hint.textContent);
            return ok;
        }
        loan.addEventListener('input', auto);
        n.addEventListener('input', auto);
        inst.addEventListener('input', check);
        form.addEventListener('submit', function (e) { if (!check()) { e.preventDefault(); inst.reportValidity(); } });
        check();
    })();
</script>
