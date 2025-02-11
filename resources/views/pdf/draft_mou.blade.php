<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memorandum of Understanding (MoU)</title>
    @php
    $path = public_path('images/mgc_logo.jpeg');
    $base64 = is_file($path) ? 'data:image/png;base64,' . base64_encode(file_get_contents($path)) : null;
    @endphp



    <style>
        @import url('https://fonts.googleapis.com/css2?family=Noto+Sans&display=swap');

        body {
            font-family: 'DejaVu Sans', 'Arial Unicode MS', sans-serif;
            width: 180mm;
            height: 297mm;
            margin: 0;
            padding-top: 0px;
            background-color: white;
        }

        p {
            font-size: 12px;
        }

        li {
            font-size: 12px;
        }

        h3 {
            background-color: #004b7a;
            text-align: center;
            margin-top: 2%;
            color: white;
            font-size: 16px;
        }

        .text-center {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2%;
            table-layout: fixed;
            word-wrap: break-word;
        }

        th {
            padding: 8px;
            text-align: center;
            border: solid 1px black;
            font-size: 12px;
        }

        td {
            padding: 8px;
            text-align: center;
            border: solid 1px black;
            font-size: 12px;
        }

        .sign {
            width: 100%;
            margin-top: 2%;
            border-collapse: separate;
        }

        .sign th {
            padding: 8px;
            text-align: left;
            border: none;
            color: #004b7a;
            font-size: 14px;
        }

        .sign td {
            padding: 0px;
            text-align: left;
            border: none;
            font-size: 12px;
        }

        .headerimg {
            text-align: center;
            margin-bottom: 2%;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 14px;
            padding: 10px 0;
        }

           /* Watermark for PDF */
           .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.1;
            width: 50%;
            z-index: -1;
        }
    </style>
</head>

<body>
    @if($base64)
    <img src="{{ $base64 }}" class="watermark">
    @endif
    @php
    $path = public_path('images/mgc_logo.jpeg');
    $base64 = is_file($path) ? 'data:image/png;base64,' . base64_encode(file_get_contents($path)) : null;
    @endphp

    <div class="headerimg">
        @if($base64)
        <img src="{{ $base64 }}" alt="Company Logo" style="max-width: 200px;">
        @else
        <p>Image not found</p>
        @endif
    </div>

    <h3>MEMORANDUM OF UNDERSTANDING (MoU)</h3>
    <p style="text-align: left">This Memorandum of Understanding (MoU) is made and entered into on <strong>{{ \Carbon\Carbon::parse($mou->date)->toDateString() }}
        </strong>,<br><span class="text-center" style="text-align: center;">between:</span><br><strong>[Your Organization Name]</strong>, having its registered office at <strong>[Address]</strong>, hereinafter referred to as the "Service Provider".</p>
    <p style="text-align: left"><strong>{{ $mou->school_name }}</strong>, located at <strong>{{ $mou->school_address }}</strong>, hereinafter referred to as the "School".</p>
    <p style="text-align: left">Both parties collectively referred to as the "Parties", agree to the terms and conditions outlined in this MoU for the delivery of educational services.</p>

    <h3>SERVICE DETAILS & COST STRUCTURE</h3>
    <p><b>1. Scope of Services Provided</b></p>
    <p style="text-align: left">The Service Provider shall deliver the following services to the School for the academic year <strong>{{ $mou->academic_year_start->format('Y-m-d') }}
        </strong>:</p>
    <ul>
        <li>{{ $mou->services }}</li>
    </ul>
    <p><b>2. Student Count & Cost Structure</b></p>
    {{-- <table>
        <tr>
            <th>Class</th>
            <th>No. of Students</th>
            <th>Cost Per Student</th>
            <th>Total Cost</th>
        </tr>
        @foreach ($mou->classes as $class)
        <tr>
            <td>{{ $class['class'] }}</td>
    <td>{{ $class['no_of_students'] }}</td>
    <td>&#8377;{{ $class['cost_per_student'] }}</td>
    <td>&#8377;{{ $class['total_cost'] }}</td>
    </tr>
    @endforeach
    </table> --}}
 
    <table>
    <tr>
        <th>Class</th>
        <th>No. of Students</th>
        <th>Cost Per Student (₹)</th>
        <th>Total Cost (₹)</th>
    </tr>
    
    @php
        function classOrdinal($num) {
            return match ($num) {
                1 => '1st',
                2 => '2nd',
                3 => '3rd',
                default => $num . 'th',
            };
        }

        $totalStudents = 0;
        $totalCost = 0;
    @endphp

    @for ($i = 1; $i <= 9; $i++)
        @php
            $classData = collect($mou->classes)->firstWhere('class', $i);
            $students = $classData['no_of_students'] ?? 0;
            $costPerStudent = $classData['cost_per_student'] ?? 0;
            $totalClassCost = $classData['total_cost'] ?? 0;

            // Sum up total students and total cost
            $totalStudents += $students;
            $totalCost += $totalClassCost;
        @endphp

        <tr>
            <td>{{ classOrdinal($i) }}</td>
            <td>{{ $students ?: '---' }}</td>
            <td>{{ $costPerStudent ? number_format($costPerStudent, 2) : '---' }}</td>
            <td>{{ $totalClassCost ? number_format($totalClassCost, 2) : '---' }}</td>
        </tr>
    @endfor

    <tr>
        <th colspan="2">Total</th>
        <th>---</th>
        <td><strong>₹{{ number_format($totalCost, 2) }}</strong></td>
    </tr>
</table>



    <table class="sign">
        <tr>
            <th colspan="2">For and on behalf of Million Genius Coders</th>
            <th colspan="2">For and on behalf of {{ $mou->school_name }}</th>
        </tr>
        <tr>
            <td>Signature:</td>
            <td>_ _ _ _ _ _ _ _ _</td>
            <td>Signature:</td>
            <td>_ _ _ _ _ _ _ _ _</td>
        </tr>
        <tr>
            <td>Name:</td>
            <td>_ _ _ _ _ _ _ _ _</td>
            <td>Name:</td>
            <td>_ _ _ _ _ _ _ _ _</td>
        </tr>
        <tr>
            <td>Designation:</td>
            <td>_ _ _ _ _ _ _ _ _</td>
            <td>Designation:</td>
            <td>_ _ _ _ _ _ _ _ _</td>
        </tr>
        <tr>
            <td>Date:</td>
            <td>_ _ _ _ _ _ _ _ _</td>
            <td>Date:</td>
            <td>_ _ _ _ _ _ _ _ _</td>
        </tr>
    </table>

    <h3>TERMS & CONDITIONS</h3>
    <p><b>3. Payment Terms</b></p>
    <ul>
        <li>The total contract value is &#8377;{{ $mou->total_contract_value }}</li>
        <li>Payment shall be made in three installments as follows:
            <ul>
                <li>Advance Payment: &#8377;{{ $mou->advance_payment }}</li>
                <li>Mid-Term Payment: &#8377;{{ $mou->mid_payment }}</li>
                <li>Final Payment: &#8377;{{ $mou->final_payment }}</li>
            </ul>
        </li>
        <li>Payment shall be made via [Bank Transfer / Cheque / Online Payment] to the Service Provider's designated account.</li>
    </ul>

    <p><b>4. Late Payment Clause</b></p>
    <ul>
        <li>If payment is not made within [X] days from the due date, a penalty of XX% per month will be applicable.</li>
        <li>If non-payment exceeds [X] days, the Service Provider reserves the right to pause or terminate services.</li>
    </ul>

    <p><b>5. Deliverables & Responsibilities</b></p>
    <ul>
        <li>The Service Provider will ensure the quality and timely delivery of all agreed services.</li>
        <li>The School shall provide necessary infrastructure, internet connectivity, and administrative support.</li>
    </ul>

    <p><b>6. Confidentiality & Data Protection</b></p>
    <ul>
        <li>The Service Provider will not share student data with third parties without prior consent.</li>
        <li>Both parties agree to comply with data privacy laws and ensure secure handling of student information.</li>
    </ul>

    <p class="mt-4"><b>7. Term & Termination</b></p>
    <ul class="pl-6 list-disc">
        <li>This MoU shall remain in effect for [1 year / 2 years] from the date of signing.</li>
        <li>Either party may terminate this MoU by providing a [30-day] written notice.</li>
        <li>In the event of termination, the School shall settle all pending payments before disengagement.</li>
    </ul>
    <p class="mt-4"><b>8. Dispute Resolution</b></p>
    <ul class="pl-6 list-disc">
        <li>Any disputes arising under this MoU shall be resolved through mutual discussion and mediation.</li>
        <li>If a resolution is not achieved, disputes will be settled under the jurisdiction of [City, State]
            courts.</li>
    </ul>

    {{-- <h3>SIGNATURES</h3> --}}
    <table class="sign">
        <tr>
            <th colspan="2">For and on behalf of Million Genius Coders</th>
            <th colspan="2">For and on behalf of {{ $mou->school_name }}</th>
        </tr>
        <tr>
            <td style="width: 20%">Signature:</td>
            <td style="width: 30%">_ _ _ _ _ _ _ _ _</td>
            <td style="width: 20%">Signature:</td>
            <td style="width: 30%">_ _ _ _ _ _ _ _ _</td>
        </tr>
        <tr>
            <td style="width: 20%">Name:</td>
            <td style="width: 30%">_ _ _ _ _ _ _ _ _</td>
            <td style="width: 20%">Name:</td>
            <td style="width: 30%">_ _ _ _ _ _ _ _ _</td>
        </tr>
        <tr>
            <td style="width: 20%">Designation:</td>
            <td style="width: 30%">_ _ _ _ _ _ _ _ _</td>
            <td style="width: 20%">Designation:</td>
            <td style="width: 30%">_ _ _ _ _ _ _ _ _</td>
        </tr>
        <tr>
            <td style="width: 20%">Date:</td>
            <td style="width: 30%">_ _ _ _ _ _ _ _ _</td>
            <td style="width: 20%">Date:</td>
            <td style="width: 30%">_ _ _ _ _ _ _ _ _</td>
        </tr>
    </table>

    <div class="footer">
        <p>https://milliongeniuscoders.com | +91 8248826374</p>
    </div>
</body>

</html>