<x-report-layout :title="'Insurance Reports & Analytics'">
<div class="w-full min-w-0 max-w-none mx-0 px-0 py-4 space-y-6">
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div>
                <p class="text-sm font-medium {{ $isReadOnly ? 'text-green-700' : ($isFinalized ? 'text-blue-700' : 'text-indigo-700') }}">{{ $isReadOnly ? 'Reconciled summary — read-only' : ($isFinalized ? 'Finalized summary — editable until reconciliation' : 'Draft review') }}</p>
                <h1 class="mt-1 text-xl font-semibold text-gray-900">{{ $batch->insurance_provider_name }} — {{ \Carbon\Carbon::createFromFormat('Y-m', $batch->period)->format('F Y') }}</h1>
                <p class="mt-1 text-sm text-gray-600">Invoice number: {{ $batch->invoice_number }}. N0 is assigned per examination, not per patient, starting at 1 for this insurer and month.</p>
                <p class="mt-1 text-sm text-gray-600">Rows are sorted by the source MRI test number. A patient with two MRI examinations has two separate rows, even when the dates match.</p>
            </div>
            <a href="{{ route('reports.insurance-summary.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Back to summaries</a>
        </div>

        @if (session('success'))
            <div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
    </div>

    <form id="review-form" method="POST" action="{{ route('reports.insurance-summary.save-draft', $batch) }}" class="bg-white shadow-sm rounded-xl border border-gray-200 p-4 md:p-6">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
            <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                <p class="text-xs text-gray-500">Examinations</p><p class="text-lg font-semibold text-gray-900">{{ $batch->items->count() }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                <p class="text-xs text-gray-500">Total amount</p><p id="summary-total" class="text-lg font-semibold text-gray-900">{{ number_format((float) $batch->total_cost, 2) }}</p>
            </div>
            <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                <p class="text-xs text-gray-500">Amount to be paid by insurer (90%)</p><p id="summary-payable" class="text-lg font-semibold text-gray-900">{{ number_format((float) $batch->amount_to_be_paid_total, 2) }}</p>
            </div>
        </div>

        @if ($isReadOnly)
            <div class="rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-900 mb-4">
                This summary has been reconciled. All fields and actions are read-only because the global bill has been uploaded. The original records and this saved claim cannot be changed through this workflow.
            </div>
        @elseif ($isFinalized)
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900 mb-4">
                This summary is finalized but not reconciled. You may still edit, verify, or delete summary rows. Save your changes to update the stored Excel workbook. Uploading the global bill will permanently switch this summary to read-only.
            </div>
        @else
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900 mb-4">
                Modify the editable fields and save draft changes. Verify each examination individually; finalization is blocked until all rows are verified. You can leave and return later. The template uses 10% copayment and 90% payable by the insurer.
            </div>
        @endif

        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-[2050px] w-full divide-y divide-gray-200 text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-3 text-left font-semibold text-gray-700">N0</th>
                        <th class="px-2 py-3 text-left font-semibold text-gray-700">Test No.</th>
                        <th class="px-2 py-3 text-left font-semibold text-gray-700">Exam date</th>
                        <th class="px-2 py-3 text-left font-semibold text-gray-700">Beneficiary name</th>
                        <th class="px-2 py-3 text-left font-semibold text-gray-700">Affiliation / insurance No.</th>
                        <th class="px-2 py-3 text-left font-semibold text-gray-700">Date of birth</th>
                        <th class="px-2 py-3 text-left font-semibold text-gray-700">Gender</th>
                        <th class="px-2 py-3 text-left font-semibold text-gray-700">Affiliate / head of household</th>
                        <th class="px-2 py-3 text-left font-semibold text-gray-700">Family code</th>
                        <th class="px-2 py-3 text-left font-semibold text-gray-700">Prescribing facility</th>
                        <th class="px-2 py-3 text-right font-semibold text-gray-700">Cost</th>
                        <th class="px-2 py-3 text-right font-semibold text-gray-700">Total (100%)</th>
                        <th class="px-2 py-3 text-right font-semibold text-gray-700">Copayment (10%)</th>
                        <th class="px-2 py-3 text-right font-semibold text-gray-700">Insurer (90%)</th>
                        <th class="px-2 py-3 text-left font-semibold text-gray-700">Verification / Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($batch->items as $item)
                        <tr data-review-row>
                            <td class="px-2 py-2 whitespace-nowrap font-semibold text-gray-800">{{ $item->n0 }}<input type="hidden" name="items[{{ $item->id }}][id]" value="{{ $item->id }}"></td>
                            <td class="px-2 py-2 whitespace-nowrap text-gray-600">{{ $item->test_number }}</td>
                            <td class="px-2 py-2"><input {{ $isReadOnly ? 'disabled' : '' }} type="date" name="items[{{ $item->id }}][exam_date]" required value="{{ optional($item->exam_date)->format('Y-m-d') }}" class="w-36 rounded border-gray-300 px-2 py-1"></td>
                            <td class="px-2 py-2"><input {{ $isReadOnly ? 'disabled' : '' }} type="text" name="items[{{ $item->id }}][beneficiary_name]" required maxlength="191" value="{{ $item->beneficiary_name }}" class="w-48 rounded border-gray-300 px-2 py-1"></td>
                            <td class="px-2 py-2"><input {{ $isReadOnly ? 'disabled' : '' }} type="text" name="items[{{ $item->id }}][beneficiary_affiliation_number]" maxlength="191" value="{{ $item->beneficiary_affiliation_number }}" class="w-44 rounded border-gray-300 px-2 py-1"></td>
                            <td class="px-2 py-2"><input {{ $isReadOnly ? 'disabled' : '' }} type="date" name="items[{{ $item->id }}][date_of_birth]" value="{{ optional($item->date_of_birth)->format('Y-m-d') }}" class="w-36 rounded border-gray-300 px-2 py-1"></td>
                            <td class="px-2 py-2"><input {{ $isReadOnly ? 'disabled' : '' }} type="text" name="items[{{ $item->id }}][gender]" maxlength="30" value="{{ $item->gender }}" class="w-20 rounded border-gray-300 px-2 py-1"></td>
                            <td class="px-2 py-2"><input {{ $isReadOnly ? 'disabled' : '' }} type="text" name="items[{{ $item->id }}][affiliate_name]" maxlength="191" value="{{ $item->affiliate_name }}" class="w-48 rounded border-gray-300 px-2 py-1"></td>
                            <td class="px-2 py-2"><input {{ $isReadOnly ? 'disabled' : '' }} type="text" name="items[{{ $item->id }}][family_code]" maxlength="191" value="{{ $item->family_code }}" class="w-40 rounded border-gray-300 px-2 py-1"></td>
                            <td class="px-2 py-2"><input {{ $isReadOnly ? 'disabled' : '' }} type="text" name="items[{{ $item->id }}][prescribing_facility]" maxlength="191" value="{{ $item->prescribing_facility }}" class="w-44 rounded border-gray-300 px-2 py-1"></td>
                            <td class="px-2 py-2 text-right"><input {{ $isReadOnly ? 'disabled' : '' }} data-cost-input type="number" name="items[{{ $item->id }}][cost]" required min="0" step="0.01" value="{{ number_format((float) $item->cost, 2, '.', '') }}" class="w-28 rounded border-gray-300 px-2 py-1 text-right"></td>
                            <td class="px-2 py-2 text-right whitespace-nowrap"><span data-total>{{ number_format((float) $item->total_amount, 2) }}</span></td>
                            <td class="px-2 py-2 text-right whitespace-nowrap"><span data-copay>{{ number_format((float) $item->copayment_amount, 2) }}</span></td>
                            <td class="px-2 py-2 text-right whitespace-nowrap"><span data-payable>{{ number_format((float) $item->amount_to_be_paid, 2) }}</span></td>
                            <td class="px-2 py-2 min-w-44">
                                @if ($item->is_verified)
                                    <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">Verified</span>
                                    <div class="mt-1 text-[10px] text-gray-500">{{ optional($item->verified_at)->format('d-m-Y H:i') }}</div>
                                @else
                                    <span class="inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Pending</span>
                                @endif
                                @unless ($isReadOnly)
                                    @unless ($item->is_verified)
                                        <button type="submit" formmethod="POST" formaction="{{ route('reports.insurance-summary.verify-item', [$batch, $item]) }}" class="mt-2 block rounded bg-green-600 px-2 py-1 text-xs font-semibold text-white" onclick="return confirm('Have you checked and saved this examination? Save your changes first if you edited this row.');">Verify</button>
                                    @endunless
                                    <button type="submit" formmethod="POST" formaction="{{ route('reports.insurance-summary.delete-item', [$batch, $item]) }}" name="_method" value="DELETE" class="mt-2 block rounded border border-red-300 px-2 py-1 text-xs font-semibold text-red-700" onclick="return confirm('Delete this examination from this summary only? The original system record will remain unchanged.');">Delete from summary</button>
                                @endunless
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 font-semibold text-gray-800">
                    <tr>
                        <td colspan="10" class="px-2 py-3 text-right">TOTAL</td>
                        <td class="px-2 py-3 text-right"><span id="footer-cost">{{ number_format((float) $batch->total_cost, 2) }}</span></td>
                        <td class="px-2 py-3 text-right"><span id="footer-total">{{ number_format((float) $batch->total_cost, 2) }}</span></td>
                        <td class="px-2 py-3 text-right"><span id="footer-copay">{{ number_format((float) $batch->copayment_total, 2) }}</span></td>
                        <td class="px-2 py-3 text-right"><span id="footer-payable">{{ number_format((float) $batch->amount_to_be_paid_total, 2) }}</span></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-5 flex flex-col sm:flex-row sm:items-center gap-3 sm:justify-between">
            <a href="{{ route('reports.insurance-summary.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 underline">Back to summaries</a>
            @unless ($isReadOnly)
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" formaction="{{ route('reports.insurance-summary.save-draft', $batch) }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 hover:bg-gray-50">{{ $isFinalized ? 'Save Summary Changes' : 'Save Draft Changes' }}</button>
                    @unless ($isFinalized)
                        <button type="submit" formaction="{{ route('reports.insurance-summary.finalize-download', $batch) }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700" onclick="return confirm('Finalize this insurance summary and download the Excel file? It will remain editable until reconciliation.');">Finalize &amp; Download Excel</button>
                    @endunless
                </div>
            @endunless
        </div>
    </form>
</div>

<script>
(function () {
    const money = (value) => Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const rows = Array.from(document.querySelectorAll('[data-review-row]'));

    function recalculate() {
        let costTotal = 0;
        let totalTotal = 0;
        let copayTotal = 0;
        let payableTotal = 0;

        rows.forEach((row) => {
            const input = row.querySelector('[data-cost-input]');
            const cost = Math.max(0, Number(input ? input.value : 0) || 0);
            const total = Math.round((cost + Number.EPSILON) * 100) / 100;
            const copay = Math.round((total * 0.10 + Number.EPSILON) * 100) / 100;
            const payable = Math.round((total * 0.90 + Number.EPSILON) * 100) / 100;
            row.querySelector('[data-total]').textContent = money(total);
            row.querySelector('[data-copay]').textContent = money(copay);
            row.querySelector('[data-payable]').textContent = money(payable);
            costTotal += cost;
            totalTotal += total;
            copayTotal += copay;
            payableTotal += payable;
        });

        document.getElementById('footer-cost').textContent = money(costTotal);
        document.getElementById('footer-total').textContent = money(totalTotal);
        document.getElementById('footer-copay').textContent = money(copayTotal);
        document.getElementById('footer-payable').textContent = money(payableTotal);
        document.getElementById('summary-total').textContent = money(totalTotal);
        document.getElementById('summary-payable').textContent = money(payableTotal);
    }

    document.querySelectorAll('[data-cost-input]').forEach((input) => input.addEventListener('input', recalculate));
    recalculate();
})();
</script>

</x-report-layout>
