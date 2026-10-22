<x-admin-layout>
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-md p-5">
        <div class="max-w-3xl mx-auto p-6 bg-white shadow rounded">
            <h1 class="text-2xl font-semibold text-gray-700 mb-4 capitalize">Add {{ $level }}</h1>
            <a href="{{ route('admin.locations.index', ['level' => $level]) }}" class="text-sm text-gray-500">Back to list</a>
        </div>
    
        @if(session('success'))
            <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-2 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        
        @if (session('error'))
            <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4">
                {{ session('success') }}
            </div>            
        @endif
        
        
            
        <form x-data="locationCreateForm('{{ $level }}', @js($preselect))" 
          x-init="init()" 
          @submit.prevent="debugAndSubmit($event)" 
          method="POST" 
          action="{{ route('admin.locations.store') }}" 
          class="space-y-5">
        @csrf
        <input type="hidden" name="level" value="{{ $level }}">

        {{-- Country --}}
        <template x-if="showCountry">
            <div>
                <label class="block text-sm font-medium text-gray-700">Country</label>
                <select x-model="country" @change="onCountry()" name="country_id" class="mt-1 w-full border rounded px-3 py-2">
                    <option value="">Select Country</option>
                    <template x-for="c in countries" :key="c.id">
                        <option :value="c.id" x-text="c.name" :selected="country == c.id"></option>
                    </template>
                </select>
                <div x-show="errors.country" class="text-red-600 text-sm mt-1" x-text="errors.country"></div>
            </div>
        </template>

        {{-- Province --}}
        <template x-if="showProvince">
            <div>
                <label class="block text-sm font-medium text-gray-700">Province</label>
                <select x-model="province" @change="onProvince()" name="province_id" class="mt-1 w-full border rounded px-3 py-2">
                    <option value="">Select Province</option>
                    <template x-for="p in provinces" :key="p.id">
                        <option :value="p.id" x-text="p.name" :selected="province == p.id"></option>
                    </template>
                </select>
                <div x-show="errors.province" class="text-red-600 text-sm mt-1" x-text="errors.province"></div>
            </div>
        </template>

        {{-- District --}}
        <template x-if="showDistrict">
            <div>
                <label class="block text-sm font-medium text-gray-700">District</label>
                <select x-model="district" @change="onDistrict()" name="district_id" class="mt-1 w-full border rounded px-3 py-2">
                    <option value="">Select District</option>
                    <template x-for="d in districts" :key="d.id">
                        <option :value="d.id" x-text="d.name" :selected="district == d.id"></option>
                    </template>
                </select>
                <div x-show="errors.district" class="text-red-600 text-sm mt-1" x-text="errors.district"></div>
            </div>
        </template>

        {{-- Sector --}}
        <template x-if="showSector">
            <div>
                <label class="block text-sm font-medium text-gray-700">Sector</label>
                <select x-model="sector" @change="onSector()" name="sector_id" class="mt-1 w-full border rounded px-3 py-2">
                    <option value="">Select Sector</option>
                    <template x-for="s in sectors" :key="s.id">
                        <option :value="s.id" x-text="s.name" :selected="sector == s.id"></option>
                    </template>
                </select>
                <div x-show="errors.sector" class="text-red-600 text-sm mt-1" x-text="errors.sector"></div>
            </div>
        </template>

        

        {{-- Name --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 capitalize">Name of {{ $level }}</label>
            <input type="text" x-model="name" name="name" placeholder="Enter name" 
                   class="mt-1 w-full border rounded px-3 py-2"
                   value="{{ old('name') }}">
            @error('name') 
                <div class="text-red-600 text-sm mt-1">{{ $message }}</div> 
            @enderror
        </div>

        {{-- Buttons --}}
        <div class="pt-4 flex justify-between">
            <a href="{{ route('admin.locations.index', ['level' => $level]) }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
            <button type="submit" 
                    class="bg-teal-600 text-white px-5 py-2 rounded hover:bg-teal-700 focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                Save {{ ucfirst($level) }}
            </button>
        </div>
    </form>
 

</div>
<script>
function locationCreateForm(level, preselect) {
    return {
        level,
        errors: {},
        countries: [],
        provinces: [],
        districts: [],
        sectors: [],

        country: preselect.country_id ?? '{{ old('country_id') ?? '' }}',
        province: preselect.province_id ?? '{{ old('province_id') ?? '' }}',
        district: preselect.district_id ?? '{{ old('district_id') ?? '' }}',
        sector: preselect.sector_id ?? '{{ old('sector_id') ?? '' }}',
        name: '{{ old('name') ?? '' }}',

        // Show logic
        get showCountry() { return this.level !== 'country'; },
        get showProvince() { return ['district','sector'].includes(this.level); },
        get showDistrict() { return ['sector'].includes(this.level); },
        get showSector() { return false; }, // sector is last level now

        // Init
        async init() {
            console.log('[DEBUG:init] Level:', this.level);
            await this.loadCountries();
            if (this.country) await this.onCountry();
            if (this.province) await this.onProvince();
            if (this.district) await this.onDistrict();
            if (this.sector) await this.onSector();

            if (window.location.search.includes('success')) {
                this.name = '';
            }

            console.log('[DEBUG:init done]', {
                country: this.country,
                province: this.province,
                district: this.district,
                sector: this.sector
            });
        },

        // Loaders
        async loadCountries() {
            try {
                const res = await fetch('/api/countries');
                this.countries = await res.json();
                console.log('[DEBUG:loadCountries]', this.countries);
            } catch (e) { console.error('[ERROR:loadCountries]', e); }
        },

        async onCountry() {
            console.log('[DEBUG:onCountry] country selected:', this.country);

            const currentProvince = this.province;
            this.provinces = [];
            this.districts = [];
            this.sectors = [];

            if (!this.country) return;

            try {
                const res = await fetch(`/api/provinces/${this.country}`);
                if (!res.ok) throw new Error('Province fetch failed');
                this.provinces = await res.json();
                console.log('[DEBUG:onCountry] loaded provinces:', this.provinces);

                if (currentProvince) {
                    this.province = currentProvince;
                    await this.onProvince();
                }
            } catch (e) {
                console.error('[ERROR:onCountry]', e);
            }
        },

        async onProvince() {
            console.log('[DEBUG:onProvince] province selected:', this.province);

            const currentDistrict = this.district;
            this.districts = [];
            this.sectors = [];

            if (!this.province) return;

            try {
                const res = await fetch(`/api/districts/${this.province}`);
                if (!res.ok) throw new Error('District fetch failed');
                this.districts = await res.json();
                console.log('[DEBUG:onProvince] loaded districts:', this.districts);

                if (currentDistrict) {
                    this.district = currentDistrict;
                    await this.onDistrict();
                }
            } catch (e) {
                console.error('[ERROR:onProvince]', e);
            }
        },

        async onDistrict() {
            console.log('[DEBUG:onDistrict] district selected:', this.district);

            const currentSector = this.sector;
            this.sectors = [];

            if (!this.district) return;

            try {
                const res = await fetch(`/api/sectors/${this.district}`);
                if (!res.ok) throw new Error('Sector fetch failed');
                this.sectors = await res.json();
                console.log('[DEBUG:onDistrict] loaded sectors:', this.sectors);

                if (currentSector) {
                    this.sector = currentSector;
                }
            } catch (e) {
                console.error('[ERROR:onDistrict]', e);
            }
        },

        async onSector() {
            console.log('[DEBUG:onSector] sector selected:', this.sector);
        },

        async debugAndSubmit(event) {
            console.log('[DEBUG:Submit] Level:', this.level);
            console.log('[DEBUG:Payload before submit]', {
                country: this.country,
                province: this.province,
                district: this.district,
                sector: this.sector,
                name: this.name
            });

            // client-side validation
            const missing = [];
            if (this.level === 'province' && !this.country) missing.push('country');
            if (this.level === 'district' && !this.province) missing.push('province');
            if (this.level === 'sector' && !this.district) missing.push('district');

            if (missing.length) {
                alert('Please select: ' + missing.join(', '));
                console.warn('[DEBUG:Submit blocked - missing]', missing);
                return;
            }

            event.target.submit();
        }
    }
}
</script>

</x-admin-layout>
