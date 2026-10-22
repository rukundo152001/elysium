<?php

namespace App\Http\Controllers;

use App\Models\PatientActivity;
use App\Models\PaymaentSummary;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index()
    {
        $patientActivities = PatientActivity::with('patient')
            ->latest()
            ->paginate(50);

        return view('payments.index', compact('payments'));
    }

    public function show($id)
    {
        $patientActivities = PatientActivity::with('patient')->findOrFail($id);
        
        return view('receipt', compact('patientActivities'));
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,paid,cancelled',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string'
        ]);

        $payment = PatientActivity::findOrFail($id);
        
        $payment->update([
            'status' => $validated['status'],
            'paid_amount' => $validated['paid_amount'] ?? $payment->amount,
            'payment_method' => $validated['payment_method'] ?? $payment->payment_method,
            'paid_at' => $validated['status'] === 'paid' ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated successfully'
        ]);
    }

    public function report(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $payments = PatientActivity::with('patient')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'paid')
            ->get();

        $summary = [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'by_method' => $payments->groupBy('payment_method')->map->count(),
            'by_date' => $payments->groupBy(fn($p) => $p->created_at->format('Y-m-d'))->map->sum('amount')
        ];

        return view('payments.report', compact('payments', 'summary', 'startDate', 'endDate'));
    }
}
