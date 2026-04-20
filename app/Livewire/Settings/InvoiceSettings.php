<?php

namespace App\Livewire\Settings;

use App\Models\Settings\InvoiceSettings as InvoiceSettingsModel;
use App\Models\Master\Company;
use App\Models\Customer\Customer;
use App\Models\Job\Job;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;

class InvoiceSettings extends Component
{
    // Settings properties
    public $theme = 'stylish';
    public $primaryColor = '#0b6aa0';
    public $partyBalance = false;
    public $freeItemQty = false;
    public $itemDescription = true;
    public $altUnit = false;
    public $showPhone = true;
    public $showTime = false;
    public $awbHbl = false;
    public $incoterm = false;
    public $polPod = false;
    public $voyageFlight = false;
    public $shipmentMode = false;
    public $carrier = false;
    public $hsnSac = false;
    public $unit = true;
    public $rate = true;
    public $discount = false;

    // Custom fields as arrays
    public $invoiceDetails = [];
    public $partyDetails = [];
    public $miscDetails = [];

    // Preview data
    public $company;
    public $customer;
    public $job;
    public $customerInvoice;
    public $bank;

    // Cache key
    private static string $cache = 'invoice_settings:';

    public function mount()
    {
        // Load settings from database
        $settings = InvoiceSettingsModel::where('company_id', session('company_id'))->first();

        if ($settings) {
            $this->theme = $settings->theme;
            $this->primaryColor = $settings->primary_color;
            $this->partyBalance = $settings->party_balance;
            $this->freeItemQty = $settings->free_item_qty;
            $this->itemDescription = $settings->item_description;
            $this->altUnit = $settings->alt_unit;
            $this->showPhone = $settings->show_phone;
            $this->showTime = $settings->show_time;
            $this->awbHbl = $settings->awb_hbl;
            $this->incoterm = $settings->incoterm;
            $this->polPod = $settings->pol_pod;
            $this->voyageFlight = $settings->voyage_flight;
            $this->shipmentMode = $settings->shipment_mode;
            $this->carrier = $settings->carrier;
            $this->hsnSac = $settings->hsn_sac;
            $this->unit = $settings->unit;
            $this->rate = $settings->rate;
            $this->discount = $settings->discount;

            // Load custom fields from JSON
            if ($settings->custom_fields) {
                $customFields = json_decode($settings->custom_fields, true);
                $this->invoiceDetails = $customFields['invoice_details'] ?? [];
                $this->partyDetails = $customFields['party_details'] ?? [];
                $this->miscDetails = $customFields['misc_details'] ?? [];
            }
        }

        // Initialize preview data
        $this->initializePreviewData();
    }

    public function initializePreviewData()
    {
        // Get company data
        $this->company = Company::companies();

        // Get a sample customer for preview
        $this->customer = Customer::first();

        // Get a sample job for preview
        $this->job = Job::first();

        // Create a mock customer invoice for preview
        $this->customerInvoice = (object)[
            'status' => 0,
            'row_no' => 'INV-12345',
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'currency' => 'SAR',
            'sub_total' => 1000,
            'tax_total' => 150,
            'grand_total' => 1150,
            'customer' => $this->customer,
            'job' => $this->job,
            'customerInvoiceSubs' => [
                (object)[
                    'description' => 'Service Item',
                    'comment' => 'Sample comment',
                    'unit_price' => 1000,
                    'quantity' => 1,
                    'total' => 1000,
                    'tax_percent' => 15,
                    'tax_amount' => 150,
                    'total_with_tax' => 1150
                ]
            ]
        ];

        // Mock bank details
        $this->bank = (object)[
            'account_holder' => 'Company Name',
            'account_holder_arabic' => 'اسم الشركة',
            'bank_name' => 'Sample Bank',
            'account_number' => '1234567890',
            'iban_code' => 'SA1234567890123456789012',
            'bank_address' => 'Sample Bank Address',
            'swift_code' => 'SAMPLECODE'
        ];
    }

    public function updated($name)
    {
        $this->saveSettings();
    }

    public function saveSettings()
    {
        // Get or create settings model
        $settings = InvoiceSettingsModel::where('company_id', session('company_id'))->first();

        if (!$settings) {
            $settings = new InvoiceSettingsModel();
            $settings->company_id = session('company_id');
        }

        // Update settings from properties
        $settings->theme = $this->theme;
        $settings->primary_color = $this->primaryColor;
        $settings->party_balance = $this->partyBalance;
        $settings->free_item_qty = $this->freeItemQty;
        $settings->item_description = $this->itemDescription;
        $settings->alt_unit = $this->altUnit;
        $settings->show_phone = $this->showPhone;
        $settings->show_time = $this->showTime;
        $settings->awb_hbl = $this->awbHbl;
        $settings->incoterm = $this->incoterm;
        $settings->pol_pod = $this->polPod;
        $settings->voyage_flight = $this->voyageFlight;
        $settings->shipment_mode = $this->shipmentMode;
        $settings->carrier = $this->carrier;
        $settings->hsn_sac = $this->hsnSac;
        $settings->unit = $this->unit;
        $settings->rate = $this->rate;
        $settings->discount = $this->discount;

        // Save custom fields as JSON
        $customFields = [
            'invoice_details' => $this->invoiceDetails,
            'party_details' => $this->partyDetails,
            'misc_details' => $this->miscDetails
        ];

        $settings->custom_fields = json_encode($customFields);

        try {
            $settings->save();

            // Clear the cache
            Cache::forget(self::$cache . cacheName());

            // Show success message
            $this->dispatch('settings-saved', ['message' => 'Invoice settings updated successfully']);
        } catch (\Exception $e) {
            // Show error message
            $this->dispatch('settings-error', ['message' => 'Error saving settings: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.settings.invoice-settings');
    }
}
