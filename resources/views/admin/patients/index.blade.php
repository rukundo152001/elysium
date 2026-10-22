<x-admin-layout :title="'Patients Management'">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-900">All Patients</h2>
    </div>

    {{-- Success & Error --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-4 rounded-r-lg shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-4 rounded-r-lg shadow-sm">
            {{ session('error') }}
        </div>
    @endif


    {{-- ============================
     SEARCH SECTION
============================ --}}
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form method="GET" action="{{ route('admin.patients.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            {{-- Unified Search Field --}}
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Search by name, Ref No, or ID..."
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-600">

            {{-- Search Button --}}
            <button type="submit"
                class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                Search
            </button>

            {{-- Reset Button --}}
            <a href="{{ route('admin.patients.index') }}"
                class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition inline-flex items-center justify-center">
                Reset
            </a>

        </div>
    </form>
</div>




    {{-- ============================
     FILTER SECTION
============================ --}}
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form method="GET" action="{{ route('admin.patients.index') }}">
        
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6">

            {{-- Insurance Category --}}
            <div class="flex flex-col">
                <label class="text-sm font-semibold text-gray-700 mb-1">
                    Insurance Category
                </label>
                <select name="category"
                    class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-teal-600 focus:border-teal-600">
                    <option value="">All Insurance Categories</option>

                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->id }}"
                            {{ request('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Single Date Filter --}}
            <div class="flex flex-col">
                <label class="text-sm font-semibold text-gray-700 mb-1">
                    Date
                </label>
                <input 
                    type="date" 
                    name="date" 
                    value="{{ request('date') }}"
                    class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-teal-600 focus:border-teal-600"
                />
            </div>

            {{-- Period Start --}}
            <div class="flex flex-col">
                <label class="text-sm font-semibold text-gray-700 mb-1">
                    Start Date
                </label>
                <input 
                    type="date" 
                    name="start_date" 
                    value="{{ request('start_date') }}"
                    class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-teal-600 focus:border-teal-600"
                />
            </div>

            {{-- Period End --}}
            <div class="flex flex-col">
                <label class="text-sm font-semibold text-gray-700 mb-1">
                    End Date
                </label>
                <input 
                    type="date" 
                    name="end_date" 
                    value="{{ request('end_date') }}"
                    class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:ring-2 focus:ring-teal-600 focus:border-teal-600"
                />
            </div>

            {{-- Submit --}}
            <div class="flex flex-col justify-end">
                <button type="submit"
                    class="w-full px-4 py-2 bg-teal-600 text-white font-semibold rounded-lg hover:bg-teal-700 transition">
                    Apply Filters
                </button>
            </div>

        </div>

    </form>
</div>


    {{-- ============================
         PATIENTS TABLE
    ============================ --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Patient Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Ref Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-600 uppercase">Provider</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase">Total Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase">Patient Pays</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase">Paid</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-600 uppercase">Outstanding</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    @foreach($patients as $p)
                        @php 
                            $sum = $patientSummaries[$p->id] ?? [
                                'total_amount' => 0,
                                'patient_pays' => 0,
                                'insurance_pays' => 0,
                                'paid' => 0,
                                'outstanding' => 0,
                            ];
                        @endphp

                        <tr onclick="window.location='{{ route('admin.patients.details', $p->id) }}'" 
                            class="hover:bg-teal-50 cursor-pointer transition">

                            <td class="px-6 py-4 text-sm text-gray-800 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center text-teal-600 font-semibold text-sm">
                                        {{ strtoupper(substr($p->first_name,0,1)) }}
                                    </div>
                                    <span class="ml-3 font-medium">
                                        {{ $p->first_name }} {{ $p->last_name }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">
                                {{ $p->ref_number }}
                            </td>

                            <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">
                                {{ $p->insuranceCategory->name }}
                            </td>

                            <td class="px-6 py-4 text-sm text-right font-semibold text-gray-800">
                                Rwf {{ number_format((int) $sum['total_amount']) }}
                            </td>

                            <td class="px-6 py-4 text-sm text-right text-gray-700">
                                Rwf {{ number_format((int) $sum['patient_pays']) }}
                            </td>

                            

                            <td class="px-6 py-4 text-sm text-right text-green-600 font-semibold">
                                Rwf {{ number_format((int) $sum['paid']) }}
                            </td>

                            <td class="px-6 py-4 text-sm text-right text-red-600 font-semibold">
                                Rwf {{ number_format((int) $sum['outstanding']) }}
                            </td>

                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $patients->links() }}
    </div>

</x-admin-layout>
