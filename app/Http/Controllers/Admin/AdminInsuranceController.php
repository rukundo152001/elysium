<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InsuranceProvider;
use App\Models\InsuranceCategory;
use App\Models\InsurancePackage;

class AdminInsuranceController extends Controller
{
    /**
     * Display the full hierarchical structure (Provider → Category → Package)
     */
    public function index()
    {
        $providers = InsuranceProvider::with('categories.packages')->get();
        return view('admin.insurance.index', compact('providers'));
    }

    /**
     * Show the form for creating a provider/category/package
     */
    public function create(Request $request)
    {
        $type = $request->get('type', 'provider');
        $providers = InsuranceProvider::all();
        $categories = InsuranceCategory::with('provider')->get();

        return view('admin.insurance.create', compact('type', 'providers', 'categories'));
    }

    /**
     * Store a new provider/category/package
     */
    public function store(Request $request)
    {
        $type = $request->get('type', 'provider');

        if ($type === 'provider') {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);
            InsuranceProvider::create($request->only('name', 'description'));

            return redirect()->route('admin.insurances.index')->with('success', 'Provider added successfully.');

        } elseif ($type === 'category') {
            $request->validate([
                'insurance_provider_id' => 'required|exists:insurance_providers,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);
            InsuranceCategory::create($request->only('insurance_provider_id', 'name', 'description'));

            return redirect()->route('admin.insurances.index')->with('success', 'Category added successfully.');

        } elseif ($type === 'package') {
            $request->validate([
                'insurance_category_id' => 'required|exists:insurance_categories,id',
                'package_name' => 'required|string|max:255',
                'patient_cover' => 'required|numeric|min:0|max:100',
                'insurance_cover' => 'required|numeric|min:0|max:100',
            ]);
            InsurancePackage::create($request->only('insurance_category_id', 'package_name', 'patient_cover', 'insurance_cover'));

            return redirect()->route('admin.insurances.index')->with('success', 'Package added successfully.');
        }

        return back()->with('error', 'Invalid type specified.');
    }

   

    /**
 * Show edit form for provider/category/package
 */
public function edit(Request $request, $id)
{
    $type = $request->get('type', 'provider');
    $item = null;

    if ($type === 'provider') {
        $item = InsuranceProvider::findOrFail($id);
    } elseif ($type === 'category') {
        $item = InsuranceCategory::findOrFail($id);
    } elseif ($type === 'package') {
        $item = InsurancePackage::findOrFail($id);
    }

    return view('admin.insurance.edit', compact('item', 'type'));
}

/**
 * Update provider/category/package
 */
public function update(Request $request, $id)
{
    $type = $request->get('type', 'provider');

    if ($type === 'provider') {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $provider = InsuranceProvider::findOrFail($id);
        $provider->update($request->only('name', 'description'));

        return redirect()->route('admin.insurances.index')
                         ->with('success', 'Insurance provider updated successfully.');

    } elseif ($type === 'category') {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $category = InsuranceCategory::findOrFail($id);
        $category->update($request->only('name', 'description'));

        return redirect()->route('admin.insurances.index')
                         ->with('success', 'Insurance category updated successfully.');

    } elseif ($type === 'package') {
        $request->validate([
            'package_name' => 'required|string|max:255',
            'patient_cover' => 'required|numeric|min:0|max:100',
            'insurance_cover' => 'required|numeric|min:0|max:100',
        ]);
        $package = InsurancePackage::findOrFail($id);
        $package->update($request->only('package_name', 'patient_cover', 'insurance_cover'));

        return redirect()->route('admin.insurances.index')
                         ->with('success', 'Insurance package updated successfully.');
    }

    return back()->with('error', 'Invalid type specified.');
}

/**
 * Delete provider/category/package
 */
public function destroy(Request $request, $id)
{
    $type = $request->get('type', 'provider');

    if ($type === 'provider') {
        $provider = InsuranceProvider::findOrFail($id);
        $provider->delete();

        return redirect()->route('admin.insurances.index')
                         ->with('success', 'Insurance provider deleted successfully.');
    } elseif ($type === 'category') {
        $category = InsuranceCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.insurances.index')
                         ->with('success', 'Insurance category deleted successfully.');
    } elseif ($type === 'package') {
        $package = InsurancePackage::findOrFail($id);
        $package->delete();

        return redirect()->route('admin.insurances.index')
                         ->with('success', 'Insurance package deleted successfully.');
    }

    return back()->with('error', 'Invalid type specified.');
}


}
