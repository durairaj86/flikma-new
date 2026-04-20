<?php

namespace App\Http\Controllers\Zatca;

use App\Enums\TaxSubmit;
use App\Enums\Zatca;
use App\Http\Controllers\Controller;
use App\Model\Finance\Finance;
use App\Model\Finance\FinanceSub;

//use App\Model\Settings\Codes\Operation\DescriptionCode;
use App\Models\Finance\Adjustment\CreditNote;
use App\Models\Finance\CustomerInvoice\CustomerInvoice;
use App\Models\Zatca\ZatcaConfig;
use App\Models\Zatca\ZatcaHistory;
use App\Traits\Zatca\InvoiceSign;
use App\Traits\Zatca\ZatcaCsr;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class ZatcaController extends Controller
{
    use ZatcaCsr, InvoiceSign;

    public function submitTax($invoice, $submitType = 'invoice')
    {
        $mode = zatcaMode();
        $invoiceId = $invoice->id;
        $table = $invoice->getTable();
        if ($submitType == 'invoice') {
            $invoiceType = TaxSubmit::INVOICE_TEXT;
        } elseif ($submitType == 'credit-note') {
            $invoiceType = TaxSubmit::CREDIT_NOTE_TEXT;
        } else {
            throw new Exception('Submit only invoice or credit note');
        }

        $customer = $invoice->customer;
        $invoiceCategory = $customer->business_type == 'registered' ? TaxSubmit::TAX_INVOICE_TEXT : TaxSubmit::SIMPLIFIED_TAX_INVOICE_TEXT;

        if ($table == 'customer_invoices') {
            $subTable = 'customerInvoiceSubs';
            $canceledInvoice = '';
        } elseif ($table == 'credit_notes') {
            $subTable = 'creditNoteSubs';
            $invoice_number = CustomerInvoice::select('row_no')->findOrFail($invoice->invoice_id);
            $canceledInvoice = $invoice_number->row_no;
        }
        $invoice->load([
            $subTable => function ($q) {
                $q->with('description');
            },
        ]);

        $ZOC = new ZatcaEGSController();

        $lineItems = [];
        foreach ($invoice->$subTable as $item) {
            if ($item->quantity > 0) {
                $lineItems[] = [
                    'id' => $item->description_id,
                    'name' => str_replace('&', '&amp;', optional($item->description)->description ?? 'Unknown'),//'Samsung Tv',
                    //'name' => DescriptionCode::select('id', 'description','description_local')->find($item->description_id),
                    'quantity' => (float)$item->quantity ?? 1, //54,
                    'tax_exclusive_price' => abs(round($item->base_unit_price, 2) * round(1, 2)),
                    'VAT_percent' => round($item->tax_percent, 2) / 100,
                    'service_type' => $item->tax_code,
                    /*'tax_exclusive_price' => 1000.0,
                    'VAT_percent' => 0.5,*/
                    'other_taxes' => [],
                    'discounts' => []
                ];
            }
        }

        $totalCount = ZatcaHistory::where('company_id', companyId())->count();
        $previousInvoiceHash = ZatcaHistory::select('invoice_hash')
            ->where('company_id', companyId())
            ->whereIn('status', ['REPORTED', 'CLEARED'])
            //->orderBy('id', 'DESC')
            ->latest('updated_at')
            ->pluck('invoice_hash')
            ->first();

        $zatcaConfig = ZatcaConfig::where('company_id', companyId())
            ->where('status', zatcaMode() == Zatca::SIMULATION_TEXT ? Zatca::SIMULATION_MODE : Zatca::CORE_MODE)
            ->orderBy('id', 'DESC')
            ->first();

        if ($mode == 'developer-portal' || $mode == Zatca::SIMULATION_TEXT) {
            $previousInvoiceHash = 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==';
        }

        if (!$zatcaConfig) {
            throw new Exception('EGS Details is missing, Do ZATCA registration from settings');
        }
        $egs = json_decode($zatcaConfig->egs_details, true);

        $invoiceArray = [
            'invoice_counter_number' => $totalCount + 1 ?? 1,
            'invoice_serial_number' => $invoice->row_no,
            'issue_date' => date('Y-m-d'),
            'issue_time' => date('H:i:s'),
            'previous_invoice_hash' => $previousInvoiceHash ?? 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==', // 'S8IqHiwkdb0O0sgu25+28wkG/yWW/5P1qDu76g8UzhM=' /*$previousInvoiceHash ?? */'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==', //'5feceb66ffc86f38d952786c6d696c79c2dbc239dd4e91b46729d73a27fb57e9', // 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==',    //$previousInvoiceHash'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==', // AdditionalDocumentReference/PIH
            'line_items' => $lineItems,
            'category' => $invoiceCategory,
            'customer' => $customer
        ];

        /*if($invoiceType != TaxSubmit::INVOICE_TEXT){
            $canceledInvoice = 'EGS1-886431145-2';
        }*/

        $egs['cancelation'] = [
            'cancelation_type' => $invoiceType,
            'canceled_invoice_number' => $invoiceType != TaxSubmit::INVOICE_TEXT ? $canceledInvoice : '',
        ];

        // Sign invoice
        list($signedInvoiceString, $invoiceHash, $qr) = $ZOC->signInvoice($invoiceArray, $egs, $zatcaConfig->binary_security_token, $zatcaConfig->private_key);

        // reporting/clearance

        $certificate_stripped = $this->cleanUpCertificateString($zatcaConfig->binary_security_token);
        $certificate_stripped = base64_encode($certificate_stripped);
        $basic = base64_encode($certificate_stripped . ':' . $zatcaConfig->secret);

        $ZAC = new ZatcaApiController();
        $taxSubmissionResponse = $ZAC->taxSubmitApi(
            $signedInvoiceString,
            $invoiceHash,
            $egs['uuid'],
            $basic,
            $invoiceCategory,
            $mode
        );
        $jsonResponse = json_decode($taxSubmissionResponse);
//dd($jsonResponse,$signedInvoiceString);
        if ($jsonResponse) {
            if (isset($jsonResponse->message)) {
                return errorResponse([$jsonResponse->message, 'ZATCA Error']);
            }

            $submittedStatus = $invoiceCategory == TaxSubmit::TAX_INVOICE_TEXT ? $jsonResponse->clearanceStatus : $jsonResponse->reportingStatus;
            $finalQr = $invoiceCategory == TaxSubmit::TAX_INVOICE_TEXT ? $this->pureQrCodeString($jsonResponse->clearedInvoice) : $qr;

            $history = new ZatcaHistory();
            $history->company_id = companyId();
            $history->invoice_hash = $invoiceHash;
            $history->signed_invoice_string = $invoiceCategory == TaxSubmit::TAX_INVOICE_TEXT ? $jsonResponse->clearedInvoice : base64_encode($signedInvoiceString);
            $history->qr = $finalQr;
            $history->status = $submittedStatus;
            $history->response = json_encode($jsonResponse->validationResults);
            $history->submitted_json = actionJson();
            $invoice->zatcaHistory()->save($history);
            $message = '';
            $validationStatus = $jsonResponse->validationResults->status;
            if ($validationStatus == 'ERROR') {
                foreach ($jsonResponse->validationResults->errorMessages as $error) {
                    $errorMessage = commonZatcaErrors($error->code) ?? $error->message;
                    $message .= nl2br($errorMessage . ".\n");
                }
            } elseif ($validationStatus == 'WARNING') {
                foreach ($jsonResponse->validationResults->warningMessages as $warning) {
                    $warningMessage = commonZatcaErrors($warning->code) ?? $warning->message;
                    $message .= nl2br("\n" . $warningMessage);
                }
            }
            //$jsonResponse->validationResults->status == 'WARNING'

            $postingDate = date('Y-m-d');
            if (in_array($submittedStatus, [TaxSubmit::REPORTED_STATUS, TaxSubmit::CLEARED_STATUS])) {
                $taxSubmitStatus = $submittedStatus == TaxSubmit::CLEARED_STATUS ? TaxSubmit::B2B_SUBMITTED_STATUS : TaxSubmit::B2C_SUBMITTED_STATUS;
                if ($invoiceType == TaxSubmit::INVOICE_TEXT) {
                    $customerInvoice = CustomerInvoice::find($invoiceId);
                    $customerInvoice->tax_submit_status = $taxSubmitStatus;
                    $customerInvoice->qr = $finalQr;
                    $customerInvoice->save();
                } else {
                    $creditNote = CreditNote::find($invoiceId);
                    $creditNote->tax_submit_status = $taxSubmitStatus;
                    $creditNote->qr = $finalQr;
                    $creditNote->save();
                }

                /*Finance::where('table_title', $invoice->title)->where('table_id', $invoiceId)
                    ->update(['old_voucher_date' => DB::raw('`voucher_date`'), 'voucher_date' => $postingDate]);
                FinanceSub::where('table_title', $invoice->title)->where('table_id', $invoiceId)
                    ->update(['old_voucher_date' => DB::raw('`voucher_date`'), 'voucher_date' => $postingDate]);*/

                if ($validationStatus == 'WARNING') {
                    return [
                        'type' => 'warning',
                        'title' => $submittedStatus,
                        'message' => $message,
                    ];
                }
                return [
                    'type' => 'success',
                    'title' => $submittedStatus,
                    'message' => __('Tax Submited Successfully'),
                ];
            }
            return [
                'type' => 'error',
                'title' => TaxSubmit::NOT_SUBMITTED_STATUS["{$submittedStatus}"],
                'message' => $message,
            ];
        }
        //dd($taxSubmissionResponse->response['']);
        return [
            'type' => 'error',
            'title' => __('Unauthorized'),
            'message' => __('ZATCA Not Submitted'),
        ];
        //return errorResponse(__('Zatca Not Submitted'));
    }

    public function pureQrCodeString($encodedInvoiceXMLResponse)
    {
        if (!$encodedInvoiceXMLResponse) {
            return null;
        }
        $qrOut = explode('<cbc:ID>QR</cbc:ID>', base64_decode($encodedInvoiceXMLResponse));
        $qrOut = explode('</cac:Attachment>', $qrOut[1]);
        $qrOut = str_replace([
            '<cac:Attachment>', '<cbc:EmbeddedDocumentBinaryObject mimeCode="text/plain">',
            '</cbc:EmbeddedDocumentBinaryObject>',
        ], '', $qrOut[0]);
        return str_replace(["\n", ' '], '', $qrOut);
    }

    public function submitTestTax($type)
    {
        //dd($type);
        //$mode = zatcaMode();
        $mode = Zatca::SIMULATION_TEXT;
        $invoiceCategory = $type == 'b2b' ? TaxSubmit::TAX_INVOICE_TEXT : ($type == 'b2c' ? TaxSubmit::SIMPLIFIED_TAX_INVOICE_TEXT : '');
        $ZOC = new ZatcaEGSController();
        $invoiceArray = $ZOC->sampleInvoiceLineItems();
        $invoiceArray['category'] = $invoiceCategory;

        $zatcaConfig = ZatcaConfig::where('company_id', companyId())
            ->where('status', 1)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$zatcaConfig) {
            throw new Exception('EGS is missing a certificate/private key/api secret to check the invoice compliance.');
        }

        $egs = json_decode($zatcaConfig->egs_details, true);
        $egs['cancelation'] = [
            'cancelation_type' => TaxSubmit::INVOICE_TEXT,
            'canceled_invoice_number' => '',
        ];

        list($signedInvoiceString, $invoiceHash, $qr) = $ZOC->signInvoice($invoiceArray, $egs, $zatcaConfig->binary_security_token, $zatcaConfig->private_key);


        $certificate_stripped = $this->cleanUpCertificateString($zatcaConfig->binary_security_token);
        $certificate_stripped = base64_encode($certificate_stripped);
        $basic = base64_encode($certificate_stripped . ':' . $zatcaConfig->secret);

        $ZAC = new ZatcaApiController();
        $taxSubmissionResponse = $ZAC->taxSubmitApi(
            $signedInvoiceString,
            $invoiceHash,
            $egs['uuid'],
            $basic,
            $invoiceCategory,
            $mode
        );
        $jsonResponse = json_decode($taxSubmissionResponse);
        if ($jsonResponse) {
            if (isset($jsonResponse->message)) {
                return errorResponse([$jsonResponse->message, 'ZATCA Error']);
            }
            $submittedStatus = $invoiceCategory == TaxSubmit::TAX_INVOICE_TEXT ? $jsonResponse->clearanceStatus : $jsonResponse->reportingStatus;

            $message = '';
            foreach ($jsonResponse->validationResults->errorMessages as $error) {
                $message .= nl2br($error->message . ".\n");
            }
            //dd($message);
            if (in_array($submittedStatus, [TaxSubmit::REPORTED_STATUS, TaxSubmit::CLEARED_STATUS])) {
                return successResponse([$submittedStatus, __('ZATCA Status')]);
            }
            return errorResponse([$submittedStatus . ' ' . $message, __('ZATCA Status')]);
        }
        return errorResponse([$taxSubmissionResponse->reason(), __('ZATCA Status')]);
    }
}
