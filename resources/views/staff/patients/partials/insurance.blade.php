<!-- resources/views/components/insurance.blade.php -->
<div 
  x-data="insuranceComponent()" 
  x-init="init()" 
  class="bg-gradient-to-br from-teal-50 to-white border border-teal-200 rounded-2xl p-6 shadow-sm mb-6"
>
  <h2 class="text-2xl font-bold text-teal-900 mb-6 flex items-center">
    <span class="bg-gradient-to-br from-teal-500 to-teal-600 text-white w-10 h-10 rounded-full flex items-center justify-center mr-3 text-lg font-semibold shadow-md">1</span>
    Insurance Details
  </h2>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
    <!-- Provider (always visible) -->
    <div>
      <label class="block text-sm font-semibold text-teal-900 mb-2">Insurance Provider</label>
      <select id="provider" x-model="selectedProvider" @change="onProviderChange" class="w-full border rounded-lg p-2">
        <option value="">Select Provider</option>
        <template x-for="provider in providers" :key="provider.id">
          <option :value="provider.id" x-text="provider.name"></option>
        </template>
      </select>
    </div>

    <!-- Category (hidden when PRIVATE) -->
    <div x-show="!isPRIVATE" x-transition>
      <label class="block text-sm font-semibold text-teal-900 mb-2">Insurance Category</label>
      <select 
        x-model="selectedCategory" 
        @change="onCategoryChange()"
        class="w-full border-2 rounded-xl px-4 py-2.5 transition-all duration-300 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
      >
        <option value="">Select Category</option>
        <template x-for="category in categories" :key="category.id">
          <option :value="category.id" x-text="category.name"></option>
        </template>
      </select>
    </div>

    <!-- Package (hidden when PRIVATE) -->
    <div x-show="!isPRIVATE" x-transition>
      <label class="block text-sm font-semibold text-teal-900 mb-2">Insurance Package</label>
      <select 
        x-model="selectedPackage"
        @change="onPackageChange"
        :disabled="isRAMA"
        class="w-full border-2 rounded-xl px-4 py-2.5 transition-all duration-300 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
        :class="isRAMA? 'opacity-60 cursor-not-allowed' : ''"
      >
        <option value="">Select Package</option>
        <template x-for="pkg in packages" :key="pkg.id">
          <option :value="pkg.id" x-text="pkg.package_name"></option>
        </template>
      </select>
    </div>

    <!-- Insurance Number (hidden when PRIVATE) -->
    <div x-show="!isPRIVATE" x-transition>
      <label class="block text-sm font-semibold text-teal-900 mb-2">Patient Ins No / NID </label>
      <input 
        x-model="insurance.insurance_number" 
        @input="updateInsuranceDataJSON"
        type="text" 
        placeholder="INS-XXXXXXX"
        class="w-full border-2 rounded-xl px-4 py-2.5 bg-gray-50 text-gray-900 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
      >
    </div>

    <!-- Affiliate Name (hidden when PRIVATE) -->
    <div x-show="!isPRIVATE" x-transition>
      <label class="block text-sm font-semibold text-teal-900 mb-2">Household / Adherent Name</label>
      <input 
        x-model="insurance.affiliate_name"
        @input="updateInsuranceDataJSON"
        type="text" 
        placeholder="Affiliate Full Name"
        class="w-full border-2 rounded-xl px-4 py-2.5 bg-gray-50 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
      >
    </div>

    <!-- Affiliate Insurance Number (hidden when PRIVATE) -->
    <div x-show="!isPRIVATE" x-transition>
      <label class="block text-sm font-semibold text-teal-900 mb-2">Household / Adherent Ins No.</label>
      <input 
        x-model="insurance.affiliate_insurance_number" 
        @input="updateInsuranceDataJSON"
        type="text" 
        placeholder="INS-XXXXXXX"
        class="w-full border-2 rounded-xl px-4 py-2.5 bg-gray-50 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
      >
    </div>

    <!-- Relationship (hidden when PRIVATE) -->
    <div x-show="!isPRIVATE && isRAMA" x-transition>
      <label class="block text-sm font-semibold text-teal-900 mb-2">Relationship</label>
      <input 
        x-model="insurance.affiliate_relationship" 
        @input="updateInsuranceDataJSON"
        type="text" 
        placeholder="Relationship (e.g., Spouse)"
        class="w-full border-2 rounded-xl px-4 py-2.5 bg-gray-50 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
      >
    </div>

    <!-- Assigned Location (only if RAMA and not PRIVATE) -->
    <div x-show="isRAMA && !isPRIVATE" x-transition>
      <label class="block text-sm font-semibold text-teal-900 mb-2">Ass Location / Affectation</label>
      <input 
        x-model="insurance.assigned_location" 
        @input="updateInsuranceDataJSON"
        type="text" 
        placeholder="Assigned Location"
        class="w-full border-2 rounded-xl px-4 py-2.5 bg-gray-50 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
      >
    </div>
    
    <!-- Payment Method - Only visible when patient_cover > 0 -->
    <div x-show="Number(insurance.patient_cover) > 0" x-transition>
      <label class="block text-sm font-semibold text-teal-900 mb-2">Payment Method</label>
      <select 
        x-model="insurance.payment_method"
        @change="updateInsuranceDataJSON"                  
        class="w-full border-2 rounded-xl px-4 py-2.5 transition-all duration-300 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
      >
        <option value="">Select Payment Method</option>
        <option value="cash">Cash</option>
        <option value="mobile">MOMO</option>
        <option value="card">None</option>
      </select>
    </div>
  </div>

  <!-- Hidden field to store insurance JSON -->
  <input type="hidden" name="insurance_data" x-model="insuranceDataJSON">
</div>

<script> 
document.addEventListener('alpine:init', () => {
  Alpine.data('insuranceComponent', () => ({

    selectedProvider: '',
    selectedCategory: '',
    selectedPackage: '',

    isRAMA: false,
    isPRIVATE: false,

    providers: [],
    categories: [],
    packages: [],
    packageDetails: {},

    insurance: {
      provider_id: null,
      category_id: null,
      package_id: null,
      insurance_number: null,
      affiliate_name: null,
      affiliate_insurance_number: null,
      affiliate_relationship: null,
      assigned_location: null,
      payment_method: '',
      patient_cover: 0,
      insurance_cover: 0,
    },

    insuranceDataJSON: '',


    // ---------------------------------------------------
    // DISPATCH % TO PAYMENT SUMMARY
    // ---------------------------------------------------
    dispatchInsuranceUpdate() {
      const insRate = Number(this.insurance.insurance_cover) || 0;
      const patientRate = Number(this.insurance.patient_cover) || (100 - insRate);

      window.dispatchEvent(new CustomEvent('insurance:updated', {
        detail: { insuranceCover: insRate, patientCover: patientRate }
      }));
    },



    // ---------------------------------------------------
    // INIT
    // ---------------------------------------------------
    async init() {
      await this.loadProviders();
      this.updateInsuranceDataJSON();
    },



    // ---------------------------------------------------
    // LOAD PROVIDERS
    // ---------------------------------------------------
    async loadProviders() {
      try {
        const res = await fetch('/api/insurance/providers');
        const json = await res.json();
        this.providers = Array.isArray(json) ? json : json.data || [];
      } catch (e) {
        console.error('[ERROR:loadProviders]', e);
      }
    },



    // ---------------------------------------------------
    // PROVIDER CHANGE
    // ---------------------------------------------------
    async onProviderChange() {
      const provider = this.providers.find(p => p.id == this.selectedProvider);
      this.insurance.provider_id = this.selectedProvider;

      this.isPRIVATE = provider && provider.name?.toUpperCase() === 'PRIVATE';
      this.isRAMA = false;

      // Reset %
      this.insurance.patient_cover = 0;
      this.insurance.insurance_cover = 0;
      this.dispatchInsuranceUpdate();

      try {
        const res = await fetch(`/api/insurance/categories/${this.selectedProvider}`);
        const json = await res.json();
        this.categories = Array.isArray(json) ? json : json.data || [];

        // ---------------------------------------------------
        // 🔥 PRIVATE — AUTO SELECT CATEGORY (HIDDEN)
        // ---------------------------------------------------
        if (this.isPRIVATE && this.categories.length === 1) {
          this.selectedCategory = String(this.categories[0].id);
          this.insurance.category_id = this.selectedCategory;
          await this.loadPrivatePackage();
          return;
        }

      } catch (e) {
        console.error('[ERROR:ProviderChange]', e);
      }
    },



    // ---------------------------------------------------
    // PRIVATE: AUTO LOAD PACKAGE (1 ONLY)
    // ---------------------------------------------------
    async loadPrivatePackage() {
      try {
        const res = await fetch(`/api/insurance/packages/${this.selectedCategory}`);
        const json = await res.json();
        this.packages = Array.isArray(json) ? json : json.data || [];

        if (this.packages.length === 1) {
          this.selectedPackage = String(this.packages[0].id);
          await this.onPackageChange();
        }

      } catch (e) {
        console.error('[ERROR:PrivatePackage]', e);
      }
    },



    // ---------------------------------------------------
    // CATEGORY CHANGE
    // ---------------------------------------------------
    async onCategoryChange() {
      this.insurance.category_id = this.selectedCategory;

      const cat = this.categories.find(c => c.id == this.selectedCategory);
      this.isRAMA = cat && cat.name?.toUpperCase() === 'RAMA';

      try {
        const res = await fetch(`/api/insurance/packages/${this.selectedCategory}`);
        const json = await res.json();
        this.packages = Array.isArray(json) ? json : json.data || [];

        // 🔥 RAMA AUTO-SELECT
        if (this.isRAMA && this.packages.length === 1) {
          this.selectedPackage = String(this.packages[0].id);
          await this.onPackageChange();
          this.dispatchInsuranceUpdate();
        }

      } catch (e) {
        console.error('[ERROR:CategoryChange]', e);
      }
    },



    // ---------------------------------------------------
    // PACKAGE CHANGE — MAIN SOURCE OF %
    // ---------------------------------------------------
    async onPackageChange() {
      const pkg = this.packages.find(p => p.id == this.selectedPackage);
      if (!pkg) return;

      this.insurance.package_id = this.selectedPackage;
      this.packageDetails = pkg;

      this.insurance.patient_cover = Number(pkg.patient_cover) || 0;
      this.insurance.insurance_cover = Number(pkg.insurance_cover) || 0;

      if (Number(this.insurance.patient_cover) <= 0) {
        this.insurance.payment_method = 'none';
      }

      this.updateInsuranceDataJSON();

      await new Promise(resolve => setTimeout(resolve, 0));
      this.dispatchInsuranceUpdate();
    },



    // ---------------------------------------------------
    // JSON SYNC
    // ---------------------------------------------------
    updateInsuranceDataJSON() {
      this.insuranceDataJSON = JSON.stringify(this.insurance);
    }

  }));
});
</script>
