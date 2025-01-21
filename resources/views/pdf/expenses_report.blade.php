<!DOCTYPE html>
<html>
<head>
    <title>Expenses Report</title>
    <style>
        /* PDF & Print Friendly Styles */
        @page {
            margin: 20px;
            size: auto;

        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 20px;
            font-size: 14px;
            color: #333;
            position: relative;
        }

        /* Heading / Title */
        .report-title {
            text-align: center;
            margin-bottom: 10px;
            font-size: 22px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .report-subtitle {
            text-align: center;
            margin-bottom: 30px;
            font-size: 16px;
        }

        /* Summary Section */
        .summary-section {
            margin: 20px 0;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .summary-table th {
            background-color: #f2f2f2;
            padding: 8px 12px;
            font-weight: bold;
            border: 1px solid #ccc;
            text-align: left;
        }
        .summary-table td {
            padding: 8px 12px;
            border: 1px solid #ccc;
            vertical-align: middle;
        }

        /* Detailed Records Table */
        table.records-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        table.records-table thead tr {
            background-color: #f2f2f2;
        }
        table.records-table th,
        table.records-table td {
            padding: 8px 10px;
            border: 1px solid #ccc;
        }
        table.records-table th {
            font-weight: bold;
            text-align: left;
        }
        table.records-table td {
            vertical-align: middle;
        }

        /* Chart Section */
        .chart-container {
            text-align: center;
            margin: 40px 0;
        }
        .chart-title {
            font-weight: bold;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .chart-container img {
            max-width: 600px;
        }

        /* Signature Section */
        .signature-section {
    position: fixed;
    bottom: 0;
    width: 100%;
    text-align: center;
    margin-top: 20px;
}
.signature-box {
    display: inline-block;
    width: 160px;
    border-top: 1px solid #000;
    margin: 0 40px;
    text-align: center;
    padding-top: 6px;
    font-weight: bold;
}
        /* Optional: Add a small footer or page number for the PDF */
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

    </style>
</head>
<body>
    <!-- Report Heading -->
    <div class="report-title">Expenses Report</div>
    <div class="report-subtitle">Generated on {{ now()->format('d M Y') }}</div>

    <!-- Summary Section -->
    <div class="summary-section">
        <table class="summary-table">
            <tr>
            <th>Date</th>
                <td>
                    @if($records->isNotEmpty())
                        {{ \Carbon\Carbon::parse($records->first()->visit_date)->format('d M Y') }}
                    @else
                        N/A
                    @endif
                </td>
            </tr>
            <tr>
                <th>Total KM</th>
                <td>{{ number_format($totalKm, 2) }}</td>
            </tr>
            <tr>
                <th>Total Expenses</th>
                <td>{{ number_format($totalExpenses, 2) }}</td>
            </tr>
            <tr>
                <th>Total Wallet Balance</th>
                <td>{{ number_format($totalWalletBalance, 2) }}</td>
            </tr>
            <tr>
                <th>Total Approved Expense</th>
                <td>{{ number_format($totalApprovedExp, 2) }}</td>
            </tr>
            <tr>
                <th>Total Pending Expense</th>
                <td>{{ number_format($totalPendingExp, 2) }}</td>
            </tr>
            <tr>
                <th>Total Request</th>
                <td>{{ $totalRequests }}</td>
            </tr>
            <tr>
                <th>Total Cars</th>
                <td>{{ $totalCars }}</td>
            </tr>
            <tr>
                <th>Total Bikes</th>
                <td>{{ $totalBikes }}</td>
            </tr>
        </table>
    </div>

    <!-- Table of Detailed Records -->
    <table class="records-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Verify Status</th>
                <th>Approval Status</th>
                <th>Travel Mode</th>
                <th>Distance (KM)</th>
                <th>Travel Expense</th>
                <th>Total Expense</th>
                
                <th>Cash in Hand</th>

            </tr>
        </thead>
        <tbody>
            @foreach ($records as $record)
            <tr>
                <td>{{ $record->user->name }}</td>
                <td>{{ ucfirst($record->verify_status) }}</td>
                <td>{{ ucfirst($record->approval_status) }}</td>
                <td>{{ $record->travel_mode }}</td>
                <td>{{ number_format($record->distance_traveled, 2) }}</td>
                <td>{{ number_format($record->travel_expense, 2) }}</td>
                <td>{{ number_format($record->total_expense, 2) }}</td>
                <td>{{ number_format($record->user->wallet_balance, 2) }}</td>

        
            </tr>
            @endforeach
        </tbody>
    </table>


    <!-- Remarks Table Section -->
@if ($records->whereNotNull('remarks')->count() > 0)
    <table class="records-table">
        <thead>
            <tr>
                <th> Name</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records->whereNotNull('remarks') as $record)
                <tr>
                    <td>{{ $record->user->name }}</td>
                    <td>{{ $record->remarks }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif


    <!-- Bar Chart Section -->
    <div class="chart-container">
        <div class="chart-title">Total Expense vs. Approved Expense by Date</div>
        <img
            src="data:image/png;base64,{{ $chartBase64 }}"
            alt="Bar Chart: Total Expense vs. Approved Expense"
        />
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">Sales Ops</div>
        <div class="signature-box">Operations</div>
        <div class="signature-box">Head</div>
    </div>

   
</body>
</html>
