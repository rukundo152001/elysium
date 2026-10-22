<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Patient;
use Illuminate\View\View;
use App\Models\PaymentSummary;
use App\Models\PatientActivity;
use App\Models\InsurancePackage;
use App\Models\InsuranceCategory;
use App\Models\InsuranceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;   // ✅ CORRECT

    
class AdminPatientController extends Controller
{
   
    /**
     * Display a listing of the patients.
     */

    public function index(Request $request)
{
    // Unified search (name, ref number, ID)
    $search = trim($request->query('search'));

    // Filters
    $category    = $request->query('category');
    $date        = $request->query('date');
    $start_date  = $request->query('start_date');
    $end_date    = $request->query('end_date');

    // Categories for dropdown
    $categories = InsuranceCategory::orderBy('name')->get();

    // Base query
    $query = Patient::with([
        'patientActivities.service',
        'insuranceProvider',
        'insuranceCategory',
        'insurancePackage'
    ])->orderBy('treatment_date', 'desc'); // 🔥 CHANGED

    /*
    |--------------------------------------------------------------------------
    | SEARCH LOGIC
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
    | FILTER: Insurance Category
    |--------------------------------------------------------------------------
    */
    if (!empty($category)) {
        $query->where('insurance_category_id', $category);
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER: Single Treatment Date
    |--------------------------------------------------------------------------
    */
    if (!empty($date)) {
        $query->whereDate('treatment_date', $date); // 🔥 CHANGED
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER: Period (Start + End Treatment Dates)
    |--------------------------------------------------------------------------
    */
    if (!empty($start_date) && !empty($end_date)) {
        $query->whereBetween('treatment_date', [$start_date, $end_date]); // 🔥 CHANGED
    }

    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    */
    $patients = $query->orderBy('ref_number', 'desc')
                  ->paginate(20)
                  ->withQueryString();


    /*
    |--------------------------------------------------------------------------
    | SUMMARIES PER PATIENT
    |--------------------------------------------------------------------------
    */
    $patientSummaries = [];

    foreach ($patients as $p) {
        $activities = $p->patientActivities ?? collect();

        $totalAmount = $activities->sum(fn($a) => (float) ($a->unit_price ?? 0));
        $patientPays = $activities->sum(fn($a) => (float) ($a->patient_coverage_amount ?? 0));
        $insurancePays = $activities->sum(fn($a) => (float) ($a->insurance_coverage_amount ?? 0));

        // Payment totals
        $summary = PaymentSummary::where('patient_id', $p->id)->first();
        $totalPaid = (float) ($summary->total_patient_amount ?? 0);

        $outstanding = max(0, $totalAmount - $totalPaid);

        $patientSummaries[$p->id] = [
            'total_amount'   => $totalAmount,
            'patient_pays'   => $patientPays,
            'insurance_pays' => $insurancePays,
            'paid'           => $totalPaid,
            'outstanding'    => $outstanding,
        ];
    }

    return view('admin.patients.index', compact('patients', 'patientSummaries', 'categories'));
}




    
    
    public function edit($patientId)
    {
        $patient = Patient::with([
            'insuranceProvider',
            'insuranceCategory',
            'insurancePackage'
        ])->findOrFail($patientId);

        $insuranceProviders = InsuranceProvider::orderBy('name')->get();
        $insuranceCategories = InsuranceCategory::orderBy('name')->get();
        $insurancePackages = InsurancePackage::orderBy('name')->get();
        $technicians = User::where('role', 'technician')
            ->select('id', 'name')
            ->get();

        return view('admin.patients.edit', compact(
            'patient',
            'insuranceProviders',
            'insuranceCategories',
            'insurancePackages',
            'technicians'
        ));
    }

    public function show($patientId)
    {
        $patient = Patient::with([
            'patientActivities.service',
            'insuranceProvider',
            'insuranceCategory',
            'insurancePackage',
            'paymentSummary',
            'technician'
        ])->findOrFail($patientId);

        $activities = $patient->patientActivities;

        $totalAmount = 0;
        $patientPays = 0;
        $insurancePays = 0;

        foreach ($activities as $act) {
            $totalAmount += $act->unit_price ?? 0;
            $patientPays += $act->patient_coverage_amount ?? 0;
            $insurancePays += $act->insurance_coverage_amount ?? 0;
        }

        $summary = PaymentSummary::where('patient_id', $patientId)->first();

        $paid = ($summary->total_patient_amount ?? 0) + ($summary->total_insurance_amount ?? 0);
        $outstanding = max(0, $totalAmount - $paid);

        return view('admin.patients.show', compact(
            'patient',
            'activities',
            'summary',
            'totalAmount',
            'patientPays',
            'insurancePays',
            'paid',
            'outstanding',
        ));
    }

    




       /**
     * Validate Services Information
     * Validates: service_id, cost, and total
     */
 private function validateServiceInformation(Request $request)
{
    Log::debug('[DEBUG:BLADE:service] Starting validation (structure-only mode)');

    // 🔹 Step 1: Parse JSON safely
    $serviceData = [];
    if ($request->has('service_data')) {
        try {
            $serviceData = json_decode($request->input('service_data'), true);
            Log::debug('[DEBUG:BLADE:service] Service data decoded', [
                'services_count' => count($serviceData['services'] ?? [])
            ]);
        } catch (\Exception $e) {
            Log::error('[ERROR:BLADE:service] Failed to parse service_data', [
                'raw_data' => $request->input('service_data'),
                'error' => $e->getMessage()
            ]);
            return ['valid' => false, 'errors' => ['Invalid service data format']];
        }
    }

    // 🔹 Step 2: Basic existence check
    $services = $serviceData['services'] ?? [];
    if (empty($services)) {
        Log::warning('[WARNING:BLADE:service] No services provided');
        return ['valid' => false, 'errors' => ['At least one service must be selected']];
    }

    // 🔹 Step 3: Validate service structure only
    $errors = [];

    foreach ($services as $index => $service) {
        $row = $index + 1;

        // 3.1: Ensure service_id is valid
        if (empty($service['service_id'])) {
            $errors[] = "Service {$row}: service_id is required";
            continue;
        }

        // 3.2: Ensure service exists
        $exists = DB::table('services')->where('id', $service['service_id'])->exists();
        if (!$exists) {
            $errors[] = "Service {$row}: service_id does not exist in database";
            continue;
        }
    }

    // 🔹 Step 4: Return results
    if (!empty($errors)) {
        Log::error('[ERROR:BLADE:service] Service validation failed', ['errors' => $errors]);
        return ['valid' => false, 'errors' => $errors];
    }

    Log::debug('[DEBUG:BLADE:service] Service validation passed', [
        'validated_services' => count($services)
    ]);

    return ['valid' => true];
}



    /**
     * Validate Address Information
     * Validates hierarchical location IDs fetched from database
     */
    private function validateAddressInformation(Request $request)
    {
        Log::debug('[DEBUG:BLADE:address] Starting address validation');

        $addressData = $request->input('address_data');
        
        if (!$addressData) {
            Log::error('[ERROR:BLADE:address] Address data is missing');
            throw new \Exception('Address data is required');
        }

        $addressInfo = json_decode($addressData, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('[ERROR:BLADE:address] Invalid address data format');
            throw new \Exception('Invalid address data format');
        }

        $required = ['country_id', 'province_id', 'district_id', 'sector_id'];
        
        foreach ($required as $field) {
            if (!isset($addressInfo[$field]) || empty($addressInfo[$field])) {
                throw new \Exception("Address {$field} is required");
            }
        }

        // Verify country exists
        $country = DB::table('countries')->where('id', $addressInfo['country_id'])->first();
        if (!$country) {
            throw new \Exception('Invalid country selected');
        }

        // Verify province exists and belongs to country
        $province = DB::table('provinces')
            ->where('id', $addressInfo['province_id'])
            ->where('country_id', $addressInfo['country_id'])
            ->first();
        if (!$province) {
            throw new \Exception('Invalid province selected for this country');
        }

        // Verify district exists and belongs to province
        $district = DB::table('districts')
            ->where('id', $addressInfo['district_id'])
            ->where('province_id', $addressInfo['province_id'])
            ->first();
        if (!$district) {
            throw new \Exception('Invalid district selected for this province');
        }

        // Verify sector exists and belongs to district
        $sector = DB::table('sectors')
            ->where('id', $addressInfo['sector_id'])
            ->where('district_id', $addressInfo['district_id'])
            ->first();
        if (!$sector) {
            throw new \Exception('Invalid sector selected for this district');
        }

        

        Log::debug('[DEBUG:BLADE:address] Address validation passed');
    }
   

    

    /**
     * Validate Patient Information
     * Validates: name, dob, gender, phone, email
     * Updated to match frontend JSON structure: uses 'name' instead of 'first_name'/'last_name', 'dob' instead of 'date_of_birth', and converts gender to lowercase
     */
    private function validatePatientInformation(Request $request)
{
    Log::debug('[DEBUG:BLADE:patient] Starting patient validation');

    try {
        // ✅ Parse JSON safely
        $patientData = json_decode($request->input('patient_data'), true);

        if (!$patientData) {
            throw new \Exception('Invalid JSON format');
        }

        // ✅ Define Laravel validation rules
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'gender' => ['required', 'in:male,female'],
            'phone' => [
                'required',
                'regex:/^[\d+\-\s()]+$/',
                function ($attribute, $value, $fail) {
                    $digits = preg_replace('/\D/', '', $value);
                    if (strlen($digits) < 10 || strlen($digits) > 12) {
                        $fail('Phone number must be between 10 and 12 digits');
                    }
                },
            ],
            
        ];

        // ✅ Validate using Laravel’s validator
        $validator = Validator::make($patientData, $rules);

        if ($validator->fails()) {
            Log::error('[ERROR:BLADE:patient] Patient validation failed', [
                'errors' => $validator->errors()->all(),
            ]);

            return [
                'valid' => false,
                'errors' => $validator->errors()->all(),
            ];
        }

        Log::debug('[DEBUG:BLADE:patient] Patient validation passed');
        return ['valid' => true];

    } catch (\Exception $e) {
        Log::error('[ERROR:BLADE:patient] Exception during validation', [
            'error' => $e->getMessage(),
        ]);
        return ['valid' => false, 'errors' => ['Invalid patient data format']];
    }
}


    /**
     * Validate Insurance Information
     * Validates: insurance_provider, insurance_number, category_id
     */
    private function validateInsuranceInformation(Request $request)
{
    Log::debug('[DEBUG:BLADE:insurance] Starting insurance validation');

    // Decode insurance_data JSON
    $insuranceData = [];
    if ($request->has('insurance_data')) {
        try {
            $insuranceData = json_decode($request->input('insurance_data'), true);
            Log::debug('[DEBUG:BLADE:insurance] Insurance data decoded');
        } catch (\Exception $e) {
            Log::error('[ERROR:BLADE:insurance] Failed to parse insurance_data', [
                'error' => $e->getMessage()
            ]);
            return ['valid' => false, 'errors' => ['Invalid insurance data format']];
        }
    }

    $errors = [];

    /** ------------------------------------
     *  ALWAYS REQUIRED
     * ------------------------------------*/
    if (empty($insuranceData['provider_id'])) {
        $errors[] = 'Insurance provider is required';
    }

    if (empty($insuranceData['payment_method'])) {
        $errors[] = 'Payment method is required';
    }

    /** ------------------------------------------------
     *  CHECK WHETHER PROVIDER IS PRIVATE
     * ------------------------------------------------*/
    $provider = InsuranceProvider::find($insuranceData['provider_id']);
    $isPrivate = $provider && strtoupper($provider->name) === 'PRIVATE';

    Log::debug('[DEBUG:VALIDATION] isPrivate = ' . ($isPrivate ? 'YES' : 'NO'));

    /** ------------------------------------------------
     *  PRIVATE PROVIDER VALIDATION
     *  Only category + package are required
     * ------------------------------------------------*/
    if ($isPrivate) {

        // category_id REQUIRED
        if (empty($insuranceData['category_id'])) {
            $errors[] = 'Insurance category is required';
        }

        // package_id REQUIRED
        if (empty($insuranceData['package_id'])) {
            $errors[] = 'Insurance package is required';
        }

        // Others are OPTIONAL for PRIVATE:
        // insurance_number
        // affiliate_name
        // affiliate_insurance_number
        // affiliate_relationship

        if (!empty($errors)) {
            Log::error('[ERROR:BLADE:insurance] PRIVATE validation failed', ['errors' => $errors]);
            return ['valid' => false, 'errors' => $errors];
        }

        Log::debug('[DEBUG:BLADE:insurance] PRIVATE insurance validation passed');
        return ['valid' => true];
    }

    /** ------------------------------------------------
     *  NORMAL PROVIDER VALIDATION (NOT PRIVATE)
     * ------------------------------------------------*/

    // insurance_number REQUIRED
    if (empty($insuranceData['insurance_number']) || empty(trim($insuranceData['insurance_number']))) {
        $errors[] = 'Insurance number is required';
    } elseif (strlen(trim($insuranceData['insurance_number'])) > 50) {
        $errors[] = 'Insurance number cannot exceed 50 characters';
    }

    // category_id REQUIRED
    if (empty($insuranceData['category_id'])) {
        $errors[] = 'Coverage type is required';
    }

    // package_id REQUIRED
    if (empty($insuranceData['package_id'])) {
        $errors[] = 'Insurance package is required';
    }

    if (!empty($errors)) {
        Log::error('[ERROR:BLADE:insurance] INSURANCE validation failed', ['errors' => $errors]);
        return ['valid' => false, 'errors' => $errors];
    }

    Log::debug('[DEBUG:BLADE:insurance] Insurance validation passed');
    return ['valid' => true];
}




   private function extractFirstName($fullName)
{
    $parts = preg_split('/\s+/', trim($fullName));
    return $parts[0] ?? '';
}

private function extractLastName($fullName)
{
    $parts = preg_split('/\s+/', trim($fullName));
    array_shift($parts); // remove first name
    return implode(' ', $parts); // join remaining names
}










public function update(Request $request, $patientId)
{
    // 🔹 1. Validate sections
    $patientValidation = $this->validatePatientInformation($request);
    if (!$patientValidation['valid']) {
        return response()->json(['errors' => $patientValidation['errors']], 422);
    }

    $insuranceValidation = $this->validateInsuranceInformation($request);
    if (!$insuranceValidation['valid']) {
        return response()->json(['errors' => $insuranceValidation['errors']], 422);
    }

    $serviceValidation = $this->validateServiceInformation($request);
    if (!$serviceValidation['valid']) {
        return response()->json(['errors' => $serviceValidation['errors']], 422);
    }

    try {
        $this->validateAddressInformation($request);
    } catch (\Exception $e) {
        Log::error('[ERROR:BLADE:address] Address validation failed', ['error' => $e->getMessage()]);
        return response()->json(['error' => $e->getMessage()], 422);
    }

    try {
        DB::beginTransaction();

        // 🔹 2. Load Patient
        $patient = Patient::findOrFail($patientId);

        // Save old ref number FIRST
        $oldRef = $patient->ref_number;

        $registeredById = $patient->registered_by;
        // 🔹 Extract OLD cashier from PatientActivity (preferred)
        $lastActivityBeforeUpdate = PatientActivity::where('patient_id', $patient->id)
            ->where('ref_number', $oldRef)
            ->orderBy('id', 'desc')
            ->first();

        $previousCashierId = $lastActivityBeforeUpdate->cashier_id ?? null;

        // 🔹 Fallback: if no cashier found on activities, try PaymentSummary (defensive)
        if (is_null($previousCashierId)) {
            $existingSummary = PaymentSummary::where('patient_id', $patient->id)
                ->where('ref_number', $oldRef)
                ->orderBy('id', 'desc')
                ->first();

            $previousCashierId = $existingSummary->cashier_id ?? null;
            Log::debug('[DEBUG:BLADE:update] Fallback cashier from PaymentSummary: ' . ($previousCashierId ?? 'NULL'));
        } else {
            Log::debug('[DEBUG:BLADE:update] Previous cashier from PatientActivity: ' . $previousCashierId);
        }

        // 🔹 3. Parse JSON blocks
        $patientData   = json_decode($request->patient_data, true);
        $addressData   = json_decode($request->address_data, true);
        $insuranceData = json_decode($request->insurance_data, true);
        $serviceData   = json_decode($request->service_data, true);

        // 🔹 Fetch insurance package
        $package = InsurancePackage::find($insuranceData['package_id']);
        $patientCoverPercent = $package->patient_cover ?? 0;
        $insuranceCoverPercent = $package->insurance_cover ?? 0;

        // 🔹 4. Update Patient
        $patient->update([
            'ref_number' => $patientData['ref_number'],
            'first_name' => $this->extractFirstName($patientData['name']),
            'last_name'  => $this->extractLastName($patientData['name']),
            'dob'        => $patientData['dob'],
            'gender'     => strtolower($patientData['gender']),
            'phone'      => $patientData['phone'],
            'email'      => $patientData['email'] ?? null,
            'referred_from' => $patientData['referred_from'],

            // Address
            'country_id' => $addressData['country_id'],
            'province_id' => $addressData['province_id'],
            'district_id' => $addressData['district_id'],
            'sector_id' => $addressData['sector_id'],
            'cell_id' => $addressData['cell_id'] ?? null,
            'village_id' => $addressData['village_id'] ?? null,

            // Insurance info
            'insurance_provider_id' => $insuranceData['provider_id'],
            'insurance_category_id' => $insuranceData['category_id'],
            'insurance_package_id'  => $package->id ?? null,
            'insurance_number'      => $insuranceData['insurance_number'] ?? null,

            'affiliate_name' => $insuranceData['affiliate_name'] ?? null,
            'affiliate_insurance_number' => $insuranceData['affiliate_insurance_number'] ?? null,
            'affiliate_relationship' => $insuranceData['affiliate_relationship'] ?? null,

            'assigned_location' => $insuranceData['assigned_location'] ?? null,
            'technician_id' => $patientData['technician_id'] ?: null,
            'treatment_date' => $patientData['treatment_date'],
            'registered_by' => $registeredById,
        ]);

        // 🔹 Ref number changed? Update related tables
        $newRef = $patient->ref_number;

        if ($oldRef !== $newRef) {
            PatientActivity::where('ref_number', $oldRef)
                ->where('patient_id', $patient->id)
                ->update(['ref_number' => $newRef]);

            PaymentSummary::where('ref_number', $oldRef)
                ->where('patient_id', $patient->id)
                ->update(['ref_number' => $newRef]);
        }

        // 🔹 5. Rebuild Patient Activities
        // We delete existing activities and recreate them.
        PatientActivity::where('patient_id', $patient->id)->delete();

        $services = $serviceData['services'] ?? [];
        $lastActivity = null;

        foreach ($services as $service) {
            $unitPrice = isset($service['cost'])
                ? floatval(str_replace(',', '', $service['cost']))
                : 0;

            if ($unitPrice <= 0) continue;

            $patientPays   = $unitPrice * ($patientCoverPercent / 100);
            $insurancePays = $unitPrice * ($insuranceCoverPercent / 100);

            // ✅ When recreating activities, persist the previous cashier_id into each activity
            $service_name = DB::table('services')
                ->where('id', $service['service_id'])
                ->value('name');
            $activityData = [
                'ref_number' => $newRef,
                'patient_id' => $patient->id,
                'service_id' => $service['service_id'],
                'service_name' => $service_name ?? '',
                'unit_price' => $unitPrice,
                'insurance_coverage_amount' => $insurancePays,
                'patient_coverage_amount' => $patientPays,
            ];

            if (!is_null($previousCashierId)) {
                $activityData['cashier_id'] = $previousCashierId;
            }

            $activity = PatientActivity::create($activityData);

            if ($activity) $lastActivity = $activity;
        }

        // 🔹 6. Payment Summary Update
        $isPrivate = 0;
        if (!empty($insuranceData['provider_id'])) {
            $provider = DB::table('insurance_providers')->find($insuranceData['provider_id']);
            if ($provider && strtoupper($provider->name) === 'PRIVATE') {
                $isPrivate = 1;
            }
        }

       if ($lastActivity) {
    $activities = PatientActivity::where('ref_number', $newRef)
        ->where('patient_id', $patient->id)
        ->get();

    // 🔹 Delete before recreate to avoid conflicts
    PaymentSummary::where('patient_id', $patient->id)
        ->where('ref_number', $newRef) // newRef because we already updated oldRef → newRef
        ->delete();

    PaymentSummary::create([
        'patient_id' => $patient->id,
        'ref_number' => $newRef,
        'total_amount' => $activities->sum('unit_price'),
        'total_patient_amount' => $activities->sum('patient_coverage_amount'),
        'total_insurance_amount' => $activities->sum('insurance_coverage_amount'),
        'paid_at' => now(),
        'payment_method' => $insuranceData['payment_method'] ?? 'cash',
        'is_private' => $isPrivate,

        // KEEP OLD CASHIER (do not change)
        'cashier_id' => $previousCashierId,
    ]);
}

        DB::commit();

        return response()->json([
            'redirect' => route('admin.patients.details', $patient->id),
            'message' => 'Patient updated successfully.'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('[ERROR:BLADE:update] Failed to update patient data', [
            'error' => $e->getMessage()
        ]);

        return response()->json(['error' => $e->getMessage()], 500);
    }
}

    
}