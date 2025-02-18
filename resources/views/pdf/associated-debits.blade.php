<!DOCTYPE html>
<html>
<head>
    <title>Associated Debits Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        .section-title { font-size: 14px; font-weight: bold; margin: 20px 0 5px 0; }
    </style>
</head>
<body>

    <h2 style="text-align: center;">Associated Debits Report</h2>

    <div class="section-title">WalletLog Info</div>
    <table>
        <tr>
            <th>WalletLog ID</th>
            <th>User ID</th>
            <th>Amount</th>
            <th>Description</th>
        </tr>
        <tr>
            <td>{{ $walletLog->id }}</td>
            <td>{{ $walletLog->user->name }}</td>
            <td>₹{{ $walletLog->amount }}</td>
            <td>{{ $walletLog->description }}</td>
        </tr>
    </table>

    <div class="section-title">Associated Debits</div>
    <table>
        <tr>
            <th>ID</th>
            <th>Amount</th>
            <th>Description</th>
            <th>Created At</th>
        </tr>
        @foreach ($associatedDebits as $debit)
            <tr>
                <td>{{ $debit->id }}</td>trainerVisits
                <td>₹{{ $debit->trainerVisits }}</td>
                <td>₹{{ $debit->trainerVisits }}</td>
                <td>₹{{ $debit->trainerVisits }}</td>

                <td>₹{{ $debit->amount }}</td>
                <td>{{ $debit->description }}</td>
                <td>{{ $debit->created_at->format('d/m/Y') }}</td>
            </tr>
        @endforeach
    </table>

</body>
</html>
