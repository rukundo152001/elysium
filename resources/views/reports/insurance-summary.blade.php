<x-report-layout :title="'Insurance Reports & Analytics'">
<div class="w-full min-w-0 max-w-none mx-0 px-0 py-4 space-y-6">
    <section class="w-full min-w-0 max-w-none bg-white shadow-sm rounded-xl border border-gray-200 p-4 sm:p-6">
        <h1 class="text-xl font-semibold text-gray-900">Insurance Summary &amp; Global Bill Reconciliation</h1>
        <p class="mt-1 text-sm text-gray-600">
            Generate a draft from the system's MRI examination records, review and correct every row, then finalize and download the Excel template.
            Final summaries are saved by insurance provider and monthly period.
        </p>

        @if (session('success'))
            <div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.reports.insurance-summary.generate') }}" class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-5 items-end">
            @csrf
            <div>
                <label for="period" class="block text-sm font-medium text-gray-700">Insurance month</label>
                <input id="period" name="period" type="month" required value="{{ old('period', now()->format('Y-m')) }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label for="insurance_provider" class="block text-sm font-medium text-gray-700">Insurance provider</label>
                <input id="insurance_provider" name="insurance_provider" type="text" required maxlength="191"
                       value="{{ old('insurance_provider', 'RSSB / CBHI') }}" placeholder="Use the insurer name in your system"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                
            </div>
            <!--<div>
                <label for="invoice_number" class="block text-sm font-medium text-gray-700">Invoice number</label>
                <input id="invoice_number" name="invoice_number" type="number" min="1" required value="{{ old('invoice_number', 1) }}"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div> -->
            <div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 3v12m0 0 4-4m-4 4-4-4" stroke-linecap="round" stroke-linejoin="round"/><path d="M5 17v3h14v-3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Generate Draft for Review
                </button>
            </div>
        </form>
    </section>

    <section class="block w-full min-w-0 max-w-none mx-0 bg-white shadow-sm border border-gray-200 p-2 sm:p-4">    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <h2 class="text-lg font-semibold text-gray-900">Saved insurance summaries</h2>
            <p class="text-sm text-gray-500">Finalized workbooks and reconciled global bills are retained in private storage.</p>
        </div>

        <div class="mt-4 w-full max-w-full overflow-x-auto">
            <table class="w-full min-w-max divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-3 text-left font-semibold text-gray-700">Period</th>
                        <th class="px-3 py-3 text-left font-semibold text-gray-700">Insurance provider</th>
                        <th class="px-3 py-3 text-right font-semibold text-gray-700">Exams</th>
                        <th class="px-3 py-3 text-right font-semibold text-gray-700">Total amount</th>
                        <th class="px-3 py-3 text-left font-semibold text-gray-700">Status</th>
                        <th class="px-3 py-3 text-left font-semibold text-gray-700">Last updated</th>
                        <th class="px-3 py-3 text-left font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($batches as $batch)
                        <tr class="align-top">
                            <td class="px-3 py-3 whitespace-nowrap">{{ \Carbon\Carbon::createFromFormat('Y-m', $batch->period)->format('F Y') }}</td>
                            <td class="px-3 py-3">{{ $batch->insurance_provider_name }}</td>
                            <td class="px-3 py-3 text-right tabular-nums">{{ $batch->items_count }}</td>
                            <td class="px-3 py-3 text-right tabular-nums">{{ number_format((float) $batch->total_cost, 2) }}</td>
                            <td class="px-3 py-3 whitespace-nowrap">
                                @if ($batch->status === 'draft')
                                    <span class="inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-800">Draft</span>
                                @elseif ($batch->status === 'reconciled')
                                    <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">Reconciled</span>
                                @else
                                    <span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-800">Finalized</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 whitespace-nowrap text-gray-600">{{ optional($batch->updated_at)->format('d M Y H:i') }}</td>
                            <td class="px-3 py-3 min-w-[260px]">
                                <div class="flex flex-col items-start gap-3">
                                    @if ($batch->status === 'draft')
                                        <a href="{{ route('reports.insurance-summary.review', $batch) }}" class="font-medium text-indigo-700 hover:text-indigo-900 underline">Review draft</a>
                                    @else
                                        <a href="{{ route('reports.insurance-summary.review', $batch) }}" class="font-medium text-indigo-700 hover:text-indigo-900 underline">{{ $batch->status === 'reconciled' ? 'View reconciled summary' : 'Review / edit finalized summary' }}</a>
                                        <a href="{{ route('reports.insurance-summary.download-finalized', $batch) }}" class="font-medium text-indigo-700 hover:text-indigo-900 underline">Download current Excel</a>
                                        @if ($batch->global_bill_path)
                                            <a href="{{ route('reports.insurance-summary.download-global-bill', $batch) }}" class="font-medium text-gray-700 hover:text-gray-900 underline">View/download global bill: {{ $batch->global_bill_original_name }}</a>
                                        @endif
                                        @if ($batch->status === 'finalized')
                                            <form method="POST" action="{{ route('reports.insurance-summary.upload-global-bill', $batch) }}" enctype="multipart/form-data" class="w-full rounded-lg border border-gray-200 p-3 space-y-2">
                                                @csrf
                                                <label class="block text-xs font-medium text-gray-700">Upload global bill (this locks the summary)</label>
                                                <input type="file" name="global_bill" required accept=".pdf,.xlsx,.xls,.csv" class="block w-full text-xs text-gray-700 file:mr-2 file:rounded file:border-0 file:bg-gray-100 file:px-2 file:py-1.5 file:text-xs file:font-semibold">
                                                <p class="text-xs text-gray-500">PDF / Excel / CSV, max 20 MB. Once uploaded, the summary becomes permanently read-only in this workflow. The file cannot be replaced here.</p>
                                                <button type="submit" class="rounded-md bg-gray-800 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-700" onclick="return confirm('Upload the global bill and lock this summary as reconciled? This cannot be undone in the normal workflow.');">Upload Global Bill &amp; Reconcile</button>
                                            </form>
                                        @else
                                            <p class="text-xs font-semibold text-green-700">Reconciled: read-only. No further changes or global bill replacement are allowed.</p>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-3 py-8 text-center text-gray-500">No summaries generated yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($batches->hasPages())
            <div class="mt-4">{{ $batches->links() }}</div>
        @endif
    </section>
</div>

</x-report-layout>
