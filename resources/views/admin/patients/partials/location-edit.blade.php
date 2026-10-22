<!-- Updated to remove mode logic - always use database mode with hierarchical IDs -->
<div x-data="addressComponent()" x-init="init()"
     class="bg-gradient-to-br from-teal-50 to-white border border-teal-200 rounded-2xl p-6 shadow-sm mb-6">

    <h2 class="text-2xl font-bold text-teal-900 mb-6 flex items-center">
        <span class="bg-gradient-to-br from-orange-400 to-orange-500 text-white w-10 h-10 rounded-full flex items-center justify-center mr-3 text-lg font-semibold shadow-md">2</span>
        Address Information
    </h2>

    <div>
        <!-- COUNTRY -->
        <div>
            <label class="block text-sm font-semibold text-teal-900 mb-2">
                Country <span class="text-red-500">*</span>
            </label>
            <select 
                x-model="selectedCountry"
                @change="onCountryChange"
                class="w-full border-2 rounded-lg px-4 py-2.5 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-teal-100"
                :class="selectedCountry ? 'border-teal-400 bg-teal-50 text-teal-900' : 'border-gray-200 bg-gray-50 text-gray-900'"
            >
                <option value="">Select Country</option>
                <template x-for="country in countries" :key="country.id">
                    <option :value="String(country.id)" x-text="country.name"></option>
                </template>
            </select>
        </div>

        <!-- HIERARCHICAL FIELDS -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6" x-show="selectedCountry">
            
            <!-- Province -->
            <div>
                <label class="block text-sm font-semibold text-teal-900 mb-2">Province</label>
                <select 
                    x-model="selectedProvince"
                    @change="onProvinceChange"

                    :disabled="!selectedCountry && !window.currentAddress"

                    class="w-full border-2 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-100"
                    :class="(!selectedCountry && !window.currentAddress)
                        ? 'bg-gray-100 cursor-not-allowed border-gray-200 text-gray-500'
                        : 'bg-white border-teal-300 text-gray-900'"
                >
                    <option value="">Select Province</option>

                    <template x-for="province in provinces" :key="province.id">
                        <option :value="String(province.id)" x-text="province.name"></option>
                    </template>
                </select>
            </div>

            <!-- District -->
            <div>
                <label class="block text-sm font-semibold text-teal-900 mb-2">District</label>
                <select 
                    x-model="selectedDistrict"
                    @change="onDistrictChange"

                    :disabled="!selectedProvince && !window.currentAddress"

                    class="w-full border-2 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-100"
                    :class="(!selectedProvince && !window.currentAddress)
                        ? 'bg-gray-100 cursor-not-allowed border-gray-200 text-gray-500'
                        : 'bg-white border-teal-300 text-gray-900'"
                >
                    <option value="">Select District</option>

                    <template x-for="district in districts" :key="district.id">
                        <option :value="String(district.id)" x-text="district.name"></option>
                    </template>
                </select>
            </div>

            <!-- Sector -->
            <div>
                <label class="block text-sm font-semibold text-teal-900 mb-2">Sector</label>
                <select 
                    x-model="selectedSector"
                    @change="onSectorChange"
                    :disabled="!selectedDistrict && !window.currentAddress"

                    class="w-full border-2 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-teal-100"
                    :class="(!selectedDistrict && !window.currentAddress)
                        ? 'bg-gray-100 cursor-not-allowed border-gray-200 text-gray-500'
                        : 'bg-white border-teal-300 text-gray-900'"
                >
                    <option value="">Select Sector</option>

                    <template x-for="sector in sectors" :key="sector.id">
                        <option :value="String(sector.id)" x-text="sector.name"></option>
                    </template>
                </select>
            </div>

        </div>

    </div>

    <!-- Hidden input to submit structured data -->
    <input type="hidden" name="address_data" :value="JSON.stringify(getAddressPayload())">

</div>


<script>
function addressComponent() {
  return {
    // dynamic data
    countries: [], provinces: [], districts: [], sectors: [],
    selectedCountry: '', selectedProvince: '', selectedDistrict: '', selectedSector: '',

   async init() {
    console.log('[v0] Address component init started');

    // Load countries first
    const res = await fetch('/api/countries');
    this.countries = await res.json();

    if (!window.currentAddress) {
        this.updateAddressInput();
        return;
    }

    const loc = window.currentAddress;
    console.log('[v0] Loading existing address:', loc);

    // 1️⃣ Apply country
    this.selectedCountry = loc.selectedCountry ? String(loc.selectedCountry) : '';

    // Load provinces for selected country
    if (this.selectedCountry) {
        const p = await fetch(`/api/provinces/${this.selectedCountry}`);
        this.provinces = await p.json();
        await this.$nextTick(); // Ensure DOM options update
        this.selectedProvince = String(loc.selectedProvince || '');
    }

    // 2️⃣ Load districts
    if (this.selectedProvince) {
        const d = await fetch(`/api/districts/${this.selectedProvince}`);
        this.districts = await d.json();
        await this.$nextTick(); // Ensure DOM options update
        this.selectedDistrict = String(loc.selectedDistrict || '');
    }

    // 3️⃣ Load sectors
    if (this.selectedDistrict) {
        const s = await fetch(`/api/sectors/${this.selectedDistrict}`);
        this.sectors = await s.json();
        await this.$nextTick(); // Ensure DOM options update
        this.selectedSector = String(loc.selectedSector || '');
    }

    console.log("[v0] Address prefill complete.", {
        country: this.selectedCountry,
        province: this.selectedProvince,
        district: this.selectedDistrict,
        sector: this.selectedSector,
    });

    this.updateAddressInput();
},

    async onCountryChange() {
        this.provinces = [];
        this.districts = [];
        this.sectors = [];

        this.selectedProvince = '';
        this.selectedDistrict = '';
        this.selectedSector = '';

        if (this.selectedCountry) {
            const res = await fetch(`/api/provinces/${this.selectedCountry}`);
            this.provinces = await res.json();
        }

        this.updateAddressInput();
    },

    async onProvinceChange() {
        this.districts = [];
        this.sectors = [];

        this.selectedDistrict = '';
        this.selectedSector = '';

        if (this.selectedProvince) {
            const res = await fetch(`/api/districts/${this.selectedProvince}`);
            this.districts = await res.json();
        }

        this.updateAddressInput();
    },

    async onDistrictChange() {
        this.sectors = [];
        this.selectedSector = '';

        if (this.selectedDistrict) {
            const res = await fetch(`/api/sectors/${this.selectedDistrict}`);
            this.sectors = await res.json();
        }

        this.updateAddressInput();
    },

    async onSectorChange() {
        this.updateAddressInput();
    },

    getAddressPayload() {
        return {
            country_id: this.selectedCountry || null,
            province_id: this.selectedProvince || null,
            district_id: this.selectedDistrict || null,
            sector_id: this.selectedSector || null,
        };
    },

    updateAddressInput() {
        const input = document.querySelector('input[name="address_data"]');
        if (input) input.value = JSON.stringify(this.getAddressPayload());
    },
  };
}
</script>
