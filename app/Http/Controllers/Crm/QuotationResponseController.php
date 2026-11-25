<?php

namespace App\Http\Controllers\Crm;

use App\Enums\QuotationEnum;
use App\Http\Controllers\Controller;
use App\Models\Crm\CrmActivity;
use App\Models\Quotation\Quotation;
use App\Models\User;
use App\Services\Mail\EmailQueueService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The customer's page behind the link in the quotation email (no login, found by its random token):
 * they see the quotation and accept or reject it. Accepting marks it Accepted (the job is still made by
 * the salesperson); rejecting keeps it pending so the salesperson can revise it or close it with a reason.
 * Either way the salesperson gets a follow-up and an email.
 */
class QuotationResponseController extends Controller
{
    /** The token for a quotation's link (made once, kept until the quotation is revised). */
    public static function tokenFor(Quotation $quotation): string
    {
        if (!$quotation->customer_token) {
            $quotation->customer_token = Str::random(40);
            Quotation::withoutGlobalScopes()->whereKey($quotation->id)->update(['customer_token' => $quotation->customer_token]);
        }

        return $quotation->customer_token;
    }

    public static function urlFor(Quotation $quotation): string
    {
        return url('/q/' . self::tokenFor($quotation));
    }

    public function show(Request $request, string $token)
    {
        $this->locale($request);
        $quotation = $this->find($token);

        return view('modules.crm.quotation-response', $this->pageData($quotation));
    }

    public function respond(Request $request, string $token)
    {
        $this->locale($request);
        $quotation = $this->find($token);
        $request->validate([
            'decision' => ['required', 'in:accept,reject'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        $back = url('/q/' . $token) . ($request->input('lang') === 'ar' ? '?lang=ar' : '');
        if ($why = $this->closedReason($quotation)) {
            return redirect()->to($back)->with('notice', $why);
        }

        $accepted = $request->input('decision') === 'accept';
        $note = trim((string) $request->input('note')) ?: null;
        $now = CrmActivity::localNowNaive((int) $quotation->company_id);

        DB::transaction(function () use ($quotation, $accepted, $note, $now) {
            $quotation->customer_status = $accepted ? Quotation::CUSTOMER_ACCEPTED : Quotation::CUSTOMER_REJECTED;
            $quotation->customer_response_note = $note;
            $quotation->customer_response_at = $now;
            if ($accepted) {
                $quotation->status = QuotationEnum::ACCEPTED->value;
            }
            $quotation->save();

            // an acceptance is logged as "Confirmed" by the status change (CrmAutoLog); a rejection is logged here
            $quoted = "\"{$note}\"";
            if (!$accepted) {
                CrmActivity::record((int) $quotation->company_id, 'quotation', $quotation->id, 'negotiating',
                    'The customer rejected the quotation online.' . ($note ? "\n" . $quoted : ''));
            }
            // the salesperson's next step lands in their Follow-ups (and morning email)
            if ($owner = $quotation->followUpOwnerId()) {
                CrmActivity::plan((int) $quotation->company_id, 'quotation', $quotation->id, $accepted ? 'follow_up_due' : 'call_back_due', $now,
                    ($accepted ? 'The customer accepted online: convert the quotation to a job' : 'The customer rejected online: call them, revise the quotation or close it')
                    . ($note ? "\n" . $quoted : ''), $owner);
            }
        });

        $this->notifyOwner($quotation, $accepted, $note);

        return redirect()->to($back)
            ->with('notice', $accepted ? __('Thank you. Your acceptance has been sent; we will contact you to arrange the shipment.')
                : __('Thank you for letting us know. Your answer has been sent to our sales team.'));
    }

    // ───────────────────────── helpers ─────────────────────────

    private function find(string $token): Quotation
    {
        return Quotation::withoutGlobalScopes()->with(['charges', 'containers', 'packages'])
            ->whereNull('deleted_at')->where('customer_token', $token)->firstOrFail();
    }

    /** Why the customer can no longer answer (null = they can). */
    private function closedReason(Quotation $quotation): ?string
    {
        if ((int) $quotation->customer_status !== Quotation::CUSTOMER_NO_ANSWER) {
            return __('Your answer has already been received.');
        }

        return match ((int) $quotation->status) {
            QuotationEnum::PENDING->value => $this->isPastValidity($quotation) ? __('This quotation has expired. Please contact us for a new rate.') : null,
            QuotationEnum::EXPIRED->value => __('This quotation has expired. Please contact us for a new rate.'),
            QuotationEnum::REVISED->value => __('This quotation has been replaced by a newer version. Please use the link in our latest email.'),
            QuotationEnum::CANCELLED->value => __('This quotation is no longer open. Please contact us.'),
            default => __('This quotation has already been confirmed.'),
        };
    }

    private function isPastValidity(Quotation $quotation): bool
    {
        if (!$quotation->valid_until) {
            return false;
        }

        return Carbon::createFromFormat('d-m-Y', $quotation->valid_until)->startOfDay()
            ->lt(CrmActivity::localNowNaive((int) $quotation->company_id)->startOfDay());
    }

    private function pageData(Quotation $quotation): array
    {
        $company = DB::table('companies')->where('id', $quotation->company_id)->first(['name', 'name_ar', 'email', 'phone', 'logo', 'base_currency']);
        $party = $quotation->customer_id
            ? DB::table('customers')->where('id', $quotation->customer_id)->value('name_en')
            : DB::table('prospects')->where('id', $quotation->prospect_id)->value('name');

        return [
            'quotation' => $quotation,
            'company' => $company,
            'party' => $party,
            'total' => $quotation->chargesTotal(),
            'currency' => $company->base_currency ?? 'SAR',
            'closed' => $this->closedReason($quotation),
            'rtl' => App::getLocale() === 'ar',
        ];
    }

    /** Customers may read the page in Arabic (?lang=ar); it has no session of its own to keep that in. */
    private function locale(Request $request): void
    {
        if (in_array($request->input('lang'), ['en', 'ar'], true)) {
            App::setLocale($request->input('lang'));
        }
    }

    private function notifyOwner(Quotation $quotation, bool $accepted, ?string $note): void
    {
        $owner = $quotation->followUpOwnerId() ? User::find($quotation->followUpOwnerId()) : null;
        if (!$owner || !filter_var($owner->email, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        $body = ($accepted ? "The customer accepted quotation {$quotation->row_no} online." : "The customer rejected quotation {$quotation->row_no} online.")
            . ($note ? "\n\nTheir note: {$note}" : '')
            . ($accepted ? "\n\nNext step: convert it to a job." : "\n\nNext step: call them, then revise the quotation or close it with a lost reason.");
        app(EmailQueueService::class)->enqueue(
            $owner->email,
            ($accepted ? 'Accepted: ' : 'Rejected: ') . $quotation->row_no,
            'emails.generic',
            ['body' => $body, 'action_url' => url('/sales/quotations?open=' . $quotation->id), 'action_label' => 'Open the quotation'],
            ['type' => 'quotation-response', 'company_id' => $quotation->company_id]
        );
    }
}
