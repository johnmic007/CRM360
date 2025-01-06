<?php

namespace App\Http\Controllers;

use App\Models\TrainerVisit;
use Barryvdh\DomPDF\Facade\Pdf;

class TrainerVisitController extends Controller
{
    public function downloadPdf($id)
    {
        // Fetch the trainer visit details
        $trainerVisit = TrainerVisit::with(['user' ,'leadStatuses'])->findOrFail($id);

        // Generate the PDF using the Blade view
        $pdf = Pdf::loadView('pdf.trainer-visits', compact('trainerVisit'));

        // Return the PDF as a download
        return $pdf->download("trainer-visit-{$trainerVisit->id}.pdf");


    }



    public function downloadSchoolPDF($id)
    {
        $trainerVisit = TrainerVisit::findOrFail($id);

        $data = [
            'start_time' => $trainerVisit->visitEntry->start_time ?? 'N/A',
            'end_time' => $trainerVisit->visitEntry->end_time ?? 'N/A',
            'starting_km' => $trainerVisit->starting_km ?? 'N/A',
            'ending_km' => $trainerVisit->ending_km ?? 'N/A',
            'travel_mode' => $trainerVisit->travel_mode ?? 'N/A',
        ];

        $pdf = Pdf::loadView('trainer_visit.pdf', $data);

        return $pdf->download("visit_details_{$trainerVisit->id}.pdf");
    }
}
