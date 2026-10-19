<?php

namespace App\Domains\Finance\Actions;

use App\Domains\Finance\Enums\InvoiceStatus;
use App\Domains\Finance\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use InvalidArgumentException;

class GenerateInvoiceReceiptPdf
{
    public function handle(Invoice $invoice): Response
    {
        if ($invoice->status !== InvoiceStatus::Paid) {
            throw new InvalidArgumentException('Receipts are only available for paid invoices.');
        }

        $invoice->loadMissing([
            'client',
            'organization',
            'lines',
        ]);

        $pdf = Pdf::loadView('pdf.receipt', [
            'invoice' => $invoice,
            'organization' => $invoice->organization,
            'client' => $invoice->client,
        ])->setPaper('a4');

        $filename = 'receipt-'.str_replace(['/', '\\'], '-', $invoice->number).'.pdf';

        return $pdf->download($filename);
    }
}
