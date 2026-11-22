<?php

namespace App\Services\Crm;

use App\Models\Crm\LostReason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * A prospect, enquiry or quotation that is lost / cancelled must say why (a reason from the Lost Reasons master + an optional note),
 * so the sales reports can show why deals are lost. Leaving the lost status clears the reason again.
 */
class LostDeal
{
    /** Validate the reason posted with the status change and put it on the record (not saved). */
    public static function apply(Model $model, Request $request): void
    {
        $reasonId = (int) $request->input('lost_reason_id');
        if (!$reasonId || !LostReason::where('id', $reasonId)->exists()) {
            throw ValidationException::withMessages(['lost_reason_id' => __('Choose why this was lost.')]);
        }
        $note = trim((string) $request->input('lost_note'));
        if (mb_strlen($note) > 500) {
            throw ValidationException::withMessages(['lost_note' => __('The note may not be longer than :n characters.', ['n' => 500])]);
        }
        $model->lost_reason_id = $reasonId;
        $model->lost_note = $note !== '' ? $note : null;
    }

    public static function clear(Model $model): void
    {
        $model->lost_reason_id = null;
        $model->lost_note = null;
    }
}
