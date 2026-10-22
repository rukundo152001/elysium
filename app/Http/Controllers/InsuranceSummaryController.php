<?php

namespace App\Http\Controllers;

use App\Models\InsuranceSummaryBatch;
use App\Models\InsuranceSummaryItem;
use App\Models\Patients;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class InsuranceSummaryController extends Controller
{
    public function index()
    {
        $batches = InsuranceSummaryBatch::query()
            ->withCount('items')
            ->orderByDesc('period')
            ->orderByDesc('id')
            ->paginate(20);

        return view('reports.insurance-summary', compact('batches'));
    }

    /** Build or refresh a draft. No Excel file is downloaded from this action. */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'period' => ['required', 'date_format:Y-m'],
            'insurance_provider' => ['required', 'string', 'max:191'],
           // 'invoice_number' => ['required', 'integer', 'min:1', 'max:4294967295'],
        ]);

        $period = Carbon::createFromFormat('Y-m', $validated['period'])->startOfMonth();
        $providerName = trim($validated['insurance_provider']);
        $providerKey = $this->providerKey($providerName);

        $alreadyFinalized = InsuranceSummaryBatch::query()
            ->where('period', $period->format('Y-m'))
            ->where('insurance_provider_key', $providerKey)
            ->whereIn('status', ['finalized', 'reconciled'])
            ->exists();

        if ($alreadyFinalized) {
            return redirect()->route('reports.insurance-summary.index')->withErrors([
                'period' => "A final summary already exists for {$providerName} for {$period->format('F Y')}. Open it in the saved summaries below instead of generating a duplicate.",
            ]);
        }

        $examRows = $this->fetchExamRows(
            $period->copy()->startOfMonth()->startOfDay(),
            $period->copy()->endOfMonth()->endOfDay(),
            $providerName
        );

        if ($examRows->isEmpty()) {
            return redirect()->route('reports.insurance-summary.index')->withErrors([
                'period' => "No MRI examination records were found for {$providerName} in {$period->format('F Y')}. Check the insurance provider name and the database adapter described in README.md.",
            ]);
        }

        $batch = DB::transaction(function () use ($period, $providerName, $providerKey, $validated, $examRows) {
            $batch = InsuranceSummaryBatch::query()
                ->where('period', $period->format('Y-m'))
                ->where('insurance_provider_key', $providerKey)
                ->where('status', 'draft')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$batch) {
                $batch = new InsuranceSummaryBatch();
                $batch->period = $period->format('Y-m');
                $batch->insurance_provider_key = $providerKey;
            }

            $batch->insurance_provider_name = $providerName;
           // $batch->invoice_number = (int) $validated['invoice_number'];
            $batch->status = 'draft';
            $batch->generated_by = auth()->id();
            $batch->save();
            InsuranceSummaryItem::query()->where('batch_id', $batch->id)->delete();

            foreach ($examRows->values() as $index => $exam) {
                $batch->items()->create([
                    'n0' => $index + 1,
                    'test_number' => (string) ($exam['_test_number'] ?? ''),
                    'exam_date' => Carbon::parse($exam['exam_date'])->toDateString(),
                    'beneficiary_name' => $exam['beneficiary_name'] ?: 'UNKNOWN',
                    'beneficiary_affiliation_number' => $exam['beneficiary_affiliation_number'] ?: null,
                    'date_of_birth' => !empty($exam['date_of_birth']) ? Carbon::parse($exam['date_of_birth'])->toDateString() : null,
                    'gender' => $exam['gender'] ?: null,
                    'affiliate_name' => $exam['affiliate_name'] ?: null,
                    'family_code' => $exam['family_code'] ?: null,
                    'prescribing_facility' => $exam['prescribing_facility'] ?: null,
                    'imaging_test' => 'MRI examination',
                    'cost' => round((float) $exam['cost'], 2),
                    'total_amount' => round((float) $exam['cost'], 2),
                    'copayment_amount' => round((float) $exam['cost'] * 0.10, 2),
                    'amount_to_be_paid' => round((float) $exam['cost'] * 0.90, 2),
                ]);
            }

            $this->refreshBatchTotals($batch);
            return $batch;
        });

        return redirect()->route('reports.insurance-summary.review', $batch)->with('success', 'Draft generated. Review every examination and amount before finalizing.');
    }

    public function review(InsuranceSummaryBatch $batch)
    {
        // Draft and finalized batches can be opened for editing. Reconciled batches
        // are still viewable, but the Blade page renders them read-only.
        $batch->load('items');
        $isFinalized = $batch->status === 'finalized';
        $isReadOnly = $batch->status === 'reconciled';

        return view('reports.insurance-summary-review', compact('batch', 'isFinalized', 'isReadOnly'));
    }

    /**
     * Save draft OR finalized rows. Finalized is deliberately not a lock: finance staff
     * may correct it until the global bill is uploaded. A reconciled batch is immutable.
     * When a finalized batch is edited, regenerate its stored Excel so the downloaded
     * workbook always reflects the current saved summary.
     */
    public function saveDraft(Request $request, InsuranceSummaryBatch $batch)
    {
        abort_if($batch->status === 'reconciled', 403, 'This summary is reconciled and read-only.');
        $rows = $this->validatedReviewRows($request, $batch);

        DB::transaction(function () use ($batch, $rows) {
            $locked = InsuranceSummaryBatch::query()->whereKey($batch->id)->lockForUpdate()->firstOrFail();
            abort_if($locked->status === 'reconciled', 409, 'This summary became reconciled and is now read-only.');
            $this->persistReviewRows($locked, $rows);
            $this->refreshBatchTotals($locked);
        });

        $batch->refresh();
        if ($batch->status === 'finalized') {
            $this->refreshFinalWorkbook($batch);
        }

        $message = $batch->status === 'finalized'
            ? 'Finalized summary changes saved. The stored Excel workbook has also been refreshed. You may still edit this summary until reconciliation.'
            : 'Draft changes saved. You can continue reviewing it.';

        return redirect()->route('reports.insurance-summary.review', $batch)->with('success', $message);
    }

    /** Save a final snapshot, retain its Excel file, and return the download. */
    public function finalizeAndDownload(Request $request, InsuranceSummaryBatch $batch)
    {
        abort_unless($batch->isDraft(), 403, 'This summary has already been finalized.');
        $rows = $this->validatedReviewRows($request, $batch);

        $unverifiedCount = $batch->items()->where('is_verified', false)->count();
        if ($unverifiedCount > 0) {
            throw ValidationException::withMessages(['verification' => "Verify all examinations before finalizing. {$unverifiedCount} examination(s) remain unverified."]);
        }

        $duplicateFinal = InsuranceSummaryBatch::query()
            ->where('period', $batch->period)
            ->where('insurance_provider_key', $batch->insurance_provider_key)
            ->whereIn('status', ['finalized', 'reconciled'])
            ->where('id', '<>', $batch->id)
            ->exists();

        if ($duplicateFinal) {
            throw ValidationException::withMessages(['period' => 'A final summary already exists for this period and insurer. Open that saved summary instead.']);
        }

        $filename = 'Insurance_Summary_' . Str::slug($batch->insurance_provider_name) . '_' . str_replace('-', '_', $batch->period) . '.xlsx';
        $tempDirectory = storage_path('app/tmp');
        if (!is_dir($tempDirectory)) {
            mkdir($tempDirectory, 0775, true);
        }
        $tempPath = $tempDirectory . DIRECTORY_SEPARATOR . Str::uuid() . '.xlsx';
        $finalStoragePath = 'insurance-summaries/' . $batch->id . '/' . $filename;

        $this->writeWorkbook($batch, $rows, $tempPath);
        $contents = file_get_contents($tempPath);
        if ($contents === false || !Storage::disk('local')->put($finalStoragePath, $contents)) {
            @unlink($tempPath);
            throw new \RuntimeException('The final Excel workbook could not be saved to private storage. Check storage permissions.');
        }
        @unlink($tempPath);

        try {
            DB::transaction(function () use ($batch, $rows, $filename, $finalStoragePath) {
                $locked = InsuranceSummaryBatch::query()->whereKey($batch->id)->lockForUpdate()->firstOrFail();
                abort_unless($locked->isDraft(), 409, 'This summary has already been finalized.');

                $duplicate = InsuranceSummaryBatch::query()
                    ->where('period', $locked->period)
                    ->where('insurance_provider_key', $locked->insurance_provider_key)
                    ->whereIn('status', ['finalized', 'reconciled'])
                    ->where('id', '<>', $locked->id)
                    ->exists();
                if ($duplicate) {
                    throw ValidationException::withMessages(['period' => 'A final summary already exists for this period and insurer.']);
                }

                $this->persistReviewRows($locked, $rows);
                $this->refreshBatchTotals($locked);
                $locked->status = 'finalized';
                $locked->finalized_by = auth()->id();
                $locked->finalized_at = now();
                $locked->final_file_path = $finalStoragePath;
                $locked->final_file_name = $filename;
                $locked->save();
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($finalStoragePath);
            throw $exception;
        }

        return Storage::disk('local')->download($finalStoragePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /** Mark one examination as verified in a draft or finalized-but-not-reconciled summary. */
    public function verifyItem(InsuranceSummaryBatch $batch, InsuranceSummaryItem $item)
    {
        // Reconciliation is the only status that permanently disables actions.
        abort_if($batch->status === 'reconciled', 403, 'This summary is reconciled and read-only.');
        abort_unless((int) $item->batch_id === (int) $batch->id, 404);

        $item->is_verified = true;
        $item->verified_by = auth()->id();
        $item->verified_at = now();
        $item->save();

        return redirect()->route('reports.insurance-summary.review', $batch)
            ->with('success', "Examination {$item->n0} marked as verified. You can continue later.");
    }

    /**
     * Remove one row from a draft or finalized-but-not-reconciled summary only.
     * This deletes the summary snapshot row, never the original patient/exam record.
     */
    public function deleteItem(InsuranceSummaryBatch $batch, InsuranceSummaryItem $item)
    {
        abort_if($batch->status === 'reconciled', 403, 'This summary is reconciled and read-only.');
        abort_unless((int) $item->batch_id === (int) $batch->id, 404);

        $wasFinalized = $batch->status === 'finalized';
        DB::transaction(function () use ($batch, $item) {
            $locked = InsuranceSummaryBatch::query()->whereKey($batch->id)->lockForUpdate()->firstOrFail();
            abort_if($locked->status === 'reconciled', 409, 'This summary became reconciled and is now read-only.');
            $lockedItem = $locked->items()->whereKey($item->id)->firstOrFail();
            $lockedItem->delete();

            // N0 is a display sequence for rows in this summary. Re-number only the
            // snapshot items; original MRI test numbers and original records are untouched.
            $remaining = $locked->items()->orderBy('test_number')->orderBy('id')->get();
            foreach ($remaining as $index => $remainingItem) {
                $remainingItem->n0 = $index + 1;
                $remainingItem->save();
            }
            $this->refreshBatchTotals($locked);
        });

        $batch->refresh();
        if ($wasFinalized) {
            $this->refreshFinalWorkbook($batch);
        }

        return redirect()->route('reports.insurance-summary.review', $batch)
            ->with('success', 'Examination removed from this summary only. N0 numbers and totals were recalculated.' . ($wasFinalized ? ' The saved Excel workbook was refreshed.' : ''));
    }

    public function downloadFinalized(InsuranceSummaryBatch $batch)
    {
        abort_unless($batch->isFinalized() && $batch->final_file_path, 404, 'No finalized Excel file is attached to this summary.');
        abort_unless(Storage::disk('local')->exists($batch->final_file_path), 404, 'The saved Excel file could not be found.');

        return Storage::disk('local')->download(
            $batch->final_file_path,
            $batch->final_file_name ?: basename($batch->final_file_path),
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    /** Store the reconciled global bill privately and mark this batch reconciled. */
    public function uploadGlobalBill(Request $request, InsuranceSummaryBatch $batch)
    {
        // A reconciled batch represents a completed claim/payment cycle and is immutable.
        // Do not allow replacing the global bill or changing its reconciliation metadata.
        abort_unless($batch->status === 'finalized', 403, $batch->status === 'reconciled'
            ? 'This summary is already reconciled and locked. It cannot be modified or have its global bill replaced.'
            : 'Finalize the insurance summary before uploading the reconciled global bill.');

        $validated = $request->validate([
            'global_bill' => ['required', 'file', 'mimes:pdf,xlsx,xls,csv', 'max:20480'],
        ], [
            'global_bill.mimes' => 'Upload the global bill as PDF, XLSX, XLS, or CSV.',
            'global_bill.max' => 'The global bill must not exceed 20 MB.',
        ]);

        $file = $validated['global_bill'];
        $extension = strtolower($file->getClientOriginalExtension() ?: 'dat');
        $newPath = $file->storeAs('insurance-global-bills/' . $batch->id, Str::uuid() . '.' . $extension, 'local');
        if (!$newPath) {
            throw new \RuntimeException('The global bill could not be saved. Check storage permissions.');
        }

        $oldPath = $batch->global_bill_path;
        $batch->global_bill_path = $newPath;
        $batch->global_bill_original_name = Str::limit(basename($file->getClientOriginalName()), 250, '');
        $batch->global_bill_uploaded_at = now();
        $batch->reconciled_by = auth()->id();
        $batch->reconciled_at = now();
        $batch->status = 'reconciled';
        $batch->save();

        if ($oldPath && $oldPath !== $newPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return redirect()->route('reports.insurance-summary.index')->with('success', 'Global bill uploaded and linked to the finalized insurance period. The summary is now marked as reconciled.');
    }

    public function downloadGlobalBill(InsuranceSummaryBatch $batch)
    {
        abort_unless($batch->global_bill_path, 404, 'No global bill has been uploaded for this summary.');
        abort_unless(Storage::disk('local')->exists($batch->global_bill_path), 404, 'The stored global bill could not be found.');

        return Storage::disk('local')->download(
            $batch->global_bill_path,
            $batch->global_bill_original_name ?: basename($batch->global_bill_path)
        );
    }

    private function validatedReviewRows(Request $request, InsuranceSummaryBatch $batch): Collection
    {
        $currentItems = $batch->items()->get()->keyBy(fn (InsuranceSummaryItem $item) => (string) $item->id);
        $rules = ['items' => ['required', 'array', 'min:1']];

        foreach ($currentItems as $id => $item) {
            $base = "items.{$id}";
            $rules["{$base}.id"] = ['required', 'integer'];
            $rules["{$base}.exam_date"] = ['required', 'date'];
            $rules["{$base}.beneficiary_name"] = ['required', 'string', 'max:191'];
            $rules["{$base}.beneficiary_affiliation_number"] = ['nullable', 'string', 'max:191'];
            $rules["{$base}.date_of_birth"] = ['nullable', 'date', 'before_or_equal:today'];
            $rules["{$base}.gender"] = ['nullable', 'string', 'max:30'];
            $rules["{$base}.affiliate_name"] = ['nullable', 'string', 'max:191'];
            $rules["{$base}.family_code"] = ['nullable', 'string', 'max:191'];
            $rules["{$base}.prescribing_facility"] = ['nullable', 'string', 'max:191'];
            $rules["{$base}.cost"] = ['required', 'numeric', 'min:0', 'max:999999999999'];
        }

        $validated = $request->validate($rules);
        $submittedItems = $validated['items'];
        $expected = $currentItems->keys()->map(fn ($key) => (string) $key)->sort()->values()->all();
        $received = collect(array_keys($submittedItems))->map(fn ($key) => (string) $key)->sort()->values()->all();

        if ($expected !== $received) {
            throw ValidationException::withMessages(['items' => 'The reviewed rows do not match this draft. Refresh the page and try again.']);
        }

        return $currentItems->map(function (InsuranceSummaryItem $item, $id) use ($submittedItems) {
            $row = $submittedItems[$id];
            if ((int) $row['id'] !== (int) $item->id) {
                throw ValidationException::withMessages(['items' => 'A row identifier did not match the current draft.']);
            }

            return [
                'id' => $item->id,
                'n0' => $item->n0,
                'test_number' => $item->test_number,
                'exam_date' => Carbon::parse($row['exam_date'])->toDateString(),
                'beneficiary_name' => trim($row['beneficiary_name']),
                'beneficiary_affiliation_number' => $this->nullableTrim($row['beneficiary_affiliation_number'] ?? null),
                'date_of_birth' => !empty($row['date_of_birth']) ? Carbon::parse($row['date_of_birth'])->toDateString() : null,
                'gender' => $this->nullableTrim($row['gender'] ?? null),
                'affiliate_name' => $this->nullableTrim($row['affiliate_name'] ?? null),
                'family_code' => $this->nullableTrim($row['family_code'] ?? null),
                'prescribing_facility' => $this->nullableTrim($row['prescribing_facility'] ?? null),
                'imaging_test' => 'MRI examination',
                'cost' => round((float) $row['cost'], 2),
            ];
        })->values();
    }

    private function persistReviewRows(InsuranceSummaryBatch $batch, Collection $rows): void
    {
        foreach ($rows as $row) {
            $item = $batch->items()->whereKey($row['id'])->firstOrFail();
            $cost = round((float) $row['cost'], 2);
            $item->fill([
                'exam_date' => $row['exam_date'],
                'beneficiary_name' => $row['beneficiary_name'],
                'beneficiary_affiliation_number' => $row['beneficiary_affiliation_number'],
                'date_of_birth' => $row['date_of_birth'],
                'gender' => $row['gender'],
                'affiliate_name' => $row['affiliate_name'],
                'family_code' => $row['family_code'],
                'prescribing_facility' => $row['prescribing_facility'],
                'imaging_test' => 'MRI examination',
                'cost' => $cost,
                'total_amount' => $cost,
                'copayment_amount' => round($cost * 0.10, 2),
                'amount_to_be_paid' => round($cost * 0.90, 2),
            ])->save();
        }
    }

    private function refreshBatchTotals(InsuranceSummaryBatch $batch): void
    {
        $items = $batch->items()->get();
        $batch->examination_count = $items->count();
        $batch->total_cost = round((float) $items->sum('total_amount'), 2);
        $batch->copayment_total = round((float) $items->sum('copayment_amount'), 2);
        $batch->amount_to_be_paid_total = round((float) $items->sum('amount_to_be_paid'), 2);
        $batch->save();
    }

    /**
     * Rebuild the stored Excel after edits to a finalized (but not reconciled) batch.
     * This prevents the downloadable workbook from becoming stale after staff changes
     * the saved rows. The original Book1.xlsx template is always used as the source.
     */
    private function refreshFinalWorkbook(InsuranceSummaryBatch $batch): void
    {
        abort_unless($batch->status === 'finalized', 403, 'Only a not-yet-reconciled finalized summary can refresh its workbook.');

        $rows = $batch->items()->orderBy('n0')->get()->map(function (InsuranceSummaryItem $item) {
            return [
                'id' => $item->id,
                'n0' => $item->n0,
                'test_number' => $item->test_number,
                'exam_date' => $item->exam_date,
                'beneficiary_name' => $item->beneficiary_name,
                'beneficiary_affiliation_number' => $item->beneficiary_affiliation_number,
                'date_of_birth' => $item->date_of_birth,
                'gender' => $item->gender,
                'affiliate_name' => $item->affiliate_name,
                'family_code' => $item->family_code,
                'prescribing_facility' => $item->prescribing_facility,
                'imaging_test' => $item->imaging_test ?: 'MRI examination',
                'cost' => (float) $item->cost,
            ];
        })->values();

        $tempDirectory = storage_path('app/tmp');
        if (!is_dir($tempDirectory)) {
            mkdir($tempDirectory, 0775, true);
        }
        $tempPath = $tempDirectory . DIRECTORY_SEPARATOR . Str::uuid() . '.xlsx';
        try {
            $this->writeWorkbook($batch, $rows, $tempPath);
            $contents = file_get_contents($tempPath);
            if ($contents === false || !Storage::disk('local')->put($batch->final_file_path, $contents)) {
                throw new \RuntimeException('The updated final Excel workbook could not be saved. Check private storage permissions.');
            }
        } finally {
            if (is_file($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    private function writeWorkbook(InsuranceSummaryBatch $batch, Collection $reviewRows, string $path): void
    {
        $templatePath = storage_path('app/templates/Book1.xlsx');
        abort_unless(file_exists($templatePath), 404, 'The insurance summary Excel template was not found at storage/app/templates/Book1.xlsx.');

        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getSheet(0);
        $period = Carbon::createFromFormat('Y-m', $batch->period)->startOfMonth();

        $sheet->setCellValue('C2', 'ELYSIUM HEALTHCARE RWANDA LTD');
        $sheet->setCellValue('C3', Str::upper($period->translatedFormat('F-Y')));
       // $sheet->setCellValue('C4', (int) $batch->invoice_number);
        $sheet->setCellValue('C5', 'RUBAVU');
        $sheet->setCellValue('C6', 'WESTERN');

        $firstDataRow = 11;
        $templateCapacity = 9;
        $rowCount = $reviewRows->count();
        $extraRows = max(0, $rowCount - $templateCapacity);

        if ($extraRows > 0) {
            $sheet->insertNewRowBefore(20, $extraRows);
            for ($row = 20; $row < 20 + $extraRows; $row++) {
                $sheet->duplicateStyle($sheet->getStyle('A19:N19'), "A{$row}:N{$row}");
                $sheet->getRowDimension($row)->setRowHeight($sheet->getRowDimension(19)->getRowHeight());
            }
        }

        $lastDataRow = $firstDataRow + $rowCount - 1;
        $totalRow = 21 + $extraRows;

        foreach ($reviewRows->values() as $index => $exam) {
            $row = $firstDataRow + $index;
            $cost = round((float) $exam['cost'], 2);

            $sheet->setCellValue("A{$row}", $index + 1); // N0 resets to 1 for this monthly/insurer batch.
            $sheet->setCellValue("B{$row}", ExcelDate::PHPToExcel(Carbon::parse($exam['exam_date'])->startOfDay()));
            $sheet->getStyle("B{$row}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            $sheet->setCellValue("C{$row}", $exam['beneficiary_name']);
            $sheet->setCellValueExplicit("D{$row}", (string) $exam['beneficiary_affiliation_number'], DataType::TYPE_STRING);

            if (!empty($exam['date_of_birth'])) {
                $sheet->setCellValue("E{$row}", ExcelDate::PHPToExcel(Carbon::parse($exam['date_of_birth'])->startOfDay()));
                $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
            } else {
                $sheet->setCellValue("E{$row}", '');
            }

            $sheet->setCellValue("F{$row}", $exam['gender']);
            $sheet->setCellValue("G{$row}", $exam['affiliate_name']);
            $sheet->setCellValueExplicit("H{$row}", (string) $exam['family_code'], DataType::TYPE_STRING);
            $sheet->setCellValue("I{$row}", $exam['prescribing_facility']);
            $sheet->setCellValue("J{$row}", 'MRI examination');
            $sheet->setCellValue("K{$row}", $cost);
            $sheet->setCellValue("L{$row}", "=K{$row}*\$L\$10");
            $sheet->setCellValue("M{$row}", "=L{$row}*\$M\$10");
            $sheet->setCellValue("N{$row}", "=L{$row}*\$N\$10");
            $sheet->getStyle("K{$row}:N{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $sheet->setCellValue("A{$totalRow}", 'TOTAL');
        $sheet->setCellValue("K{$totalRow}", "=SUM(K{$firstDataRow}:K{$lastDataRow})");
        $sheet->setCellValue("L{$totalRow}", "=SUM(L{$firstDataRow}:L{$lastDataRow})");
        $sheet->setCellValue("M{$totalRow}", "=SUM(M{$firstDataRow}:M{$lastDataRow})");
        $sheet->setCellValue("N{$totalRow}", "=SUM(N{$firstDataRow}:N{$lastDataRow})");
        $sheet->getStyle("K{$totalRow}:N{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
        $spreadsheet->getCalculationEngine()->clearCalculationCache();
        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(true);
        $writer->save($path);
        $spreadsheet->disconnectWorksheets();
    }

    /**
     * DATABASE ADAPTER: match the date field and service relation names to this application's schema.
     * It deliberately returns one normalized row per MRI examination, not one row per patient.
     */
    private function fetchExamRows(Carbon $start, Carbon $end, string $selectedProvider): Collection
    {
        $patients = Patients::query()
        ->with([
        'patientActivities.service',   // services inside patient activity
        'insurancePackage',            // insurance % split
        'insuranceProvider',           // private check
        'insuranceCategory',
        'district',              // District check
        'sector',              // Sector check

    ])
            ->whereBetween('treatment_date', [$start, $end])
            ->get();

        $rows = collect();

        foreach ($patients as $patient) {
            $providerName = trim((string) $this->value($patient->insuranceProvider, ['name', 'provider_name', 'insurance_name'], ''));
            if (!$this->providerMatches($providerName, $selectedProvider)) {
                continue;
            }

            $patientDate = $this->value($patient, ['treatment_date', 'exam_date', 'date']);
            if (!$patientDate) {
                continue;
            }

            $patientTestNumber = (string) $this->value($patient, ['test_number', 'test_no', 'ref_number', 'reference_number'], '');
            $patientExamName = Str::lower((string) $this->value($patient, ['test_type', 'service_name', 'exam_type', 'exam_name'], ''));
            $patientClearlyMRI = Str::contains($patientExamName, ['mri', 'magnetic resonance']);

            $mriServices = collect($patient->services ?? [])->filter(function ($service) {
                $serviceName = Str::lower((string) $this->value($service, ['name', 'service_name', 'test_name', 'description'], ''));
                return Str::contains($serviceName, ['mri', 'magnetic resonance']);
            })->values();

            // Treat an exam-level patient/visit row with its own test number as ONE MRI examination.
            // This is essential when the same beneficiary has multiple MRI test numbers on the same date.
            // Only expand service rows if there is no exam-level test number on the patient row.
            if ($patientTestNumber !== '' && ($patientClearlyMRI || $mriServices->isNotEmpty())) {
                $serviceRows = collect([$mriServices->first()]);
            } elseif ($patientClearlyMRI) {
                $serviceRows = collect([null]);
            } elseif ($mriServices->isNotEmpty()) {
                $serviceRows = $mriServices;
            } else {
                continue;
            }

            foreach ($serviceRows as $service) {
                $testNumber = $patientTestNumber !== ''
                    ? $patientTestNumber
                    : (string) $this->value($service, ['test_number', 'test_no', 'number', 'id'],
                        $this->value($patient, ['ref_number', 'reference_number', 'id'], ''));

                // Prefer the amount on the exam-level row when it has its own test number.
                // For expanded service rows, prefer the service/pivot line amount.
                if ($patientTestNumber !== '') {
                    $amount = $this->value($patient, ['total_amount', 'amount', 'cost', 'price'],
                        $this->value($service, ['pivot.amount', 'pivot.price', 'amount', 'cost', 'price', 'total_amount'], 0));
                } else {
                    $amount = $this->value($service, ['pivot.amount', 'pivot.price', 'amount', 'cost', 'price', 'total_amount'],
                        $this->value($patient, ['total_amount', 'amount', 'cost', 'price'], 0));
                }

                $gender = Str::upper((string) $this->value($patient, ['gender', 'sex'], ''));
                $gender = match ($gender) {
                    'FEMALE', 'F' => 'F',
                    'MALE', 'M' => 'M',
                    default => $gender,
                };

                $rows->push([
                    '_test_number' => $testNumber,
                    'exam_date' => Carbon::parse($patientDate)->startOfDay(),
                    'beneficiary_name' => trim((string) $this->value($patient, ['beneficiary_name', 'full_name', 'patient_name'], trim(
                        (string) $this->value($patient, ['first_name', 'firstname'], '') . ' ' .
                        (string) $this->value($patient, ['last_name', 'lastname', 'surname'], '')
                    ))),
                    'beneficiary_affiliation_number' => (string) $this->value($patient, ['affiliation_number', 'insurance_number', 'member_number', 'insurance_card_number'], ''),
                    'date_of_birth' => $this->dateOrNull($this->value($patient, ['date_of_birth', 'dob', 'birth_date'])),
                    'gender' => $gender,
                    'affiliate_name' => (string) $this->value($patient, ['affiliate_name', 'affiliate_full_name', 'principal_member_name', 'policy_holder_name', 'head_of_household_name'], ''),
                    'family_code' => (string) $this->value($patient, ['affiliate_code', 'affiliate_insurance_number', 'principal_member_code', 'family_code', 'affiliate_number'],
                        $this->value($patient, ['insurance_number', 'member_number', 'insurance_card_number'], '')),
                    'prescribing_facility' => (string) $this->value($patient, ['prescribing_facility', 'referring_facility', 'facility_prescribing'], ''),
                    'cost' => (float) $amount,
                ]);
            }
        }

        // Sequential N0 follows natural ascending source test number order, not patient grouping.
        return $rows->sort(function ($a, $b) {
            $testCompare = strnatcasecmp((string) $a['_test_number'], (string) $b['_test_number']);
            return $testCompare !== 0 ? $testCompare : ($a['exam_date'] <=> $b['exam_date']);
        })->values();
    }

    private function providerMatches(string $actualProvider, string $selectedProvider): bool
    {
        if ($actualProvider === '') {
            return false;
        }

        $selectedKey = $this->providerKey($selectedProvider);
        $actualKey = $this->providerKey($actualProvider);

        if ($selectedKey === 'rssb-cbhi') {
            return $actualKey === 'rssb-cbhi';
        }

        return $actualKey === $selectedKey
            || Str::contains(Str::lower($actualProvider), Str::lower(trim($selectedProvider)));
    }

    private function providerKey(string $name): string
    {
        $upper = Str::upper($name);
        if (Str::contains($upper, ['CBHI', 'COMMUNITY BASED HEALTH', 'COMMUNITY-BASED HEALTH', 'RSSB'])) {
            return 'rssb-cbhi';
        }

        return Str::slug($name) ?: 'unknown-insurer';
    }

    private function value($source, array $keys, $default = null)
    {
        if ($source === null) {
            return $default;
        }

        foreach ($keys as $key) {
            $value = data_get($source, $key);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $default;
    }

    private function dateOrNull($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        try {
            return Carbon::parse($value)->startOfDay();
        } catch (Throwable $exception) {
            return null;
        }
    }

    private function nullableTrim($value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value === '' ? null : $value;
    }
}
