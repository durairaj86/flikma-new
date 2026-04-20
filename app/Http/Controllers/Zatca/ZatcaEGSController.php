<?php

namespace App\Http\Controllers\Zatca;

use App\Enums\TaxSubmit;
use App\Enums\Zatca;
use App\Http\Controllers\Controller;

//use App\Model\ZatcaRegisterDetails;
use App\Models\Master\Company;
use App\Models\Zatca\ZatcaConfig;
use App\Traits\Zatca\InvoiceSign;
use App\Traits\Zatca\ZatcaCsr;
use Cache;

//use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Mockery\Exception;

//use Storage;

class ZatcaEGSController extends Controller
{
    use ZatcaCsr, InvoiceSign;

    public $egs_info;

    //public $production = false;

    public function init($companyEgsDetails)
    {
        $uuid = (string)Str::orderedUuid();
        $this->egs_info = [
            'uuid' => $uuid,
            'custom_id' => 'AD-' . strtoupper(str_replace(' ', '', $companyEgsDetails['city'])),
            'model' => 'V2.1',
            'CRN_number' => $companyEgsDetails->cr_number,
            'VAT_name' => str_replace('&', 'and', $companyEgsDetails->name),
            'VAT_number' => $companyEgsDetails->tax_number,
            'location' => [
                'city' => $companyEgsDetails->city,
                'city_subdivision' => $companyEgsDetails->city_sub_division,
                'street' => str_replace('&', 'and', $companyEgsDetails->address_1),
                'plot_identification' => $companyEgsDetails->plot_no,
                'building' => $companyEgsDetails->building_no,
                'postal_zone' => $companyEgsDetails->postal_code,
            ],
            'branch_name' => $companyEgsDetails->cr_number,
            'branch_industry' => 'Logistic',
        ];
    }

    public function zatcaDeviceRegister()
    {
        $companyEgsDetails = Company::find(companyId());
        //$zatcaMode = zatcaMode();

//dd($companyEgsDetails);
        if (blank($companyEgsDetails->wave) || blank($companyEgsDetails->plot_no) || blank($companyEgsDetails->building_no) || blank($companyEgsDetails->city_sub_division)) {
            return errorResponse('Before simulation validation. Please fill ZATCA wave, Plot Identification number, Building number, city subdivision in company profile');
        }

        $this->init($companyEgsDetails);
        $request = \Illuminate\Support\Facades\Request::all();
        if (isset($request['simulation_otp']) && filled($request['simulation_otp'])) {
            $otp = $request['simulation_otp'];
            $zatcaMode = \App\Enums\Zatca::SIMULATION_TEXT;
        } elseif (isset($request['core_otp']) && filled($request['core_otp'])) {
            $otp = $request['core_otp'];
            $zatcaMode = \App\Enums\Zatca::CORE_TEXT;
        } else {
            $otp = '123456';
            $zatcaMode = 'developer-portal';
        }

        list($private_key, $csr) = $this->generateNewKeysAndCSR('FLIKMA-ZKT-V0.1', $zatcaMode);

        $complianceCSIDApiResponse = (new ZatcaApiController())->generateComplianceWithCSR($csr, $otp, $zatcaMode);

        if (isset($complianceCSIDApiResponse['errors'])) {
            return errorResponse($complianceCSIDApiResponse['errors'][0]['message'], $complianceCSIDApiResponse['errors'][0]['code']);
        }

        $z = $this->complianceCheck(
            $complianceCSIDApiResponse['requestID'],
            base64_decode($complianceCSIDApiResponse['binarySecurityToken']),
            $complianceCSIDApiResponse['secret'],
            $private_key,
            $zatcaMode
        );
        if ($z && isset($z->dispositionMessage) && $z->dispositionMessage == 'ISSUED') {
            return successResponse('ZATCA Onboarded Successfully');
        }
        dd($z);
    }

    private function complianceCheck($request_id, $binary_security_token, $secret, $private_key, $mode)
    {
        //$private_key = $this->generateSecp256k1KeyPair();
        //$previousInvoiceHash = 'OX5NPMUdqy0j3NMFHpIP7SGAtlnF2RhUIE/XHS6aKKs=';
        $invoice = $this->sampleInvoiceLineItems();
        $invoiceMainTypes = [TaxSubmit::SIMPLIFIED_TAX_INVOICE_TEXT, TaxSubmit::TAX_INVOICE_TEXT];
        foreach ($invoiceMainTypes as $invoiceMainType) {
            $invoice['category'] = $invoiceMainType;
            $invoiceTypes = [
                'INVOICE', 'CREDIT_NOTE', 'DEBIT_NOTE',
            ];
            foreach ($invoiceTypes as $invoiceType) {
                $uuid = (string)Str::orderedUuid();
                $this->egs_info['uuid'] = $uuid;
                $this->egs_info['cancelation'] = [
                    'cancelation_type' => $invoiceType,
                    'canceled_invoice_number' => $invoiceType == 'INVOICE' ? '' : 'INV001',
                ];
                list($signed_invoice_string, $invoice_hash, $qr) = $this->signInvoice($invoice, $this->egs_info, $binary_security_token, $private_key, 1);
                $certificate_stripped = $this->cleanUpCertificateString($binary_security_token);
                $certificate_stripped = base64_encode($certificate_stripped);
                $basic = base64_encode($certificate_stripped . ':' . $secret);

                // Check invoice compliance
                $response = $this->checkInvoiceCompliance(
                    $signed_invoice_string,
                    $invoice_hash,
                    $basic,
                    $this->egs_info,
                    $mode
                );
                $jsonResponse = json_decode($response);
                if ($jsonResponse->clearanceStatus !== 'CLEARED' && $jsonResponse->reportingStatus !== 'REPORTED') {
                    dd('Failed to compliance check type : ' . $invoiceType);
                }
            }
        }
        $response = (new ZatcaApiController())->productionCSIDApi($basic, $request_id, $mode);
        $productionResponse = json_decode($response);
        if (isset($productionResponse->dispositionMessage) && $productionResponse->dispositionMessage == 'ISSUED') {
            $createdDate = date('Y-m-d H:i:s');
            ZatcaConfig::updateOrCreate(
                ['company_id' => companyId()],
                [
                    'company_id' => companyId(),
                    'uuid' => $this->egs_info['uuid'],
                    'egs_details' => json_encode($this->egs_info),
                    'request_id' => $productionResponse->requestID,
                    'binary_security_token' => base64_decode($productionResponse->binarySecurityToken),
                    'secret' => $productionResponse->secret,
                    'private_key' => $private_key,
                    'status' => $mode == Zatca::SIMULATION_TEXT ? Zatca::SIMULATION_MODE : Zatca::CORE_MODE,
                    'created_at' => $createdDate,
                    'updated_at' => $createdDate,
                ]
            );
            if ($mode == Zatca::CORE_TEXT || $mode == Zatca::SIMULATION_TEXT || isTestingDomain()) {
                DB::table('companies')->where('id', companyId())->update(['zatca_registered' => 1]);
                Cache::forget('company:' . cacheName());
            }
        } else {
            $productionResponse = $productionResponse->message;
        }
        return $productionResponse;
    }

    public function checkInvoiceCompliance(string $signed_invoice_string, string $invoice_hash, string $basic, array $egs_unit, $mode): string
    {
        if (!$basic) {
            throw new Exception('EGS is missing a certificate/private key/api secret to check the invoice compliance.');
        }
        return (new ZatcaApiController())->complianceCheckApi($signed_invoice_string, $invoice_hash, $egs_unit['uuid'], $basic, $mode);
    }

    public function sampleInvoiceLineItems(): array
    {
        $line_item = [];
        $line_item[0] = [
            'id' => 1,
            'name' => 'cake',
            'quantity' => 1,
            /* 'tax_exclusive_price' => abs(round($item->rate, 2) * round($item->generalCustomerInvoice->currency_rate, 2)),
             'VAT_percent' => round($item->service_tax, 2) / 100,*/
            'tax_exclusive_price' => 1000.0,
            'VAT_percent' => 0.15,
            'service_type' => 'STANDARD',
            'other_taxes' => [],
            'discounts' => [],
            /*'discounts' => [
                ['amount' => 0, 'reason' => 'No discount'],
            ],*/
        ];
        $line_item[1] = [
            'id' => 2,
            'name' => 'bun',
            'quantity' => 1,
            /* 'tax_exclusive_price' => abs(round($item->rate, 2) * round($item->generalCustomerInvoice->currency_rate, 2)),
             'VAT_percent' => round($item->service_tax, 2) / 100,*/
            'tax_exclusive_price' => 500.0,
            'VAT_percent' => 0,
            'service_type' => 'ZERO',
            'other_taxes' => [],
            'discounts' => [],
            /*'discounts' => [
                ['amount' => 0, 'reason' => 'No discount'],
            ],*/
        ];
        /*$line_item[2] = [
            'id' => 2,
            'name' => 'cake2',
            'quantity' => 1,
            'tax_exclusive_price' => 500.0,
            'VAT_percent' => 0,
            'service_type' => 5,
            'other_taxes' => [],
            'discounts' => [],
        ];*/
        $customer = [
            'name_en' => 'Test Customer',
            'address1_en' => 'Palasteen St', //street
            'building_number' => '2845', //street
            'plot_no' => '2845', //street
            'city_sub_division' => 'Riyadh', //street
            'city_en' => 'Riyadh',
            'postal_code' => 27387,
            'country' => 'Saudi Arabia',
            'vat_number' => 308989898989893,
            'cr_number' => 4030409954
        ];
        return [
            'invoice_counter_number' => 1,
            'invoice_serial_number' => 'INV001',
            'issue_date' => date('Y-m-d'),
            'issue_time' => date('H:i:s'),
            'previous_invoice_hash' => 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==',
            'line_items' => $line_item,
            'customer' => $customer
        ];
    }


}
