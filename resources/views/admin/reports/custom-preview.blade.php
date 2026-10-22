
<x-admin-layout :title="'Custom Date Range Report'">

    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">

        {{-- Header Section --}}
        <div class="bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-6 py-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">{{ $reportData['period_label'] ?? 'Custom Report' }} Insurance  {{ $reportData['insurance_provider'] }}</h1>
                        <p class="text-slate-600 mt-1">Detailed revenue breakdown by patient share and insurance</p>
                    </div>
                    <a href="{{ route('admin.reports.custom-date-range') }}" class="text-blue-600 hover:text-blue-700 font-semibold">← Back to Custom Report</a>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="max-w-7xl mx-auto px-6 py-8">

            {{-- Revenue Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

                {{-- Patient Share --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition">
                    <div class="bg-gradient-to-br from-amber-600 to-amber-700 px-6 py-4">
                        <p class="text-amber-100 text-xs font-semibold uppercase tracking-wide">Amount Received</p>
                        <p class="text-amber-50 text-xs mt-1">Patient Share</p>
                    </div>
                    <div class="px-6 py-6">
                        <p class="text-3xl font-bold text-slate-900">Rwf {{ number_format($reportData['patient_share'] ?? 0) }}</p>
                        <p class="text-slate-600 text-xs mt-2">From Patients</p>
                    </div>
                </div>

                {{-- Insurance Share --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition">
                    <div class="bg-gradient-to-br from-green-600 to-green-700 px-6 py-4">
                        <p class="text-green-100 text-xs font-semibold uppercase tracking-wide">Amount Received</p>
                        <p class="text-green-50 text-xs mt-1">Insurance Share</p>
                    </div>
                    <div class="px-6 py-6">
                        <p class="text-3xl font-bold text-slate-900">Rwf {{ number_format($reportData['insurance_share'] ?? 0) }}</p>
                        <p class="text-slate-600 text-xs mt-2">From Insurance</p>
                    </div>
                </div>

                {{-- Total Revenue --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition">
                    <div class="bg-gradient-to-br from-blue-600 to-blue-700 px-6 py-4">
                        <p class="text-blue-100 text-xs font-semibold uppercase tracking-wide">Total Revenue</p>
                        <p class="text-blue-50 text-xs mt-1">All Sources</p>
                    </div>
                    <div class="px-6 py-6">
                        <p class="text-3xl font-bold text-slate-900">Rwf {{ number_format(($reportData['patient_share'] ?? 0) + ($reportData['insurance_share'] ?? 0)) }}</p>
                        <p class="text-slate-600 text-xs mt-2">Combined Income</p>
                    </div>
                </div>

            </div>

            {{-- Detailed Breakdown --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-slate-900 to-slate-800 px-6 py-5">
                    <h2 class="text-xl font-bold text-white">Detailed Revenue Breakdown</h2>
                    <p class="text-slate-300 text-sm mt-1">Payment details from treatment records</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="text-left text-sm font-semibold text-slate-700 px-6 py-4">Treated Date</th>
                                <th class="text-left text-sm font-semibold text-slate-700 px-6 py-4">Patient Name</th>
                                <th class="text-right text-sm font-semibold text-slate-700 px-6 py-4">Patient Share</th>
                                <th class="text-right text-sm font-semibold text-slate-700 px-6 py-4">Insurance Share</th>
                                <th class="text-right text-sm font-semibold text-slate-700 px-6 py-4">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($activities as $detail)
                                <tr
                                onclick="window.location='{{ route('admin.patients.details', $detail->patient->id) }}'" 
                                 class="hover:bg-slate-100 transition">
                                    <td class="px-6 py-4 text-sm font-medium text-slate-900">
                                        {{ \Carbon\Carbon::parse($detail->patient->treatment_date)->format('d, M Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-slate-600">{{ $detail->patient->first_name ?? '' }} {{ $detail->patient->last_name ?? '' }}</td>
                                    <td class="text-right px-6 py-4 text-sm text-slate-900">Rwf {{ number_format($detail->total_patient_amount ?? 0) }}</td>
                                    <td class="text-right px-6 py-4 text-sm text-slate-900">Rwf {{ number_format($detail->total_insurance_amount ?? 0) }}</td>
                                    <td class="text-right px-6 py-4 text-sm font-semibold text-slate-900">
                                        Rwf {{ number_format(($detail->total_patient_amount ?? 0) + ($detail->total_insurance_amount ?? $detail->total_amount)) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-slate-500 text-sm">
                                        <svg class="w-12 h-12 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        No treatment records found for the selected period.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Table Footer with Totals --}}
                <div class="bg-slate-50 border-t border-slate-200 px-6 py-4 flex justify-end">
                    <div class="space-y-2">
                        <div class="flex gap-12">
                            <span class="font-semibold text-slate-700">Patient Share Total:</span>
                            <span class="font-bold text-lg text-amber-700">Rwf {{ number_format($reportData['patient_share'] ?? 0) }}</span>
                        </div>
                        <div class="flex gap-12">
                            <span class="font-semibold text-slate-700">Insurance Share Total:</span>
                            <span class="font-bold text-lg text-green-700">Rwf {{ number_format($reportData['insurance_share'] ?? 0) }}</span>
                        </div>
                        <div class="flex gap-12 border-t border-slate-300 pt-2">
                            <span class="font-semibold text-slate-900">Grand Total:</span>
                            <span class="font-bold text-lg text-blue-700">Rwf {{ number_format(($reportData['patient_share'] ?? 0) + ($reportData['insurance_share'] ?? 0)) }}</span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Action Buttons --}}
            <div class="flex justify-center gap-4 mt-8">
                <a href="{{ route('admin.reports.custom-date-range') }}" class="px-8 py-3 bg-gradient-to-r from-slate-600 to-slate-700 text-white rounded-lg hover:from-slate-700 hover:to-slate-800 transition font-semibold shadow-md">
                    ← Back to Custom Report
                </a>
                <button onclick="window.print()" class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition font-semibold shadow-md">
                    Print This Report
                </button>
            </div>

        </div>

    </div>

    <script>
        console.log('[v0] Custom Preview Report Loaded');
        console.log('[v0] Report Data:', {
            patient_share: '{{ $reportData['patient_share'] ?? 0 }}',
            insurance_share: '{{ $reportData['insurance_share'] ?? 0 }}',
            detail_count: {{ count($reportData['details'] ?? []) }}
        });
    </script>

</x-admin-layout>
