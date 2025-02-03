<?php

use Illuminate\Support\Facades\Route;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\DraftMou;

Route::get('/', function () {
    return view('welcome');
});



use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\TrainerVisitController;

Route::get('/invoices/{id}/download', [InvoiceController::class, 'downloadInvoice'])->name('invoice.download');

Route::get('/draft-mou/{id}/download', function ($id) {
    // Retrieve the MOU record
    $mou = DraftMou::findOrFail($id);

    // Generate the PDF
    $pdf = Pdf::loadView('pdf.draft_mou', ['mou' => $mou]);

    // Return the PDF as a download
    return $pdf->download('Draft_MOU_' . $mou->id . '.pdf');
})->name('draft-mou.download');


Route::get('/invoices/{id}/download_curriculum', [InvoiceController::class, 'downloadInvoiceCurriculum'])->name('invoice.downloadCurriculum');



Route::get('/trainer-visits/{id}/download', [TrainerVisitController::class, 'downloadPdf'])->name('trainer-visit.download');



Route::get('/', function () {
    return redirect('/admin');
});

use App\Http\Controllers\TaskController;

Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
