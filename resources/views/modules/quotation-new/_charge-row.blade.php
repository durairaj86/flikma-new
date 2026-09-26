<tr class="charge-row">
    {{-- Col 1: checkbox --}}
    <td><input type="checkbox" class="form-check-input charge-select"></td>

    {{-- Col 2: Charge Description --}}
    <td>
        <input type="text" name="charge_description[]" class="form-control form-control-sm"
               value="{{ $charge?->charge_description ?? '' }}" placeholder="Charge description">
    </td>

    {{-- Col 3: OFD Type --}}
    <td>
        <select name="ofd_type[]" class="form-select form-select-sm">
            @foreach(['N/A','ORIGIN','DESTINATION','OTHERS'] as $ofd)
                <option value="{{ $ofd }}" @selected(($charge?->ofd_type ?? 'N/A') === $ofd)>{{ $ofd }}</option>
            @endforeach
        </select>
    </td>

    {{-- Col 4: Unit --}}
    <td>
        <input type="text" name="unit[]" class="form-control form-control-sm"
               value="{{ $charge?->unit ?? '' }}" placeholder="e.g. PER CONTAINER">
    </td>

    {{-- Col 5: Qty --}}
    <td>
        <input type="number" name="qty[]" class="form-control form-control-sm charge-qty"
               value="{{ $charge?->qty ?? 1 }}" min="1">
    </td>

    {{-- Col 6: Freight --}}
    <td>
        <select name="freight[]" class="form-select form-select-sm">
            <option value="PREPAID" @selected(($charge?->freight ?? 'PREPAID') === 'PREPAID')>PREPAID</option>
            <option value="COLLECT" @selected(($charge?->freight ?? '') === 'COLLECT')>COLLECT</option>
        </select>
    </td>

    {{-- Col 7: Dr/Cr --}}
    <td>
        <select name="dr_cr[]" class="form-select form-select-sm">
            <option value="Cr" @selected(($charge?->dr_cr ?? 'Cr') === 'Cr')>Cr</option>
            <option value="Dr" @selected(($charge?->dr_cr ?? '') === 'Dr')>Dr</option>
        </select>
    </td>

    {{-- Col 8: Qty/Amount --}}
    <td>
        <input type="number" name="qty_amount[]" class="form-control form-control-sm charge-qty-amount"
               value="{{ $charge?->qty_amount ?? '' }}" step="0.01" min="0"
               placeholder="0.00">
    </td>

    {{-- Col 9: FCY Amount --}}
    <td>
        <input type="text" name="fcy_amount[]" class="form-control form-control-sm charge-fcy"
               value="{{ $charge?->fcy_amount ? 'INR 1 * '.intval($charge?->qty_amount ?? 0) : '' }}"
               readonly placeholder="INR 1 * ...">
    </td>

    {{-- Col 10: Amount (INR) --}}
    <td>
        <input type="number" name="amount_inr[]" class="form-control form-control-sm charge-amount-inr"
               value="{{ $charge?->amount_inr ?? '' }}" step="0.01" min="0"
               placeholder="0.00">
    </td>

    {{-- Col 11: Tax Amount (INR) --}}
    <td>
        <input type="number" name="tax_amount_inr[]" class="form-control form-control-sm charge-tax"
               value="{{ $charge?->tax_amount_inr ?? 0 }}" step="0.01" min="0">
    </td>
</tr>
