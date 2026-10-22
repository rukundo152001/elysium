<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /**
     * Get all active services
     */
    public function getActiveServices(Request $request)
    {
        try {
            $query = Service::active();

            // Filter by category if provided
            if ($request->has('category')) {
                $query->byCategory($request->category);
            }

            $services = $query->orderBy('category')
                ->orderBy('name')
                ->get(['id', 'name', 'category', 'price', 'description']);

            return response()->json([
                'success' => true,
                'data' => $services
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch services'
            ], 500);
        }
    }

    /**
     * Get all service categories
     */
    public function getCategories()
    {
        try {
            $categories = Service::active()
                ->select('category')
                ->distinct()
                ->whereNotNull('category')
                ->orderBy('category')
                ->pluck('category');

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch service categories'
            ], 500);
        }
    }
}
