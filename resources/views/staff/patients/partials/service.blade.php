<div 
  x-data="serviceComponent()" 
  x-init="init()" 
  class="bg-gradient-to-br from-teal-50 to-white border border-teal-200 rounded-2xl p-6 shadow-sm mb-6"
>
  <h2 class="text-2xl font-bold text-teal-900 mb-6 flex items-center">
    <span class="bg-gradient-to-br from-teal-500 to-teal-600 text-white w-10 h-10 rounded-full flex items-center justify-center mr-3 text-lg font-semibold shadow-md">2</span>
    Service & Treatment Details
  </h2>

  <template x-for="(srv, index) in servicesSelected" :key="index">
    <div class="flex flex-col md:flex-row md:items-center gap-4 mb-4">
      
      <!-- Service Select -->
      <div class="flex-1">
        <label class="block text-sm font-semibold text-teal-900 mb-1">Service</label>
        <select 
  x-model.number="srv.service_id"
  @change="updateServiceCost(index)"
  class="w-full border-2 rounded-xl px-4 py-2.5 bg-gray-50 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
>
  <option value="">Select Service</option>

  <template x-for="service in services" :key="service.id">
    <option 
      :value="Number(service.id)" 
      x-text="service.name">
    </option>
  </template>
</select>

      </div>

      <!-- Cost Input -->
      <div class="flex-1">
        <label class="block text-sm font-semibold text-teal-900 mb-1">Cost</label>
        <input 
          x-model.number="srv.cost"
          @input="calculateServiceTotal"
          type="number"
          placeholder="Amount"
          class="w-full border-2 rounded-xl px-4 py-2.5 bg-gray-50 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-100"
        >
      </div>

      <!-- Delete Button -->
      <div class="mt-1 md:mt-7">
        <button 
          type="button"
          @click="removeService(index)"
          class="bg-red-500 hover:bg-red-600 text-white rounded-xl px-4 py-2 text-sm shadow-md"
        >
          Delete
        </button>
      </div>

    </div>
</template>


  <div class="mt-2 mb-6 flex justify-start">
    <button 
      type="button"
      @click="addServiceRow"
      class="bg-gradient-to-br from-teal-500 to-teal-600 hover:from-teal-600 hover:to-teal-700 text-white font-medium px-6 py-2 rounded-xl shadow-md transition-all duration-300"
    >
      + Add Service
    </button>
  </div>

  <!-- Added hidden input to store all service data as JSON for backend submission -->
  <input type="hidden" name="service_data" x-model="serviceDataJSON">
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('serviceComponent', () => ({
    services: [],
    servicesSelected: [],
    serviceTotal: 0,
    serviceDataJSON: '',

    async init() {
    console.log('[DEBUG:service:init] Initializing service component...');

    // Deep watch
    this.$watch('servicesSelected', () => {
        this.updateServiceDataJSON();
    }, { deep: true });

    // Load list of all available services
    await this.loadServices();

    // ✅ Editing: load existing services
    if (Array.isArray(window.currentService) && window.currentService.length > 0) {
        console.log('[v0] Loading existing services:', window.currentService);
        this.servicesSelected = window.currentService;
    } 
    // New patient: start with 1 empty row
    else {
        this.servicesSelected = [{ service_id: '', cost: 0 }];
    }

    // Make available globally
    window.patientRegistrationStore = window.patientRegistrationStore || {};
    window.patientRegistrationStore.serviceComponent = this;

    // Update hidden JSON field
    this.updateServiceDataJSON();
},



    async loadServices() {
      try {
        console.log('[DEBUG:service:loadServices] Fetching /api/services ...');
        const res = await fetch('/api/services');
        const json = await res.json();
        console.log('[DEBUG:service:loadServices] Raw response:', json);

        // ✅ Correctly handle your response structure { success:true, data:[...] }
        
        this.services = Array.isArray(json.data) ? json.data : [];
        console.log('[DEBUG:service:loadServices] Services loaded:', this.services);
      } catch (error) {
        console.error('[ERROR:service:loadServices]', error);
      }
    },

    addServiceRow() {
      console.log('[DEBUG:service:addServiceRow] Adding new service row...');
      this.servicesSelected.push({ service_id: '', cost: 0 });
      this.updateServiceDataJSON();
    },

    removeService(index) {
      console.log('[DEBUG:service:removeService] Removing row index:', index);
      this.servicesSelected.splice(index, 1);
      this.calculateServiceTotal();
      this.updateServiceDataJSON();
    },

    updateServiceCost(index) {
      const selected = this.services.find(s => s.id == this.servicesSelected[index].service_id);

      // ✅ Use price field from API
      this.servicesSelected[index].cost = selected ? Number(selected.price || 0) : 0;

      this.calculateServiceTotal();
      this.updateServiceDataJSON();
      console.log('[DEBUG:service:updateServiceCost] Updated cost for service index', index, '=>', this.servicesSelected[index].cost);
    },

    // ✅ Core calculation logic
    calculateServiceTotal() {
      console.log('[DEBUG:service:calculateServiceTotal] Recalculating totals...');

      // Sum up all selected service costs
      const totalCost = this.servicesSelected.reduce((sum, s) => sum + Number(s.cost || 0), 0);

      this.serviceTotal = totalCost;
      window.serviceTotal = totalCost; // global backup

      console.log('[DEBUG:service:calculateServiceTotal] Total service cost:', totalCost);

      // ✅ Dispatch event for Grand Total component
      window.dispatchEvent(new CustomEvent('service:updated', {
        detail: { 
          total: totalCost
        }
      }));
    },

    updateServiceDataJSON() {
      this.serviceDataJSON = JSON.stringify({
        services: this.servicesSelected,
        total: this.serviceTotal
      });
      console.log('[DEBUG:service:updateServiceDataJSON] Service data updated:', this.serviceDataJSON);
    }

  }));
});

</script>
