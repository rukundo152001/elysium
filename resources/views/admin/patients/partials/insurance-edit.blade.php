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
      <select id="provider" x-model="selectedProvider" @change="onProviderChange()" class="w-full border rounded-lg p-2">
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
        @change="onPackageChange()"
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
      <label class="block text-sm font-semibold text-teal-900 mb-2">Insurance Number / NID</label>
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
      <label class="block text-sm font-semibold text-teal-900 mb-2">Affiliate Name</label>
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
      <label class="block text-sm font-semibold text-teal-900 mb-2">Affiliate Insurance No.</label>
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
      <label class="block text-sm font-semibold text-teal-900 mb-2">Assigned Location</label>
      <input 
        x-model="insurance.assigned_location" 
        @input="updateInsuranceDataJSON"
        type="text" 
        placeholder="Assigned Location"
        class="w-full border-2 rounded-xl px-4 py-2.5 bg-gray-50 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
      >
    </div>
    
    <!-- Payment Method (only visible when patient has a cost to pay) -->
    <div x-show="insurance.patient_cover !== 0" x-transition>
      <label class="block text-sm font-semibold text-teal-900 mb-2">Payment Method</label>
      <select 
        x-model="insurance.payment_method"
        @change="updateInsuranceDataJSON"                  
        class="w-full border-2 rounded-xl px-4 py-2.5 transition-all duration-300 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
      >
        <option value="">Select Payment Method</option>
        <option value="cash">Cash</option>
        <option value="mobile">MOMO</option>
      </select>
    </div>
  </div>

  <!-- Hidden field to store insurance JSON -->
  <input type="hidden" name="insurance_data" x-model="insuranceDataJSON">

  <!-- Hidden input: when patient covers 0%, automatically set payment_method to 'none' -->
  <input type="hidden" name="payment_method_hidden" x-model="insurance.payment_method" x-effect="if (insurance.patient_cover === 0) insurance.payment_method = 'none'">
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



    /*---------------------------------------------------
     DISPATCH % TO PAYMENT SUMMARY
    ---------------------------------------------------*/
    dispatchInsuranceUpdate() {
      const insRate = Number(this.insurance.insurance_cover) || 0;
      const patientRate = Number(this.insurance.patient_cover) || (100 - insRate);

      window.dispatchEvent(new CustomEvent('insurance:updated', {
        detail: { insuranceCover: insRate, patientCover: patientRate }
      }));
    },



    /*---------------------------------------------------
     INIT (Handles both CREATE + EDIT)
    ---------------------------------------------------*/
    async init() {
      await this.loadProviders();

// --------- EDIT MODE ----------
if (window.currentInsurance && window.currentInsurance.provider_id) {

    const ci = window.currentInsurance;

    // PRE-FILL BASIC VALUES
    this.selectedProvider = Number(ci.provider_id);
    Object.assign(this.insurance, {
        provider_id: ci.provider_id,
        insurance_number: ci.insurance_number,
        affiliate_name: ci.affiliate_name,
        affiliate_insurance_number: ci.affiliate_insurance_number,
        affiliate_relationship: ci.affiliate_relationship,
        assigned_location: ci.assigned_location,
        payment_method: ci.payment_method,
    });

    // 1. Load provider logic (no reset)
    await this.loadCategories(ci.provider_id);

    // 2. WAIT until categories options render
    await this.$nextTick();

    // 3. Now the category dropdown has options → APPLY IT
    this.selectedCategory = Number(ci.category_id);
    this.insurance.category_id = Number(ci.category_id);

    // 4. Load packages
    await this.loadPackages(ci.category_id);

    // 5. WAIT until package options render
    await this.$nextTick();

    // 6. Now apply package
    this.selectedPackage = Number(ci.package_id);
    this.insurance.package_id = Number(ci.package_id);

    // 7. Apply coverage
    const pkg = this.packages.find(p => p.id == ci.package_id);
    if (pkg) {
        this.insurance.patient_cover   = Number(pkg.patient_cover) || 0;
        this.insurance.insurance_cover = Number(pkg.insurance_cover) || 0;
    }

    // 8. Final sync
    this.updateInsuranceDataJSON();
    this.dispatchInsuranceUpdate();
    return;
}

      // CREATE MODE
      this.updateInsuranceDataJSON();
    },



    /*---------------------------------------------------
     LOAD PROVIDERS
    ---------------------------------------------------*/
    async loadProviders() {
      try {
        const res = await fetch('/api/insurance/providers');
        const json = await res.json();
        this.providers = Array.isArray(json) ? json : json.data || [];
      } catch (e) {
        console.error('[ERROR:loadProviders]', e);
      }
    },


    /*---------------------------------------------------
     LOAD CATEGORIES (Reusable)
    ---------------------------------------------------*/
    async loadCategories(providerId) {
      try {
        const res = await fetch(`/api/insurance/categories/${providerId}`);
        const json = await res.json();
        this.categories = Array.isArray(json) ? json : json.data || [];
      } catch (e) {
        console.error('[ERROR:loadCategories]', e);
      }
    },


    /*---------------------------------------------------
     LOAD PACKAGES (Reusable)
    ---------------------------------------------------*/
    async loadPackages(categoryId) {
      try {
        const res = await fetch(`/api/insurance/packages/${categoryId}`);
        const json = await res.json();
        this.packages = Array.isArray(json) ? json : json.data || [];
      } catch (e) {
        console.error('[ERROR:loadPackages]', e);
      }
    },



    /*---------------------------------------------------
     PROVIDER CHANGE
    ---------------------------------------------------*/
    async onProviderChange(isEdit = false) {
      const provider = this.providers.find(p => p.id == this.selectedProvider);
      this.insurance.provider_id = this.selectedProvider;

      this.isPRIVATE = provider && provider.name?.toUpperCase() === 'PRIVATE';
      this.isRAMA = false;

      if (!isEdit) {
        this.selectedCategory = '';
        this.selectedPackage = '';
      }

      this.insurance.category_id = null;
      this.insurance.package_id = null;
      this.insurance.patient_cover = 0;
      this.insurance.insurance_cover = 0;
      this.dispatchInsuranceUpdate();

      await this.loadCategories(this.selectedProvider);

      /*---------------------------------------------------
        PRIVATE — AUTO SELECT CATEGORY + PACKAGE
      ---------------------------------------------------*/
      if (this.isPRIVATE && this.categories.length === 1) {
        this.selectedCategory = String(this.categories[0].id);
        this.insurance.category_id = this.selectedCategory;

        await this.loadPackages(this.selectedCategory);

        if (this.packages.length === 1) {
          this.selectedPackage = String(this.packages[0].id);
          await this.onPackageChange(true);
        }
      }
    },



    /*---------------------------------------------------
     CATEGORY CHANGE
    ---------------------------------------------------*/
    async onCategoryChange(isEdit = false) {
      this.insurance.category_id = this.selectedCategory;

      const cat = this.categories.find(c => c.id == this.selectedCategory);
      this.isRAMA = cat && cat.name?.toUpperCase() === 'RAMA';

      if (!isEdit) {
        this.selectedPackage = '';
      }

      await this.loadPackages(this.selectedCategory);

      // RAMA AUTO SELECT
      if (this.isRAMA && this.packages.length === 1 && !isEdit) {
        this.selectedPackage = String(this.packages[0].id);
        await this.onPackageChange();
        this.dispatchInsuranceUpdate();
      }
    },



    /*---------------------------------------------------
     PACKAGE CHANGE
    ---------------------------------------------------*/
    async onPackageChange(isEdit = false) {
      const pkg = this.packages.find(p => p.id == this.selectedPackage);
      if (!pkg) return;

      this.insurance.package_id = this.selectedPackage;
      this.packageDetails = pkg;

      this.insurance.patient_cover = Number(pkg.patient_cover) || 0;
      this.insurance.insurance_cover = Number(pkg.insurance_cover) || 0;

      if (this.insurance.patient_cover === 0) {
        this.insurance.payment_method = 'none';
      } else {
        this.insurance.payment_method = ''; // Reset to allow user selection
      }

      this.updateInsuranceDataJSON();
      await new Promise(res => setTimeout(res, 0));
      this.dispatchInsuranceUpdate();
    },



    /*---------------------------------------------------
     JSON SYNC
    ---------------------------------------------------*/
    updateInsuranceDataJSON() {
      this.insuranceDataJSON = JSON.stringify(this.insurance);
    }

  }));
});
</script>
