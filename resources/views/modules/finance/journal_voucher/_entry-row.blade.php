{{-- blank journal entry row: the first row of a new voucher and the Add Entry template --}}
<tr class="entry-row">
    <td>
        <select class="tom-select account-select" data-live-search="true" data-dropdown-parent="body" name="account_ids[]" required>
            <option value="">{{ __('Select Account') }}</option>
            @foreach($accounts as $account)
                <option value="{{ $account->id }}">
                    {{ $account->code }} - {{ $account->name }}
                </option>
            @endforeach
        </select>
    </td>
    <td>
        <select class="tom-select entity-type-select" data-dropdown-parent="body" name="entity_types[]">
            <option value="">{{ __('None') }}</option>
            <option value="customer">{{ __('Customer') }}</option>
            <option value="supplier">{{ __('Supplier') }}</option>
            <option value="job">{{ __('Job') }}</option>
            <option value="tax">{{ __('Tax') }}</option>
        </select>
    </td>
    <td>
        <select class="tom-select entity-select" data-live-search="true" data-dropdown-parent="body" name="entity_ids[]" data-entity-type="">
            <option value="">{{ __('Select entity type first') }}</option>
        </select>
    </td>
    <td>
        <input type="text" class="form-control" name="descriptions[]">
    </td>
    <td>
        <input type="text" step="0.01" class="decimal form-control debit-amount" name="debit_amounts[]" value="0" min="0">
    </td>
    <td>
        <input type="text" step="0.01" class="decimal form-control credit-amount" name="credit_amounts[]" value="0" min="0">
    </td>
    <td>
        <select class="tom-select tax-account-select" data-live-search="true" data-dropdown-parent="body" name="tax_ids[]">
            <option value="">{{ __('Select Tax Account') }}</option>
            @foreach($accounts as $account)
                <option value="{{ $account->id }}">
                    {{ $account->code }} - {{ $account->name }}
                </option>
            @endforeach
        </select>
    </td>
    <td>
        <input type="text" step="0.01" class="decimal form-control tax-amount" name="tax_amounts[]" value="0" min="0">
    </td>
    <td>
        <button type="button" class="btn btn-sm btn-danger remove-entry-row">
            <i class="bi bi-trash"></i>
        </button>
    </td>
</tr>
