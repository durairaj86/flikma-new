<?php

namespace App\Http\Controllers;

use App\Models\Master\PeriodClosing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

abstract class Controller
{
    /*public static function companySessionCache()
    {
        $cache = cacheName();
        $cacheResponse = $cache->getData(true);
        if ($cacheResponse['status'] == 'logout') {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
            return redirect()->route('login');
        }
        return $cache;
    }*/

    /**
     * An invoice, collection or payment is in the currency set on its customer / supplier (Customer / Supplier module);
     * it is never chosen per document, so a different one posted from a form is refused.
     */
    protected function assertPartyCurrency(string $partyClass, $partyId, ?string $currency): void
    {
        $party = $partyId ? $partyClass::find($partyId) : null;
        $partyCurrency = strtoupper((string) ($party->currency ?? ''));
        if ($partyCurrency !== '' && strtoupper((string) $currency) !== $partyCurrency) {
            throw ValidationException::withMessages(['currency' => __('The currency is set on the :party (:cur). Change it there, not on the document.', ['party' => $party instanceof \App\Models\Supplier\Supplier ? __('supplier') : __('customer'), 'cur' => $partyCurrency])]);
        }
    }

    /**
     * Set common base columns for any model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param int|null $companyId
     * @return void
     */
    protected function setBaseColumns($model, $companyId = null): void
    {
        $model->user_id = Auth::id();
        $model->company_id = $companyId ?? (Auth::user()->company_id ?? null);
    }

    /**
     * Guard a financial transaction's date against Period Closing. Call
     * this after validation, before save, in any controller that posts to
     * the ledger (invoices, payments, collections, journal vouchers, credit
     * notes) — throws the same shape of error `$request->validate()` would,
     * so existing AJAX error handling on the frontend needs no changes.
     *
     * @param string $date
     * @param string $field The form field name to attach the error to.
     * @throws ValidationException
     */
    protected function assertPeriodOpen($date, string $field = 'date'): void
    {
        if (!PeriodClosing::isLocked($date)) {
            return;
        }

        $lockedThrough = PeriodClosing::lockedThroughDate()->format('d-m-Y');

        throw ValidationException::withMessages([
            $field => "This date falls within a closed accounting period (locked through {$lockedThrough}). Contact your administrator to reopen the period before saving.",
        ]);
    }

    /**
     * The account chosen on each line of an invoice / note / expense. The poster adds the receivable / payable, VAT and
     * cash-bank lines itself, so a line may not use those (an income line on the bank account was posted as cash), nor a
     * group account. Empty lines are fine (the poster falls back to its default account).
     * @throws ValidationException
     */
    protected function assertLineAccounts($accountIds, string $field = 'account'): void
    {
        $ids = collect((array) $accountIds)->filter(fn ($id) => filled($id))->map(fn ($id) => (int) $id);
        if ($ids->isEmpty()) {
            return;
        }
        $accounts = \App\Models\Finance\Account\Account::whereIn('id', $ids->unique())->get(['id', 'code', 'name', 'is_last'])->keyBy('id');
        $cashBank = \App\Models\Finance\Account\Account::query()->cashOrBank()->whereIn('accounts.id', $ids->unique())->pluck('accounts.id')->all();
        $control = \App\Models\Finance\Account\Account::whereIn('code', ['1130', '2110', '1150', '2130'])->pluck('id')->all();
        foreach ($ids as $n => $id) {
            $a = $accounts->get($id);
            if (!$a || !(int) $a->is_last || in_array($id, $cashBank, true) || in_array($id, $control, true)) {
                throw ValidationException::withMessages([$field => __('Line :n: the account :account cannot be used on a document line (cash / bank, receivable, payable, VAT or a group account).',
                    ['n' => $n + 1, 'account' => $a ? trim($a->code . ' ' . $a->name) : '#' . $id])]);
            }
        }
    }

    /**
     * Approving a document, or taking an approved one back (draft / cancel), writes or removes ledger entries on its
     * date: refused (422) when that date is in a closed period. Returns null when the change may go ahead.
     */
    protected function refuseInClosedPeriod($date, $previousStatus, $newStatus, int $approved): ?\Illuminate\Http\JsonResponse
    {
        $touchesLedger = (int) $newStatus === $approved || (int) $previousStatus === $approved;
        if (!$touchesLedger || (int) $newStatus === (int) $previousStatus || !PeriodClosing::isLocked($date)) {
            return null;
        }
        $lockedThrough = PeriodClosing::lockedThroughDate()->format('d-m-Y');

        return response()->json([
            'status' => 'error',
            'message' => __('This document is dated in a closed accounting period (locked through :date). Reopen the period to change its status.', ['date' => $lockedThrough]),
        ], 422);
    }
}
