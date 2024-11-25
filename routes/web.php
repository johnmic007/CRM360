<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



use App\Http\Controllers\InvoiceController;

Route::get('/invoices/{id}/download', [InvoiceController::class, 'downloadInvoice'])->name('invoice.download');
