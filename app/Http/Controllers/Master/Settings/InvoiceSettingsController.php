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
        $settings->primary_color = $request->input('primary_color', '#0b6aa0');

        // Update boolean settings
        $settings->party_balance = $request->has('party_balance');
        $settings->free_item_qty = $request->has('free_item_qty');
        $settings->item_description = $request->has('item_description');
        $settings->alt_unit = $request->has('alt_unit');
        $settings->show_phone = $request->has('show_phone');
        $settings->show_time = $request->has('show_time');

        // Invoice details
        $settings->awb_hbl = $request->has('awb_hbl');
        $settings->incoterm = $request->has('incoterm');
        $settings->pol_pod = $request->has('pol_pod');
        $settings->voyage_flight = $request->has('voyage_flight');
        $settings->shipment_mode = $request->has('shipment_mode');
        $settings->carrier = $request->has('carrier');

        // Item table columns
        $settings->hsn_sac = $request->has('hsn_sac');
        $settings->unit = $request->has('unit');
        $settings->rate = $request->has('rate');
        $settings->discount = $request->has('discount');

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
                'message' => 'Invoice settings updated successfully',
            ]);
        } catch (\Exception $e) {
            // Log the error and return a user-friendly message
            \Log::error('Invoice settings save failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while saving the invoice settings.',
            ], 500);
        }
    }
}
