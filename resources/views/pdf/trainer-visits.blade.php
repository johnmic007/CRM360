<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Visit Report</title>

    <!-- Improved CSS Styles -->
    <style>
        /* Global Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            background-color: #f9f9f9;
            color: #333;
            padding: 20px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 10px;
            background-color: #2c3e50;
            color: #fff;
            border-radius: 4px;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 400;
        }

        /* Details Section */
        .details {
            background-color: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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

        .details .row span strong {
            color: #2c3e50;
        }

        /* Table Section */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: #fff;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        table thead {
            background-color: #2c3e50;
            color: #fff;
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
        }

        table th {
            font-weight: 500;
        }

        table tr:nth-child(even) td {
            background-color: #f2f2f2;
        }

        /* Photos Section */
        .photos {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }

        .photo {
            background-color: #fff;
            padding: 10px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .photo-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .photo img {
            max-width: 100%;
            height: auto;
            display: block;
            border-radius: 3px;
        }

        /* Lead Statuses Section */
        .lead-statuses {
            margin-top: 30px;
        }

        .lead-statuses h2 {
            margin-bottom: 10px;
            color: #2c3e50;
            font-size: 20px;
        }

        .status {
            background-color: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .status p {
            margin-bottom: 5px;
        }

        .status img {
            max-width: 200px;
            display: block;
            margin-top: 10px;
            border-radius: 3px;
        }

        .status hr {
            margin: 15px 0;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            text-align: center;
        }

        .footer p {
            margin-bottom: 8px;
        }

        .footer p strong {
            color: #2c3e50;
        }


        /* Lead Statuses Container */
        .lead-statuses {
            margin-top: 30px;
        }

        .lead-statuses h2 {
            margin-bottom: 10px;
            color: #2c3e50;
            font-size: 20px;
        }

        /* Individual Status Card */
        .status {
            background-color: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .status p {
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .status p strong {
            color: #2c3e50;
        }

        .status img {
            display: block;
            margin-top: 10px;
            border-radius: 4px;
            max-width: 200px;
            height: auto;
        }

        /* Horizontal Rule between Statuses */
        .status hr {
            margin: 15px 0;
            border: none;
            border-top: 1px solid #ddd;
        }
    </style>
</head>

<!DOCTYPE html>
<html lang="en">



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
        <div class="photo">
            <p class="photo-title">Starting Meter Photo:</p>
            @php
            $prefixedPath = $trainerVisit->starting_meter_photo ? 'CRM/' . $trainerVisit->starting_meter_photo : null;
            $startingMeterUrl = $prefixedPath && Storage::disk('s3')->exists($prefixedPath)
            ? Storage::disk('s3')->url($prefixedPath)
            : null;
            @endphp
            @if ($startingMeterUrl)
            <p>{{ $startingMeterUrl }}</p>
            <img src="{{ $startingMeterUrl }}" alt="Starting Meter Photo">
            @else
            <p>No Image</p>
            @endif
        </div>

        <div class="photo">
            <p class="photo-title">Ending Meter Photo:</p>
            @php
            $prefixedPath = $trainerVisit->ending_meter_photo ? 'CRM/' . $trainerVisit->ending_meter_photo : null;
            $endingMeterUrl = $prefixedPath && Storage::disk('s3')->exists($prefixedPath)
            ? Storage::disk('s3')->url($prefixedPath)
            : null;
            @endphp
            @if ($endingMeterUrl)
            <p>{{ $endingMeterUrl }}</p>
            <img src="{{ $endingMeterUrl }}" alt="Ending Meter Photo">
            @else
            <p>No Image</p>
            @endif
        </div>

        <div class="photo">
            <p class="photo-title">GPS Photo:</p>
            @if ($trainerVisit->gps_photo)
            @php
            $gpsPhotoUrl = 'CRM/' . $trainerVisit->gps_photo;
            $gpsPhotoFullUrl = Storage::disk('s3')->exists($gpsPhotoUrl)
            ? Storage::disk('s3')->url($gpsPhotoUrl)
            : null;
            @endphp
            @if ($gpsPhotoFullUrl)
            <p>{{ $gpsPhotoFullUrl }}</p>
            <img src="{{ $gpsPhotoFullUrl }}" alt="GPS Photo">
            @else
            <p>No Image</p>
            @endif
            @else
            <p>No Image</p>
            @endif
        </div>

        <div class="photo">
            <p class="photo-title">Travel Bill:</p>
            @if (is_array($trainerVisit->travel_bill) && count($trainerVisit->travel_bill) > 0)
            @foreach ($trainerVisit->travel_bill as $billPath)
            @php
            $prefixedPath = 'CRM/' . $billPath;
            $imageUrl = Storage::disk('s3')->exists($prefixedPath) ? Storage::disk('s3')->url($prefixedPath) : null;
            @endphp
            @if ($imageUrl)
            <p>{{ $imageUrl }}</p>
            <img src="{{ $imageUrl }}" alt="Travel Bill" style="max-width: 200px; max-height: 200px;">
            @else
            <p>No Image</p>
            @endif
            @endforeach
            @else
            <p>No Image</p>
            @endif
        </div>
    </div>


    <div class="lead-statuses">
        <h2>Lead Statuses</h2>
        @foreach ($trainerVisit->leadStatuses as $status)
        <div class="status">
            <p><strong>Status:</strong> {{ ucfirst($status->status) }}</p>
            <p><strong>Remarks:</strong> {{ $status->remarks ?? 'N/A' }}</p>
            <p><strong>Contacted Person:</strong> {{ $status->contacted_person ?? 'N/A' }}</p>
            <p><strong>Designation:</strong> {{ $status->contacted_person_designation ?? 'N/A' }}</p>
            <p><strong>Follow-Up Date:</strong> {{ $status->follow_up_date ?? 'N/A' }}</p>
            <p><strong>Visited Date:</strong> {{ $status->visited_date ?? 'N/A' }}</p>
            <p><strong>Image:</strong></p>
            @if ($status->image)
            @php
            $prefixedPath = 'CRM/' . $status->image;
            $imageUrl = Storage::disk('s3')->exists($prefixedPath)
            ? Storage::disk('s3')->url($prefixedPath)
            : null;
            @endphp
            @if ($imageUrl)
            <p>Generated URL: {{ $imageUrl }}</p>
            <img src="{{ $imageUrl }}" alt="Lead Status Image" style="max-width: 200px;">
            @else
            <p>No Image Available</p>
            @endif
            @else
            <p>No Image</p>
            @endif

        </div>
        <hr>
        @endforeach
    </div>

    <div class="footer">
        <p><strong>Approval Status:</strong> {{ ucfirst($trainerVisit->approval_status) }}</p>
        <p><strong>Approved By:</strong> {{ $trainerVisit->approved_by ?? 'N/A' }}</p>
    </div>
</body>



</html>