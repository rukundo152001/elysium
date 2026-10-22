
<div id="printInvoiceModal"
     style="display: none;"
     class="fixed inset-0 z-[9999] items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">

    <div id="invoiceModalContent"
         class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5">

        <!-- Header -->
        <div class="flex items-center justify-between bg-gradient-to-r from-teal-500 to-teal-700 px-6 py-5">

            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/20 text-white">
                    <i class="bi bi-printer text-xl"></i>
                </div>

                <div>
                    <h2 class="text-lg font-bold text-white">
                        Print Invoice
                    </h2>
                    <p class="text-sm text-blue-100">
                        Select an invoice type
                    </p>
                </div>
            </div>

            <!-- Close button 
            <button type="button"
                    onclick="closeInvoiceModal()"
                    aria-label="Close modal"
                    class="flex h-9 w-9 items-center justify-center rounded-full text-white transition hover:bg-white/50 focus:outline-none focus:ring-2 focus:ring-white">
                <i class="bi bi-x-lg"></i>
            </button> -->
        </div>

        <!-- Body -->
        <div class="p-6">

            <p id="invoiceMessage"
               class="mb-5 text-sm leading-6 text-slate-600">
                Choose the type of invoice you want to print.
            </p>

            <div class="space-y-3">

                <!-- Patient invoice -->
                <button type="button"
                        id="patientInvoiceButton"
                        class="group flex w-full items-center gap-4 rounded-xl border border-blue-200 bg-blue-50 p-4 text-left transition hover:border-blue-500 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500">

                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-xl text-white shadow-sm">
                        <i class="bi bi-person-vcard"></i>
                    </span>

                    <span class="flex-1">
                        <span class="block font-semibold text-slate-800">
                            Patient Invoice
                        </span>
                        <span class="mt-1 block text-sm text-slate-500">
                            View and print the patient's invoice
                        </span>
                    </span>

                    <i class="bi bi-chevron-right text-slate-400 transition group-hover:translate-x-1"></i>
                </button>

                <!-- Insurance invoice -->
                <button type="button"
                        id="insuranceInvoiceButton"
                        class="group flex w-full items-center gap-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-left transition hover:border-emerald-500 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">

                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-xl text-white shadow-sm">
                        <i class="bi bi-shield-check"></i>
                    </span>

                    <span class="flex-1">
                        <span class="block font-semibold text-slate-800">
                            Insurance Invoice
                        </span>
                        <span class="mt-1 block text-sm text-slate-500">
                            View and print the insurance invoice
                        </span>
                    </span>

                    <i class="bi bi-chevron-right text-slate-400 transition group-hover:translate-x-1"></i>
                </button>

            </div>

            <!-- Footer -->
            <div class="mt-6 flex justify-end border-t border-slate-100 pt-4">
                <button type="button"
                        onclick="closeInvoiceModal()"
                        class="rounded-lg border border-slate-300 bg-white px-5 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">
                    Cancel
                </button>
            </div>

        </div>
    </div>
</div>

<script>
(function () {
    let patientInvoiceUrl = '';
    let insuranceInvoiceUrl = '';

    const modal = document.getElementById('printInvoiceModal');

    if (!modal || modal.dataset.initialized === 'true') {
        return;
    }

    modal.dataset.initialized = 'true';

    const patientButton = document.getElementById('patientInvoiceButton');
    const insuranceButton = document.getElementById('insuranceInvoiceButton');
    const message = document.getElementById('invoiceMessage');

    window.prepareInvoiceOptions = function (button) {
        patientInvoiceUrl = button.dataset.patientInvoiceUrl || '';
        insuranceInvoiceUrl = button.dataset.insuranceInvoiceUrl || '';

        const isInsured = button.dataset.isInsured === '1';

        // Always show the patient invoice option.
        patientButton.classList.remove('hidden');

        // Show insurance invoice only for insured patients.
        insuranceButton.style.display = isInsured ? 'flex' : 'none';

        message.textContent = isInsured
            ? 'This patient is insured. Select the invoice you want to view or print.'
            : 'This patient is not insured. Only the patient invoice is available.';

        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    window.closeInvoiceModal = function () {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    };

    // Close when clicking the dark background.
    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            window.closeInvoiceModal();
        }
    });

    
 // Patient invoice: open in a new tab.
patientButton.addEventListener('click', function () {
    if (patientInvoiceUrl) {
        window.open(patientInvoiceUrl, '_blank');
    }
});

// Insurance invoice: open in a new tab.
insuranceButton.addEventListener('click', function () {
    if (insuranceInvoiceUrl) {
        window.open(insuranceInvoiceUrl, '_blank');
    }
});


    // Close using Escape.
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.style.display !== 'none') {
            window.closeInvoiceModal();
        }
    });
})();
</script>
