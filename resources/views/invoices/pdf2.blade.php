<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agreement for Industrial Curriculum Services</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
        }
        .agreement-title {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .signature-section {
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            vertical-align: top;
            padding: 10px;
        }
    </style>
</head>
<body>
    <div class="agreement-title">Agreement for Industrial Curriculum Services</div>

    <div class="section">
        <p>This Agreement is made on this {{ $invoice->issue_date ?? 'N/A' }} between:</p>
        <ol>
            <li>
                <strong>MILLION GENIUS CODERS (MGC)</strong>, powered by KGISL Educational Institutions, having its principal place of business at 
                365, Thudiyalur Rd, Saravanampatti, Coimbatore, Tamil Nadu 641035 
                (hereinafter referred to as "MGC"), which expression shall, unless repugnant to the context or meaning thereof, 
                be deemed to include its successors and assigns, of the FIRST PART;
            </li>
            <li>
                <strong>{{ $invoice->school->name ?? 'N/A' }}</strong>, located at {{ $invoice->school->address ?? 'N/A' }}
                (hereinafter referred to as the "CLIENT"), represented by the Principal/Correspondent, 
                which expression shall, unless repugnant to the context or meaning thereof, be deemed to include its successors and assigns, of the SECOND PART.
            </li>
        </ol>
    </div>

    <div class="section">
        <p><strong>WHEREAS:</strong></p>
        <ul>
            <li>The CLIENT agrees to take the Industrial Curriculum of Million Genius Coders (MGC) from KGISL Educational Institutions, 
                which includes {{ $invoice->items->first()->item->name }} for the price of Rs. {{ $invoice->items->first()->price }} per student.</li>
            <li>The CLIENT agrees to settle the payment for the services as detailed below:</li>
        </ul>
    </div>

    <div class="section">
        <p><strong>PAYMENT TERMS</strong></p>
        <p>The payment for the services under this Agreement shall be made by the CLIENT to MGC in four equal installments as follows:</p>
        <ol>
            <li><strong>First Payment:</strong> ___ % of the total payment shall be made at the time of signing this Memorandum of Understanding (MOU).</li>
            <li><strong>Second Payment:</strong> ___ % of the total payment shall be made in the month of ____ 2025.</li>
            <li><strong>Third Payment:</strong> ___ % of the total payment shall be made in the month of _____ 2025.</li>
        </ol>
    </div>

    <div class="section signature-section">
        <p><strong>IN WITNESS WHEREOF</strong>, the Parties hereto have caused this Agreement to be executed by their duly authorized representatives on the date first written above.</p>
        <table>
            <tr>
                <td>
                    <strong>For Million Genius Coders (MGC):</strong><br>
                    [Authorized Signatory]<br>
                    [Designation]<br>
                    [Date]
                </td>
                <td>
                    <strong>For {{ $invoice->school->name ?? 'N/A' }}:</strong><br>
                    [Principal/Correspondent Name]<br>
                    [Designation]<br>
                    [Date]
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
