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

    @if(isset($walletLogs) && $walletLogs->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>School Name</th>
                    <th>Address</th>
                    <th>District</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($walletLogs as $log)
                    <tr>
                        <td>
                            {{ $log->trainerVisit->visit_date ? \Carbon\Carbon::parse($log->trainerVisit->visit_date)->format('d/m/Y') : 'N/A' }}
                        </td>
                        <td>{{ optional($log->trainerVisit->salesLeadStatus->school)->name ?? 'N/A' }}</td>
                        <td>{{ optional($log->trainerVisit->salesLeadStatus->school)->address ?? 'N/A' }}</td>
                        <td>{{ optional($log->trainerVisit->salesLeadStatus->school->district)->name ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No associated debits found.</p>
    @endif

</body>
</html>
