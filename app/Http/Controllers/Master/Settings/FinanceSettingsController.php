<?php

namespace App\Http\Controllers\Master\Settings;

use App\Http\Controllers\Controller;
use App\Services\Finance\FinanceSettings;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Settings › Finance: credit limit check on customer invoices, attachment of emailed statements. */
class FinanceSettingsController extends Controller
{
    public function edit()
    {
        return view('modules.settings.finance', ['settings' => FinanceSettings::all()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'credit_check' => ['required', Rule::in(array_keys(FinanceSettings::CREDIT_CHECKS))],
            'statement_attachment' => ['required', Rule::in(array_keys(FinanceSettings::STATEMENT_ATTACHMENTS))],
        ], [], [
            'credit_check' => __('Credit limit check'), 'statement_attachment' => __('Statement email attachment'),
        ]);
        FinanceSettings::save($data);

        return response()->json(['status' => 'success', 'message' => __('Finance settings saved')]);
    }
}
