<?php

namespace App\Traits\Zatca;

use App\Models\Zatca\ZatcaRegisterDetails;
use Illuminate\Support\Str;
use Mockery\Exception;

trait ZatcaCompanyInfo
{
    public $egs_info;
    public $production = false;

    public function __construct()
    {
        $uuid = (string)Str::orderedUuid();
        $companyEgsDetails = ZatcaRegisterDetails::where('company_id', companyId())->first();
        //dd($companyEgsDetails,companyId());
        $this->egs_info = [
            'uuid' => $uuid,
            'custom_id' => 'LCS-55800',
            'model' => 'IOS',
            'CRN_number' => $companyEgsDetails->cr_number,
            'VAT_name' => $companyEgsDetails->tax_payer,
            'VAT_number' => $companyEgsDetails->tax_number,
            'location' => [
                'city' => $companyEgsDetails->city,
                'city_subdivision' => $companyEgsDetails->city_subdivision,
                'street' => $companyEgsDetails->street,
                'plot_identification' => $companyEgsDetails->plot_no,
                'building' => $companyEgsDetails->building_no,
                'postal_zone' => $companyEgsDetails->postal_code,
            ],
            'branch_name' => $companyEgsDetails->cr_number,
            'branch_industry' => 'Logistic',
            /*'custom_id' => 'FALCON-LCS-55800',
            'model' => 'IOS',
            'CRN_number' => 4030171595,
            'VAT_name' => 'KHALID NASER AHMED AL QAHTAN',
            'VAT_number' => 300776361900003,
            'location' => [
                'city' => 'JEDDAH',
                'city_subdivision' => 'AL BAGDADIYAH AL GHARBIYAH',
                'street' => 'KING KHALID',
                'plot_identification' => 'HALA COMMERCIAL TOWER',
                'building' => '7656',
                'postal_zone' => '22234',
            ],
            'branch_name' => '4030171595',
            'branch_industry' => 'Logistic',*/
            /*'cancelation' => [
                'cancelation_type' => $invoiceType,
                'canceled_invoice_number' => $invoiceType != 'INVOICE' ? 'INV/2024/001' : '',
            ]*/
        ];
    }
}
