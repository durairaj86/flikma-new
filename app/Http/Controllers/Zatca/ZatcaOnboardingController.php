<?php

namespace App\Http\Controllers\Zatca;

use App\Enums\TaxSubmit;
use App\Enums\Zatca;
use App\Models\Master\Company;
use App\Models\Zatca\ZatcaConfig;
use App\Models\Zatca\ZatcaRegisterDetails;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * ZATCA device onboarding in five visible steps (same flow as the Fatoora register page):
 *   1. info      - check company details, store the device serial / send-start-date / OTP
 *   2. csr       - generate private key + CSR
 *   3. compliance- request the Compliance CSID from ZATCA with the OTP
 *   4. checks    - sign and submit the sample invoices for compliance checks
 *   5. production- request the Production CSID and save the device
 * The browser calls one endpoint per step. Keys, tokens and secrets never leave the server:
 * they are kept in the session between steps.
 */
class ZatcaOnboardingController extends ZatcaEGSController
{
    private const SESSION = 'zatca_onboarding';

    /** Company fields ZATCA needs in the EGS unit. Label => value. */
    public static function requiredCompanyFields(Company $c): array
    {
        return [
            'Company Name' => $c->name,
            'Tax Registration Number (TRN)' => $c->vat_number,
            'CR Number' => $c->cr_number,
            'City' => $c->city,
            'City Sub Division' => $c->city_sub_division,
            'Street / Address' => $c->address,
            'Plot No' => $c->plot_no,
            'Building No' => $c->building_number,
            'Postal Code' => $c->postal_code,
        ];
    }

    private function fail(string $message, int $code = 422): JsonResponse
    {
        return response()->json(['type' => 'error', 'status' => 'error', 'message' => $message], $code);
    }

    private function state(): array
    {
        return session(self::SESSION, []);
    }

    private function put(array $data): void
    {
        session([self::SESSION => array_merge($this->state(), $data)]);
    }

    /** Steps 1-2: validate the form and company details, then generate the key pair and CSR. */
    public function csr(Request $request): JsonResponse
    {
        $data = $request->validate([
            'serial' => 'required|string|max:60',
            'zatca_submit_start_date' => 'required|date',
            'otp' => ['required', 'digits:6'],
        ], ['otp.digits' => 'The OTP must be the 6-digit code from the ZATCA Fatoora portal.']);

        $company = Company::find(companyId());
        if (!$company) {
            return $this->fail('Company not found.');
        }
        $missing = array_keys(array_filter(self::requiredCompanyFields($company), fn ($v) => blank($v)));
        if ($missing) {
            return $this->fail('Please complete these company details in Settings > Manage Business first: ' . implode(', ', $missing) . '.');
        }
        if (!preg_match('/^\d{15}$/', (string) $company->vat_number)) {
            return $this->fail('The VAT number (TRN) in company settings must be 15 digits.');
        }

        $mode = Zatca::CORE_TEXT;
        $this->egs_info = [
            'uuid' => (string) Str::orderedUuid(),
            'custom_id' => $data['serial'],
            'model' => 'V2.1',
            'CRN_number' => $company->cr_number,
            'VAT_name' => str_replace('&', 'and', $company->name),
            'VAT_number' => $company->vat_number,
            'location' => [
                'city' => $company->city,
                'city_subdivision' => $company->city_sub_division,
                'street' => str_replace('&', 'and', $company->address),
                'plot_identification' => $company->plot_no,
                'building' => $company->building_number,
                'postal_zone' => $company->postal_code,
            ],
            'branch_name' => $company->cr_number,
            'branch_industry' => 'Logistic',
        ];

        try {
            [$privateKey, $csr] = $this->generateNewKeysAndCSR('FLIKMA-ZKT-V0.1', $mode);
        } catch (\Throwable $e) {
            report($e);
            return $this->fail('Could not generate the private key and CSR: ' . $e->getMessage(), 500);
        }

        $this->put([
            'mode' => $mode, 'otp' => $data['otp'], 'private_key' => $privateKey, 'csr' => $csr,
            'egs_info' => $this->egs_info, 'start_date' => $data['zatca_submit_start_date'],
            'company_id' => companyId(),
        ]);

        return response()->json(['type' => 'success', 'status' => 'success', 'step' => 'csr_generated']);
    }

    /** Step 3: Compliance CSID from ZATCA. */
    public function compliance(): JsonResponse
    {
        $s = $this->state();
        if (empty($s['csr']) || ($s['company_id'] ?? null) != companyId()) {
            return $this->fail('Registration session expired. Please start again.', 409);
        }

        try {
            $res = (new ZatcaApiController())->generateComplianceWithCSR($s['csr'], $s['otp'], $s['mode']);
        } catch (\Throwable $e) {
            report($e);
            return $this->fail('Could not reach ZATCA: ' . $e->getMessage(), 502);
        }
        if (!is_array($res) || isset($res['errors']) || empty($res['requestID']) || empty($res['binarySecurityToken'])) {
            $msg = $res['errors'][0]['message'] ?? ($res['message'] ?? 'ZATCA rejected the request. Check the OTP and try again.');
            return $this->fail($msg);
        }

        $this->put([
            'request_id' => $res['requestID'],
            'token' => base64_decode($res['binarySecurityToken']),
            'secret' => $res['secret'],
        ]);

        return response()->json(['type' => 'success', 'status' => 'success', 'step' => 'compliance_csid']);
    }

    /** Step 4: sign and submit the sample invoices (standard + simplified; invoice, credit and debit note). */
    public function checks(): JsonResponse
    {
        $s = $this->state();
        if (empty($s['token']) || ($s['company_id'] ?? null) != companyId()) {
            return $this->fail('Registration session expired. Please start again.', 409);
        }

        $this->egs_info = $s['egs_info'];
        $invoice = $this->sampleInvoiceLineItems();
        $basic = null;

        try {
            foreach ([TaxSubmit::SIMPLIFIED_TAX_INVOICE_TEXT, TaxSubmit::TAX_INVOICE_TEXT] as $mainType) {
                $invoice['category'] = $mainType;
                foreach (['INVOICE', 'CREDIT_NOTE', 'DEBIT_NOTE'] as $type) {
                    $this->egs_info['uuid'] = (string) Str::orderedUuid();
                    $this->egs_info['cancelation'] = [
                        'cancelation_type' => $type,
                        'canceled_invoice_number' => $type == 'INVOICE' ? '' : 'INV001',
                    ];
                    [$signed, $hash] = $this->signInvoice($invoice, $this->egs_info, $s['token'], $s['private_key'], 1);
                    $cert = base64_encode($this->cleanUpCertificateString($s['token']));
                    $basic = base64_encode($cert . ':' . $s['secret']);

                    $json = json_decode($this->checkInvoiceCompliance($signed, $hash, $basic, $this->egs_info, $s['mode']));
                    $cleared = ($json->clearanceStatus ?? null) === 'CLEARED' || ($json->reportingStatus ?? null) === 'REPORTED';
                    if (!$cleared) {
                        return $this->fail("ZATCA compliance check failed for {$mainType} / {$type}.");
                    }
                }
            }
        } catch (\Throwable $e) {
            report($e);
            return $this->fail('Compliance check error: ' . $e->getMessage(), 500);
        }

        $this->put(['basic' => $basic, 'egs_info' => $this->egs_info]);

        return response()->json(['type' => 'success', 'status' => 'success', 'step' => 'compliance_checked']);
    }

    /** Step 5: Production CSID, then save the device. */
    public function production(): JsonResponse
    {
        $s = $this->state();
        if (empty($s['basic']) || ($s['company_id'] ?? null) != companyId()) {
            return $this->fail('Registration session expired. Please start again.', 409);
        }

        try {
            $res = json_decode((new ZatcaApiController())->productionCSIDApi($s['basic'], $s['request_id'], $s['mode']));
        } catch (\Throwable $e) {
            report($e);
            return $this->fail('Could not reach ZATCA: ' . $e->getMessage(), 502);
        }
        if (!isset($res->dispositionMessage) || $res->dispositionMessage !== 'ISSUED') {
            return $this->fail($res->message ?? 'ZATCA did not issue the Production CSID.');
        }

        $now = date('Y-m-d H:i:s');
        $company = Company::find(companyId());
        DB::transaction(function () use ($s, $res, $now, $company) {
            ZatcaConfig::updateOrCreate(['company_id' => companyId()], [
                'company_id' => companyId(),
                'uuid' => $s['egs_info']['uuid'],
                'egs_details' => json_encode($s['egs_info']),
                'request_id' => $res->requestID,
                'binary_security_token' => base64_decode($res->binarySecurityToken),
                'secret' => $res->secret,
                'private_key' => $s['private_key'],
                'status' => Zatca::CORE_MODE,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // The invoice signing/submission code reads the EGS unit from zatca_register_details.
            $d = ZatcaRegisterDetails::where('company_id', companyId())->first() ?? new ZatcaRegisterDetails();
            $d->company_id = companyId();
            $d->tax_payer = $company->name;
            $d->cr_number = $company->cr_number;
            $d->tax_number = $company->vat_number;
            $d->city = $company->city;
            $d->city_subdivision = $company->city_sub_division;
            $d->street = $company->address;
            $d->plot_no = $company->plot_no;
            $d->building_no = $company->building_number;
            $d->postal_code = $company->postal_code;
            $d->branch_name = $company->cr_number;
            $d->branch_industry = 'Logistics';
            $d->custom_id = $s['egs_info']['custom_id'];
            $d->wave_date = Carbon::parse($s['start_date'])->format('Y-m-d');
            $d->created_at = $now;
            $d->save();

            DB::table('companies')->where('id', companyId())->update(['zatca_registered' => 1]);
        });
        Cache::forget('company:' . cacheName());
        session()->forget(self::SESSION);

        return response()->json(['type' => 'success', 'status' => 'success', 'step' => 'production', 'message' => __('ZATCA device registered.')]);
    }
}
