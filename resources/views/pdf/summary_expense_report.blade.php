<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary Expense Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="title">Summary Expense Report</div>
    <table>
        <thead>
            <tr>
                <th>User Name</th>
                <th>Cash in Hand</th>
                <th>Total Requests</th>
                <th>Total Expense</th>
                <th>Total Travel Expense</th>
                <th>Total Food Expense</th>
                <th>Total Extra Expense</th>
                <th>Verified Expense</th>
                <th>Approved Expense</th>
                <th>Average Expense</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ e($row['User Name']) }}</td>
                    <td>{{ e($row['Cash in Hand']) }}</td>
                    <td>{{ e($row['Total Requests']) }}</td>
                    <td>{{ e($row['Total Expense']) }}</td>
                    <td>{{ e($row['Total Travel Expense']) }}</td>
                    <td>{{ e($row['Total Food Expense']) }}</td>
                    <td>{{ e($row['Total Extra Expense']) }}</td>
                    <td>{{ e($row['Verified Expense']) }}</td>
                    <td>{{ e($row['Approved Expense']) }}</td>
                    <td>{{ e($row['Average Expense']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
