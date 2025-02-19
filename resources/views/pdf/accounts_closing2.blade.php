<!DOCTYPE html>
<html>
<head>
    <title>Individual Accounts Closing Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 8px;  }
        th { background-color: #f2f2f2; }
        .section-title { font-size: 14px; font-weight: bold; margin: 20px 0 5px 0; }
    </style>
</head>
<body>

    <h2 style="text-align: center;">Individual Accounts Closing Report</h2>

    @if(isset($closingData) && count($closingData) > 0)
@else
    <p>No associated debits found.</p>
@endif

<table>
    <thead>
        <tr>
            <th>S.No</th>
            <th>Date</th>
            <th>Employee Name - Emp ID</th>
            <th>Kms</th>
            <th>Total TA</th>
            <th>Total DA</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @php
            // Initialize total sum variables
            $totalKms = 0;
            $totalTA = 0;
            $totalDA = 0;
            $grandTotal = 0;
        @endphp

        @foreach ($walletLogs as $index => $log)
            @php
                // Fetch trainer visit details
                $kms = $log->trainerVisit ? $log->trainerVisit->distance_traveled : 0;
                $ta = $log->trainerVisit ? $log->trainerVisit->travel_expense : 0;
                $da = $log->trainerVisit ? $log->trainerVisit->food_expense : 0;
                $total = $log->trainerVisit ? $log->trainerVisit->total_expense : 0;

                // Add to total sums
                $totalKms += $kms;
                $totalTA += $ta;
                $totalDA += $da;
                $grandTotal += $total;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td> <!-- S.No -->
                <td>
                    {{ $log->trainerVisit && $log->trainerVisit->visit_date ?
                        \Carbon\Carbon::parse($log->trainerVisit->visit_date)->format('d/m/Y') : 'N/A' }}
                </td> <!-- Payment Date -->
                <td>{{ $log->user->name ?? 'N/A' }}</td> <!-- Employee Name -->
                <td style="text-align: right">{{ number_format($kms, 2) }}</td> <!-- Kms -->
                <td style="text-align: right">{{ number_format($ta, 2) }}</td> <!-- Travel Expense (TA) -->
                <td style="text-align: right">{{ number_format($da, 2) }}</td> <!-- Food Expense (DA) -->
                <td style="text-align: right">{{ number_format($total, 2) }}</td> <!-- Total -->
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td style="border: none"></td>
            <td style="border: none"></td>
            <td><strong>Total</strong></td>
            <td style="text-align: right"><strong>{{ number_format($totalKms, 2) }}</strong></td> <!-- Sum of all Kms -->
            <td style="text-align: right" style="text-align: right"><strong>{{ number_format($totalTA, 2) }}</strong></td> <!-- Sum of all TA -->
            <td style="text-align: right"><strong>{{ number_format($totalDA, 2) }}</strong></td> <!-- Sum of all DA -->
            <td style="text-align: right"><strong>{{ number_format($grandTotal, 2) }}</strong></td> <!-- Grand Total -->
        </tr>
    </tfoot>
</table>
<table>
    <tr>
        <th style="text-align: left">Advance received</th>
        <td style="text-align: right">{{ $walletLog->amount ?? 'N/A' }}</td>
    </tr>
    <tr>
        <th style="text-align: left">TA</th>
        <td style="text-align: right">{{ number_format($totalTA, 2) }}</td>
    </tr>
    <tr>
        <th style="text-align: left">DA</th>
        <td style="text-align: right">{{ number_format($totalDA, 2) }}</td>
    </tr>
    <tr>
        <th style="text-align: left">Total Expenses Spent</th>
        <td style="text-align: right">{{ number_format($grandTotal, 2) }}</td>
    </tr>
    <tr>
        <th style="text-align: left">Amount Reimbursement</th>
        <td style="text-align: right">
            {{ isset($walletLog->balance) && $walletLog->balance < 0 ? abs($walletLog->balance) : '0' }}
        </td>    </tr>
</table>
</body>
</html>
