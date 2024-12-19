
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
    body {
        font-family: "Open Sans", Arial, sans-serif;
        background: #f9f9f9;
        color: #333;
        margin: 20px;
        line-height: 1.6;
    }
    .container {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h1, h2, h3, h4, h5, h6 {
        font-weight: 600;
    }
    pre {
        white-space: pre-wrap;
        word-wrap: break-word;
        font-family: "Open Sans", Arial, sans-serif;
        font-size: 14px;
        margin: 0;
    }
</style>
<title>Agreement</title>
</head>
<body>
<div class="container">

<img src="{{ public_path('storage/logo.png') }}" alt="Starting Meter Photo">

<pre>
AGREEMENT
THIS AGREEMENT is entered into at _______________ (Location) on this ____ day of
______________, Year

BETWEEN

1. MILLION GENIUS CODERS, powered by KGISL Educational Institutions, a company
incorporated under the provisions of the Companies Act, 1956, and having its principal place of
business at 365, Thudiyalur Rd, Saravanampatti, Coimbatore, Tamil Nadu 641035 (hereinafter
referred to as "MGC"), which expression shall, unless repugnant to the context or meaning
thereof, be deemed to include its successors and assigns, of the FIRST PART;

AND

2. <strong>{{ $invoice->school->name ?? 'N/A' }}</strong>  , a [Type of Entity: Corporation/Society/Section 8 Company, etc.],
incorporated under the provisions of the Companies Act, 2013/1956 (or as per applicable laws),
having its PAN [PAN Number] and principal place of business at {{ $invoice->school->address ?? 'N/A' }} (hereinafter
referred to as the "CLIENT"), which expression shall, unless repugnant to the context or
meaning thereof, be deemed to include its trustees, beneficiaries, members, successors, and
assigns, of the SECOND PART.

MGC and CLIENT are hereinafter collectively referred to as the "Parties" and individually as a
"Party"

WHEREAS

A. MGC has conceptualized, created and developed and is the proprietor of the Million Genius
Coders Academic System (as defined later) and the Components (as defined later) that empowers
schools such as the School (as defined below) with excellent student learning and serves as a one
stop, technology blended system for all academic requirements of such schools;

B. CLIENT runs and operates a school by and under the name <strong>{{ $invoice->school->name ?? 'N/A' }}</strong>
and has seen the demo of the Million Genius Coders Academic System and the Components and is
now desirous of enhancing the education and learning of its students by availing the same from
MGC; and

C. MGC has agreed to provide the Million Genius Coders Academic System and the Components to
the CLIENT for the Term (as defined later) and upon the terms as hereinafter appearing.

(a) “Million Genius Coders Academic System” shall mean and refer to an integrated, technology
blended solution consisting of all the Components designed for and implemented by MGC at
schools to improve student learning.

MGC's Responsibilities

1. High-Quality Instructional Resources:
Provide high-quality, well-structured instructional materials to support student learning.

2. Up-to-Date and Relevant Courses:
Ensure that all courses are continuously updated and aligned with the latest educational standards
and industry trends.

3. Technical Support:
Offer technical support to ensure smooth operation and address any issues that arise throughout
the duration of the course.

4. Teacher Training:
Provide comprehensive training to teachers before the commencement of the courses to ensure
they are well-prepared to facilitate learning.

5. Certificates of Completion:
Issue certificates of completion to students who successfully finish each course.

Individual Logins

Million Genius Coders (MGC) will provide individual logins to ensure each stakeholder has
personalized access to the system. The login access will be provided for the following:

1. School:
The school administration will have access to manage overall course management, student
progress tracking, and administrative features.

2. Teachers:
Teachers will have access to instructional resources, student performance tracking, and teaching
tools for course delivery.

3. Students:
Students will be provided with access to course materials, assignments, assessments, and
progress reports to facilitate their learning.

4. Parents:
Parents will have access to monitor their child's performance, track course completion, and
receive updates on their academic progress.

1. TERM
The duration of this Agreement shall be for a period of ______ ( Mention the no of academic years
signed ) commencing from the Effective Date Start Date & End Date unless the Term is renewed prior
to termination for such period and upon same, similar or such terms as mutually agreed by the Parties in
writing. The Parties hereby agree and covenant that, except as provided herein, this Agreement shall not
be terminated by the CLIENT for the Term hereof (“Lock-in Period”).

2. SCOPE OF AGREEMENT AND OBLIGATIONS OF PARTIES
3.1 The CLIENT promises and undertakes to implement the Million Genius Coders Academic System
only at the School Premises and only in the classes and divisions, The CLIENT shall be
responsible for meeting and maintaining (including updating and upgrading), all hardware and
networking requirements for effective implementation and usage thereof as per details

3.2 Post realization of the payment from the CLIENT, MGC will provide Training the teachers and the
assessment materials(Soft Copies) to the CLIENT based on the number of divisions in the School

3.3 MGC will provide services to monitor and manage the performance of teachers, students and
School of the CLIENT. CLIENT will make School teachers, students and School Premises
available to MGC staff for effective implementation in all aspects thereof.

3.4 The License granted hereunder shall permit the CLIENT to adopt and use the words ‘Academic
Partner: Million Genius Coders’ or any Trademarks of MGC in CLIENT’s external
communication for the Term hereof. However, the CLIENT shall get the style and format for the
same as well as all creatives and artwork pre-approved by MGC in writing before the CLIENT
releases any such external communication.

3.5 The CLIENT shall provide its KYC details as requested by MGC.

3. PAYMENT & TERMS
4.1 For providing Million Genius Coders Academic System and the Components, the CLIENT shall
pay to MGC fees (“Fees”) inclusive of applicable Goods & Services Tax as per Applicable Laws
as provided

4.2 The charges would be {{ $invoice->items->first()->price }} Rupees Per student which includes {{ $invoice->items->first()->item->name }}

4.3 The Parties hereby agree that the Fees can only be varied by a written agreement of the Parties.

4.4 The CLIENT hereby agrees and acknowledges making timely payment of Fees as per aforesaid
Clause 4.1.

The payment for the services under this Agreement shall be made by the CLIENT to MGC in four equal
installments as follows:
1. First Payment: ___% of the total payment shall be made at the time of signing this
Memorandum of Understanding (MOU).
2. Second Payment: ___% of the total payment shall be made in the month of _____ 2025.
3. Third Payment: ___% of the total payment shall be made in the month of _____ 2025.
4. Final Payment: The remaining ___% of the total payment shall be made in the month of _____
2025.

4.Confidentiality
5.1 Definitions: "Disclosing Party" refers to the Party sharing Confidential Information, and "Receiving
Party" refers to the Party receiving it.

5.2 Any Confidential Information disclosed must be treated with the same care as the Receiving Party’s
own confidential information and used only for the purposes of this Agreement. It cannot be disclosed
without prior written permission from the Disclosing Party.

5.3 Obligations of the Receiving Party:
● Use Confidential Information only for this Agreement.
● Do not disclose, share, or misuse the information.
● Do not assign or transfer it without written consent.
● Disclose only to authorized personnel who need it to fulfill their duties.

5.4 Duration: These confidentiality obligations remain in effect for (No of years) from the Effective
Date.

5.5 Post-Termination: Upon termination or expiration, the Receiving Party must return or destroy all
Confidential Information and notify the Disclosing Party of the destruction.

5. REPRESENTATIONS AND WARRANTIES
6.1 Representations and Warranties of the CLIENT: Apart from other representations and warranties
as agreed elsewhere in this agreement, the CLIENT hereby represents and warrants to MGC that:
(a) it has all approvals required under Applicable Laws for undertaking the business of running the
School and to perform its obligations in connection therewith;
(b) it will pay the Fees as agreed to be paid to MGC as per Clause 3 above
(c) it will not do any act which would bring disrepute to MGC or to the Trademarks or any goodwill
attached thereto;
(d) it shall not infringe, misappropriate or pass off any intellectual property rights of the other Party in
the course of performance of its duties under this Agreement;
(e) all statements, information and materials as well as KYC details it has provided to MGC under this
Agreement, are accurate and complete and that it has not made any misrepresentation or material
omission therefrom; and
(f) unless agreed otherwise in writing by MGC, upon the expiry or termination of this Agreement, the
License granted hereunder shall forthwith lapse and the CLIENT shall forthwith cease any and all
use of any and all Intellectual Property, materials, content and information, whether online or
offline, depicting or referring to Trademarks, Million Genius Coders Academic System,
Components and Related Materials or anything portraying MGC to be associated with the
CLIENT.

6.2 Each Party (“Indemnifying Party”) agrees to defend and indemnify the other Party (“Indemnified
Party”) against any losses, liabilities, claims, damages, costs, and expenses (including legal fees)
arising from misrepresentation, non-fulfillment of obligations, or any act related to the Indemnifying
Party’s activities, including third-party claims and intellectual property violations.

7 6.3 Neither Party shall be liable for loss of profits, use, or consequential damages related to this
Agreement, unless required by law. The maximum liability of MGC, if applicable, is limited as
specified in this Agreement.

6. TERMINATION:
● Lock-in Period: CLIENT cannot terminate during the Lock-in Period. If MGC terminates due to
CLIENT's breach, or CLIENT terminates early, CLIENT will pay the remaining fees and
compensate MGC for the unexpired period. MGC will reclaim physical devices provided.
● Upon termination, all rights and obligations cease, but MGC is entitled to payment for services
rendered and compensation for the unexpired period.

7. DISPUTE RESOLUTION:
● Good Faith Discussions: Parties will attempt to resolve disputes through mutual discussions.
● If unresolved within 30 days, disputes will be referred to arbitration under the Indian Arbitration
and Conciliation Act, 1996.
● Arbitration will take place in Coimbatore, with English as the language.

In witness whereof, the aforenamed Parties have signed and executed this Agreement on the date first
above written.

<div>
    
    <table style="width: 100%; border-collapse: collapse;">

    <tr>
            <td><p><strong>For and on behalf of Million Genius Coders</strong></p></td>
            <td><strong>For and on behalf of: </strong>{{ $invoice->school->name ?? 'N/A' }}</td>
        </tr>

        <tr>
            <td><strong>Signature:</strong> _______________________</td>
            <td><strong>Signature:</strong> _______________________</td>
        </tr>
        <tr>
            <td><strong>Name:</strong> ____________________________</td>
            <td><strong>Name:</strong> ____________________________</td>
        </tr>
        <tr>
            <td><strong>Designation:</strong> _____________________</td>
            <td><strong>Designation:</strong> _____________________</td>
        </tr>
        <tr>
            <td><strong>Date:</strong> _____________________________</td>
            <td><strong>Date:</strong> _____________________________</td>
        </tr>
        <tr>
            <td><strong>Email:</strong> ____________________________</td>
            <td><strong>Email:</strong> ____________________________</td>
        </tr>
    </table>
</div>


</pre>
</div>
</body>
</html>
