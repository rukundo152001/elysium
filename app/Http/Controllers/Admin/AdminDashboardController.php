<?php

namespace App\Http\Controllers\Admin;

use App\Models\PaymentSummary;
use App\Models\User;
use App\Models\Patient;
use App\Models\PatientActivity;
use Illuminate\View\View;
use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        // ---------------------------
        // 1. DASHBOARD STATISTICS
        // ---------------------------
        $stats = [
            'totalRevenue' => PaymentSummary::sum('total_amount'),
            'totalPatients' => Patient::count(),
            'activeStaff'   => User::count(), // Adjust later if you introduce roles
            'insurance_revenue' => PaymentSummary::where('is_private', false)->sum('total_insurance_amount'),
            'insured_patient_revenue' => PaymentSummary::where('is_private', false)->sum('total_patient_amount'),
            'privateRevenue' => PaymentSummary::where('is_private', true)->sum('total_amount'),
        ];

        // ---------------------------
        // 2. RECENT TRANSACTIONS (PATIENT ACTIVITIES)
        // ---------------------------
        $recentTransactions = PatientActivity::with(['patient', 'service'])
            ->latest()
            ->take(5)
            ->get();

        // ---------------------------
        // 3. SYSTEM ACTIVITY STREAM
        // ---------------------------
        $recentPatients = Patient::with('registeredBy')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($p) => [
                'type' => 'patient_registered',
                'message' => "Patient {$p->ref_number} registered by {$p->registeredBy->name}",
                'time' => $p->created_at
            ]);

        $recentPayments = PatientActivity::with('patient')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($pa) => [
                'type' => 'activity_created',
                'message' => "Service added for patient {$pa->patient->ref_number}",
                'time' => $pa->created_at
            ]);

        $systemActivity = collect()
            ->merge($recentPatients)
            ->merge($recentPayments)
            ->sortByDesc('time')
            ->take(7);

        // ---------------------------
        // 4. STAFF PERFORMANCE
        // ---------------------------

        // Fetch all users ("staff")
        $staff = User::with([
            'patientsCreatedBy.patientActivities',
            'patientsCreatedBy.paymentSummary'
        ])->get();

        $staffPerformance = $staff->map(function ($user) {

            $patients = $user->patientsCreatedBy;

            // Patients handled count
            $patientsHandled = $patients->count();

            // Revenue = SUM of all patient payment summaries
            $revenue = $patients->flatMap(function ($p) {
                return $p->paymentSummary;
            })->sum('total_amount');

            return [
                'name' => $user->name,
                'patients_handled' => $patientsHandled,
                'revenue_generated' => $revenue,
                'status' => 'Active'
            ];
        });

        return view('admin.dashboard', compact(
            'stats',
            'recentTransactions',
            'systemActivity',
            'staffPerformance'

        ));
    }

    public function patients(): View
    {
        return view('admin.patients.index');
    }

    
}
