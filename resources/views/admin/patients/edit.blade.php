<script>
@php
$method = App\Models\PaymentSummary::where('patient_id', $patient->id)->first()->payment_method ?? 'cash';
    $patientArray = [
        'ref_number' => $patient->ref_number,
        'referred_from' => $patient->referred_from,
        'name' => $patient->first_name . ' ' . $patient->last_name,
        'gender' => $patient->gender,
        'dob' => $patient->dob->format('Y-m-d'),
        'phone' => $patient->phone,
        'email' => $patient->email,
        'technician_id' => $patient->technician_id,
        'treatment_date' => $patient->treatment_date->format('Y-m-d'),
    ];

    $locationArray = [
        'selectedCountry' => $patient->country_id,
        'selectedProvince' => $patient->province_id, 
        'selectedDistrict' => $patient->district_id,
        'selectedSector' => $patient->sector_id,
        'selectedCell' => $patient->cell_id,
        'selectedVillage' => $patient->village_id,
    ];
    $insuranceArray= [
    'provider_id' => $patient->insurance_provider_id,
    'category_id' => $patient->insurance_category_id,
    'package_id' => $patient->insurance_package_id,
    'insurance_number' => $patient->insurance_number,
    'affiliate_name' => $patient->affiliate_name,
    'affiliate_insurance_number' => $patient->affiliate_insurance_number,
    'affiliate_relationship' => $patient->affiliate_relationship,
    'assigned_location' => $patient->assigned_location,
    'payment_method' => $method,
];

    $services = [];

    foreach ($patient->patientActivities as $activity) {
        $services[] = [
            'service_id' => $activity->service_id,
            'cost' => $activity->unit_price,
        ];
    }

    $serviceArray = $services;
    
    
@endphp

window.currentPatient = @json($patientArray);
window.currentAddress = @json($locationArray);
window.currentInsurance = @json($insuranceArray);
window.currentService = @json($serviceArray);

console.log('[v0] Patient data loaded into globals:', {
    currentPatient: window.currentPatient,
    currentAddress: window.currentAddress
});
</script>

<x-admin-layout :title="'Patients Management'">
<div 
    x-data="editPatient()" 
    class="max-w-6xl mx-auto bg-white rounded-2xl shadow p-8 mt-8"
>
    <h1 class="text-2xl font-semibold text-gray-800 mb-6">
        Edit Patient
    </h1>

    <form @submit.prevent="submitForm" id="editPatientForm">
        @csrf
        @method('PUT')

        <!-- PATIENT INFORMATION -->
        @include('admin.patients.partials.patient-information-edit')

        <!-- LOCATION (address) -->
        @include('admin.patients.partials.location-edit')

        <!-- INSURANCE -->
        @include('admin.patients.partials.insurance-edit')

        <!-- SERVICES -->
        @include('admin.patients.partials.service-edit')

        <!-- GRAND TOTAL / PAYMENT INFORMATION -->
        @include('admin.patients.partials.grand-total-edit')

        <!-- ACTION BUTTON -->
        <div class="flex justify-end gap-4 mt-6 pt-6 border-t-2 border-teal-200">
            <a 
                href="{{ route('admin.patients.details', $patient->id) }}"
                class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200"
            >
                Cancel
            </a>
            <button 
                type="submit"
                class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200"
            >
                Update Patient
            </button>
        </div>
    </form>
</div>

<!-- ✅ SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('editPatient', () => ({
        
        init() {
            console.log('[v0] Edit patient component initialized');
            // Window globals already set at page top
        },

        async submitForm() {
            try {
                console.log('[v0] Form submission started (EDIT mode)');
                
                const form = document.getElementById('editPatientForm');
                const patientDataInput = form.querySelector('input[name="patient_data"]');
                const addressDataInput = form.querySelector('input[name="address_data"]');
                const insuranceDataInput = form.querySelector('input[name="insurance_data"]');
                const serviceDataInput = form.querySelector('input[name="service_data"]');
                const grandTotalInput = form.querySelector('input[name="grand_total_data"]');

                const patientData = patientDataInput ? JSON.parse(patientDataInput.value) : {};
                const addressData = addressDataInput ? JSON.parse(addressDataInput.value) : {};
                const insuranceData = insuranceDataInput ? JSON.parse(insuranceDataInput.value) : {};
                const serviceData = serviceDataInput ? JSON.parse(serviceDataInput.value) : {};
                const grandTotalData = grandTotalInput ? JSON.parse(grandTotalInput.value) : {};

                console.log('[v0] Patient data:', patientData);
                console.log('[v0] Address data:', addressData);
                console.log('[v0] Insurance data:', insuranceData);
                console.log('[v0] Service data:', serviceData);
                console.log('[v0] Grand total data:', grandTotalData);

                const totals = {
                    grand_total: grandTotalData.grand_total ?? window.grandTotal ?? 0,
                    insurance_cost: grandTotalData.insurance_cost ?? window.insurancePays ?? 0,
                    patient_cost: grandTotalData.patient_cost ?? window.patientPays ?? 0
                };

                const formData = {
                    patient_data: patientDataInput?.value || '{}',
                    address_data: addressDataInput?.value || '{}',
                    insurance_data: insuranceDataInput?.value || '{}',
                    service_data: serviceDataInput?.value || '{}',
                    grand_total_data: JSON.stringify(totals),
                };

                console.log('[v0] Final payload ready:', formData);
                console.log('[v0] Sending to backend: /admin/patients/{{ $patient->id }}/update');

                const response = await axios.put('/admin/patients/{{ $patient->id }}/update', formData);

                console.log('[v0] Success response received:', response.data);

                if (response.data.redirect) {
                    window.location.href = response.data.redirect;
                }

            } catch (error) {
                console.error('[v0] SUBMISSION ERROR:', {
                    message: error.message,
                    status: error.response?.status,
                    statusText: error.response?.statusText,
                    data: error.response?.data,
                    fullError: error
                });

                if (error.response && error.response.status === 422) {
                    console.warn('[v0] Validation failed. Errors:', error.response.data.errors);
                    
                    const errors = error.response.data.errors;
                    let errorHtml = '<div style="text-align: left; font-family: monospace; font-size: 14px;">';
                    
                    for (const [field, messages] of Object.entries(errors)) {
                        errorHtml += `<div style="margin-bottom: 12px; border-left: 3px solid #ef4444; padding-left: 12px;">`;
                        errorHtml += `<strong style="color: #dc2626;">${field}:</strong><br/>`;
                        
                        const messageArray = Array.isArray(messages) ? messages : [messages];
                        messageArray.forEach(msg => {
                            errorHtml += `<span style="color: #991b1b; font-size: 13px;">• ${msg}</span><br/>`;
                        });
                        
                        errorHtml += '</div>';
                    }
                    errorHtml += '</div>';

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Errors!',
                        html: errorHtml,
                        width: 700,
                        didOpen: () => {
                            const popup = Swal.getHtmlContainer();
                            popup.style.maxHeight = '500px';
                            popup.style.overflowY = 'auto';
                        },
                        confirmButtonColor: '#dc2626',
                        confirmButtonText: 'Fix and Try Again'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed!',
                        text: error.response?.data?.message || 'Something went wrong. Check browser console for details.',
                        confirmButtonText: 'OK'
                    });
                }
            }
        }

    }));
});
</script>

</x-admin-layout>
