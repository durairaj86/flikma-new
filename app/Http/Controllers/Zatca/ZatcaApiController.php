<?php

namespace App\Http\Controllers\Zatca;

use App\Enums\TaxSubmit;
use App\Http\Controllers\Controller;

//use App\Model\ZatcaHistory;
use App\Traits\Zatca\InvoiceSign;
use Illuminate\Support\Facades\Http;

class ZatcaApiController extends Controller
{
    use InvoiceSign;

    public function generateComplianceWithCSR($csr, $otp, $mode)
    {
        $complianceCSIDapi = Http::withHeaders([
            'accept' => 'application/json',
            'accept-language' => 'en',
            'Clearance-Status' => 0,
            'Accept-Version' => 'V2',
            'Content-Type' => 'application/json',
            'OTP' => $otp,
        ])->post('https://gw-fatoora.zatca.gov.sa/e-invoicing/' . $mode . '/compliance', [   //simulation
            'csr' => base64_encode($csr),
        ]);
        return $complianceCSIDapi->json();
    }

    public function complianceCheckApi(string $signed_invoice_string, string $invoice_hash, string $uuid, $basic, $mode)
    {
        /*$certificate_stripped = $this->cleanUpCertificateString($certificate);
        $certificate_stripped = base64_encode($certificate_stripped);
        $basic = base64_encode($certificate_stripped . ':' . $secret);*/

        return Http::withHeaders([
            //'accept' => 'application/json',
            'accept-language' => 'en',
            //'Clearance-Status' => 0,
            'Accept-Version' => 'V2',
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . $basic,
        ])->post('https://gw-fatoora.zatca.gov.sa/e-invoicing/' . $mode . '/compliance/invoices', [
            'invoiceHash' => $invoice_hash,
            'uuid' => $uuid,
            'invoice' => base64_encode($signed_invoice_string),
        ]);
    }

    public function productionCSIDApi($basic, $requestId, $mode)
    {
        return Http::withHeaders([
            //'accept' => 'application/json',
            'accept-language' => 'en',
            //'Clearance-Status' => 0,
            'Accept-Version' => 'V2',
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . $basic,
        ])->post('https://gw-fatoora.zatca.gov.sa/e-invoicing/' . $mode . '/production/csids', [
            'compliance_request_id' => $requestId,
        ]);
    }

    public function taxSubmitApi(string $signedInvoiceString, string $invoiceHash, string $uuid, $basic, $invoiceCategory, $mode)
    {
        $urlString = 'https://gw-fatoora.zatca.gov.sa/e-invoicing/' . $mode;
        $url = $invoiceCategory == TaxSubmit::SIMPLIFIED_TAX_INVOICE_TEXT ? $urlString . '/invoices/reporting/single' : $urlString . '/invoices/clearance/single';

        return \Illuminate\Support\Facades\Http::withHeaders([
            //'accept' => 'application/json',
            'accept-language' => 'en',
            'Clearance-Status' => 0,
            'Accept-Version' => 'V2',
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . $basic,
        ])->post($url, [
            'invoiceHash' => $invoiceHash,
            'uuid' => $uuid,
            'invoice' => base64_encode($signedInvoiceString),
        ]);
    }

}
