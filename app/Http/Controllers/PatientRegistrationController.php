<?php

namespace App\Http\Controllers;

use App\Models\InsurancePackage;
use App\Models\PaymentSummary;
use App\Models\User;
use App\Models\Patient;
use App\Models\PatientActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PatientRegistrationController extends Controller
{

    public function index()
{
    $user = Auth::user();

    $technicians = User::where('role', 'technician')
        ->select('id', 'name')
        ->get();

    return view('staff.patients.register', compact('user', 'technicians'));
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
        // Parse JSON safely
        $patientData = json_decode($request->input('patient_data'), true);

        if (!$patientData) {
            throw new \Exception('Invalid JSON format');
        }

        // Add ref_number to validation rules
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

            // ✅ NEW: check if ref_number is unique
            'ref_number' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    if (\App\Models\Patient::where('ref_number', $value)->exists()) {
                        $fail('The reference number already exists.');
                    }
                },
            ],
        ];

        // Validate using Laravel Validator
        $validator = Validator::make($patientData, $rules);

if ($validator->fails()) {
    Log::error('[ERROR:BLADE:patient] Patient validation failed', [
        'errors' => $validator->errors()->all(),
        'patient_data' => $patientData, // ✅ FULL patient data
    ]);

    return [
        'valid' => false,
        'errors' => $validator->errors()->all(),
        'patient_data' => $patientData, // ✅ Pass back if needed
    ];
}

Log::info('[INFO:BLADE:patient] Patient validation passed', [
    'patient_data' => $patientData, // ✅ FULL patient data
]);

return [
    'valid' => true,
    'patient_data' => $patientData, // ✅ Included in info
];

} catch (\Exception $e) {
    Log::error('[ERROR:BLADE:patient] Exception during validation', [
        'error' => $e->getMessage(),
        'patient_data' => $patientData ?? null,
    ]);

    return [
        'valid' => false,
        'errors' => ['Invalid patient data format'],
        'patient_data' => $patientData ?? null,
    ];
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

        Log::info('[INFO:BLADE:insurance] Insurance data received', [
            'insurance_data' => $insuranceData
        ]);

    } catch (\Exception $e) {
        Log::error('[ERROR:BLADE:insurance] Failed to parse insurance_data', [
            'error' => $e->getMessage(),
            'raw_data' => $request->input('insurance_data'),
        ]);

        return [
            'valid' => false,
            'errors' => ['Invalid insurance data format']
        ];
    }
}


    $errors = [];

    /** ------------------------------------
     * ALWAYS REQUIRED
     * ------------------------------------*/
    if (empty($insuranceData['provider_id'])) {
        $errors[] = 'Insurance provider is required';
    }

    /** ------------------------------------------------
     *  FETCH PROVIDER AND PACKAGE FOR PAYMENT LOGIC
     * ------------------------------------------------*/
    $provider = \App\Models\InsuranceProvider::find($insuranceData['provider_id']);
    $isPrivate = $provider && strtoupper($provider->name) === 'PRIVATE';

    $package = null;
    $patientCoverPercent = null;
    $insuranceCoverPercent = null;

    if (!empty($insuranceData['package_id'])) {
        $package = \App\Models\InsurancePackage::find($insuranceData['package_id']);

        if ($package) {
            $patientCoverPercent   = (int)$package->patient_cover;
            $insuranceCoverPercent = (int)$package->insurance_cover;
        }
    }

    /** ------------------------------------------------
     *  PAYMENT METHOD VALIDATION RULE
     * ------------------------------------------------*/

    // ✔ If FULL COVERAGE → payment_method is NOT required
    if (!($patientCoverPercent === 0 && $insuranceCoverPercent === 100)) {

        // Otherwise → payment_method must be filled
        if (empty($insuranceData['payment_method'])) {
            $errors[] = 'Payment method is required';
        }
    }

    Log::debug('[DEBUG:VALIDATION] isPrivate = ' . ($isPrivate ? 'YES' : 'NO'));

    /** ------------------------------------------------
     * PRIVATE PROVIDER VALIDATION (category + package required)
     * ------------------------------------------------*/
    if ($isPrivate) {

        if (empty($insuranceData['category_id'])) {
            $errors[] = 'Insurance category is required';
        }

        if (empty($insuranceData['package_id'])) {
            $errors[] = 'Insurance package is required';
        }

        if (!empty($errors)) {
            Log::error('[ERROR:BLADE:insurance] PRIVATE validation failed', ['errors' => $errors]);
            return ['valid' => false, 'errors' => $errors];
        }

        Log::debug('[DEBUG:BLADE:insurance] PRIVATE insurance validation passed');
        return ['valid' => true];
    }

    /** ------------------------------------------------
     * NORMAL PROVIDER VALIDATION
     * ------------------------------------------------*/

    if (empty($insuranceData['insurance_number']) || empty(trim($insuranceData['insurance_number']))) {
        $errors[] = 'Insurance number is required';
    } elseif (strlen(trim($insuranceData['insurance_number'])) > 50) {
        $errors[] = 'Insurance number cannot exceed 50 characters';
    }

    if (empty($insuranceData['category_id'])) {
        $errors[] = 'Coverage type is required';
    }

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





    public function store(Request $request)
{
    // 🔹 1. Validate all sections
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
        return response()->json(['error' => $e->getMessage()], 422);
    }

    // ---------------------------------------------------------
    // 🔹 Ensure ref_number is unique before transaction
    // ---------------------------------------------------------
    $incomingPatientData = json_decode($request->input('patient_data'), true);

    if (!$incomingPatientData || !isset($incomingPatientData['ref_number'])) {
        return response()->json(['error' => 'Invalid patient data'], 422);
    }

    $refNumber = $incomingPatientData['ref_number'];

    if (Patient::where('ref_number', $refNumber)->exists()) {
        return response()->json(['error' => 'Reference number already exists in Patients.'], 422);
    }

    if (PaymentSummary::where('ref_number', $refNumber)->exists()) {
        return response()->json(['error' => 'Reference number already exists in Payment Summary.'], 422);
    }

    try {

        DB::beginTransaction();

        // ---------------------------------------------------------
        // 🔹 Parse JSON Data
        // ---------------------------------------------------------
        $patientData   = json_decode($request->input('patient_data'), true);
        $addressData   = json_decode($request->input('address_data'), true);
        $insuranceData = json_decode($request->input('insurance_data'), true);
        $serviceData   = json_decode($request->input('service_data'), true);

        // ---------------------------------------------------------
        // 🔹 Fetch Insurance Package (coverage %)
        // ---------------------------------------------------------
        $package = InsurancePackage::find($insuranceData['package_id']);

        $patientCoverPercent   = $package->patient_cover ?? 0;
        $insuranceCoverPercent = $package->insurance_cover ?? 0;

        // ---------------------------------------------------------
        // 🔹 Create Patient
        // ---------------------------------------------------------
        $patient = Patient::create([
            'ref_number' => $refNumber,
            'first_name' => $this->extractFirstName($patientData['name']),
            'last_name'  => $this->extractLastName($patientData['name']),
            'dob'        => $patientData['dob'],
            'gender'     => strtolower($patientData['gender']),
            'phone'      => $patientData['phone'],
            'email'      => $patientData['email'] ?? null,
            'referred_from' => $patientData['referred_from'],

            // Address
            'country_id'  => $addressData['country_id'],
            'province_id' => $addressData['province_id'],
            'district_id' => $addressData['district_id'],
            'sector_id'   => $addressData['sector_id'],
            'cell_id'     => $addressData['cell_id'] ?? null,
            'village_id'  => $addressData['village_id'] ?? null,

            // Insurance
            'insurance_provider_id' => $insuranceData['provider_id'],
            'insurance_category_id' => $insuranceData['category_id'],
            'insurance_package_id'  => $package->id ?? null,
            'insurance_number'      => $insuranceData['insurance_number'] ?? null,

            // Affiliate
            'affiliate_name'             => $insuranceData['affiliate_name'] ?? null,
            'affiliate_insurance_number' => $insuranceData['affiliate_insurance_number'] ?? null,
            'affiliate_relationship'     => $insuranceData['affiliate_relationship'] ?? null,

            'assigned_location' => $insuranceData['assigned_location'] ?? null,

            // Other
            'technician_id' => $patientData['technician_id'] ?: null,
            'registered_by' => auth()->id(),
            'treatment_date' => $patientData['treatment_date'],
        ]);

        // ---------------------------------------------------------
        // 🔹 Determine if provider is PRIVATE
        // ---------------------------------------------------------
        $isPrivate = 0;
        $provider = DB::table('insurance_providers')->where('id', $insuranceData['provider_id'])->first();
        if ($provider && strtoupper($provider->name) === 'PRIVATE') {
            $isPrivate = 1;
        }

        // ---------------------------------------------------------
        // 🔹 Create Patient Activities
        // ---------------------------------------------------------
        $services = $serviceData['services'] ?? [];
        $totalUnit = 0;
        $totalPatient = 0;
        $totalInsurance = 0;

        foreach ($services as $service) {

            $unitPrice = isset($service['cost'])
                ? (float) str_replace(',', '', $service['cost'])
                : 0;

            if ($unitPrice <= 0) {
                continue;
            }

            $patientPays   = $unitPrice * ($patientCoverPercent / 100);
            $insurancePays = $unitPrice * ($insuranceCoverPercent / 100);

            $service_name = DB::table('services')
                ->where('id', $service['service_id'])
                ->value('name');
                
            PatientActivity::create([
                'ref_number' => $refNumber,
                'patient_id' => $patient->id,
                'service_id' => $service['service_id'],
                'service_name' => $service_name ?? '',
                'cashier_id' => auth()->id(),
                'unit_price' => $unitPrice,
                'insurance_coverage_amount' => $insurancePays,
                'patient_coverage_amount' => $patientPays,
            ]);

            // accumulate totals
            $totalUnit      += $unitPrice;
            $totalPatient   += $patientPays;
            $totalInsurance += $insurancePays;
        }

        // ---------------------------------------------------------
        // 🔹 Determine payment method:
        // ---------------------------------------------------------
        // If FULL insurance coverage (0% patient), patient pays nothing
        /*
        if ($patientCoverPercent == 00 && $insuranceCoverPercent == 100) {
            $paymentMethod = 'none';
        } else {
            $paymentMethod = $insuranceData['payment_method'] ?? 'cash';
        }
            */

        $paymentMethod = $insuranceData['payment_method'] ?? 'cash';

        // ---------------------------------------------------------
        // 🔹 Create Payment Summary
        // ---------------------------------------------------------
        PaymentSummary::create([
            'patient_id' => $patient->id,
            'ref_number' => $refNumber,
            'total_amount'           => $totalUnit,
            'total_patient_amount'   => $totalPatient,
            'total_insurance_amount' => $totalInsurance,
            'paid_at'                => now(),
            'payment_method'         => $paymentMethod,
            'is_private'             => $isPrivate,
        ]);

        DB::commit();

        return response()->json([
            'redirect' => route('staff.patients.details', $patient->id),
            'message'  => 'Patient registered successfully.'
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Failed to register patient: ' . $e->getMessage()], 500);
    }
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

public function show($id)
{
    $patient = Patient::with([
        'registeredBy',
        'technician',
        'insuranceProvider',
        'insuranceCategory',
        'insurancePackage',
        'country','province','district','sector',
        'patientActivities','services', 'paymentSummary'
    ])->findOrFail($id);

    return view('staff.patients.show', compact('patient'));
}











    


}
