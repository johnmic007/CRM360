<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Visit Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .details {
            margin-bottom: 20px;
        }

        .details .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .details .row span {
            display: block;
            width: 48%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f4f4f4;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
        }

        .photos {
            margin-top: 20px;
        }

        .photos img {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
        }

        .photos .photo-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Trainer Visit Report</h1>
</div>

<div class="details">
    <div class="row">
        <span><strong>Trainer Name:</strong> {{ $trainerVisit->user->name }}</span>
        <span><strong>Visit Date:</strong> {{ $trainerVisit->visit_date }}</span>
    </div>
    <div class="row">
        <span><strong>Company:</strong> {{ $trainerVisit->company_id }}</span>
    </div>
    <div class="row">
        <span><strong>Travel Mode:</strong> {{ ucfirst($trainerVisit->travel_mode) }}</span>
        <span><strong>Distance Traveled:</strong> {{ $trainerVisit->distance_traveled }} km</span>
    </div>
    <div class="row">
        <span><strong>Starting KM:</strong> {{ $trainerVisit->starting_km }}</span>
        <span><strong>Ending KM:</strong> {{ $trainerVisit->ending_km }}</span>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Expense Type</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Travel Expense</td>
            <td>{{ $trainerVisit->travel_expense }}</td>
        </tr>
        <tr>
            <td>Food Expense</td>
            <td>{{ $trainerVisit->food_expense }}</td>
        </tr>
        <tr>
            <td>Total Expense</td>
            <td>{{ $trainerVisit->total_expense }}</td>
        </tr>
    </tbody>
</table>

<div class="photos">
    @php
        // Helper function to convert image to base64
        function getBase64Image($path) {
            $imagePath = public_path('storage/' . $path);
            return file_exists($imagePath) ? base64_encode(file_get_contents($imagePath)) : null;
        }
    @endphp

    <div class="photo">
        <p class="photo-title">Starting Meter Photo:</p>
        @php
            $base64Image = getBase64Image($trainerVisit->starting_meter_photo);
        @endphp
        @if($base64Image)
            <img src="data:image/webp;base64,{{ $base64Image }}" alt="Starting Meter Photo">
        @else
            <p>Image not available</p>
        @endif
    </div>

    <div class="photo">
        <p class="photo-title">Ending Meter Photo:</p>
        @php
            $base64Image = getBase64Image($trainerVisit->ending_meter_photo);
        @endphp
        @if($base64Image)
            <img src="data:image/webp;base64,{{ $base64Image }}" alt="Ending Meter Photo">
        @else
            <p>Image not available</p>
        @endif
    </div>

    <div class="photo">
        <p class="photo-title">GPS Photo:</p>
        @php
            $base64Image = getBase64Image($trainerVisit->gps_photo);
        @endphp
        @if($base64Image)
            <img src="data:image/webp;base64,{{ $base64Image }}" alt="GPS Photo">
        @else
            <p>Image not available</p>
        @endif
    </div>

    <div class="photo">
        <p class="photo-title">Travel Bill:</p>
        @php
            $base64Image = getBase64Image($trainerVisit->travel_bill);
        @endphp
        @if($base64Image)
            <img src="data:image/webp;base64,{{ $base64Image }}" alt="Travel Bill">
        @else
            <p>Image not available</p>
        @endif
    </div>
</div>



</div>



<div class="footer">
    <p><strong>Approval Status:</strong> {{ ucfirst($trainerVisit->approval_status) }}</p>
    <p><strong>Approved By:</strong> {{ $trainerVisit->approved_by ?? 'N/A' }}</p>
</div>

</body>
</html>
