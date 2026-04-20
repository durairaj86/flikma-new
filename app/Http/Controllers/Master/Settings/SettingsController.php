<?php

namespace App\Http\Controllers\Master\Settings;

use App\Http\Controllers\Controller;
use App\Models\Master\Company;
use App\Models\Zatca\ZatcaConfig;
use App\Models\Zatca\ZatcaRegisterDetails;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function zatcaRegister()
    {
        return view('modules.settings.zatca.register', [
            'zatcaConfig' => ZatcaConfig::where('company_id', companyId())->latest()->first() ?? new ZatcaConfig(),
        ]);
    }

    public function detailSave()
    {
        $request = Request::all();
        $validator = $this->validator($request);
        if ($validator->fails()) {
            return $this->handle($validator);
        }
        $zatcaDetails = ZatcaRegisterDetails::where('company_id', companyId())->first();
        if (!filled($zatcaDetails)) {
            $zatcaDetails = new ZatcaRegisterDetails();
        }
        $zatcaDetails->company_id = companyId();
        $zatcaDetails->tax_payer = $request['tax_payer'];
        $zatcaDetails->cr_number = $request['cr_number'];
        $zatcaDetails->tax_number = $request['vat_number'];
        $zatcaDetails->city = $request['city'];
        $zatcaDetails->city_subdivision = $request['city_division'];
        $zatcaDetails->street = $request['street'];
        $zatcaDetails->plot_no = $request['plot'];
        $zatcaDetails->building_no = $request['building_no'];
        $zatcaDetails->postal_code = $request['postal_code'];
        $zatcaDetails->branch_name = $request['branch_name'];
        $zatcaDetails->branch_industry = 'Logistics';
        $zatcaDetails->wave = $request['wave'];
        $zatcaDetails->wave_date = formDate(zatcaWave($request['wave']));
        $zatcaDetails->created_at = now();
        $zatcaDetails->save();

        return successResponse();
    }

    protected function validator($request)
    {
        return \Illuminate\Support\Facades\Validator::make($request, [
            'tax_payer' => 'required',
            'cr_number' => 'required|max:32',
            'vat_number' => 'required|max:15',
            'city' => 'required|max:144',
            'city_division' => 'required|max:144',
            'street' => 'required|max:144',
            'plot' => 'required|max:32',
            'building_no' => 'required|max:4',
            'postal_code' => 'required|max:6',
            'branch_name' => 'required',
            'branch_industry' => 'required',
            'wave' => 'required',
        ]);
    }

    public function zatcaSave()
    {
        $request = Request::all();
        $companyId = companyId();
        $zatcaDetails = ZatcaRegisterDetails::select('id', 'company_id')->where('company_id', $companyId)->first();
        $zatcaDetails->otp = $request['otp'];
        $zatcaDetails->custom_id = $request['custom_id'];
        $zatcaDetails->save();
        Company::where('id', $companyId)->update(['zatca_registered' => 1]);
        return successResponse();
    }
}
