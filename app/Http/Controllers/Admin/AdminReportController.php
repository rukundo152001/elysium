<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InsurancePackage;
use App\Models\InsuranceProvider;
use App\Models\PatientActivity;
use App\Models\Patient;
use App\Models\PaymentSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminReportController extends Controller
{
    public function index(Request $request)
    {
        

        // ================================
        // CALCULATE TOTALS AND BREAKDOWN
        // ================================
        
        
        $totalInsuredPatientRevenue = PaymentSummary::where('is_private', false)
    ->sum('total_patient_amount');

$totalRevenue = PaymentSummary::sum('total_amount');

$totalInsuranceRevenue = PaymentSummary::where('is_private', false)
    ->sum('total_insurance_amount');

$totalPrivateRevenue = PaymentSummary::where('is_private', true)
    ->sum('total_amount');


        

        

        // ================================
        // CALCULATE PERIOD-BASED STATS
        // ================================
        
        Log::info("[AdminReport] === PERIOD-BASED STATS CALCULATION STARTED ===");
        
        // Daily stats
        $dailyStart = Carbon::today()->startOfDay();
        $dailyEnd = Carbon::today()->endOfDay();
        $dailyActivities = PatientActivity::with('patient')
            ->whereBetween('created_at', [$dailyStart, $dailyEnd])
            ->get();
        $dailyPatients = $dailyActivities->count();
        $dailyRevenue = $dailyActivities->sum('unit_price');
        $dailyCashReceived = $dailyActivities->sum('patient_coverage_amount');
        
        Log::info("[AdminReport] DAILY Stats", [
            'date_range' => $dailyStart->toDateString() . ' to ' . $dailyEnd->toDateString(),
            'patients' => $dailyPatients,
            'revenue' => $dailyRevenue
        ]);
        
        // Weekly stats
        $weeklyStart = Carbon::now()->startOfWeek();
        $weeklyEnd = Carbon::now()->endOfWeek();
        $weeklyActivities = PatientActivity::with('patient')
            ->whereBetween('created_at', [$weeklyStart, $weeklyEnd])
            ->get();
        $weeklyPatients = $weeklyActivities->count();
        $weeklyRevenue = $weeklyActivities->sum('unit_price');
        $weeklyCashReceived = $weeklyActivities->sum('patient_coverage_amount');
        
        Log::info("[AdminReport] WEEKLY Stats", [
            'date_range' => $weeklyStart->toDateString() . ' to ' . $weeklyEnd->toDateString(),
            'patients' => $weeklyPatients,
            'revenue' => $weeklyRevenue
        ]);
        
        // Monthly stats
        $monthlyStart = Carbon::now()->startOfMonth();
        $monthlyEnd = Carbon::now()->endOfMonth();
        $monthlyActivities = PatientActivity::with('patient')
            ->whereBetween('created_at', [$monthlyStart, $monthlyEnd])
            ->get();
        $monthlyPatients = $monthlyActivities->count();
        $monthlyRevenue = $monthlyActivities->sum('unit_price');
        $monthlyCashReceived = $monthlyActivities->sum('patient_coverage_amount');
        
        Log::info("[AdminReport] MONTHLY Stats", [
            'date_range' => $monthlyStart->toDateString() . ' to ' . $monthlyEnd->toDateString(),
            'patients' => $monthlyPatients,
            'revenue' => $monthlyRevenue
        ]);
        
        // Yearly stats
        $yearlyStart = Carbon::now()->startOfYear();
        $yearlyEnd = Carbon::now()->endOfYear();
        $yearlyActivities = PatientActivity::with('patient')
            ->whereBetween('created_at', [$yearlyStart, $yearlyEnd])
            ->get();
        $yearlyPatients = $yearlyActivities->count();
        $yearlyRevenue = $yearlyActivities->sum('unit_price');
        $yearlyCashReceived = $yearlyActivities->sum('patient_coverage_amount');
        
        Log::info("[AdminReport] YEARLY Stats", [
            'date_range' => $yearlyStart->toDateString() . ' to ' . $yearlyEnd->toDateString(),
            'patients' => $yearlyPatients,
            'revenue' => $yearlyRevenue
        ]);

        // ================================
        // SUMMARY CARDS DATA
        // ================================
        $summaryCards = [
            'total_revenue'         => round($totalRevenue, 2),
            'private_revenue'       => round($totalPrivateRevenue, 2),
            'insured_patient_revenue'=> round($totalInsuredPatientRevenue, 2),
            'insurance_revenue'        => round($totalInsuranceRevenue, 2),
            'daily_patients'        => $dailyPatients,
            'daily_revenue'         => round($dailyRevenue, 2),
            'daily_cash_received' =>round($dailyCashReceived,2),
            'weekly_patients'       => $weeklyPatients,
            'weekly_revenue'        => round($weeklyRevenue, 2),
            'weekly_cash_received' =>round($weeklyCashReceived,2),
            'monthly_patients'      => $monthlyPatients,
            'monthly_revenue'       => round($monthlyRevenue, 2),
            'monthly_cash_received' =>round($monthlyCashReceived,2),
            'yearly_patients'       => $yearlyPatients,
            'yearly_revenue'        => round($yearlyRevenue, 2),
            'yearly_cash_received' =>round($yearlyCashReceived,2),
        ];
        
        Log::info("[AdminReport] === SUMMARY CARDS ===", $summaryCards);
        Log::info("[AdminReport] === BREAKDOWN DATA ===", [
            'total_revenue' => $totalRevenue,
            'total_insurance_revenue' => $totalInsuranceRevenue,
            'total_private_revenue' => $totalPrivateRevenue,
            
        ]);

        return view('admin.reports.index', compact(
            'summaryCards',
        ));
    }

    public function preview(Request $request)
    {
        $period = $request->period ?? 'daily';
        
        Log::info("[AdminReport] === PREVIEW METHOD STARTED ===", ['period' => $period]);
        
        

        // Determine date range based on period
        if ($period === 'daily') {
            $date = $request->date ? Carbon::parse($request->date) : Carbon::today();
            $start = $date->copy()->startOfDay();
            $end = $date->copy()->endOfDay();
        } elseif ($period === 'monthly') {
            $month = $request->month ? Carbon::parse($request->month . '-01') : Carbon::now();
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();
        } elseif ($period === 'yearly') {
            $year = $request->year ?? Carbon::now()->year;
            $start = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $end = Carbon::createFromDate($year, 12, 31)->endOfYear();
        } else {
            // Weekly - default to current week
            $weekDate = $request->week_date ? Carbon::parse($request->week_date) : Carbon::now()->startOfWeek();
            $start = $weekDate->copy()->startOfWeek();
            $end = $weekDate->copy()->endOfWeek();
        }

        // Get all patient activities for the period
        $activities= paymentSummary::with([
    'patient',
    'patientActivities',
])
->whereHas('patient', function ($query) use ($start, $end) {
    $query->whereBetween('treatment_date', [$start, $end]);
})
->get();

        
        Log::info("[AdminReport] Preview Period Range", [
            'period' => $period,
            'start' => $start->toDateTimeString(),
            'end' => $end->toDateTimeString(),
            'activities_count' => $activities->count()
        ]);

        

        $patient_share = $activities->sum('total_patient_amount');
        $insurance_share = $activities->sum('total_insurance_amount');
        $period_label = $start->format('d M, Y') . ' to ' . $end->format('d M, Y');
        // Round the totals
        $reportData = [
            'patient_share' => round($patient_share, 2),
            'insurance_share' => round($insurance_share, 2),
            'period_label' => $period_label,
        ];
        
        
        Log::info("[AdminReport] === PREVIEW REPORT DATA ===", [
            'patient_share_total' => $reportData['patient_share'],
            'insurance_share_total' => $reportData['insurance_share'],
        ]);
        
        Log::info("[AdminReport] === PREVIEW METHOD COMPLETED ===");

        return view('admin.reports.preview', compact(['reportData','activities']));
    }




    public function customDateRange(Request $request)
    {
        Log::info("[AdminReport] === CUSTOM DATE RANGE PAGE LOADED ===");
        
        // Get active providers for filter dropdown
        $providers = InsuranceProvider::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('admin.reports.custom-date-range', compact('providers'));
    }

    public function customPreview(Request $request)
    {
        Log::info("[AdminReport] === CUSTOM PREVIEW METHOD STARTED ===");
        
        // Validate dates
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();
        
        Log::info("[AdminReport] Custom Date Range", [
            'from' => $fromDate->toDateString(),
            'to' => $toDate->toDateString()
        ]);

        $provider = $request->provider;

// Get patient activities for the custom date range
$activities = paymentSummary::with([
    'patient',
    'patientActivities',
])
->whereHas('patient', function ($query) use ($fromDate, $toDate, $provider) {
    $query->whereBetween('treatment_date', [$fromDate, $toDate]);

    if (!is_null($provider)) {
        $query->where('insurance_provider_id', $provider);
    }
})
->get();

log::info("[AdminReport] Activities Query Built", [
    'from_date' => $fromDate->toDateString(),
    'to_date' => $toDate->toDateString(),
    'provider_filter' => $provider,
    'activities_count' => $activities->count()
]);


        $patient_share = $activities->sum('total_patient_amount');
        $insurance_share = $activities->sum('total_insurance_amount');
        $period_label = $fromDate->format('d M, Y') . ' to ' . $toDate->format('d M, Y');
        if($provider === null)
            {
            $insurarance_provider = 'All Providers';
        } else {
            $insurarance_provider = InsuranceProvider::find($provider)->name;
        }
    
        
        // Round the totals
        $reportData = [
            'patient_share' => round($patient_share, 2),
            'insurance_share' => round($insurance_share, 2),
            'period_label' => $period_label,
            'insurance_provider' => $insurarance_provider,

            ];
        
        Log::info("[AdminReport] === CUSTOM PREVIEW REPORT DATA ===", [
            'patient_share_total' => $reportData['patient_share'],
            'insurance_share_total' => $reportData['insurance_share'],
        ]);

        return view('admin.reports.custom-preview', compact(['reportData','activities']));
    }






    

    public function printReport(Request $request)
    {
        Log::info("[AdminReport] === UNIVERSAL PRINT REPORT METHOD STARTED ===");
        
        // Date range for index report (current month by default)
        $start = $request->start ? Carbon::parse($request->start)->startOfDay() : now()->startOfMonth();
        $end   = $request->end ? Carbon::parse($request->end)->endOfDay() : now()->endOfDay();

        // Base query: All patient activities
        $activitiesQuery = PatientActivity::with('patient')
            ->whereBetween('created_at', [$start, $end]);

        Log::info("[AdminReport] Query built for date range", ['start' => $start, 'end' => $end]);

        // Filter by provider if specified
        if ($request->provider && $request->provider !== 'private') {
            $activitiesQuery->whereHas('patient', function ($q) use ($request) {
                $q->where('insurance_provider_id', $request->provider);
            });
        }

        if ($request->provider === 'private') {
            $activitiesQuery->where(function ($q) {
                $q->whereNull('insurance_package_id')
                  ->orWhere('insurance_package_id', 0);
            });
        }

        $activities = $activitiesQuery->get();
        
        Log::info("[AdminReport] Activities Retrieved", ['count' => $activities->count()]);

        // Calculate totals
        $totalRevenue = 0;
        $totalPatientExpected = 0;
        $totalInsurancePayable = 0;
        $totalInsurancePaid = 0;
        $totalInsuranceOutstanding = 0;
        $totalPrivateRevenue = 0;

        foreach ($activities as $act) {
            $unitPrice = $act->unit_price ?? 0;
            $qty = 1;
            $lineTotal = $qty * $unitPrice;
            $totalRevenue += $lineTotal;

            $patient = $act->patient ?? Patient::find($act->patient_id);
            $package = null;

            if ($act->insurance_package_id) {
                $package = InsurancePackage::find($act->insurance_package_id);
            }

            if (!$package && $patient && $patient->insurance_package_id) {
                $package = InsurancePackage::find($patient->insurance_package_id);
            }

            $insuranceCoverPct = $package ? floatval($package->insurance_cover ?? 0) : 0;
            $patientCoverPct = 100 - $insuranceCoverPct;

            if ($insuranceCoverPct > 0 && $package) {
                $insurancePortion = $act->insurance_coverage_amount ?? (($insuranceCoverPct / 100) * $lineTotal);
                $totalInsurancePayable += $insurancePortion;
                $totalInsurancePaid += $act->insurance_paid_amount ?? 0;
            } else {
                $patientCoverPct = 100;
                $totalPrivateRevenue += $lineTotal;
            }

            $totalPatientExpected += (($patientCoverPct / 100) * $lineTotal);
        }

        $totalInsuranceOutstanding = $totalInsurancePayable - $totalInsurancePaid;

        // Get insurance breakdown
        $insuranceProviders = InsuranceProvider::where('is_active', true)->orderBy('name')->get();
        $insuranceRows = [];

        foreach ($insuranceProviders as $provider) {
            $providerActivities = $activities->filter(function ($act) use ($provider) {
                $patient = $act->patient ?? Patient::find($act->patient_id);
                return $patient && $patient->insurance_provider_id === $provider->id;
            });

            $payable = 0;
            $paid = 0;
            $private = 0;

            foreach ($providerActivities as $act) {
                $unitPrice = $act->unit_price ?? 0;
                $qty = 1;
                $lineTotal = $qty * $unitPrice;

                $package = $act->insurance_package_id ? 
                    InsurancePackage::find($act->insurance_package_id) : null;

                $patient = $act->patient ?? Patient::find($act->patient_id);
                if (!$package && $patient && $patient->insurance_package_id) {
                    $package = InsurancePackage::find($patient->insurance_package_id);
                }

                if ($package) {
                    $insuranceCoverPct = floatval($package->insurance_cover ?? 0);
                    $payable += $act->insurance_coverage_amount ?? (($insuranceCoverPct / 100) * $lineTotal);
                    $paid += $act->insurance_paid_amount ?? 0;
                } else {
                    $private += $lineTotal;
                }
            }

            $insuranceRows[] = [
                'provider' => $provider->name,
                'payable' => round($payable, 2),
                'paid' => round($paid, 2),
                'outstanding' => round($payable - $paid, 2),
                'private' => round($private, 2),
            ];
        }

        // Summary cards
        $summaryCards = [
            'total_revenue'         => round($totalRevenue, 2),
            'cash_expected'         => round($totalPatientExpected + $totalPrivateRevenue, 2),
            'private_revenue'       => round($totalPrivateRevenue, 2),
            'insurance_payable'     => round($totalInsurancePayable, 2),
            'insurance_outstanding' => round($totalInsuranceOutstanding, 2),
        ];

        Log::info("[AdminReport] === UNIVERSAL PRINT REPORT DATA PREPARED ===", [
            'summary' => $summaryCards,
            'insurance_rows_count' => count($insuranceRows)
        ]);

        return view('admin.reports.print', compact('summaryCards', 'insuranceRows'));
    }
}
