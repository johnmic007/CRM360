<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\School;
use TomatoPHP\FilamentInvoices\Facades\FilamentInvoices;

use App\Models\Invoice;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\View;

class InvoiceController extends Controller
{
   
    public function downloadInvoice($id)
    {
        $invoice = Invoice::with(['school', 'company', 'items.item'])->findOrFail($id);

        // Render the Blade template
        $html = View::make('invoices.pdf', compact('invoice'))->render();

        // Initialize Dompdf
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);

        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Return the PDF as a download
        return response()->streamDownload(
            fn () => print($dompdf->output()),
            'invoice_' . $invoice->invoice_number . '.pdf'
        );
    }


    public function downloadInvoiceCurriculum ($id){

        $invoice = Invoice::with(['school', 'company', 'items.item'])->findOrFail($id);

        // Render the Blade template
        $html = View::make('invoices.pdf2', compact('invoice'))->render();

        // Initialize Dompdf
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);

        // Set paper size and orientation
        $dompdf->setPaper('A4', 'portrait');

        // Render the PDF
        $dompdf->render();

        // Return the PDF as a download
        return response()->streamDownload(
            fn () => print($dompdf->output()),
            'invoice_' . $invoice->invoice_number . '.pdf'
        );

    }
}
