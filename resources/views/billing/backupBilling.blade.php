<!-- resources/views/receipt.blade.php -->
<x-receipt-layout>
<div class="max-w-4xl mx-auto bg-white shadow-lg rounded-2xl p-8 text-gray-800 print:p-0 print:shadow-none relative pb-20 print:pb-24">

        <!-- Watermark (centered, under content, horizontal) -->
        <img 
            src="{{ asset('img/logo.png') }}" 
            alt="Watermark" 
            class="absolute opacity-5 pointer-events-none select-none z-0"
            style="top: 50%; left: 50%; transform: translate(-50%, -50%); width: 70%; height: auto; object-fit: contain;"
        >

        <!-- Print Button (hidden on print) -->
        <button 
            x-data 
            x-on:click="window.print()" 
            class="absolute top-1 right-20 bg-teal-600 hover:bg-teal-700 text-white text-sm px-4 py-2 rounded-lg shadow-md transition print:hidden z-10">
            🖨️ Print Invoice
        </button>

        <!-- All your existing content (unchanged except for spacing tweaks) -->
      <div class="relative z-10">
            <!-- Enhanced professional header with logo left, company info center, invoice details right -->
            <div class="border-b-2 border-teal-300 pb-5 mb-6">
                <!-- Top Row: Logo, Company Name & Details, Invoice Info -->
                <div class="flex items-start justify-between gap-6 mb-4">
                    <!-- Logo (Left) -->
                    <div class="flex-shrink-0">
                        <img src="{{ asset('img/logo.png') }}" alt="EHRL Logo" class="h-16 w-auto">
                    </div>

                    <!-- Company Information (Center) -->
                    <div class="flex-1 text-center">
                        <h1 class="text-lg font-bold text-teal-700 uppercase tracking-wider mb-1">EHRL - ELYSIUM HEALTHCARE</h1>
                        <p class="text-xs text-gray-600">Rue de l'Hopital, Gisenyi, Rwanda</p>
                        <p class="text-xs text-gray-600">Tel: +250 787 555 090 | Email: info@elysiumhealthcareafrica.com</p>
                    </div>

                    <!-- Invoice Details (Right) -->
                    <div class="flex-shrink-0 text-right">
                        <p class="text-sm font-bold text-teal-700 mb-2">OFFICIAL INVOICE</p>
                        <p class="text-xs text-gray-700"><span class="font-semibold">Invoice No:</span></p>
                        <p class="text-xs text-gray-800 font-semibold">
                            #{{ optional(\Carbon\Carbon::parse($patient->treatment_date))->format('Y') }}/{{ $patient->ref_number }}

                        </p>
                        <p class="text-xs text-gray-700 mt-2"><span class="font-semibold">Date:</span></p>
                        <p class="text-xs text-gray-800 font-semibold">
                            {{ $patient->treatment_date ? \Carbon\Carbon::parse($patient->treatment_date)->format('d-m-Y') : 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- 1. Patient Information -->
          <div class="bg-white rounded-2xl shadow p-6 mb-6 border border-teal-100">
               <h2 class="flex justify-between items-center text-lg font-semibold text-teal-800 border-b-2 border-teal-500 mb-4 pb-1">
                <span>1. Patient Information</span>
                <p>Test ID: {{ $patient->ref_number ?? 'N/A' }}</p>
               </h2>
               <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-x-8 gap-y-3 text-gray-700">
                    <p>
                        <span class="font-semibold text-teal-700">Full Name:</span>
                        {{ $patient->first_name }} {{ $patient->last_name }}
                    </p>

                    <p>
                        <span class="font-semibold text-teal-700">Gender:</span>
                        {{ ucfirst($patient->gender ?? 'N/A') }}
                    </p>

                    <p>
                        <span class="font-semibold text-teal-700">Date of Birth:</span>
                        {{ $patient->dob ? \Carbon\Carbon::parse($patient->dob)->format('d-m-Y') : 'N/A' }}
                    </p>

                    <p>
                        <span class="font-semibold text-teal-700">Mobile Number:</span>
                        {{ $patient->phone ?? 'N/A' }}
                    </p>
                    </div>
                    <div class="border-t border-teal-500 my-4"></div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-x-8 gap-y-3 text-gray-700">
                    <p>
                        <span class="font-semibold text-teal-700">Referred From :</span>
                        {{ $patient->referred_from ?? 'N/A' }}
                    </p>
                
                    <p>
                        <span class="font-semibold text-teal-700">Technician:</span>
                        {{ optional($patient->technician)->name ?? 'N/A' }}
                    </p>

                    <p>
                        <span class="font-semibold text-teal-700">Treatment Date:</span>
                        {{ $patient->treatment_date ? \Carbon\Carbon::parse($patient->treatment_date)->format('d-m-Y') : 'N/A' }}
                    </p>

                    <p>
                        <span class="font-semibold text-teal-700">Cashier:</span>
                        {{ $patient->patientActivities->first()->cashier->name ?? 'N/A' }}
                    </p>
                </div>
        </div>


     <!-- 2. Insurance Information -->
     <div class="bg-white rounded-2xl shadow p-6 mb-6 border border-teal-100">
    <h2 class="text-lg font-semibold text-teal-800 mb-4 border-b-2 border-teal-500 pb-1">2. Insurance Information</h2>

    @if(optional($patient->insuranceProvider)->name === 'PRIVATE')
        <!-- Private Payment Case -->
        <div class="text-gray-700">
            <p class="text-red-700 font-medium mb-2">This patient is registered as a <span class="font-semibold">PRIVATE</span> client.</p>
            <p class="text-sm text-gray-600 mb-4">
                No insurance coverage applies. All costs are paid directly by the patient.
            </p>
        </div>
    @else
        <!-- Insured Case -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-x-8 gap-y-3 text-gray-700">
            <p>
                <span class="font-semibold text-teal-700">Insurance Provider:</span>
                {{ optional($patient->insuranceProvider)->name ?? 'N/A' }} || {{ optional($patient->insuranceCategory)->name }}
            </p>
            

            <p>
                @if ($patient->insuranceCategory->name ==='CBHI')
                <span class="font-semibold text-teal-700">NID:</span>
                @else
                <span class="font-semibold text-teal-700">INS No:</span>
                @endif
                {{ $patient->insurance_number ?? 'N/A' }}
            </p>

            <p>
                <span class="font-semibold text-teal-700">Patient Coverage:</span>
                {{ (int) $patient->insurancePackage->patient_cover ?? '0' }}%
            </p>

            <p>
                <span class="font-semibold text-teal-700">Insurance Coverage:</span>
                {{ (int) $patient->insurancePackage->insurance_cover ?? '0' }}%
            </p>

            
        </div>

        <!-- Divider -->
        <div class="border-t border-teal-600 my-4"></div>

        @if ($patient->insuranceCategory->name ==='CBHI')
        <!-- Affiliate Information -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-x-8 gap-y-3 text-gray-700">
            <p>
                <span class="font-semibold text-teal-700">HouseHold Name:</span>
                {{ $patient->affiliate_name ?? 'N/A' }}
            </p>

            <p>
                <span class="font-semibold text-teal-700">HouseHold ID.:</span>
                {{ $patient->affiliate_insurance_number ?? 'N/A' }}
            </p>
        </div>
        @else
        <!-- Affiliate Information -->
        <h3 class="text-md font-semibold text-teal-700 mb-3">Affiliate Information</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-x-8 gap-y-3 text-gray-700">
            <p>
                <span class="font-semibold text-teal-700">Affiliate Name:</span>
                {{ $patient->affiliate_name ?? 'N/A' }}
            </p>

            <p>
                <span class="font-semibold text-teal-700">Affiliate Insurance No.:</span>
                {{ $patient->affiliate_insurance_number ?? 'N/A' }}
            </p>

            <p>
                <span class="font-semibold text-teal-700">Assigned Location:</span>
                {{ $patient->assigned_location ?? 'N/A' }}
            </p>

            <p>
                <span class="font-semibold text-teal-700">Relationship:</span>
                {{ $patient->affiliate_relationship ?? 'N/A' }}
            </p>
        </div>
        @endif
    @endif
      </div>


     <!-- 3. Service Details -->
      <div class="bg-white rounded-2xl shadow p-6 mb-6 border border-teal-100">
    <h3 class="text-md font-semibold text-teal-700 mb-3">3. Service Details</h3>
    
    <div class="overflow-x-auto mb-6">
    <table class="min-w-full border border-gray-300 text-sm text-left">
        <thead class="bg-teal-100 text-teal-800">
            <tr>
                <th class="px-3 py-2 border">Service Type</th>
                <th class="px-3 py-2 border">Service Name</th>
                <th class="px-3 py-2 border text-center">Total (RWF)</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Calculate the grand total once before the loop
                $grandTotal = $patient->patientActivities->sum('unit_price');
            @endphp

            @foreach($patient->patientActivities as $ptActivity)
                @php $service = $ptActivity->service; @endphp
                
                @if($service)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 border">{{ $service->category ?? 'N/A' }}</td>
                        <td class="px-3 py-2 border">{{ $service->name ?? '-' }}</td>
                        <td class="px-3 py-2 border text-right">{{ number_format($ptActivity->unit_price ?? 0) }}</td>
                    </tr>
                @endif
            @endforeach

            {{-- Grand total row --}}
            <tr class="bg-teal-50 font-semibold">
                <td colspan="2" class="text-right px-3 py-2 border">Total</td>
                <td class="text-right px-3 py-2 border">
                    {{ number_format($grandTotal, 2) }}
                </td>
            </tr>
        </tbody>
    </table>
</div>

      </div>


        <!-- 4. Payment Summary -->
         <div class="bg-white rounded-2xl shadow p-6 mb-6 border border-teal-100">
    <!-- 4. Payment Details -->
    <h2 class="text-lg font-semibold text-teal-800 mb-4 border-b-2 border-teal-500 pb-1">
        4. Payment Details
    </h2>

    @php
        // Group patientActivities by ref_number
        $groupedPayments = $patient->patientActivities->groupBy('ref_number');
    @endphp

    @foreach($groupedPayments as $refNumber => $patientActivities)
        @php
            // Try to load PaymentSummary
            $summary = \App\Models\PaymentSummary::where('ref_number', $refNumber)->first();

            // Raw totals (from patient activities or patientActivities)
            $rawTotal = $patientActivities->sum('unit_price');
            $rawInsurance = $patientActivities->sum('insurance_coverage_amount');
            $rawPatient = $patientActivities->sum('patient_coverage_amount');

            // Decide totals: Use summary *if it exists & is correct*
            if ($summary && $summary->total_amount == $rawTotal) {
                $total = $summary->total_amount;
                $insuranceAmount = $summary->total_insurance_amount;
                $patientAmount = $summary->total_patient_amount;
            } else {
                // Fallback to LIVE calculation
                $total = $rawTotal;
                $insuranceAmount = $rawInsurance;
                $patientAmount = $rawPatient;
            }

            // Insurance package percentages
            $insuranceCover = $patient->insurancePackage->insurance_cover ?? 0;
            $patientCover = $patient->insurancePackage->patient_cover ?? 0;

            // Private patient?
            $isPrivate = optional($patient->insuranceProvider)->name === 'PRIVATE';
        @endphp

        <div class="mb-4 border border-teal-100 rounded-xl p-4 bg-gray-50">

            {{-- ======================= PRIVATE ========================= --}}
 @if($isPrivate)
    <!-- PRIVATE PATIENT SUMMARY -->
    <div class="space-y-2 text-gray-700">

        <div class="flex justify-between border-b pb-1">
            <span class="font-semibold text-teal-700">Grand Total (100%)</span>
            <span class="font-medium text-teal-800">{{ number_format($total, 0) }} RWF</span>
        </div>

        <div class="flex justify-between border-b pb-1">
            <span class="font-semibold text-teal-700">Patient Pay (100%)</span>
            <span class="font-medium">{{ number_format($patientAmount ?: $total, 0) }} RWF</span>
        </div>

        <div class="flex justify-between border-b pb-1">
            <span class="font-semibold text-teal-700">Insurance</span>
            <span class="font-medium">No Insurance</span>
        </div>

    </div>

    {{-- Balance --}}
    @if(($patientAmount ?: $total) < $total)
        <p class="mt-4 text-yellow-700 font-medium">
            ⚠️ Patient Balance: {{ number_format($total - ($patientAmount ?: 0), 0) }} RWF
        </p>
    @else
        <p class="mt-4 text-teal-700 font-medium">✅ Fully Paid</p>
    @endif


@else
    <!-- INSURED PATIENT SUMMARY -->
    <div class="space-y-2 text-gray-700">

        <div class="flex justify-between border-b pb-1">
            <span class="font-semibold text-teal-700">Grand Total (100%)</span>
            <span class="font-medium text-teal-800">{{ number_format($total, 0) }} RWF</span>
        </div>

        <div class="flex justify-between border-b pb-1">
            <span class="font-semibold text-teal-700">
                Patient Pay ({{ (int) $patientCover }}%)
            </span>
            <span class="font-medium">{{ number_format($patientAmount, 0) }} RWF</span>
        </div>

        <div class="flex justify-between border-b pb-1">
            <span class="font-semibold text-teal-700">
                Insurance Pay ({{ (int) $insuranceCover }}%)
            </span>
            <span class="font-medium">{{ number_format($insuranceAmount, 0) }} RWF</span>
        </div>

    </div>

    <div class="mt-4 text-gray-600 text-sm italic">
        *Insurance details are shown for reference; insurer may pay later.*
    </div>
@endif
            {{-- ======================= END PRIVATE/INSURED ========================= --}}

            {{-- Wrong summary warning --}}
            @if($summary && $summary->total_amount != $rawTotal)
                <p class="mt-3 text-red-700 text-sm font-semibold">
                    ⚠️ Warning: PaymentSummary mismatch (System recalculated values)
                </p>
            @endif

        </div>
    @endforeach
</div>




        <!-- 5. Signatures -->
       <div class="mt-8 pt-4 border-t border-gray-300 print:mt-6 print:pt-2 print:text-sm">

    <table class="w-full text-center border-collapse">
        <thead>
            <tr class="text-gray-800 font-medium">

                @if (
                    $patient->insuranceCategory->name === 'RAMA' ||
                    (
                        strtolower($patient->insuranceProvider->name) === 'mmi' &&
                        optional($patient->insurancePackage)->insurance_cover == 100
                    )
                )
                    <th class="pb-8 print:pb-4">Patient Signature</th>
                @endif

                <th class="pb-8 print:pb-4">Cashier Signature</th>

                @if (
                    $patient->insuranceCategory->name === 'RAMA' ||
                    (
                        strtolower($patient->insuranceProvider->name) === 'mmi' &&
                        optional($patient->insurancePackage)->insurance_cover == 100
                    )
                )
                    <th class="pb-8 print:pb-4">Doctor Signature & Stamp</th>
                @endif

            </tr>
        </thead>

        <tbody>
            <tr>

                @if (
                    $patient->insuranceCategory->name === 'RAMA' ||
                    (
                        strtolower($patient->insuranceProvider->name) === 'mmi' &&
                        optional($patient->insurancePackage)->insurance_cover == 100
                    )
                )
                    <td class="align-bottom">
                        <div class="print:w-4/5"></div>
                    </td>
                @endif

                <td class="align-bottom">
                    <div class="print:w-4/5"></div>
                </td>

                @if (
                    $patient->insuranceCategory->name === 'RAMA' ||
                    (
                        strtolower($patient->insuranceProvider->name) === 'mmi' &&
                        optional($patient->insurancePackage)->insurance_cover == 100
                    )
                )
                    <td class="align-bottom">
                        <div class="print:w-4/5"></div>
                    </td>
                @endif

            </tr>

            <tr>

                @if (
                    $patient->insuranceCategory->name === 'RAMA' ||
                    (
                        strtolower($patient->insuranceProvider->name) === 'mmi' &&
                        optional($patient->insurancePackage)->insurance_cover == 100
                    )
                )
                    <td class="align-bottom">
                        <div class="border-t border-gray-500 w-3/4 mx-auto print:w-4/5"></div>
                    </td>
                @endif

                <td class="align-bottom">
                    <div class="border-t border-gray-500 w-3/4 mx-auto print:w-4/5"></div>
                </td>

                @if (
                    $patient->insuranceCategory->name === 'RAMA' ||
                    (
                        strtolower($patient->insuranceProvider->name) === 'mmi' &&
                        optional($patient->insurancePackage)->insurance_cover == 100
                    )
                )
                    <td class="align-bottom">
                        <div class="border-t border-gray-500 w-3/4 mx-auto print:w-4/5"></div>
                    </td>
                @endif

            </tr>
        </tbody>
    </table>

</div>

        
      </div>

        <!-- Removed fixed footer - company info now in header -->

</div>

</x-receipt-layout>
