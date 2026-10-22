<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($reportData) ? ($reportData['period_label'] ?? 'Report') : 'Insurance Report' }} - Print</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1f2937;
            background: white;
            line-height: 1.6;
        }

        .page {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 20mm;
            background: white;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: avoid;
        }

        .header {
            border-bottom: 3px solid #1f2937;
            margin-bottom: 30px;
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 12px;
            color: #6b7280;
        }

        .print-date {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 5px;
        }

        .summary-section {
            margin-bottom: 30px;
        }

        .summary-title {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .card {
            border: 1px solid #e5e7eb;
            padding: 15px;
            border-radius: 6px;
            background: #f9fafb;
            page-break-inside: avoid;
        }

        .card-label {
            font-size: 11px;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }

        .card-value {
            font-size: 20px;
            font-weight: bold;
            color: #111827;
        }

        .card-subtext {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 5px;
        }

        .card.patient {
            border-left: 4px solid #b45309;
        }

        .card.insurance {
            border-left: 4px solid #15803d;
        }

        .card.total {
            border-left: 4px solid #1e40af;
        }

        .table-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }

        .table-title {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #111827;
        }

        td {
            border: 1px solid #d1d5db;
            padding: 9px 10px;
            color: #1f2937;
        }

        tr:nth-child(even) {
            background: #f9fafb;
        }

        .text-right {
            text-align: right;
        }

        .total-row {
            background: #f3f4f6;
            font-weight: 600;
            border-top: 2px solid #111827;
            border-bottom: 2px solid #111827;
        }

        .total-row td {
            padding: 12px 10px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
            font-size: 12px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
        }

        .grand-totals {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .grand-total-card {
            text-align: center;
            padding: 15px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            background: #f9fafb;
        }

        .grand-total-label {
            font-size: 11px;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 0.3px;
        }

        .grand-total-value {
            font-size: 22px;
            font-weight: bold;
            color: #111827;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .page {
                margin: 0;
                padding: 20mm;
                page-break-after: always;
                box-shadow: none;
            }

            a {
                color: #1f2937;
                text-decoration: none;
            }
        }

        @page {
            size: A4;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="page">
        {{-- Header --}}
        <div class="header">
            @if(isset($reportData))
                <h1>{{ $reportData['period_label'] ?? 'Report' }}</h1>
                <p>Detailed Revenue Breakdown - Patient Share & Insurance</p>
            @else
                <h1>Insurance Reports & Analytics</h1>
                <p>Comprehensive Revenue Analysis by Insurance Provider</p>
            @endif
            <p class="print-date">Generated: {{ now()->format('F d, Y \a\t H:i') }}</p>
        </div>

        {{-- Summary Cards --}}
        <div class="summary-section">
            <div class="summary-title">Revenue Summary</div>
            <div class="summary-cards">
                @if(isset($reportData))
                    {{-- Custom/Period Report Summary --}}
                    <div class="card patient">
                        <div class="card-label">Patient Share</div>
                        <div class="card-value">Rwf {{ number_format($reportData['patient_share'] ?? 0) }}</div>
                        <div class="card-subtext">Amount Received from Patients</div>
                    </div>
                    <div class="card insurance">
                        <div class="card-label">Insurance Share</div>
                        <div class="card-value">Rwf {{ number_format($reportData['insurance_share'] ?? 0) }}</div>
                        <div class="card-subtext">Amount Received from Insurance</div>
                    </div>
                    <div class="card total">
                        <div class="card-label">Total Revenue</div>
                        <div class="card-value">Rwf {{ number_format(($reportData['patient_share'] ?? 0) + ($reportData['insurance_share'] ?? 0)) }}</div>
                        <div class="card-subtext">Combined Income</div>
                    </div>
                @else
                    {{-- Index Report Summary --}}
                    <div class="card total">
                        <div class="card-label">Total Revenue</div>
                        <div class="card-value">Rwf {{ number_format($summaryCards['total_revenue'] ?? 0) }}</div>
                        <div class="card-subtext">All MRI Activities</div>
                    </div>
                    <div class="card patient">
                        <div class="card-label">Patient Expected</div>
                        <div class="card-value">Rwf {{ number_format($summaryCards['cash_expected'] ?? 0) }}</div>
                        <div class="card-subtext">Expected to Collect</div>
                    </div>
                    <div class="card insurance">
                        <div class="card-label">Insurance Payable</div>
                        <div class="card-value">Rwf {{ number_format($summaryCards['insurance_payable'] ?? 0) }}</div>
                        <div class="card-subtext">Should Be Paid</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Detailed Table --}}
        <div class="table-section">
            @if(isset($reportData))
                {{-- Custom/Period Report Table --}}
                <div class="table-title">Treatment Records</div>
                @if(count($reportData['details'] ?? []) > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>Treatment Date</th>
                                <th>Patient Name</th>
                                <th class="text-right">Patient Share</th>
                                <th class="text-right">Insurance Share</th>
                                <th class="text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['details'] ?? [] as $detail)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($detail['treatment_date'])->format('M d, Y H:i') }}</td>
                                    <td>{{ $detail['patient_name'] ?? 'N/A' }}</td>
                                    <td class="text-right">Rwf {{ number_format($detail['patient_share'] ?? 0) }}</td>
                                    <td class="text-right">Rwf {{ number_format($detail['insurance_share'] ?? 0) }}</td>
                                    <td class="text-right">Rwf {{ number_format(($detail['patient_share'] ?? 0) + ($detail['insurance_share'] ?? 0)) }}</td>
                                </tr>
                            @endforeach
                            <tr class="total-row">
                                <td colspan="2"><strong>TOTAL</strong></td>
                                <td class="text-right"><strong>Rwf {{ number_format($reportData['patient_share'] ?? 0) }}</strong></td>
                                <td class="text-right"><strong>Rwf {{ number_format($reportData['insurance_share'] ?? 0) }}</strong></td>
                                <td class="text-right"><strong>Rwf {{ number_format(($reportData['patient_share'] ?? 0) + ($reportData['insurance_share'] ?? 0)) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        No treatment records found for the selected period.
                    </div>
                @endif
            @else
                {{-- Index Report Table (Insurance Breakdown) --}}
                <div class="table-title">Insurance Provider Breakdown</div>
                @if(count($insuranceRows ?? []) > 0)
                    <table>
                        <thead>
                            <tr>
                                <th>Insurance Provider</th>
                                <th class="text-right">Patient Paid</th>
                                <th class="text-right">Insurance  Paid</th>
                                <th class="text-right">Private Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($insuranceRows ?? [] as $row)
                                <tr>
                                    <td>{{ $row['provider'] ?? 'N/A' }}</td>
                                    <td class="text-right">Rwf {{ number_format($row['payable'] ?? 0) }}</td>
                                    <td class="text-right">Rwf {{ number_format($row['paid'] ?? 0) }}</td>
                                    <td class="text-right">Rwf {{ number_format($row['private'] ?? 0) }}</td>
                                </tr>
                            @endforeach
                            <tr class="total-row">
                                <td><strong>TOTAL</strong></td>
                                <td class="text-right"><strong>Rwf {{ number_format($summaryCards['insurance_payable'] ?? 0) }}</strong></td>
                                <td class="text-right"><strong>Rwf {{ number_format(($summaryCards['insurance_payable'] ?? 0) - ($summaryCards['insurance_outstanding'] ?? 0)) }}</strong></td>
                                <td class="text-right"><strong>Rwf {{ number_format($summaryCards['insurance_outstanding'] ?? 0) }}</strong></td>
                                <td class="text-right"><strong>Rwf {{ number_format($summaryCards['private_revenue'] ?? 0) }}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">
                        No insurance records found for the selected period.
                    </div>
                @endif
            @endif
        </div>

        {{-- Grand Totals --}}
        @if(isset($reportData))
            @if(count($reportData['details'] ?? []) > 0)
                <div class="grand-totals">
                    <div class="grand-total-card">
                        <div class="grand-total-label">Patient Share</div>
                        <div class="grand-total-value">Rwf {{ number_format($reportData['patient_share'] ?? 0) }}</div>
                    </div>
                    <div class="grand-total-card">
                        <div class="grand-total-label">Insurance Share</div>
                        <div class="grand-total-value">Rwf {{ number_format($reportData['insurance_share'] ?? 0) }}</div>
                    </div>
                    <div class="grand-total-card">
                        <div class="grand-total-label">Grand Total</div>
                        <div class="grand-total-value">Rwf {{ number_format(($reportData['patient_share'] ?? 0) + ($reportData['insurance_share'] ?? 0)) }}</div>
                    </div>
                </div>
            @endif
        @else
            @if(count($insuranceRows ?? []) > 0)
                <div class="grand-totals">
                    <div class="grand-total-card">
                        <div class="grand-total-label">Total Insurance Payable</div>
                        <div class="grand-total-value">Rwf {{ number_format($summaryCards['insurance_payable'] ?? 0) }}</div>
                    </div>
                    <div class="grand-total-card">
                        <div class="grand-total-label">Outstanding</div>
                        <div class="grand-total-value">Rwf {{ number_format($summaryCards['insurance_outstanding'] ?? 0) }}</div>
                    </div>
                    <div class="grand-total-card">
                        <div class="grand-total-label">Grand Total Revenue</div>
                        <div class="grand-total-value">Rwf {{ number_format($summaryCards['total_revenue'] ?? 0) }}</div>
                    </div>
                </div>
            @endif
        @endif

        {{-- Footer --}}
        <div class="footer">
            <p>This is a confidential report generated by the Healthcare Management System.</p>
            <p>Report Date: {{ now()->format('F d, Y') }} | Page <span id="page">1</span></p>
        </div>
    </div>

    <script>
        // Auto-open print dialog
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
