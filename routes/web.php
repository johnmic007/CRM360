<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\TrainerVisitController;

Route::get('/invoices/{id}/download', [InvoiceController::class, 'downloadInvoice'])->name('invoice.download');


Route::get('/invoices/{id}/download_curriculum', [InvoiceController::class, 'downloadInvoiceCurriculum'])->name('invoice.downloadCurriculum');



Route::get('/trainer-visits/{id}/download', [TrainerVisitController::class, 'downloadPdf'])->name('trainer-visit.download');



use App\Http\Controllers\TaskController;

Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
