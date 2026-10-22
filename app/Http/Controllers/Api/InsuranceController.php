<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InsuranceProvider;
use App\Models\InsuranceCategory;
use App\Models\InsurancePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InsuranceController extends Controller
{
    /**
     * Return all insurance providers
     */
    public function providers()
    {
        Log::info('[API:Insurance] Fetching providers...');
        $providers = InsuranceProvider::select('id', 'name')->orderBy('name')->get();

        return response()->json($providers);
    }

    

    /**
     * Return all categories for a given provider
     */
    public function categories($providerId)
    {
        Log::info('[API:Insurance] Fetching categories for provider ID: ' . $providerId);

        $categories = InsuranceCategory::where('insurance_provider_id', $providerId)
            ->select('id', 'name', 'insurance_provider_id')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    /**
     * Return all packages for a given category
     */
    public function packages($categoryId)
    {
        Log::info('[API:Insurance] Fetching packages for category ID: ' . $categoryId);

        $packages = InsurancePackage::where('insurance_category_id', $categoryId)
            ->select('id', 'package_name', 'insurance_cover', 'patient_cover', 'insurance_category_id')
            ->orderBy('package_name')
            ->get();

        return response()->json($packages);
    }

    /**
     * Return details for a specific package (including percentages)
     */
    public function package($packageId)
    {
        Log::info('[API:Insurance] Fetching package details for ID: ' . $packageId);

        $package = InsurancePackage::select(
            'id',
            'package_name',
            'insurance_category_id',
            'insurance_cover',
            'patient_cover',
        )->find($packageId);

        if (!$package) {
            Log::warning('[API:Insurance] Package not found: ' . $packageId);
            return response()->json(['error' => 'Package not found'], 404);
        }

        return response()->json($package);
    }
}
