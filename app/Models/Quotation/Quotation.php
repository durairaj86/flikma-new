<?php

namespace App\Models\Quotation;

use App\Models\Customer\Customer;
use App\Models\Master\LogisticActivity;
use App\Models\Prospect\Prospect;
use App\Models\Master\Salesperson\Salesperson;
use App\Traits\Log\LogHistoryTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\CompanyScopeTrait;

class Quotation extends Model
{
    use CompanyScopeTrait, LogHistoryTrait;

    protected $fillable = [
        'customer_id', 'posted_at', 'valid_until', 'services', 'salesman',
        'prepared_by', 'quotation_type', 'shipment_mode', 'shipment_category',
        'incoterm', 'pol', 'pod', 'place_of_receipt', 'place_of_delivery',
        'final_destination', 'carrier', 'terms', 'shipper', 'volume', 'pickup_date',
        'prospect_id',
        // Container / Consignment header fields
        'department', 'vessel_name', 'voyage_no', 'no_of_pcs',
        'gross_weight', 'weight_unit', 'volume_weight', 'volume_unit',
        'chargeable_unit', 'hs_code', 'cargo_description', 'consignment_remarks',
        'container_type', 'no_of_containers',
    ];

    protected $casts = [
        'services' => 'array',
        'customer_response_at' => 'datetime',
        'expiry_reminded_at' => 'datetime',
    ];

    /** customer_status: the customer's own answer from the accept / reject link in the quotation email. */
    public const CUSTOMER_NO_ANSWER = 0;
    public const CUSTOMER_ACCEPTED = 1;
    public const CUSTOMER_REJECTED = 2;

    /** Days before valid_until when the salesperson gets an "expires soon" follow-up. */
    public const EXPIRY_REMINDER_DAYS = 3;

    /** The first quotation of a revision chain (revisions point at it with parent_quotation_id). */
    public function rootId(): int
    {
        return (int) ($this->parent_quotation_id ?: $this->id);
    }

    /** Every version of this quotation, oldest first (the original and its -R1, -R2 ...). */
    public function revisionChain()
    {
        $root = $this->rootId();

        return static::where(fn($q) => $q->whereKey($root)->orWhere('parent_quotation_id', $root))
            ->orderBy('revision_count')->orderBy('id')
            ->get(['id', 'row_no', 'status', 'revision_count', 'revision_notes', 'posted_at', 'valid_until', 'created_at']);
    }

    /** Who follows this quotation up: the salesperson's user, else whoever created it. */
    public function followUpOwnerId(): ?int
    {
        $userId = $this->salesperson_id
            ? Salesperson::withoutGlobalScopes()->whereKey($this->salesperson_id)->value('user_id')
            : null;

        return (int) ($userId ?: $this->user_id) ?: null;
    }

    /** Quotation total in the company currency: the sum of its charge lines (grand_total is not kept up to date). */
    public function chargesTotal(): float
    {
        return (float) ($this->relationLoaded('charges') ? $this->charges->sum('local_amount') : $this->charges()->sum('local_amount'));
    }

    public function containers()
    {
        return $this->hasMany(QuotationContainer::class);
    }

    public function packages()
    {
        return $this->hasMany(QuotationPackage::class);
    }

    public function charges()
    {
        return $this->hasMany(QuotationCharge::class)->orderBy('line_no');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(Salesperson::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(LogisticActivity::class);
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    protected function postedAt(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    /**
     * Mutator for valid_until.
     */
    protected function validUntil(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    /**
     * Mutator for pickup_date.
     */
    protected function pickupDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : null,
            set: fn($value) => $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null,
        );
    }
}
