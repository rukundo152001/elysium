<x-admin-layout :title="'Generate Custom Date Range Report'">

    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100">

        {{-- Header Section --}}
        <div class="bg-white border-b border-slate-200">
            <div class="max-w-4xl mx-auto px-6 py-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900">Custom Date Range Report</h1>
                        <p class="text-slate-600 mt-1">Select a date range to generate a detailed revenue report</p>
                    </div>
                    <a href="{{ route('admin.reports.index') }}" class="text-blue-600 hover:text-blue-700 font-semibold">← Back to Reports</a>
                </div>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="max-w-4xl mx-auto px-6 py-12">

            {{-- Date Range Selection Form --}}
            <div class="bg-white rounded-xl shadow-md border border-slate-200 p-8">
                
                <form id="dateRangeForm" method="GET" action="{{ route('admin.reports.custom-preview') }}" class="space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- From Date --}}
                        <div>
                            <label for="from_date" class="block text-sm font-semibold text-slate-700 mb-3">
                                From Date
                            </label>
                            <input 
                                type="date" 
                                id="from_date" 
                                name="from_date" 
                                value="{{ old('from_date', \Carbon\Carbon::now()->startOfMonth()->toDateString()) }}"
                                required
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            >
                            <p class="text-slate-500 text-xs mt-1">Start date of the report</p>
                        </div>

                        {{-- To Date --}}
                        <div>
                            <label for="to_date" class="block text-sm font-semibold text-slate-700 mb-3">
                                To Date
                            </label>
                            <input 
                                type="date" 
                                id="to_date" 
                                name="to_date" 
                                value="{{ old('to_date', \Carbon\Carbon::now()->toDateString()) }}"
                                required
                                class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                            >
                            <p class="text-slate-500 text-xs mt-1">End date of the report</p>
                        </div>

                    </div>

                    {{-- Provider Filter (Optional) --}}
                    <div>
                        <label for="provider" class="block text-sm font-semibold text-slate-700 mb-3">
                            Filter by Provider (Optional)
                        </label>
                        <select 
                            id="provider" 
                            name="provider"
                            class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        >
                            <option value="">All Providers</option>
                            @foreach($providers ?? [] as $prov)
                                <option value="{{ $prov->id }}">{{ $prov->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-slate-500 text-xs mt-1">Optional: Filter results by a specific insurance provider</p>
                    </div>

                    {{-- Buttons Section --}}
                    <div class="flex gap-4 pt-6 border-t border-slate-200">
                        
                        {{-- Preview Button --}}
                        <button 
                            type="submit" 
                            name="action" 
                            value="preview"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 transition shadow-md"
                        >
                            <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Preview Report
                        </button>

                        {{-- Generate Button --}}
                        <button 
                            type="submit" 
                            name="action" 
                            value="generate"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 text-white font-semibold rounded-lg hover:from-green-700 hover:to-green-800 transition shadow-md"
                        >
                            <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3v-6"></path>
                            </svg>
                            Generate Report
                        </button>

                    </div>

                </form>

            </div>

            {{-- Info Section --}}
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <div class="flex gap-4">
                    <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="font-semibold text-blue-900">Report Information</h3>
                        <ul class="text-blue-800 text-sm mt-2 space-y-1">
                            <li>• <strong>Preview:</strong> View the report data before downloading</li>
                            <li>• <strong>Generate:</strong> Download the report as PDF or Excel</li>
                            <li>• All amounts are displayed in Rwf (Rwandan Franc)</li>
                            <li>• Reports include patient share and insurance share breakdown</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script>
        document.getElementById('dateRangeForm').addEventListener('submit', function(e) {
            const fromDate = new Date(document.getElementById('from_date').value);
            const toDate = new Date(document.getElementById('to_date').value);
            
            if (fromDate > toDate) {
                e.preventDefault();
                alert('Error: "From Date" must be before "To Date"');
                return false;
            }
            
            console.log('[v0] Date range form submitted:', {
                from: document.getElementById('from_date').value,
                to: document.getElementById('to_date').value,
                provider: document.getElementById('provider').value
            });
        });
    </script>

</x-admin-layout>
