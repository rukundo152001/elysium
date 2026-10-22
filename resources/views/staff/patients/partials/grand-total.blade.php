<!-- resources/views/components/grand-total.blade.php -->
<div 
  x-data="grandTotalComponent()" 
  x-init="init()" 
  class="bg-gradient-to-br from-teal-50 to-white border border-teal-200 rounded-2xl p-6 shadow-sm mt-8"
>
  <h2 class="text-2xl font-bold text-teal-900 mb-6 flex items-center">
    <span class="bg-gradient-to-br from-teal-500 to-teal-600 text-white w-10 h-10 rounded-full flex items-center justify-center mr-3 text-lg font-semibold shadow-md">3</span>
    Payment Summary
  </h2>

  <!-- 3 Columns: Grand Total | Patient Pays | Insurance Pays -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Grand Total -->
    <div class="bg-white border border-teal-100 rounded-xl p-4 shadow-sm flex flex-col justify-between text-center">
      <h3 class="text-sm font-semibold text-gray-600 uppercase mb-1">Grand Total</h3>
      <p class="text-2xl font-bold text-teal-700" x-text="formatCurrency(grandTotal)"></p>
      <p class="text-xs text-gray-500 mt-1">(100%)</p>
    </div>

    <!-- Patient Pays -->
    <div class="bg-white border border-teal-100 rounded-xl p-4 shadow-sm flex flex-col justify-between text-center">
      <h3 class="text-sm font-semibold text-gray-600 uppercase mb-1">
        Patient Pays (<span x-text="patientCover"></span>%)
      </h3>
      <p class="text-2xl font-bold text-red-600" x-text="formatCurrency(patientPays)"></p>
    </div>

    <!-- Insurance Pays -->
    <div class="bg-white border border-teal-100 rounded-xl p-4 shadow-sm flex flex-col justify-between text-center">
      <h3 class="text-sm font-semibold text-gray-600 uppercase mb-1">
        Insurance Pays (<span x-text="insuranceCover"></span>%)
      </h3>
      <p class="text-2xl font-bold text-green-600" x-text="formatCurrency(insurancePays)"></p>
    </div>
  </div>

  <!-- Checkbox: Pays Full -->
  <script>
document.addEventListener('alpine:init', () => {
  Alpine.data('grandTotalComponent', () => ({
    // 🧾 Core Data
    grandTotal: 0,
    insuranceCover: 0,      // global % (0-100)
    patientCover: 0,
    patientPays: 0,
    insurancePays: 0,
    paysFull: true,
    manualPatientPays: 0,
    remainingBalance: 0,

    init() {
      console.log('%c[INIT] GrandTotalComponent Initialized', 'color: teal; font-weight: bold;');

      // listeners
      window.addEventListener('service:updated', (e) => {
        this.grandTotal = Number(e.detail.total || 0);
        console.log('[EVENT:service:updated]', this.grandTotal);
        this.calculatePayments();
      });

      window.addEventListener('insurance:updated', (e) => {
        // defensive extraction: accept many possible key names
        const ins = e.detail || {};
        const insurancePct = Number(
          ins.insuranceCover ??
          ins.insurance_package ??
          ins.insurancePackage ??
          ins.coverage ??
          ins.percentage ??
          ins.rate ??
          ins.value ?? 0
        ) || 0;

        this.insuranceCover = insurancePct;
        this.patientCover = Number(ins.patientCover ?? (100 - insurancePct));
        console.log('[EVENT:insurance:updated]', {
          insuranceCover: this.insuranceCover,
          patientCover: this.patientCover,
          rawDetail: e.detail
        });
        this.calculatePayments();
      });

      this.$watch('paysFull', () => this.calculatePayments());
      this.$watch('manualPatientPays', () => {
        if (!this.paysFull) this.calculatePayments();
      });

      // ⬇️ Trigger initial load for EDIT mode (services)
      if (window.currentService) {
        const total = (window.currentService || []).reduce((sum, item) => {
          const cost = Number(item.cost ?? item.unit_price ?? item.price ?? 0);
          return sum + cost;
        }, 0);
        console.log("[INIT] Dispatching service:updated from edit", total);
        window.dispatchEvent(new CustomEvent('service:updated', { detail: { total } }));
      }

      // ⬇️ Trigger initial load for EDIT mode (insurance) - defensive extraction
      if (window.currentInsurance) {
        console.log("[INIT] Dispatching insurance:updated from edit (raw):", window.currentInsurance);
        const ins = window.currentInsurance || {};
        const insurancePct = Number(ins.coverage ?? ins.percentage ?? ins.insurance_cover ?? ins.cover ?? ins.rate ?? ins.insurancePackage ?? ins.insurance_package ?? 0) || 0;
        window.dispatchEvent(new CustomEvent('insurance:updated', {
          detail: { insuranceCover: insurancePct, patientCover: 100 - insurancePct }
        }));
      }

      // Initialize hidden JSON for backend (ensures field exists)
      this.updateGrandTotalJSON();
    },

    // 🧮 Core calculation logic (supports per-service rates)
    calculatePayments() {
      // normalize global insurance rate
      const globalInsuranceRate = Number(this.insuranceCover) || 0;

      // if item-level service data exists, compute insurance as sum(item.cost * item.insurance_rate)
      if (Array.isArray(window.currentService) && window.currentService.length > 0) {
        let insuranceSum = 0;
        let totalFromItems = 0;

        for (const rawItem of window.currentService) {
          const cost = Number(rawItem.cost ?? rawItem.unit_price ?? rawItem.price ?? 0) || 0;
          // item-level insurance rate keys we accept (fallback to global)
          const itemInsuranceRate = Number(
            rawItem.insurance_rate ??
            rawItem.insuranceRate ??
            rawItem.coverage ??
            rawItem.cover ??
            rawItem.insurance_pct ??
            rawItem.insurance_pct ??
            rawItem.insurancePackage ??
            rawItem.insurance_package
          );
          const rate = Number.isFinite(itemInsuranceRate) && !Number.isNaN(itemInsuranceRate) ? itemInsuranceRate : globalInsuranceRate;

          insuranceSum += (cost * rate) / 100;
          totalFromItems += cost;
        }

        // If grandTotal was previously computed differently, prefer actual item sum
        this.grandTotal = totalFromItems;
        this.insurancePays = insuranceSum;
        this.patientPays = Math.max(0, this.grandTotal - this.insurancePays);
      } else {
        // fallback: apply global rate to whole grandTotal
        this.insurancePays = (this.grandTotal * globalInsuranceRate) / 100;
        this.patientPays = this.grandTotal - this.insurancePays;
      }

      // handle manual / paysFull logic (keeps behavior you already had)
      if (!this.paysFull && Number(this.manualPatientPays) > 0) {
        // if manual payment entered, recalc remaining balance
        const manual = Number(this.manualPatientPays) || 0;
        this.remainingBalance = Math.max(0, this.patientPays - manual);
      } else {
        this.remainingBalance = 0;
      }

      // 🌍 Sync globally for other scripts
      window.grandTotal = this.grandTotal;
      window.patientPays = this.patientPays;
      window.insurancePays = this.insurancePays;

      console.log('[CALC] Updated Totals:', {
        total: this.grandTotal,
        insuranceCover: this.insuranceCover,
        insurancePays: this.insurancePays,
        patientPays: this.patientPays,
        remainingBalance: this.remainingBalance
      });

      this.updateGrandTotalJSON();
    },

    // 🧾 Keep backend JSON synced
    updateGrandTotalJSON() {
      const hiddenInput = document.getElementById('grand_total_data');
      if (!hiddenInput) {
        console.warn('[WARN] Hidden input #grand_total_data not found.');
        return;
      }

      const payload = {
        grand_total: this.grandTotal,
        insurance_cost: this.insurancePays,
        patient_cost: this.patientPays,
        remaining_balance: this.remainingBalance
      };

      hiddenInput.value = JSON.stringify(payload);
      console.log('[SYNC] Hidden field updated:', payload);
    },

    // 💰 Optional formatter (for display)
    formatCurrency(value) {
      return new Intl.NumberFormat('en-RW', {
        style: 'currency',
        currency: 'RWF',
        minimumFractionDigits: 0,
      }).format(value || 0);
    },
  }));
});
</script>


<!-- Hidden input to send totals to backend -->
<input type="hidden" id="grand_total_data" name="grand_total_data" value="{}">





