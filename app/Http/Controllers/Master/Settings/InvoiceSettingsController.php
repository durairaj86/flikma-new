<?php

namespace App\Http\Controllers\Master\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings\InvoiceSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class InvoiceSettingsController extends Controller
{
    private static string $cache = 'invoice_settings:';

    /**
     * Show the form for editing the invoice settings.
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        // Fetch the invoice settings for the authenticated user/tenant
        $settings = InvoiceSettings::invoiceSettings();

        return view('modules.settings.invoice', compact('settings'));
    }

    /**
     * Store or update the invoice settings.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Get the current invoice settings
        $settings = InvoiceSettings::where('company_id', session('company_id'))->first();

        if (!$settings) {
            $settings = new InvoiceSettings();
            $settings->company_id = session('company_id');
        }

        // Update the settings from the request
        $settings->theme = $request->input('theme', 'stylish');
        $settings->primary_color = $request->input('primary_color', '#16a34a');

        // Update boolean settings
        $settings->party_balance = $request->boolean('party_balance');
        $settings->free_item_qty = $request->boolean('free_item_qty');
        $settings->item_description = $request->boolean('item_description');
        $settings->alt_unit = $request->boolean('alt_unit');
        $settings->show_phone = $request->boolean('show_phone');
        $settings->show_time = $request->boolean('show_time');

        // Invoice details
        $settings->awb_hbl = $request->boolean('awb_hbl');
        $settings->incoterm = $request->boolean('incoterm');
        $settings->pol_pod = $request->boolean('pol_pod');
        $settings->voyage_flight = $request->boolean('voyage_flight');
        $settings->shipment_mode = $request->boolean('shipment_mode');
        $settings->carrier = $request->boolean('carrier');

        // Item table columns
        $settings->hsn_sac = $request->boolean('hsn_sac');
        $settings->unit = $request->boolean('unit');
        $settings->rate = $request->boolean('rate');
        $settings->discount = $request->boolean('discount');
        $settings->currency = $request->boolean('currency');

        // Custom fields - structured as JSON
        $customFields = [
            'invoice_details' => $request->input('invoice_details', []),
            'party_details' => $request->input('party_details', []),
            'misc_details' => $request->input('misc_details', [])
        ];

        $settings->custom_fields = json_encode($customFields);

        try {
            $settings->save();

            // Clear the cache
            Cache::forget(self::$cache . cacheName());

            return response()->json([
                'status' => 'success',
                'message' => __('Invoice settings updated successfully'),
            ]);
        } catch (\Exception $e) {
            // Log the error and return a user-friendly message
            \Log::error('Invoice settings save failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => __('An error occurred while saving the invoice settings.'),
            ], 500);
        }
    }
}
