<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class GenerateInvoicePdf
{
    public function handle(Invoice $invoice): Response
    {
        $invoice->loadMissing([
            'client',
            'organization',
            'lines',
        ]);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'organization' => $invoice->organization,
            'client' => $invoice->client,
        ])->setPaper('a4');

        $filename = str_replace(['/', '\\'], '-', $invoice->number).'.pdf';

        return $pdf->download($filename);
    }
}
