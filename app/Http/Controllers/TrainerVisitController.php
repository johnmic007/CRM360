<?php

namespace App\Http\Controllers;

use App\Models\TrainerVisit;
use Barryvdh\DomPDF\Facade\Pdf;

class TrainerVisitController extends Controller
{
    public function downloadPdf($id)
    {
        // Fetch the trainer visit details
        $trainerVisit = TrainerVisit::with(['user'])->findOrFail($id);

        // Generate the PDF using the Blade view
        $pdf = Pdf::loadView('pdf.trainer-visits', compact('trainerVisit'));

        // Return the PDF as a download
        return $pdf->download("trainer-visit-{$trainerVisit->id}.pdf");


    }
}
