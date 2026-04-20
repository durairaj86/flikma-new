<?php

namespace App\Http\Controllers\Master\Settings;

use App\Http\Controllers\Controller;
use App\Models\Master\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    private static string $cache = 'company:';

    /**
     * Show the form for editing the business profile.
     * * @return \Illuminate\View\View
     */
    public function edit()
    {
        // Fetch the company details for the authenticated user/tenant
        $company = authUserCompany(); // Adjust based on your model relationship

        return view('modules.settings.company', compact('company'));
    }

    /**
     * Store or update the company profile.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // 1. Determine the current company instance
        $company = Auth::user()->company ?? new Company();

        // 2. Define Validation Rules
        $rules = [
            'business_name_en' => 'required|string|max:255',
            'business_name_ar' => 'required|string|max:255',

            // Contact & Location
            'companyPhone' => 'required|string|max:20',
            'companyEmail' => 'required|email|max:255',
            'address_en' => 'required|string|max:500',
            'address_ar' => 'required|string|max:500',
            'city_en' => 'required|string|max:100',
            'city_ar' => 'required|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'state' => 'nullable|string|max:100',

            // Business Type & Registration
            // The 'multiple' selects need array validation, though the request might send null if nothing is selected.
            'businessType' => 'nullable|array',
            'industryType' => 'nullable|array',
            'registrationType' => 'nullable|string|max:100',

            // Compliance & Banking (Saudi/Gulf Region specific)
            'crNumber' => 'nullable|integer|max:15',
            'vatNumber' => 'nullable|integer|max:15',

            // Invoice Footer
            'terms' => 'nullable|string|max:1000',

            // File Uploads
            'logoInput' => [
                'nullable',
                'file',
                'mimes:png,jpg,jpeg',
                'max:5120', // 5MB
            ],
            'signatureInput' => [
                'nullable',
                'file',
                'mimes:png,jpg,jpeg',
                'max:2048', // 2MB
            ],
        ];

        // 3. Run Validation
        $validatedData = $request->validate($rules);

        $company = Company::findOrFail(companyId());

        // 4. Handle File Uploads
        // You should implement a service or helper to store files securely (e.g., to S3 or 'public' disk)
        try {
            if ($request->hasFile('logoInput')) {
                $file = $request->file('logoInput');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', str_replace(' ', '_', $originalName));
                $extension = $file->getClientOriginalExtension();
                $finalName = $cleanName . '.' . $extension;
                $relativeStoragePath = 'logos/' . $company->id;

                // Store the new logo
                $logoPath = $file->storeAs($relativeStoragePath, $finalName, 'public');

                // Delete the old logo if it exists
                if ($company->logo_path) {
                    // If the path already has 'storage/' prefix, remove it for deletion
                    $oldPath = $company->logo_path;
                    if (strpos($oldPath, 'storage/') === 0) {
                        $oldPath = substr($oldPath, 8); // Remove 'storage/' prefix
                    }
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                }

                // Store the path without 'storage/' prefix
                $company->logo_path = $logoPath;
            }


            if ($request->hasFile('signatureInput')) {
                $file = $request->file('signatureInput');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', str_replace(' ', '_', $originalName));
                $extension = $file->getClientOriginalExtension();
                $finalName = $cleanName . '.' . $extension;
                $relativeStoragePath = 'signatures/' . $company->id;

                // Store the new signature
                $signaturePath = $file->storeAs($relativeStoragePath, $finalName, 'public');

                // Delete the old signature if it exists
                if ($company->signature_path) {
                    // If the path already has 'storage/' prefix, remove it for deletion
                    $oldPath = $company->signature_path;
                    if (strpos($oldPath, 'storage/') === 0) {
                        $oldPath = substr($oldPath, 8); // Remove 'storage/' prefix
                    }
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                }

                // Store the path without 'storage/' prefix
                $company->signature_path = $signaturePath;
            }

        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'File upload failed: ' . $e->getMessage());
        }

        $company->name = $request->input('business_name_en');
        $company->name_ar = $request->input('business_name_ar');
        $company->phone = $request->input('companyPhone');
        $company->email = $request->input('companyEmail');
        $company->address_1 = $request->input('address_en');
        $company->address_1_ar = $request->input('address_ar');
        $company->city = $request->input('city_en');
        $company->city_ar = $request->input('city_ar');
        $company->postal_code = $request->input('pincode');
        $company->city_sub_division = $request->input('city_sub_division');
        $company->business_type = $request->input('businessType');
        $company->industry_type = $request->input('industryType');
        $company->currency = 'SAR';
        if ($company->vat_status != 1) {
            $company->vat_status = $request->input('vat_status');
            $company->cr_number = $request->input('crNumber');
            $company->tax_number = $request->input('vatNumber');
        }
        $company->invoice_terms = $request->input('terms');


        // File paths are already set in the company model during file upload handling

        // 6. Save or Update the Company record
        try {
            $company->save();

            // If it's a new company, associate it with the authenticated user
            /*if (!$company->wasRecentlyCreated && Auth::user()->company_id === null) {
                Auth::user()->company()->associate($company)->save();
            }*/
            Cache::forget(self::$cache . cacheName());
            return response()->json([
                'status' => 'success',
                'message' => 'Company details updated successfully',
            ]);

        } catch (\Exception $e) {
            // Log the error and return a user-friendly message
            \Log::error('Company save failed: ' . $e->getMessage());
            return back()->withInput()->with('error', 'An error occurred while saving the company details.');
        }
    }
}
