<x-admin-layout :title="'Report Preview - ' . ucfirst(request('period', 'daily'))">

    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">

        {{-- Header Section --}}
        <div class="bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-6 py-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">{{ ucfirst(request('period', 'daily')) }} Report Preview</h1>
                        <p class="text-slate-600 mt-1">Detailed revenue breakdown by patient share and insurance</p>
                    </div>
                    <a href="{{ route('admin.reports.index') }}" class="text-blue-600 hover:text-blue-700 font-semibold">← Back to Reports</a>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="max-w-7xl mx-auto px-6 py-8">

            {{-- Filter Section --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-8" x-data="reportFilter()">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Filter Report</h3>
                
                @if(request('period') == 'daily')
                    {{-- Daily Filter --}}
                    <form action="{{ route('admin.reports.preview', ['period' => 'daily']) }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Select Date</label>
                                <input type="date" name="date" value="{{ request('date', \Carbon\Carbon::today()->toDateString()) }}"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition font-semibold shadow-sm">
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                @elseif(request('period') == 'monthly')
                    {{-- Monthly Filter --}}
                    <form action="{{ route('admin.reports.preview', ['period' => 'monthly']) }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Select Month & Year</label>
                                <input type="month" name="month" value="{{ request('month', \Carbon\Carbon::now()->format('Y-m')) }}"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition font-semibold shadow-sm">
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                @elseif(request('period') == 'yearly')
                    {{-- Yearly Filter --}}
                    <form action="{{ route('admin.reports.preview', ['period' => 'yearly']) }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Select Year</label>
                                <select name="year" class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                    @for($i = \Carbon\Carbon::now()->year; $i >= \Carbon\Carbon::now()->year - 5; $i--)
                                        <option value="{{ $i }}" {{ request('year', \Carbon\Carbon::now()->year) == $i ? 'selected' : '' }}>
                                            {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition font-semibold shadow-sm">
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                @else
                    {{-- Weekly Filter --}}
                    <form action="{{ route('admin.reports.preview', ['period' => 'weekly']) }}" method="GET" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Select Week Starting Date</label>
                                <input type="date" name="week_date" value="{{ request('week_date', \Carbon\Carbon::now()->startOfWeek()->toDateString()) }}"
                                    class="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="w-full px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition font-semibold shadow-sm">
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>

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
                                <th class="text-left text-sm font-semibold text-slate-700 px-6 py-4">Treatment Date</th>
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
            </div>

            {{-- Print Button --}}
            <div class="flex justify-center mt-8">
                <a href="{{ route('admin.reports.print-report') }}"  class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition font-semibold shadow-md">
                                Print
                            </a>
            </div>

        </div>

    </div>

</x-admin-layout>

<script>
    function reportFilter() {
        return {
            period: '{{ request("period", "daily") }}',
            selectedDate: '{{ request("date", \Carbon\Carbon::today()->toDateString()) }}',
            selectedMonth: '{{ request("month", \Carbon\Carbon::now()->format("Y-m")) }}',
            selectedYear: '{{ request("year", \Carbon\Carbon::now()->year) }}',
            selectedWeekDate: '{{ request("week_date", \Carbon\Carbon::now()->startOfWeek()->toDateString()) }}',
            
            init() {
                console.log('[v0] Report Filter initialized:', {
                    period: this.period,
                    selectedDate: this.selectedDate,
                    selectedMonth: this.selectedMonth,
                    selectedYear: this.selectedYear,
                    selectedWeekDate: this.selectedWeekDate
                });
            },

            getCurrentPeriod() {
                return this.period;
            },

            getPeriodLabel() {
                const labels = {
                    'daily': 'Daily',
                    'weekly': 'Weekly',
                    'monthly': 'Monthly',
                    'yearly': 'Yearly'
                };
                return labels[this.period] || 'Report';
            },

            formatDateForDisplay() {
                if (this.period === 'daily') {
                    const date = new Date(this.selectedDate);
                    return date.toLocaleDateString('en-US', { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                } else if (this.period === 'monthly') {
                    const [year, month] = this.selectedMonth.split('-');
                    const date = new Date(year, month - 1);
                    return date.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'long' 
                    });
                } else if (this.period === 'yearly') {
                    return `Year ${this.selectedYear}`;
                } else {
                    const date = new Date(this.selectedWeekDate);
                    const weekEnd = new Date(date);
                    weekEnd.setDate(weekEnd.getDate() + 6);
                    return `Week of ${date.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    })} - ${weekEnd.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    })}`;
                }
            },

            isCurrentPeriod(periodName) {
                return this.period === periodName;
            },

            switchToPeriod(newPeriod) {
                console.log('[v0] Switching to period:', newPeriod);
                this.period = newPeriod;
            },

            submitFilter(formSelector) {
                const form = document.querySelector(formSelector);
                if (form) {
                    console.log('[v0] Submitting filter form for period:', this.period);
                    form.submit();
                }
            },

            updateDateInput(newDate) {
                this.selectedDate = newDate;
                console.log('[v0] Date updated to:', this.selectedDate);
            },

            updateMonthInput(newMonth) {
                this.selectedMonth = newMonth;
                console.log('[v0] Month updated to:', this.selectedMonth);
            },

            updateYearInput(newYear) {
                this.selectedYear = newYear;
                console.log('[v0] Year updated to:', this.selectedYear);
            },

            updateWeekDateInput(newDate) {
                this.selectedWeekDate = newDate;
                console.log('[v0] Week date updated to:', this.selectedWeekDate);
            }
        }
    }
</script>
