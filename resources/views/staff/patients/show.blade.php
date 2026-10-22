<x-staff-layout>
<div class="max-w-7xl mx-auto p-6">

@if(session('success'))
    <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
@endif

    <!-- Header and Actions -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h1 class="text-4xl font-bold text-teal-900">Patient Details</h1>
            <p class="text-gray-600 mt-1">Reference #{{ $patient->ref_number ?? 'N/A' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            
        
           
       

@php
    $isInsured = $patient->insuranceProvider
        && $patient->insuranceProvider->name !== 'PRIVATE';
@endphp

<button
    type="button"
    class="px-4 py-2 bg-teal-600 text-white font-medium rounded-lg hover:bg-teal-700 transition"
    data-patient-invoice-url="{{ route('patients.invoice', ['ref_number' => $patient->ref_number]) }}"
    data-insurance-invoice-url="{{ route('patients.insurance-invoice', ['ref_number' => $patient->ref_number]) }}"
    data-is-insured="{{ $isInsured ? '1' : '0' }}"
    onclick="prepareInvoiceOptions(this)"
>
    <i class="bi bi-printer"></i>
    Print Invoice
</button>

{{-- Include once, outside any loops --}}
@include('billing.popup-invoice-button-modal')


            
            <a href="{{ url()->previous() }}" 
               class="px-4 py-2 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition">
               Back
            </a>
        </div>
    </div>

    <!-- Patient Overview - improved layout and spacing -->
    <div class="bg-white rounded-2xl shadow-sm p-8 mb-8 border border-gray-200 hover:shadow-md transition">
        <h2 class="text-xl font-bold text-teal-900 mb-6 pb-4 border-b-2 border-teal-200">Patient Overview</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div>
                <p class="text-gray-600 text-sm font-medium mb-2">Full Name</p>
                <p class="text-gray-900 font-semibold">{{ $patient->first_name }} {{ $patient->last_name }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm font-medium mb-2">Gender</p>
                <p class="text-gray-900 font-semibold">{{ ucfirst($patient->gender ?? 'N/A') }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm font-medium mb-2">Date of Birth</p>
                <p class="text-gray-900 font-semibold">{{ $patient->dob ? \Carbon\Carbon::parse($patient->dob)->format('M d, Y') : 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm font-medium mb-2">Treatment Date</p>
                <p class="text-gray-900 font-semibold">{{ $patient->treatment_date ? \Carbon\Carbon::parse($patient->treatment_date)->format('M d, Y') : 'N/A' }}</p>
            </div>
        </div>
        <div class="mt-6 pt-6 border-t border-gray-200">
            <p class="text-gray-600 text-sm font-medium mb-2">Referred From</p>
            <p class="text-gray-900 font-semibold">{{ $patient->referred_from ?? 'N/A' }}</p>
        </div>
    </div>

    <!-- Contact & Address Information - combined into one section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Contact Information - Email removed -->
        <div class="bg-white rounded-2xl shadow-sm p-8 border border-gray-200 hover:shadow-md transition">
            <h2 class="text-xl font-bold text-teal-900 mb-6 pb-4 border-b-2 border-teal-200">Contact Information</h2>
            <div>
                <p class="text-gray-600 text-sm font-medium mb-2">Phone Number</p>
                <p class="text-gray-900 font-semibold">{{ $patient->phone ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Address Details -->
        <div class="bg-white rounded-2xl shadow-sm p-8 border border-gray-200 hover:shadow-md transition">
            <h2 class="text-xl font-bold text-teal-900 mb-6 pb-4 border-b-2 border-teal-200">Address</h2>
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-1">Country</p>
                        <p class="text-gray-900 font-semibold">{{ $patient->country->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-1">Province</p>
                        <p class="text-gray-900 font-semibold">{{ $patient->province->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-1">District</p>
                        <p class="text-gray-900 font-semibold">{{ $patient->district->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600 text-sm font-medium mb-1">Sector</p>
                        <p class="text-gray-900 font-semibold">{{ $patient->sector->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Registration Details -->
    <div class="bg-white rounded-2xl shadow-sm p-8 mb-8 border border-gray-200 hover:shadow-md transition">
        <h2 class="text-xl font-bold text-teal-900 mb-6 pb-4 border-b-2 border-teal-200">Registration Details</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <p class="text-gray-600 text-sm font-medium mb-2">Registered By</p>
                <p class="text-gray-900 font-semibold">{{ optional($patient->registeredBy)->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm font-medium mb-2">Technician</p>
                <p class="text-gray-900 font-semibold">{{ optional($patient->technician)->name ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Insurance Information - redesigned with better layout -->
@if($patient->insuranceProvider)
@if ($patient->insuranceProvider->name !=="PRIVATE")
  <div class="bg-white rounded-2xl shadow-sm p-8 mb-8 border border-gray-200 hover:shadow-md transition">
    <h2 class="text-xl font-bold text-teal-900 mb-6 pb-4 border-b-2 border-teal-200">Insurance Details</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div>
            <p class="text-gray-600 text-sm font-medium mb-2">Insurance Provider</p>
            <p class="text-gray-900 font-semibold">{{ $patient->insuranceProvider->name ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-gray-600 text-sm font-medium mb-2">Insurance Category</p>
            <p class="text-gray-900 font-semibold">{{ optional($patient->insuranceCategory)->name ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-gray-600 text-sm font-medium mb-2">Beneficiary Insurance No</p>
            <p class="text-gray-900 font-semibold">{{ $patient->insurance_number ?? 'N/A' }}</p>
        </div>
        <div>
            <p class="text-gray-600 text-sm font-medium mb-2">Patient Cover</p>
            <div class="flex items-center gap-2">
                <div class="flex-1 bg-gray-200 rounded-full h-2">
                    <div class="bg-teal-600 h-2 rounded-full" style="width: {{ (int) ($patient->insurancePackage->patient_cover) }}%"></div>
                </div>
                <span class="text-gray-900 font-semibold min-w-fit">{{ (int) ($patient->insurancePackage->patient_cover) }}%</span>
            </div>
        </div>
        <div>
            <p class="text-gray-600 text-sm font-medium mb-2">Insurance Cover</p>
            <div class="flex items-center gap-2">
                <div class="flex-1 bg-gray-200 rounded-full h-2">
                    <div class="bg-teal-500 h-2 rounded-full" style="width: {{ (int) ($patient->insurancePackage->insurance_cover) }}%"></div>
                </div>
                <span class="text-gray-900 font-semibold min-w-fit">{{ (int) ($patient->insurancePackage->insurance_cover) }}%</span>
            </div>
        </div>
    </div>

    <!-- Affiliate Details with affiliate_relationship added -->
     @if ($patient->insuranceCategory->name =='CBHI')
    <div class="bg-teal-50 rounded-lg p-6 border border-teal-200">
        <h3 class="text-lg font-bold text-teal-900 mb-4">Household Information</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <p class="text-gray-600 text-sm font-medium mb-2">Household Name</p>
                <p class="text-gray-900 font-semibold">{{ $patient->affiliate_name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm font-medium mb-2">Household ID</p>
                <p class="text-gray-900 font-semibold">{{ $patient->affiliate_insurance_number ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm font-medium mb-2">Relationship</p>
                <p class="text-gray-900 font-semibold">{{ $patient->affiliate_relationship ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
     
     @else
    <div class="bg-teal-50 rounded-lg p-6 border border-teal-200">
        <h3 class="text-lg font-bold text-teal-900 mb-4">Affiliate Details</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <p class="text-gray-600 text-sm font-medium mb-2">Affiliate Name</p>
                <p class="text-gray-900 font-semibold">{{ $patient->affiliate_name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm font-medium mb-2">Affiliate Insurance No</p>
                <p class="text-gray-900 font-semibold">{{ $patient->affiliate_insurance_number ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm font-medium mb-2">Assigned Location / Affectation</p>
                <p class="text-gray-900 font-semibold">{{ $patient->assigned_location ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-gray-600 text-sm font-medium mb-2">Relationship</p>
                <p class="text-gray-900 font-semibold">{{ $patient->affiliate_relationship ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
    @endif
</div>
@endif
@endif

<!-- Payment History - improved styling -->
@foreach($patient->patientActivities->groupBy('ref_number') as $refNumber => $groupedPayments)

    @php
        $summary = \App\Models\PaymentSummary::where('ref_number', $refNumber)->first();
        $sumUnitPrice = $groupedPayments->sum('unit_price');
        $sumInsurance = $groupedPayments->sum('insurance_coverage_amount');
        $sumPatient = $groupedPayments->sum('patient_coverage_amount');
        $isMismatch =
            ($summary && (
                $summary->total_amount != $sumUnitPrice ||
                $summary->total_insurance_amount != $sumInsurance ||
                $summary->total_patient_amount != $sumPatient
            ));
    @endphp

    <div class="bg-white rounded-2xl shadow-sm mb-8 overflow-hidden border border-gray-200 hover:shadow-md transition @if($isMismatch) border-red-400 @endif">

        <!-- Header -->
        <div class="bg-gradient-to-r from-teal-50 to-gray-50 px-8 py-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <p class="text-2xl font-bold text-teal-900">
                        Payment {{ $refNumber }}
                    </p>
                    <p class="text-gray-600 mt-1">
                        {{ $summary?->paid_at ? $summary->paid_at->format('F d, Y') : 'N/A' }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-gray-700 mb-1">
                        <span class="text-gray-600 text-sm">Method:</span>
                        <span class="font-semibold text-teal-900 ml-2">
                            {{ ucfirst($summary->payment_method ?? 'N/A') }}
                        </span>
                    </p>
                    <p class="text-gray-600 text-sm">
                        Cashier: {{ optional($groupedPayments->first()->cashier)->name ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </div>

        @if($isMismatch)
            <div class="bg-red-50 text-red-700 px-8 py-3 border-b border-red-200 text-sm">
                ⚠ Mismatch Alert: PaymentSummary totals do not match calculated totals. ({{ number_format($summary->total_amount, 2) }} vs {{ number_format($sumUnitPrice, 2) }})
            </div>
        @endif

        <!-- Table -->
        <div class="p-8 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b-2 border-teal-200">
                        <th class="px-4 py-3 text-left text-gray-900 font-semibold">Service</th>
                        <th class="px-4 py-3 text-right text-gray-900 font-semibold">Unit Price</th>
                        <th class="px-4 py-3 text-right text-gray-900 font-semibold">Insurance Cover</th>
                        <th class="px-4 py-3 text-right text-gray-900 font-semibold">Patient Cover</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groupedPayments as $p)
                        <tr class="border-b border-gray-100 hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-900">{{ $p->service_name ?? 'N/A' }}</td>
                            <td class="px-4 py-3 text-right text-gray-900">{{ number_format($p->unit_price, 2) }}</td>
                            <td class="px-4 py-3 text-right text-teal-600 font-semibold">{{ number_format($p->insurance_coverage_amount, 2) }}</td>
                            <td class="px-4 py-3 text-right text-gray-900">{{ number_format($p->patient_coverage_amount, 2) }}</td>
                        </tr>
                    @endforeach
                    <!-- Total Row -->
                    <tr class="bg-teal-50 border-t-2 border-teal-200 font-bold">
                        <td class="px-4 py-4 text-teal-900">Total</td>
                        <td class="px-4 py-4 text-right text-teal-900">{{ number_format($summary->total_amount ?? 0, 2) }}</td>
                        <td class="px-4 py-4 text-right text-teal-700">{{ number_format($summary->total_insurance_amount ?? 0, 2) }}</td>
                        <td class="px-4 py-4 text-right text-teal-900">{{ number_format($summary->total_patient_amount ?? 0, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="bg-gray-50 px-8 py-4 text-xs text-gray-500 border-t border-gray-200">
            *Insurance details shown for reference; insurer may process payments later.
        </div>
    </div>
@endforeach



<!-- A4 Size Enforcement -->
<style>
    @page {
        margin: 8mm;
        size: portrait; /* 👈 Forces vertical orientation */
    }

    @media print {
        html, body {
            width: auto !important;
            height: auto !important;
            margin: 0;
            padding: 0;
            overflow: visible !important;
        }

        .max-w-7xl {
            max-width: none !important;
            max-height: none !important;
        }

        * {
            overflow: visible !important;
        }

        footer {
            margin-bottom: 8mm !important;
        }

        .grid-cols-3 > div {
            margin: 0 4mm;
        }
    }
</style>


</div>
{{-- Other page content --}}



</x-staff-layout>
