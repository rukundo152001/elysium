<x-admin-layout>
    

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Edit {{ ucfirst($level) }}</h2>
        <a href="{{ route('admin.locations.index', ['level' => $level]) }}" class="text-sm text-teal-600 hover:underline">Back to list</a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 border border-green-300 px-4 py-2 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form 
        x-data="locationEditForm('{{ $level }}', @js($item), @js($parentChain))" 
        x-init="init()" 
        method="POST" 
        action="{{ route('admin.locations.update', ['level' => $level, 'id' => $item->id]) }}" 
        class="space-y-5">

        @csrf
        @method('PUT')

        {{-- Parent dropdowns --}}
        <template x-if="showCountry">
            <div>
                <label class="block text-sm font-medium">Country</label>
                <select x-model="country" @change="onCountry()" name="country_id" class="mt-1 w-full border rounded px-3 py-2">
                    <template x-for="c in countries" :key="c.id">
                        <option :value="c.id" x-text="c.name"></option>
                    </template>
                </select>
            </div>
        </template>

        <template x-if="showProvince">
            <div>
                <label class="block text-sm font-medium">Province</label>
                <select x-model="province" @change="onProvince()" name="province_id" class="mt-1 w-full border rounded px-3 py-2">
                    <template x-for="p in provinces" :key="p.id">
                        <option :value="p.id" x-text="p.name"></option>
                    </template>
                </select>
            </div>
        </template>

        <template x-if="showDistrict">
            <div>
                <label class="block text-sm font-medium">District</label>
                <select x-model="district" @change="onDistrict()" name="district_id" class="mt-1 w-full border rounded px-3 py-2">
                    <template x-for="d in districts" :key="d.id">
                        <option :value="d.id" x-text="d.name"></option>
                    </template>
                </select>
            </div>
        </template>

        <template x-if="showSector">
            <div>
                <label class="block text-sm font-medium">Sector</label>
                <select x-model="sector" @change="onSector()" name="sector_id" class="mt-1 w-full border rounded px-3 py-2">
                    <template x-for="s in sectors" :key="s.id">
                        <option :value="s.id" x-text="s.name"></option>
                    </template>
                </select>
            </div>
        </template>

        {{-- Name input --}}
        <div>
            <label class="block text-sm font-medium">Name Of {{ ucfirst($level) }}</label>
            <input type="text" name="name" x-model="name" class="mt-1 w-full border rounded px-3 py-2" placeholder="Enter name" required>
        </div>

        {{-- Buttons --}}
        <div class="flex justify-between pt-4">
            <a href="{{ route('admin.locations.index', ['level' => $level]) }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded hover:bg-gray-200">Cancel</a>
            <button type="submit" class="bg-teal-600 text-white px-5 py-2 rounded hover:bg-teal-700">
                Update {{ ucfirst($level) }}
            </button>
        </div>
    </form>
</div>

{{-- Alpine Script --}}
<script>
function locationEditForm(level, item, parentChain = {}) {
    return {
        level,
        errors: {},
        countries: [],
        provinces: [],
        districts: [],
        sectors: [],

        // Prefill 
        name: item.name || '',
        country: parentChain.country_id || '',
        province: parentChain.province_id || '',
        district: parentChain.district_id || '',
        sector: parentChain.sector_id || '',

        // Display levels
        get showCountry() { return this.level !== 'country'; },
        get showProvince() { return ['district','sector'].includes(this.level); },
        get showDistrict() { return ['sector'].includes(this.level); },
        get showSector() { return false; }, // sector is last level now

        async init() {
            console.log('[DEBUG:init]', { 
                level: this.level, 
                country: this.country, 
                province: this.province, 
                district: this.district 
            });

            await this.loadCountries();

            if (this.country) {
                await this.onCountry(true);
                if (this.province) {
                    await this.onProvince(true);
                    if (this.district) {
                        await this.onDistrict(true);
                        if (this.sector) {
                            await this.onSector(true);
                        }
                    }
                }
            }

            console.log('[DEBUG:init done]', { 
                country: this.country, 
                province: this.province, 
                district: this.district, 
                sector: this.sector 
            });
        },

        async loadCountries() {
            try {
                const res = await fetch('/api/countries');
                this.countries = await res.json();
                console.log('[DEBUG:countries loaded]', this.countries.length);
            } catch (e) { console.error(e); }
        },

        async onCountry(skipReset = false) {
            if (!skipReset) {
                this.province = ''; 
                this.provinces = [];
                this.district = ''; 
                this.districts = [];
                this.sector = ''; 
                this.sectors = [];
            }

            if (!this.country) return;

            try {
                const res = await fetch(`/api/provinces/${this.country}`);
                this.provinces = await res.json();
                console.log('[DEBUG:onCountry] provinces loaded:', this.provinces.length);
            } catch (e) { console.error(e); }
        },

        async onProvince(skipReset = false) {
            if (!skipReset) {
                this.district = ''; 
                this.districts = [];
                this.sector = ''; 
                this.sectors = [];
            }

            if (!this.province) return;

            try {
                const res = await fetch(`/api/districts/${this.province}`);
                this.districts = await res.json();
                console.log('[DEBUG:onProvince] districts loaded:', this.districts.length);
            } catch (e) { console.error(e); }
        },

        async onDistrict(skipReset = false) {
            if (!skipReset) {
                this.sector = ''; 
                this.sectors = [];
            }

            if (!this.district) return;

            try {
                const res = await fetch(`/api/sectors/${this.district}`);
                this.sectors = await res.json();
                console.log('[DEBUG:onDistrict] sectors loaded:', this.sectors.length);
            } catch (e) { console.error(e); }
        },

        async onSector(skipReset = false) {
            // SECTOR IS NOW LAST LEVEL — NOTHING TO LOAD
            console.log('[DEBUG:onSector] sector selected:', this.sector);
        },
    }
}
</script>




</x-admin-layout>
