<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .invoice-container {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            position: relative;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 28px;
            margin: 0;
            font-weight: bold;
            color: #333;
        }

        .logo {
            max-width: 120px;
            height: auto;
        }

        .date {
            font-size: 14px;
            color: #666;
            text-align: right;
        }

        .details-section {
            display: flex;
            flex-direction: row;
            margin-bottom: 20px;
        }

        .details-box {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .details-box h3 {
            font-size: 16px;
            margin-bottom: 10px;
            color: #333;
        }

        .details-box p {
            font-size: 14px;
            margin: 5px 0;
            color: #555;
        }

        .invoice-summary {
            margin-bottom: 30px;
        }

        .invoice-summary h3 {
            font-size: 16px;
            color: #333;
        }

        .invoice-summary .total {
            font-size: 24px;
            font-weight: bold;
            color: #e63946;
            text-align: right;
        }

        .items {
            margin-bottom: 30px;
        }

        .items table {
            width: 100%;
            border-collapse: collapse;
        }

        .items th,
        .items td {
            text-align: left;
            padding: 10px;
        }

        .items th {
            background: #f8f8f8;
            font-size: 14px;
            color: #333;
        }

        .items td {
            font-size: 14px;
            color: #555;
            border-bottom: 1px solid #eee;
        }

        .items .total-row td {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #ddd;
        }

        .summary {
            margin-top: 30px;
            background: #f8f8f8;
            padding: 15px;
            border-radius: 8px;
        }

        .summary h3 {
            font-size: 16px;
            color: #333;
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
        }

        .summary .total {
            font-size: 20px;
            font-weight: bold;
            color: #e63946;
        }

        .footer {
            text-align: center;
            font-size: 12px;
            color: #888;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="invoice-container">

    <div class="header">
            <h1>Invoice</h1>
            <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>

        </div>

        <!-- Header -->
        <div class="header">
        <img src="{{ asset('storage/logo.png') }}" alt="Logo" class="logo">
            <div class="date">
                <p><strong>Date:</strong> {{ now()->format('F d, Y') }}</p>
            </div>
        </div>

        <!-- Details Section -->
        <div class="details-section">
            <!-- School Details -->
            <div class="details-box">
                <h3>School Information</h3>
                <p><strong>Name:</strong> {{ $invoice->school->name ?? 'N/A' }}</p>
                <p><strong>Address:</strong> {{ $invoice->school->address ?? 'N/A' }}</p>
                <p><strong>Email:</strong> {{ $invoice->school->email ?? 'N/A' }}</p>
            </div>

            <!-- Company Details -->
            <div class="details-box">
                <h3>Company Information</h3>
                <p><strong>Name:</strong> {{ $invoice->company->name ?? 'N/A' }}</p>
                <p><strong>Address:</strong> {{ $invoice->company->address ?? 'N/A' }}</p>
                <p><strong>Email:</strong> {{ $invoice->company->email ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Invoice Summary -->
        <div class="invoice-summary">
            <h3>Invoice Details</h3>
            <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
            <p><strong>Issue Date:</strong> {{ $invoice->issue_date }}</p>
            <p><strong>Due Date:</strong> {{ $invoice->due_date }}</p>
        </div>

        <!-- Items Table -->
        <div class="items">
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Cost</th>
                        <th>Qty</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $item)
                    <tr>
                        <td>{{ $item->item_name }}</td>
                        <td>${{ number_format($item->price, 2) }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ number_format($item->total, 2) }}</td>
                    </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="3">Total</td>
                        <td>${{ number_format($invoice->total_amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <div class="summary">
            <h3>
                <span>Paid Amount:</span>
                <span>$ {{ number_format($invoice->paid, 2) }}</span>
            </h3>
            <h3>
                <span>Balance Amount:</span>
                <span>$ {{ number_format($invoice->total_amount - $invoice->paid, 2) }}</span>
            </h3>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
        </div>
    </div>
</body>

</html>
