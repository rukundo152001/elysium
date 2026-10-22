<x-staff-layout :title="'Staff Dashboard'">
<div class="min-h-screen bg-gray-100 p-6">
  

  {{-- ============================
     SEARCH SECTION
============================ --}}
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <form method="GET" action="{{ route('staff.index') }}">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            {{-- Unified Search Field --}}
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Search by name, Ref No, or Ins ID..."
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-600">

            {{-- Search Button --}}
            <button type="submit"
                class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                Search
            </button>

            {{-- Reset Button --}}
            .<a href="{{ route('staff.index') }}"
                class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400 transition inline-flex items-center justify-center">
                Reset
            </a>

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
        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Ref</th>
        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Name</th>
        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">INS / NID</th>
        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Phone</th>
        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Test Date</th>
        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Registered By</th>
        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
      </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-100">
                  @forelse($patients as $p)
                  <tr onclick="window.location='{{ route('staff.patients.details', $p->id) }}'" 
                  class="hover:bg-teal-50 cursor-pointer transition duration-150 ease-in-out"
                  >
                  <td class="px-4 py-3">{{ $p->ref_number }}</td>
                  <td class="px-4 py-3">{{ $p->first_name }} {{ $p->last_name }}</td>
                  <td class="px-4 py-3">{{ $p->insurance_number }}</td>
                  <td class="px-4 py-3">{{ $p->phone }}</td>
                  <td class="px-4 py-3">{{ \Carbon\Carbon::parse($p->treatment_date)->format('Y-m-d') }}</td>
                  <td class="px-4 py-3">{{ optional($p->registeredBy)->name }}</td>
                  <td class="px-4 py-3">
                    @php
                    
                    $isInsured = $patient->insuranceProvider
                    && $patient->insuranceProvider->name !== 'PRIVATE';
                    @endphp
                    <button
                        type="button"
                        class="px-3 py-1 rounded-md text-sm bg-teal-100 text-teal-700 hover:bg-teal-200"
                        data-patient-invoice-url="{{ route('patients.invoice', ['ref_number' => $patient->ref_number]) }}"
                        data-insurance-invoice-url="{{ route('patients.insurance-invoice', ['ref_number' => $patient->ref_number]) }}"
                        data-is-insured="{{ $isInsured ? '1' : '0' }}"
                        onclick="prepareInvoiceOptions(this)"
                        >
                        <i class="bi bi-printer"></i>
                        Print Invoice
                    </button>
                </td>
            </tr>
@empty
<tr>
    <td colspan="7" class="px-6 py-8 text-center text-gray-500">No patients found.</td>
</tr>
@endforelse

    </tbody>
            </table>
        </div>
    </div>

    
  </div>
</div>
    {{-- Pagination --}}
    <div class="mt-6">
        {{ $patients->links() }}
    </div>

</x-staff-layout>
