<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient; // assuming invoices are tied to patients

class InvoiceController extends Controller
{
    /**
     * Display the invoice for a given reference number.
     */
    public function ptInvoice(Request $request)
{
    $refNumber = $request->query('ref_number');

    // Load all necessary data for receipt
    $patient = Patient::with([
        'patientActivities.service',   // services inside patient activity
        'insurancePackage',            // insurance % split
        'insuranceProvider',           // private check
        
    ])
    ->where('ref_number', $refNumber)
    ->firstOrFail();

    // Fetch PaymentSummary for this receipt
    $paymentSummary = \App\Models\PaymentSummary::where('ref_number', $refNumber)->first();

    return view('billing.patient-invoice', [
        'patient' => $patient,
        'paymentSummary' => $paymentSummary
    ]);
}


 public function insuranceInvoice(Request $request)
{
    $refNumber = $request->query('ref_number');

    // Load all necessary data for receipt
    $patient = Patient::with([
        'patientActivities.service',   // services inside patient activity
        'insurancePackage',            // insurance % split
        'insuranceProvider',           // private check
        'insuranceCategory',
        'district',              // District check
        'sector',              // Sector check

    ])
    ->where('ref_number', $refNumber)
    ->firstOrFail();
    
    // Fetch PaymentSummary for this receipt
    $paymentSummary = \App\Models\PaymentSummary::where('ref_number', $refNumber)->first();

    return view('billing.insurance-invoice', [
        'patient' => $patient,
        'paymentSummary' => $paymentSummary
    ]);
}

}
