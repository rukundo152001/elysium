{{-- 
    resources/views/receipt-patient-simple.blade.php

    Simplified patient invoice based on the existing receipt.blade.php.
    Keeps the existing $patient relationships/fields and shows:
    - Patient name, Test ID, date
    - PRIVATE or insurance provider + category/member number
    - Examination/service and price
    - Total, patient responsibility, insurance responsibility (insured patients)
    - Cashier Signature & Stamp

    IMPORTANT:
    1. Confirm the view name and route/controller that passes $patient.
    2. This template assumes patientActivities have service_name, unit_price,
       patient_coverage_amount, insurance_coverage_amount, ref_number, and service.
    3. It follows the amounts already stored on patientActivities, like your old invoice.
       Verify that these are correct for your production billing rules.
--}}

<x-receipt-layout>
    @php
        $providerName = optional($patient->insuranceProvider)->name;
        $isPrivate = strtoupper(trim((string) $providerName)) === 'PRIVATE';

        $categoryName = optional($patient->insuranceCategory)->name;
        $package = $patient->insurancePackage;

        // Use the examination activities linked to this patient record.
        $activities = $patient->patientActivities ?? collect();

        // Total cost and coverage amounts are taken from the same fields as the old invoice.
        $total = (float) $activities->sum('unit_price');
        $patientShare = (float) $activities->sum('patient_coverage_amount');
        $insuranceShare = (float) $activities->sum('insurance_coverage_amount');

        // PRIVATE patients are responsible for the full total.
        if ($isPrivate) {
            $patientShare = $total;
            $insuranceShare = 0;
        }

        $treatmentDate = $patient->treatment_date
            ? \Carbon\Carbon::parse($patient->treatment_date)->format('d-m-Y')
            : 'N/A';

        $invoiceNumber = optional(\Carbon\Carbon::parse($patient->treatment_date))->format('Y')
            . '/' . ($patient->ref_number ?? 'N/A');

        $cashierName = optional(optional($activities->first())->cashier)->name ?? 'N/A';
    @endphp

      <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-2xl p-8 text-black-800 print:p-0 print:shadow-none relative pb-20 print:pb-24">
        {{-- Light watermark --}}
        <img
            src="{{ asset('img/logo.png') }}"
            alt="Watermark"
            aria-hidden="true"
            class="absolute opacity-5 pointer-events-none select-none z-0"
            style="top: 50%; left: 50%; transform: translate(-50%, -50%); width: 70%; height: auto; object-fit: contain;">

        <button
            type="button"
            onclick="window.print()"
            class="absolute right-4 top-4 z-20 rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800 print:hidden"
        >
            Print Invoice
        </button>

        <div class="relative z-10">
            {{-- Company header --}}
            <div class="mb-5 flex items-center gap-4 border-b-2 border-teal-600 pb-4 pr-28 print:pr-0">
                
                <!-- Logo (Left) -->
                    <div class="flex-shrink-0">
                        <img src="{{ asset('img/logo.png') }}" alt="EHRL Logo" class="h-16 w-auto">
                    </div>
                <div class="min-w-0 flex-1 text-center">
                    <h1 class="text-base font-bold uppercase tracking-wide text-teal-800">
                        EHRL - Elysium Healthcare
                    </h1>
                    <p class="text-xs text-gray-600">Rue de l'hopital, Gisenyi, Rwanda</p>
                    <p class="text-xs text-gray-600">
                        Tel: +250 787 555 090 | Email: info@elysiumhealthcareafrica.com
                    </p>
                    <h2 class="mt-2 text-lg font-extrabold tracking-widest text-gray-900">PATIENT INVOICE</h2>
                </div>
            </div>

            {{-- Essential patient and invoice details only --}}
            <div class="mb-5 grid grid-cols-2 gap-x-6 gap-y-3 rounded-lg border border-gray-200 p-4 text-sm">
                <div>
                    <p class="text-xs uppercase text-gray-500">Patient Name</p>
                    <p class="font-semibold">{{ trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')) ?: 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-gray-500">Test ID</p>
                    <p class="font-semibold">{{ $patient->ref_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-gray-500">Invoice No.</p>
                    <p class="font-semibold">{{ $invoiceNumber }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase text-gray-500">Date</p>
                    <p class="font-semibold">{{ $treatmentDate }}</p>
                </div>
                <div class="col-span-2 border-t border-gray-200 pt-3">
                    <p class="text-xs uppercase text-gray-500">Payment / Coverage Type</p>
                    @if ($isPrivate)
                        <p class="font-bold text-gray-800">PRIVATE — No insurance coverage</p>
                    @else
                        <p class="font-bold text-teal-800">
                            {{ $providerName ?: 'Insurance provider not recorded' }}
                            @if ($categoryName)
                                <span class="font-normal text-gray-600">({{ $categoryName }})</span>
                            @endif
                        </p>
                        <div class="mt-1 grid grid-cols-1 gap-1 text-xs text-gray-600 sm:grid-cols-2">
                            <p>
                                <span class="font-semibold">
                                    {{ strtoupper((string) $categoryName) === 'CBHI' ? 'Member / NID No.:' : 'Insurance No.:' }}
                                </span>
                                {{ $patient->insurance_number ?? 'N/A' }}
                            </p>
                            @if ($patient->affiliate_name)
                                <p><span class="font-semibold">Affiliate:</span> {{ $patient->affiliate_name }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Examination / service and price --}}
            <div class="mb-5">
                <table class="w-full border-collapse text-sm">
                    <thead>
                        <tr class="bg-teal-800 text-left text-white">
                            <th class="border border-teal-800 px-3 py-2">Examination / Service</th>
                            <th class="w-32 border border-teal-800 px-3 py-2 text-right">Amount (RWF)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activities as $activity)
                            <tr>
                                <td class="border border-gray-300 px-3 py-3">
                                    <p class="font-medium">{{ $activity->service_name ?: optional($activity->service)->name ?: 'Examination / Service' }}</p>
                                    
                                </td>
                                <td class="border border-gray-300 px-3 py-3 text-right tabular-nums">
                                    {{ number_format((float) ($activity->unit_price ?? 0), 0) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="border border-gray-300 px-3 py-4 text-center text-gray-500">
                                    No examination or service details found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Compact payment / coverage summary --}}
            <div class="ml-auto max-w-sm space-y-2 text-sm">
                <div class="flex justify-between gap-4 border-b border-gray-200 pb-2">
                    <span class="text-gray-600">Total examination cost</span>
                    <span class="font-bold tabular-nums">{{ number_format($total, 0) }} RWF</span>
                </div>

                @if ($isPrivate)
                    <div class="flex justify-between gap-4 border-b border-gray-200 pb-2">
                        <span class="text-gray-600">Patient responsibility (100%)</span>
                        <span class="font-semibold tabular-nums">{{ number_format($patientShare, 0) }} RWF</span>
                    </div>
                @else
                    <div class="flex justify-between gap-4 border-b border-gray-200 pb-2">
                        <span class="text-gray-600">
                            Patient coverage
                            @if ($package && $package->patient_cover !== null)
                                ({{ (int) $package->patient_cover }}%)
                            @endif
                        </span>
                        <span class="font-semibold tabular-nums">{{ number_format($patientShare, 0) }} RWF</span>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-gray-200 pb-2">
                        <span class="text-gray-600">
                            Insurance coverage
                            @if ($package && $package->insurance_cover !== null)
                                ({{ (int) $package->insurance_cover }}%)
                            @endif
                        </span>
                        <span class="font-semibold tabular-nums">{{ number_format($insuranceShare, 0) }} RWF</span>
                    </div>
                @endif
            </div>

            <p class="mt-5 text-center text-xs text-gray-500">
                Thank you for choosing Elysium Healthcare Rwanda.
            </p>

            {{-- Provider / cashier stamp and signature area --}}
            <div class="mt-8 grid grid-cols-2 gap-8 border-t border-gray-300 pt-5 text-center text-sm print:mt-6">
                
                <div class="flex min-h-28 flex-col items-center justify-end">
                    <p class="mb-2 font-semibold text-gray-700">Cashier Signature &amp; Stamp</p>
                    <div class="h-16 w-full max-w-48"></div>
                    <div class="w-4/5 border-t border-gray-500"></div>
                    <p class="mt-1 text-xs text-gray-500">{{ $cashierName }}</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            @page { size: A4; margin: 12mm; }
            body { background: #fff !important; }
            .print\:hidden { display: none !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</x-receipt-layout>
