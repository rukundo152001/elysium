<x-admin-layout :title="'Insurance Reports & Analytics'">

    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">

        {{-- Header Section --}}
        <div class="bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-6 py-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">Insurance Reports & Analytics</h1>
                        <p class="text-slate-600 mt-1">Comprehensive MRI revenue analysis by insurance provider</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Messages --}}
        <div class="max-w-7xl mx-auto px-6 pt-6">
            @if(session('success'))
                <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-lg shadow-sm flex items-center gap-3">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                    {{ session('error') }}
                </div>
            @endif
        </div>

        {{-- Main Content --}}
        <div class="max-w-7xl mx-auto px-6 py-6">

            {{-- Summary Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                {{-- Total MRI Revenue --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition">
                    <div class="bg-gradient-to-br from-teal-600 to-teal-700 px-6 py-4">
                        <p class="text-teal-100 text-xs font-semibold uppercase tracking-wide">Total Revenue</p>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-3xl font-bold text-slate-900">Rwf {{ number_format($summaryCards['total_revenue']) }}</p>
                        <p class="text-slate-600 text-xs mt-2">All MRI Activities</p>
                    </div>
                </div>

                {{-- Patient Share --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition">
                    <div class="bg-gradient-to-br from-amber-600 to-amber-700 px-6 py-4">
                        <p class="text-amber-100 text-xs font-semibold uppercase tracking-wide">Patient Share</p>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-3xl font-bold text-slate-900">Rwf {{ number_format($summaryCards['insured_patient_revenue']) }}</p>
                        <p class="text-slate-600 text-xs mt-2">Insured Patient share</p>
                    </div>
                </div>

                {{-- Insurance Shares --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition">
                    <div class="bg-gradient-to-br from-green-600 to-green-700 px-6 py-4">
                        <p class="text-green-100 text-xs font-semibold uppercase tracking-wide">Insurance Share</p>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-3xl font-bold text-slate-900">Rwf {{ number_format($summaryCards['insurance_revenue']) }}</p>
                        <p class="text-slate-600 text-xs mt-2">To Be Paid by Insurance</p>
                    </div>
                </div>

                {{-- Private Revenue --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition">
                    <div class="bg-gradient-to-br from-red-600 to-red-700 px-6 py-4">
                        <p class="text-red-100 text-xs font-semibold uppercase tracking-wide">Private Revenue</p>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-3xl font-bold text-slate-900">Rwf {{ number_format($summaryCards['private_revenue']) }}</p>
                        <p class="text-slate-600 text-xs mt-2">No Insurance Involved</p>
                    </div>
                </div>

            </div>

            {{-- Period Report Buttons --}}
            <div class="mb-8 space-y-6">

                <div>
                    <a href="{{ route('admin.reports.custom-date-range') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg shadow-md transition inline-block">
                        Generate Custom Date Range Report
                    </a>
                </div>
            </div>

            {{-- Period Reports Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                {{-- Today's Report --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition">
                    <div class="bg-gradient-to-r from-teal-600 to-teal-700 px-6 py-4">
                        <h3 class="text-white font-bold text-lg">Today's Report</h3>
                    </div>
                    <div class="px-6 py-6 space-y-4">
                        <div>
                            <p class="text-slate-600 text-sm">Total Patients: 
                            <span class="text-sm font-bold text-slate-900">{{ $summaryCards['daily_patients'] ?? 0 }}</span></p>
                        </div>
                        
                        <div>
                            <p class="text-slate-600 text-sm">Total Amount: 
                            <span class="text-sm font-bold text-slate-900">Rwf {{ number_format($summaryCards['daily_revenue'] ?? 0) }}</span></p>
                        </div>
                        <div>
                            <p class="text-slate-600 text-sm">Cash In Hand: 
                            <span class="text-sm font-bold text-slate-900">{{ $summaryCards['daily_cash_received'] ?? 0 }}</span></p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.reports.preview', ['period' => 'daily']) }}" target="_blank" class="flex-1 bg-cyan-500 hover:bg-cyan-600 text-white font-semibold py-2 px-4 rounded transition text-center">
                                Preview
                            </a>
                            <a href="{{ route('admin.reports.print-report') }}" target="_blank" class="flex-1 bg-slate-600 hover:bg-slate-700 text-white font-semibold py-2 px-4 rounded transition text-center">
                                Print
                            </a>
                        </div>
                    </div>
                </div>

                {{-- This Week's Report --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition">
                    <div class="bg-gradient-to-r from-teal-600 to-teal-700 px-6 py-4">
                        <h3 class="text-white font-bold text-lg">This Week's Report</h3>
                    </div>
                    <div class="px-6 py-6 space-y-4">
                        <div>
                            <p class="text-slate-600 text-sm">Total Patients: 
                            <span class="text-sm font-bold text-slate-900">{{ $summaryCards['weekly_patients'] ?? 0 }}</span></p>
                        </div>
                        <div>
                            <p class="text-slate-600 text-sm">Total Amount: 
                            <span class="text-sm font-bold text-slate-900">Rwf {{ number_format($summaryCards['weekly_revenue'] ?? 0) }}</span></p>
                        </div>
                        <div>
                            <p class="text-slate-600 text-sm">Cash In Hand: 
                            <span class="text-sm font-bold text-slate-900">{{ $summaryCards['weekly_cash_received'] ?? 0 }}</span></p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.reports.preview', ['period' => 'weekly']) }}" target="_blank" class="flex-1 bg-cyan-500 hover:bg-cyan-600 text-white font-semibold py-2 px-4 rounded transition text-center">
                                Preview
                            </a>
                            <a href="{{ route('admin.reports.print-report') }}" target="_blank" class="flex-1 bg-slate-600 hover:bg-slate-700 text-white font-semibold py-2 px-4 rounded transition text-center">
                                Print
                            </a>
                        </div>
                    </div>
                </div>

                {{-- This Month's Report --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition">
                    <div class="bg-gradient-to-r from-teal-600 to-teal-700 px-6 py-4">
                        <h3 class="text-white font-bold text-lg">This Month's Report</h3>
                    </div>
                    <div class="px-6 py-6 space-y-4">
                        <div>
                            <p class="text-slate-600 text-sm">Total Patients: 
                            <span class="text-sm font-bold text-slate-900">{{ $summaryCards['monthly_patients'] ?? 0 }}</span></p>
                        </div>
                        <div>
                            <p class="text-slate-600 text-sm">Total Amount: 
                            <span class="text-sm font-bold text-slate-900">Rwf {{ number_format($summaryCards['monthly_revenue'] ?? 0) }}</span></p>
                        </div>
                        <div>
                            <p class="text-slate-600 text-sm">Cash In Hand: 
                            <span class="text-sm font-bold text-slate-900">{{ $summaryCards['monthly_cash_received'] ?? 0 }}</span></p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.reports.preview', ['period' => 'monthly']) }}" target="_blank" class="flex-1 bg-cyan-500 hover:bg-cyan-600 text-white font-semibold py-2 px-4 rounded transition text-center">
                                Preview
                            </a>
                            <a href="{{ route('admin.reports.print-report') }}" target="_blank" class="flex-1 bg-slate-600 hover:bg-slate-700 text-white font-semibold py-2 px-4 rounded transition text-center">
                                Print
                            </a>
                        </div>
                    </div>
                </div>

                {{-- This Year's Report --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden hover:shadow-md transition">
                    <div class="bg-gradient-to-r from-teal-600 to-teal-700 px-6 py-4">
                        <h3 class="text-white font-bold text-lg">This Year's Report</h3>
                    </div>
                    <div class="px-6 py-6 space-y-4">
                        <div>
                            <p class="text-slate-600 text-sm">Total Patients: 
                            <span class="text-sm font-bold text-slate-900">{{ $summaryCards['yearly_patients'] ?? 0 }}</span></p>
                        </div>
                        <div>
                            <p class="text-slate-600 text-sm">Total Amount: 
                            <span class="text-sm font-bold text-slate-900">Rwf {{ number_format($summaryCards['yearly_revenue'] ?? 0) }}</span></p>
                        </div>
                        <div>
                            <p class="text-slate-600 text-sm">Cash In Hand: 
                            <span class="text-sm font-bold text-slate-900">{{ $summaryCards['yearly_cash_received'] ?? 0 }}</span></p>
                        </div>
                        
                        <div class="flex gap-2">
                            <a href="{{ route('admin.reports.preview', ['period' => 'yearly']) }}" target="_blank" class="flex-1 bg-cyan-500 hover:bg-cyan-600 text-white font-semibold py-2 px-4 rounded transition text-center">
                                Preview
                            </a>
                            <a href="{{ route('admin.reports.print-report') }}" target="_blank" class="flex-1 bg-slate-600 hover:bg-slate-700 text-white font-semibold py-2 px-4 rounded transition text-center">
                                Print
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

</x-admin-layout>
