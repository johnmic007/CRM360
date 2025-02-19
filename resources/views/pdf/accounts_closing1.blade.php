<!DOCTYPE html>
<html>
<head>
    <title>Individual Accounts Closing Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 8px; }
        th { background-color: #f2f2f2; }
        .section-title { font-size: 14px; font-weight: bold; margin: 20px 0 5px 0; }
    </style>
</head>
<body>

    <h2 style="text-align: center;">Individual Accounts Closing Report</h2>

    <table>
        <thead>
            <tr>
                <th>S.No</th>
                <th>Date</th>
                <th>Name</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalTA = 0;
                $totalDA = 0;
                $grandTotal = 0;
            @endphp

            @foreach ($walletLogs as $index => $log)
                @php
                    $ta = $log->trainerVisit ? $log->trainerVisit->travel_expense : 0;
                    $da = $log->trainerVisit ? $log->trainerVisit->food_expense : 0;
                    $total = $log->trainerVisit ? $log->trainerVisit->total_expense : 0;

                    $totalTA += $ta;
                $totalDA += $da;
                $grandTotal += $total;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td> <!-- S.No -->
                    <td>
                        {{ $log->trainerVisit && $log->trainerVisit->visit_date ?
                            \Carbon\Carbon::parse($log->trainerVisit->visit_date)->format('d/m/Y') : 'N/A' }}
                    </td> <!-- Date -->
                    <td>{{ $log->user->name ?? 'N/A' }}</td> <!-- Name -->
                    <td style="text-align: right">{{ number_format($total, 2) }}</td> <!-- Total -->
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td style="text-align: right"><strong>{{ number_format($grandTotal, 2) }}</strong></td>
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
            </td>
        </tr>
    </table>

</body>
</html>
