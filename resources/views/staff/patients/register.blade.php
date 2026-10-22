<x-staff-layout>
<div 
    x-data="registerPatient()" 
    class="max-w-6xl mx-auto bg-white rounded-2xl shadow p-8 mt-8"
>
    <h1 class="text-2xl font-semibold text-gray-800 mb-6">
        Patient Registration
    </h1>

    <form @submit.prevent="submitForm" id="registrationForm">

        <!-- PATIENT INFORMATION -->
        @include('staff.patients.partials.patient-information')

        <!-- LOCATION (address) -->
        @include('staff.patients.partials.location')

        <!-- INSURANCE -->
        @include('staff.patients.partials.insurance')

        <!-- SERVICES -->
        @include('staff.patients.partials.service')

        <!-- GRAND TOTAL / PAYMENT INFORMATION -->
        @include('staff.patients.partials.grand-total')

        <!-- ACTION BUTTON -->
        <div class="flex justify-end mt-6 pt-6 border-t-2 border-teal-200">
            <button 
                type="submit"
                class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200"
            >
                Save Registration
            </button>
        </div>
    </form>
</div>

<!-- ✅ SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('registerPatient', () => ({
        
        init() {
            console.log('[v0] Register component initialized');
        },

        async submitForm() {
    try {
        console.log('[v0] Form submission started');
        
        const form = document.getElementById('registrationForm');
        const patientDataInput = form.querySelector('input[name="patient_data"]');
        const addressDataInput = form.querySelector('input[name="address_data"]');
        const insuranceDataInput = form.querySelector('input[name="insurance_data"]');
        const serviceDataInput = form.querySelector('input[name="service_data"]');
        const grandTotalInput = form.querySelector('input[name="grand_total_data"]'); // 🟢 ADDED

        const patientData = patientDataInput ? JSON.parse(patientDataInput.value) : {};
        const addressData = addressDataInput ? JSON.parse(addressDataInput.value) : {};
        const insuranceData = insuranceDataInput ? JSON.parse(insuranceDataInput.value) : {};
        const serviceData = serviceDataInput ? JSON.parse(serviceDataInput.value) : {};
        const grandTotalData = grandTotalInput ? JSON.parse(grandTotalInput.value) : {}; // 🟢 ADDED

        console.log('[v0] Patient data:', patientData);
        console.log('[v0] Address data:', addressData);
        console.log('[v0] Insurance data:', insuranceData);
        console.log('[v0] Service data:', serviceData);
        console.log('[v0] Grand total data:', grandTotalData); // 🟢 ADDED

        // Optional: fallback to window values if frontend computed totals are global
        const totals = {
            grand_total: grandTotalData.grand_total ?? window.grandTotal ?? 0,
            insurance_cost: grandTotalData.insurance_cost ?? window.insurancePays ?? 0,
            patient_cost: grandTotalData.patient_cost ?? window.patientPays ?? 0
        };

        console.log('[v0] Payment totals (final merged):', totals);

        // Build the final form data to send
        const formData = {
            patient_data: patientDataInput?.value || '{}',
            address_data: addressDataInput?.value || '{}',
            insurance_data: insuranceDataInput?.value || '{}',
            service_data: serviceDataInput?.value || '{}',
            grand_total_data: JSON.stringify(totals), // 🟢 ADDED - Send the JSON string backend expects
        };

        console.log('[v0] Final payload ready:', formData);
        console.log('[v0] Sending to backend: /staff/patients/store');

        const response = await axios.post('/staff/patients/store', formData);

console.log('[v0] Success response received:', response.data);

// 🔥 Redirect user (NO SweetAlert)
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
                title: 'Submission Failed!',
                text: error.response?.data?.message || 'Something went wrong. Check browser console for details.',
                confirmButtonText: 'OK'
            });
        }
    }
}

    }));
});
</script>

</x-staff-layout>
