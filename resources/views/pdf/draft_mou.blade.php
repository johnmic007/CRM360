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
        /* @font-face {
            font-family: 'Noto Sans';
            src: url('{{ public_path('fonts/NotoSans-Regular.ttf') }}') format('truetype');
        }*/
        @font-face {
        font-family: 'DejaVu Sans';
        font-style: normal;
        font-weight: normal;
        src: url("{{ public_path('fonts/DejaVuSans.ttf') }}") format('truetype');
    }

        html{
            font-size: 14px;
        }
        @page {
            margin: 20px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            background-color: white;
            margin: 3%;
            padding: 3%;
        }

        h3 {
            text-align: center;
            margin: 5%;
            color: #009dff;
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 5px;
            text-align: center;
            border: solid 1px black;
            /* font-size: 12px; */
        }

        li{
            text-align: justify;
        }
        .sign th {
            /* text-align: left;
            border: none; */
            /* color: #004b7a; */
            /* font-size: 14px; */
        }

        .sign td {
            text-align: left;
            border: none;
            /* font-size: 12px; */
        }

        .headerimg {
            text-align: center;
        }


        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            /* font-size: 14px; */
            padding: 10px 0;
        }
    </style>
</head>

<body>

    <!-- Company Logo -->
    <div class="headerimg">
        @if($base64)
            <img src="{{ $base64 }}" alt="Company Logo" style="max-width: 150px; max-height: 150 px;">
        @else
            <p>Image not found</p>
        @endif
    </div>

    <!-- Document Title -->
     <h3>MEMORANDUM OF UNDERSTANDING (MoU)</h3>

    <p>
        This Agreement is made on <strong>{{ \Carbon\Carbon::parse($mou->date)->toDateString() }}</strong><br><br>
        <strong>BETWEEN</strong><br>
        <ol>
            <li>
                <strong>MILLION GENIUS CODERS</strong>, powered by KGISL Educational Institutions, a company incorporated under the provisions of the Companies Act, 1956, and having its principal place of business at 365, Thudiyalur Rd, Saravanampatti, Coimbatore, Tamil Nadu 641035 (hereinafter referred to as "MGC"), which expression shall, unless repugnant to the context or meaning thereof, be deemed to include its successors and assigns, of the <strong>FIRST PART</strong>;
            </li>

            <p>AND</p>

            <li>
                <strong>{{ $mou->school->name ?? 'School Name' }}</strong>, having its principal place of business at <strong>{{ $mou->school_address ?? 'School Address' }}</strong> (hereinafter referred to as the "CLIENT"), which expression shall, unless repugnant to the context or meaning thereof, be deemed to include its trustees, beneficiaries, members, successors, and assigns, of the <strong> SECOND PART</strong>.
            </li>
        </ol>
        <br>
        <strong>MGC</strong> and <strong>CLIENT</strong> are hereinafter collectively referred to as the <strong>"Parties"</strong> and individually as <strong>a "Party"</strong>.
    </p>
    <br>
    <br>
    <!-- Signature Section -->
    <table class="sign">
        <tr>
            <td colspan="2"><b>For and on behalf of Million Genius Coders</b></td>
            <td colspan="2"><b>For and on behalf of {{ $mou->school->name ?? 'School Name' }}</b></td>
        </tr><br>
        <tr>
            <td><b>Signature:</b></td>
            <td>_________________</td>
            <td><b>Signature:</b></td>
            <td>_________________</td>
        </tr>
        <br>
        <br>
        <tr>
            <td><b>Name:</b></td>
            <td>_________________</td>
            <td><b>Name:</b></td>
            <td>_________________</td>
        </tr>
        <tr>
            <td><b>Designation:</b></td>
            <td>_________________</td>
            <td><b>Designation:</b></td>
            <td>_________________</td>
        </tr>
        <tr>
            <td><b>Date:</b></td>
            <td>_________________</td>
            <td><b>Date:</b></td>
            <td>_________________</td>
        </tr>
        <tr>
            <td><b>Email:</b></td>
            <td>_________________</td>
            <td><b>Email:</b></td>
            <td>_________________</td>
        </tr>
    </table>
    <br>
    <p style="text-decoration: underline;"><b>WHEREAS</b>
        <br>
        <ul style="list-style-type: upper-alpha;">
            <li>MGC has conceptualized, created and developed and is the proprietor of the Million
                Genius Coders Academic System (as defined later) and the Components (as defined later)
                that empowers schools such as the School (as defined below) with excellent student
                learning and serves as a one stop, technology blended system for all academic
                requirements of such schools;
            </li>
            <li>
                CLIENT runs and operates a school by and under the name {{ $mou->school->name ?? 'School Name' }} and has seen the demo of the Million Genius
Coders Academic System and the Components and is now desirous of enhancing the
education and learning of its students by availing the same from MGC; and
            </li>
            <li>
                MGC has agreed to provide the Million Genius Coders Academic System and the
Components to the CLIENT for the Term (as defined later) and upon the terms as
hereinafter appearing.
            </li>

        <ul style="list-style-type: lower-alpha;">
            <li>
                “Million Genius Coders Academic System” shall mean and refer to an integrated,
technology blended solution consisting of all the Components designed for and
implemented by MGC at schools to improve student learning.
            </li>
        </ul>
    </ul>
    </p>
    <p>
        <b>MGC's Responsibilities</b>
        <ol>
            <li>
                <b>High-Quality Instructional Resources:</b> <br>
                Provide high-quality, well-structured instructional materials to support student learning.
            </li>
            <li>
                <b>Up-to-Date and Relevant Courses:</b> <br>
Ensure that all courses are continuously updated and aligned with the latest educational
standards and industry trends.
            </li>
            <li>
                <b>Technical Support:</b> <br>
                Offer technical support to ensure smooth operation and address any issues that arise
throughout the duration of the course.
            </li>
            <li>
                <b>Teacher Training:</b> <br>
                Provide comprehensive training to teachers before the commencement of the courses to
ensure they are well-prepared to facilitate learning.
            </li>
            <li>
                <b>Certificates of Completion:</b> <br>
                Issue certificates of completion to students who successfully finish each course.
            </li>
        </ol>
    </p>

    <!-- Signature Section -->
    <table class="sign">
        <tr>
            <td colspan="2"><b>For and on behalf of Million Genius Coders</b></td>
            <td colspan="2"><b>For and on behalf of {{ $mou->school->name ?? 'School Name' }}</b></td>
        </tr><br>
        <tr>
            <td><b>Signature:</b></td>
            <td>_________________</td>
            <td><b>Signature:</b></td>
            <td>_________________</td>
        </tr>
        <br>
        <br>
        <tr>
            <td><b>Name:</b></td>
            <td>_________________</td>
            <td><b>Name:</b></td>
            <td>_________________</td>
        </tr>
        <tr>
            <td><b>Designation:</b></td>
            <td>_________________</td>
            <td><b>Designation:</b></td>
            <td>_________________</td>
        </tr>
        <tr>
            <td><b>Date:</b></td>
            <td>_________________</td>
            <td><b>Date:</b></td>
            <td>_________________</td>
        </tr>
        <tr>
            <td><b>Email:</b></td>
            <td>_________________</td>
            <td><b>Email:</b></td>
            <td>_________________</td>
        </tr>
    </table>

    <ol>
        <li>
            <b>TERM</b> <br><br>
            The Duration of this Agreement shall be for a period of <strong>{{ $mou->agreement_period ?? 'Agreement Period' }}</strong> Year(s) commencing from <strong>{{ \Carbon\Carbon::parse($mou->academic_year_start)->toDateString() }}</strong> to <strong>{{ \Carbon\Carbon::parse($mou->academic_year_end)->toDateString() }}</strong> unless the term is renewed prior to termination for such period and
upon same, similar or such terms and conditions are mutually agreed by the Parties in writing.
        </li><br><br><br>
        <li>
            <b>SERVICE DETAILS & COST STRUCTURE</b>
            <br><br>
            <ul><li>Scope of Services Provided:</li></ul><br>
                <ol>
                    @php
                        $item = \App\Models\Items::find($mou->items_id);
                    @endphp

                    <li>
                         {{ $item->remarks ?? 'No remarks available' }}
                    </li>
                </ol><br>
                <ul><li>Student Count Grade Wise & Cost Structure for <b>Junior Coders :</b></li></ul>
<br>
        <table>
            <tr>
                <th>Class</th>
                <th>No. of Students (Tentative)</th>
                <th>Cost Per Student (₹)</th>
            </tr>

            @php
                $classes = ['Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5']; // Ensure 6 rows
                $totalStudentsJunior = 0;
            @endphp

            @foreach ($classes as $className)
                @php
                    $classData = collect($mou->classes)->firstWhere('class', $className);
                    $students = $classData['no_of_students'] ?? '---';
                    $costPerStudent = $classData['cost_per_student'] ?? '---';

                    if (is_numeric($students)) {
                        $totalStudentsJunior += $students;
                    }
                @endphp

                <tr>
                    <td>{{ $className }}</td>
                    <td>{{ $students }}</td>
                    <td>{{ is_numeric($costPerStudent) ? '₹' . number_format($costPerStudent, 2) : '---' }}</td>
                </tr>
            @endforeach
        </table>

    <br>
    <!-- Signature Section -->
    <table class="sign">
        <tr>
            <td colspan="2"><b>For and on behalf of Million Genius Coders</b></td>
            <td colspan="2"><b>For and on behalf of {{ $mou->school->name ?? 'School Name' }}</b></td>
        </tr><br>
        <tr>
            <td><b>Signature:</b></td>
            <td>_________________</td>
            <td><b>Signature:</b></td>
            <td>_________________</td>
        </tr>
        <br>
        <br>
        <tr>
            <td><b>Name:</b></td>
            <td>_________________</td>
            <td><b>Name:</b></td>
            <td>_________________</td>
        </tr>
        <tr>
            <td><b>Designation:</b></td>
            <td>_________________</td>
            <td><b>Designation:</b></td>
            <td>_________________</td>
        </tr>
        <tr>
            <td><b>Date:</b></td>
            <td>_________________</td>
            <td><b>Date:</b></td>
            <td>_________________</td>
        </tr>
        <tr>
            <td><b>Email:</b></td>
            <td>_________________</td>
            <td><b>Email:</b></td>
            <td>_________________</td>
        </tr>
    </table>

    <br>
    <ul><li>Student Count Grade Wise & Cost Structure for <b>Senior Coders :</b></ul>

    <table>
        <tr>
            <th>Class</th>
            <th>No. of Students (Tentative)</th>
            <th>Cost Per Student (₹)</th>
        </tr>

        @php
            $defaultClasses = ['Grade 6', 'Grade 7', 'Grade 8', 'Grade 9']; // Always displayed
            $optionalClasses = ['Grade 10', 'Grade 11', 'Grade 12']; // Only displayed if data is available
            $totalStudentsSenior = 0;
        @endphp

        {{-- Display Default Classes (Grade 6 to 9) --}}
        @foreach ($defaultClasses as $className)
            @php
                $classData = collect($mou->classes)->firstWhere('class', $className);
                $students = $classData['no_of_students'] ?? '---';
                $costPerStudent = $classData['cost_per_student'] ?? '---';

                if (is_numeric($students)) {
                    $totalStudentsSenior += $students;
                }
            @endphp

            <tr>
                <td>{{ $className }}</td>
                <td>{{ $students }}</td>
                <td>{{ is_numeric($costPerStudent) ? '₹' . number_format($costPerStudent, 2) : '---' }}</td>
            </tr>
        @endforeach

        {{-- Display Optional Classes (Grade 10 to 12) Only If Data Exists --}}
        @foreach ($optionalClasses as $className)
            @php
                $classData = collect($mou->classes)->firstWhere('class', $className);
                $students = $classData['no_of_students'] ?? null;
                $costPerStudent = $classData['cost_per_student'] ?? null;
            @endphp

            @if (!is_null($students) || !is_null($costPerStudent))
                <tr>
                    <td>{{ $className }}</td>
                    <td>{{ $students ?? '---' }}</td>
                    <td>{{ is_numeric($costPerStudent) ? '₹' . number_format($costPerStudent, 2) : '---' }}</td>
                </tr>
            @endif
        @endforeach
    </table>


    <br>
        {{-- <b>(O)-Optional</b> --}}
    </li>
</li><br><br><br>
@php
    $installmentNames = [
        1 => "First Payment",
        2 => "Second Payment",
        3 => "Third Payment",
        4 => "Fourth Payment",
        5 => "Fifth Payment",
        6 => "Sixth Payment",
        7 => "Seventh Payment",
        8 => "Eighth Payment",
        9 => "Ninth Payment",
        10 => "Tenth Payment",
        11 => "Eleventh Payment",
        12 => "Twelfth Payment",
    ];
@endphp
<li>
    <b>PAYMENT TERMS</b><br><br>
    The payment for the services under this Agreement shall be made by the CLIENT to MGC in
    <strong>{{ $mou->installments_count ?? 'Installment count' }}</strong> equal installments as follows:
    <ol>
        <br>
        @foreach ($mou->installments as $installment)
            <li>
                {{ $installmentNames[$installment['installment']] ?? 'Payment' }}:

                @if ($mou->payment_type === 'amount')
                    <strong>₹{{ number_format($installment['installment_payment'], 2) }}</strong>
                @else
                    <strong>{{ number_format($installment['installment_payment'], 2) }}</strong>%
                @endif

                of the total payment shall be made in the month of
                <strong>{{ $installment['installment_month'] }} {{ $installment['installment_year'] }}</strong>.
            </li>
            <br>
        @endforeach
    </ol>
    Payments shall be made via [ Bank Transfer / Cheque / NEFT / RTGS ] to the service
provider's designated account.
</li><br><br><br>
<li>
    <b>DELIVERABLES & RESPONSIBILITIES</b><br><br>
    The Service Provider will ensure the quality and timely delivery of all agreed services.
The School shall provide necessary infrastructure, internet connectivity and administrative
support for smooth execution.
</li><br><br><br>
<li><b>CONFIDENTIALITY & DATA PROTECTION   </b><br><br>
    The Service Provider will not share the student data with third parties without prior consent both parties agree to comply with data protection.
</li><br><br><br>
<li><b>TERMINATION</b><br><br>
    Either Party may terminate this MoU by providing [ 30 - day ] written notice. In the event of
    termination, the school shall settle all the pending payments.</li><br><br><br>
    <li><b>DISPUTE RESOLUTION</b><br><br>
        Any disputes arising under this MoU shall be resolved through mutual discussion and
        mediation</li>
        </ol>
<br><br><br><br><br><br><br><br>
        <!-- Signature Section -->
    <table class="sign">
        <tr>
            <td colspan="2"><b>For and on behalf of Million Genius Coders</b></td>
            <td colspan="2"><b>For and on behalf of {{ $mou->school->name ?? 'School Name' }}</b></td>
        </tr><br>
        <tr>
            <td><b>Signature:</b></td>
            <td>_________________</td>
            <td><b>Signature:</b></td>
            <td>_________________</td>
        </tr>
        <br>
        <br>
        <tr>
            <td><b>Name:</b></td>
            <td>_________________</td>
            <td><b>Name:</b></td>
            <td>_________________</td>
        </tr>
        <tr>
            <td><b>Designation:</b></td>
            <td>_________________</td>
            <td><b>Designation:</b></td>
            <td>_________________</td>
        </tr>
        <tr>
            <td><b>Date:</b></td>
            <td>_________________</td>
            <td><b>Date:</b></td>
            <td>_________________</td>
        </tr>
        <tr>
            <td><b>Email:</b></td>
            <td>_________________</td>
            <td><b>Email:</b></td>
            <td>_________________</td>
        </tr>
    </table>
    <div class="footer">
        <p>https://milliongeniuscoders.com | +91 8248826374</p>
    </div>
</body>
</html>
