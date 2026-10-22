<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminServiceController extends Controller
{
    

    /**
     * Display all services, grouped by category
     */
    public function index(Request $request)
    {
        $query = Service::query();

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('category', 'like', "%{$request->search}%");
            });
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $services = $query->orderBy('category')->orderBy('name')->get();

        // Distinct categories for grouping
        $categories = Service::select('category')->distinct()->pluck('category');

        return view('admin.serviceS.index', compact('services', 'categories'));
    }

    /**
     * Create new service
     */
    public function create()
    {
        $categories = Service::select('category')->distinct()->pluck('category');
        return view('admin.serviceS.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services')->where(fn($q) => $q->where('category', $request->category)),
            ],
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);

        Service::create($validated + ['is_active' => true]);

        return redirect()->back()
            ->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        $categories = Service::select('category')->distinct()->pluck('category');
        
        $paymentCount = $service->payments()->count() ?? 0;

        return view('admin.serviceS.edit', compact('service', 'categories', 'paymentCount'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('services')->where(fn($q) => $q->where('category', $request->category))->ignore($service->id),
            ],
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
        ]);

        $service->update($validated);

        return redirect()->route('admin.services.index')
            ->with('success', 'Service updated successfully.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return back()->with('error', 'Service deleted successfully.');
    }
}


