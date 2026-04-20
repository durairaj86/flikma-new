<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class InvoiceSettings extends Model
{
    protected $table = 'invoice_settings';

    protected $fillable = [
        'company_id',
        'theme',
        'primary_color',
        'party_balance',
        'free_item_qty',
        'item_description',
        'alt_unit',
        'show_phone',
        'show_time',
        'awb_hbl',
        'incoterm',
        'pol_pod',
        'voyage_flight',
        'shipment_mode',
        'carrier',
        'hsn_sac',
        'unit',
        'rate',
        'discount',
        'custom_fields'
    ];

    protected $casts = [
        'party_balance' => 'boolean',
        'free_item_qty' => 'boolean',
        'item_description' => 'boolean',
        'alt_unit' => 'boolean',
        'show_phone' => 'boolean',
        'show_time' => 'boolean',
        'awb_hbl' => 'boolean',
        'incoterm' => 'boolean',
        'pol_pod' => 'boolean',
        'voyage_flight' => 'boolean',
        'shipment_mode' => 'boolean',
        'carrier' => 'boolean',
        'hsn_sac' => 'boolean',
        'unit' => 'boolean',
        'rate' => 'boolean',
        'discount' => 'boolean',
        'custom_fields' => 'json',
    ];

    protected static string $cache = 'invoice_settings:';

    public static function invoiceSettings()
    {
        // Clear cache for testing if needed
        // Cache::forget(self::$cache . cacheName());

        return Cache::rememberForever(static::$cache . cacheName(), function () {
            return self::firstOrCreate(
                ['company_id' => session('company_id')],
                [
                    'company_id' => session('company_id'),
                    'theme' => 'stylish',
                    'primary_color' => '#0b6aa0',
                    'party_balance' => false,
                    'free_item_qty' => false,
                    'item_description' => true,
                    'alt_unit' => false,
                    'show_phone' => true,
                    'show_time' => false,
                    'awb_hbl' => false,
                    'incoterm' => false,
                    'pol_pod' => false,
                    'voyage_flight' => false,
                    'shipment_mode' => false,
                    'carrier' => false,
                    'hsn_sac' => false,
                    'unit' => true,
                    'rate' => true,
                    'discount' => false,
                    'custom_fields' => '[]'
                ]
            );
        });
    }
}
