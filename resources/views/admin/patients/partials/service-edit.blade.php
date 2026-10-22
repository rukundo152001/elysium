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
      <div class="flex items-end">
        <button 
          type="button" 
          @click="removeService(index)"
          class="bg-red-500 hover:bg-red-600 text-white px-4 py-2.5 rounded-xl font-medium transition-all duration-200"
        >
          Remove
        </button>
      </div>
    </div>
  </template>

  <!-- Add Service Button - Available for editing -->
  <button 
    type="button" 
    @click="addServiceRow"
    class="mt-4 bg-teal-600 hover:bg-teal-700 text-white px-6 py-2.5 rounded-xl font-medium transition-all duration-200"
  >
    + Add Service
  </button>

  <!-- Hidden input to store all service data as JSON for backend submission -->
  <input type="hidden" name="service_data" x-model="serviceDataJSON">
</div>

<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('serviceComponent', () => ({
    services: [],
    servicesSelected: [],
    serviceTotal: 0,
    serviceDataJSON: '',

    /*---------------------------------------------------
     DISPATCH TO GRAND TOTAL COMPONENT
    ---------------------------------------------------*/
    dispatchServiceUpdate() {
      const total = this.servicesSelected.reduce(
        (sum, srv) => sum + Number(srv.cost || 0),
        0
      );

      // Sync window.currentService so grandTotal never uses stale DB data
      window.currentService = this.servicesSelected;

      window.dispatchEvent(
        new CustomEvent("service:updated", {
          detail: { total },
        })
      );
    },

    /*---------------------------------------------------
     INIT (EDIT MODE SUPPORT)
    ---------------------------------------------------*/
    async init() {
      console.log("[v0:service] Initializing service component (EDIT MODE ONLY)");

      this.$watch(
        "servicesSelected",
        () => {
          this.updateServiceDataJSON();
        },
        { deep: true }
      );

      await this.loadServices();

      if (Array.isArray(window.currentService) && window.currentService.length > 0) {
        console.log("[v0:service] Loading existing services from DB:", window.currentService);

        this.servicesSelected = window.currentService.map((svc) => ({
          service_id: Number(svc.service_id),
          cost: Number(svc.cost),
        }));
      } else {
        console.log("[v0:service] No existing services; starting empty.");
        this.servicesSelected = [];
      }

      window.patientRegistrationStore = window.patientRegistrationStore || {};
      window.patientRegistrationStore.serviceComponent = this;

      this.updateServiceDataJSON();
      this.calculateServiceTotal();
    },

    /*---------------------------------------------------
     LOAD ALL SERVICES
    ---------------------------------------------------*/
    async loadServices() {
      try {
        console.log("[v0:service] Fetching /api/services...");
        const res = await fetch("/api/services");
        const json = await res.json();

        this.services = Array.isArray(json.data) ? json.data : [];
        console.log("[v0:service] Services loaded:", this.services);
      } catch (error) {
        console.error("[v0:service] Error loading services:", error);
      }
    },

    /*---------------------------------------------------
     ADD SERVICE ROW
    ---------------------------------------------------*/
    addServiceRow() {
      console.log("[v0:service] Adding new service row...");

      this.servicesSelected.push({ service_id: "", cost: 0 });

      // SYNC FIX
      window.currentService = this.servicesSelected;

      this.updateServiceDataJSON();
      this.calculateServiceTotal();
      this.dispatchServiceUpdate();
    },

    /*---------------------------------------------------
     REMOVE SERVICE
    ---------------------------------------------------*/
    removeService(index) {
      console.log("[v0:service] Removing service at index:", index);

      this.servicesSelected.splice(index, 1);

      // SYNC FIX
      window.currentService = this.servicesSelected;

      this.updateServiceDataJSON();
      this.calculateServiceTotal();
      this.dispatchServiceUpdate();
    },

    /*---------------------------------------------------
     UPDATE COST WHEN SERVICE SELECT CHANGES
    ---------------------------------------------------*/
    updateServiceCost(index) {
      const selected = this.services.find(
        (s) => Number(s.id) === Number(this.servicesSelected[index].service_id)
      );

      this.servicesSelected[index].cost = selected
        ? Number(selected.price || selected.cost || 0)
        : 0;

      console.log(
        "[v0:service] Service cost updated at index",
        index,
        ":",
        this.servicesSelected[index].cost
      );

      // SYNC FIX
      window.currentService = this.servicesSelected;

      this.updateServiceDataJSON();
      this.calculateServiceTotal();
      this.dispatchServiceUpdate();
    },

    /*---------------------------------------------------
     CALCULATE TOTALS
    ---------------------------------------------------*/
    calculateServiceTotal() {
      console.log("[v0:service] Recalculating service totals...");

      const totalCost = this.servicesSelected.reduce(
        (sum, s) => sum + Number(s.cost || 0),
        0
      );

      this.serviceTotal = totalCost;
      window.serviceTotal = totalCost;

      // SYNC FIX
      window.currentService = this.servicesSelected;

      console.log("[v0:service] Total service cost:", totalCost);

      this.dispatchServiceUpdate();
    },

    /*---------------------------------------------------
     UPDATE JSON FOR BACKEND
    ---------------------------------------------------*/
    updateServiceDataJSON() {
      this.serviceDataJSON = JSON.stringify({
        services: this.servicesSelected,
        total: this.serviceTotal,
      });

      console.log("[v0:service] Service data JSON updated");
    },
  }));
});
</script>
