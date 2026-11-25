<?php

namespace App\Models\Crm;

use App\Traits\CompanyScopeTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * What a salesperson did or plans to do with a customer: "Call Done", "RFQ Received", "Quote Due" ... (master list, one per company,
 * the ACTIVITY LIST of the sales workbook). Every action is in a category; a "planned" action is a to-do on a future date, so an
 * activity with a planned action is a follow-up. The defaults carry a fixed code: the system logs some of them by itself
 * (an enquiry is "RFQ Received", an approved invoice "Invoice Issued") and finds them by code, so renaming one is safe.
 */
class CrmAction extends Model
{
    use CompanyScopeTrait;

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const PLANNED = 'planned';

    /** Category => label, colours (background / text, as in the workbook) and icon. The order is the order of the action picker. */
    public const CATEGORIES = [
        'outreach' => ['label' => 'Outreach', 'bg' => '#DCE6F1', 'fg' => '#1F3864', 'icon' => 'bi-telephone-outbound', 'meaning' => 'Contacts you made (mail, call, meeting)'],
        'quote' => ['label' => 'RFQ & Quote', 'bg' => '#F9CB9C', 'fg' => '#5B3A00', 'icon' => 'bi-file-earmark-text', 'meaning' => 'Enquiry-to-quotation stage'],
        'won' => ['label' => 'Won & Running', 'bg' => '#B6D7A8', 'fg' => '#1E4620', 'icon' => 'bi-trophy', 'meaning' => 'Job won or in progress'],
        'done' => ['label' => 'Completed', 'bg' => '#A4C2F4', 'fg' => '#10294D', 'icon' => 'bi-check2-circle', 'meaning' => 'Job finished'],
        'finance' => ['label' => 'Finance', 'bg' => '#D9D2E9', 'fg' => '#3B2A66', 'icon' => 'bi-cash-coin', 'meaning' => 'Invoice / payment'],
        'issue' => ['label' => 'Issue', 'bg' => '#D5A6BD', 'fg' => '#5A1E3E', 'icon' => 'bi-exclamation-triangle', 'meaning' => 'Problems & complicated cases'],
        'lost' => ['label' => 'Lost / Hold', 'bg' => '#EA9999', 'fg' => '#5C0000', 'icon' => 'bi-x-octagon', 'meaning' => 'Lost, cancelled or on hold'],
        'waiting' => ['label' => 'Waiting', 'bg' => '#E7E6E6', 'fg' => '#404040', 'icon' => 'bi-hourglass-split', 'meaning' => 'Waiting on others'],
        'planned' => ['label' => 'Planned', 'bg' => '#FFF2CC', 'fg' => '#7F6000', 'icon' => 'bi-calendar-event', 'meaning' => 'Future dates / to-do (not done yet)'],
    ];

    /** The workbook's activity list: [code, name, category, when to use it]. Copied to a company the first time it needs the list. */
    public const DEFAULTS = [
        ['intro_mail', 'Intro Mail', 'outreach', 'First introduction email sent to a new customer'],
        ['follow_up', 'Follow Up', 'outreach', 'General follow-up (any channel) on an open lead or RFQ'],
        ['mail_sent', 'Mail Sent', 'outreach', 'Any other email sent (rates, updates, documents)'],
        ['call_done', 'Call Done', 'outreach', 'Phone call made or received'],
        ['whatsapp', 'WhatsApp', 'outreach', 'WhatsApp / chat conversation'],
        ['meeting_done', 'Meeting Done', 'outreach', 'Meeting or customer visit completed'],
        ['rfq_received', 'RFQ Received', 'quote', 'Customer sent an enquiry / RFQ'],
        ['working_on_quote', 'Working on Quote', 'quote', 'Rates being collected / quote being prepared'],
        ['quote_sent', 'Quote Sent', 'quote', 'Quotation submitted to the customer'],
        ['re_quoted', 'Re-Quoted', 'quote', 'Revised quotation sent'],
        ['negotiating', 'Negotiating', 'quote', 'Rate / terms negotiation in progress'],
        ['confirmed', 'Confirmed', 'won', 'Customer confirmed the job (job won)'],
        ['job_running', 'Job Running', 'won', 'Shipment / job in progress'],
        ['job_done', 'Job Done', 'done', 'Job completed / delivered'],
        ['invoice_issued', 'Invoice Issued', 'finance', 'Invoice sent to the customer'],
        ['payment_pending', 'Payment Pending', 'finance', 'Invoice issued, payment not yet received'],
        ['payment_received', 'Payment Received', 'finance', 'Payment received from the customer'],
        ['issue_raised', 'Issue Raised', 'issue', 'Problem, delay, claim or complicated case'],
        ['lost', 'Lost / Cancelled', 'lost', 'Job or enquiry lost / cancelled'],
        ['on_hold', 'On Hold', 'lost', 'Customer put the job on hold'],
        ['awaiting_reply', 'Awaiting Reply', 'waiting', 'Waiting for customer / agent / carrier response'],
        ['no_response', 'No Response', 'waiting', 'Tried to contact, no reply'],
        ['meeting_booked', 'Meeting Booked', 'planned', 'Appointment fixed for this date'],
        ['meeting_requested', 'Meeting Requested', 'planned', 'Meeting requested, date not yet confirmed'],
        ['call_back_due', 'Call Back Due', 'planned', 'Customer asked you to call back on this date'],
        ['rfq_expected', 'RFQ Expected', 'planned', 'Customer said the RFQ will come on this date'],
        ['quote_due', 'Quote Due', 'planned', 'Quote must go out on this date'],
        ['follow_up_due', 'Follow-up Due', 'planned', 'Planned follow-up on this date'],
        ['payment_due', 'Payment Due', 'planned', 'Payment expected / to be chased on this date'],
    ];

    public function isPlanned(): bool
    {
        return $this->category === self::PLANNED;
    }

    /** The name as the user reads it: the default names are translated, a company's own names are shown as typed. */
    public function label(): string
    {
        return __($this->name);
    }

    public static function categoryLabel(?string $category): string
    {
        return __(self::CATEGORIES[$category]['label'] ?? ucfirst((string) $category));
    }

    /** Badge style of a category: background and text colour from the workbook. */
    public static function badgeStyle(?string $category): string
    {
        $c = self::CATEGORIES[$category] ?? self::CATEGORIES['waiting'];

        return 'background:' . $c['bg'] . ';color:' . $c['fg'] . ';';
    }

    public static function icon(?string $category): string
    {
        return self::CATEGORIES[$category]['icon'] ?? 'bi-dot';
    }

    /** Active actions of the current company, in picker order (category, then sort order); the defaults are added once. */
    public static function options()
    {
        // read once per request (a report asks for it for every row); saving an action clears it
        $companyId = (int) companyId();
        if (!isset(self::$optionsCache[$companyId])) {
            static::seedDefaults();
            $order = array_flip(array_keys(self::CATEGORIES));
            self::$optionsCache[$companyId] = static::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(['id', 'code', 'name', 'category'])
                ->sortBy(fn($a) => [$order[$a->category] ?? 99, $a->sort_order])->values();
        }

        return self::$optionsCache[$companyId];
    }

    private static array $optionsCache = [];

    protected static function booted(): void
    {
        static::saved(fn() => self::$optionsCache = []);
        static::deleted(fn() => self::$optionsCache = []);
    }

    /** The company's action with this default code (null when the company switched it off or deleted it). */
    public static function byCode(string $code, ?int $companyId = null): ?self
    {
        static $cache = [];
        $companyId ??= (int) companyId();
        if (!$companyId) {
            return null;
        }
        if (!array_key_exists($companyId, $cache)) {
            static::seedDefaults($companyId);
            $cache[$companyId] = static::withoutGlobalScopes()->where('company_id', $companyId)->whereNotNull('code')->get()->keyBy('code');
        }
        $action = $cache[$companyId][$code] ?? null;

        return $action && $action->is_active ? $action : null;
    }

    public static function seedDefaults(?int $companyId = null): void
    {
        $companyId ??= (int) companyId();
        if (!$companyId || static::withoutGlobalScopes()->where('company_id', $companyId)->exists()) {
            return;
        }
        foreach (self::DEFAULTS as $i => [$code, $name, $category, $description]) {
            $action = new static();
            $action->company_id = $companyId;
            $action->code = $code;
            $action->name = $name;
            $action->category = $category;
            $action->description = $description;
            $action->sort_order = $i + 1;
            $action->is_active = true;
            $action->user_id = auth()->id();
            $action->save();
        }
    }
}
