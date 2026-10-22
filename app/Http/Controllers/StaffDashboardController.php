<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StaffDashboardController extends Controller
{
public function index(Request $request)
{
    // Unified search (name, ref number, ID)
    $search = trim($request->query('search'));

    // Base query
    $query = Patient::with([
        'patientActivities.service',
        'insuranceProvider',
        'insuranceCategory',
        'insurancePackage'
    ])->orderBy('treatment_date', 'desc');

    /*
    |--------------------------------------------------------------------------
    | DEFAULT: SHOW ONLY TODAY'S PATIENTS
    --------------------------------------------------------------------------
    |
    | This activates only when the page loads normally without a search.
    | If there is NO search, we filter by today's date.
    |
    */
    if (empty($search)) {
        $query->whereDate('treatment_date', now()->toDateString());
    }

    /*
    |--------------------------------------------------------------------------
    | SEARCH LOGIC (Search across ALL dates)
    |--------------------------------------------------------------------------
    */
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhereRaw("first_name || ' ' || last_name LIKE ?", ["%{$search}%"])
              ->orWhere('ref_number', 'like', "%{$search}%")
              ->orWhere('insurance_number', $search);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */
    $patients = $query->orderBy('ref_number', 'desc')
                  ->paginate(20)
                  ->withQueryString();


    return view('staff.dashboard', compact('patients'));
}

  

}