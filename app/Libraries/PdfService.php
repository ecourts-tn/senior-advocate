<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Generate Application-cum-Consent Letter PDF matching official proforma.
 * PDFs are rendered in memory and streamed — nothing is stored on disk.
 */
class PdfService
{
    /**
     * Build Dompdf instance for an application (runtime only, no file write).
     */
    public function renderApplicationPdf(array $app, array $extra = []): Dompdf
    {
        $html = view('pdf/application_form', [
            'app'  => $app,
            'extra'=> $extra,
            'l1'   => $extra['l1'] ?? [],
            'l2'   => $extra['l2'] ?? [],
            'l3pb' => $extra['l3pb'] ?? [],
            'l3am' => $extra['l3am'] ?? [],
            'l4'   => $extra['l4'] ?? [],
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf;
    }

    /**
     * Render the application PDF and stream it inline to the browser.
     */
    public function streamApplicationPdf(array $app, array $extra = [], ?string $downloadName = null): void
    {
        $dompdf = $this->renderApplicationPdf($app, $extra);
        $binary = $dompdf->output();

        $name = $downloadName
            ?? (($app['application_no'] ?? '') !== ''
                ? str_replace('/', '_', (string) $app['application_no']) . '.pdf'
                : 'application.pdf');

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $name . '"');
        header('Content-Length: ' . strlen($binary));
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $binary;
        exit;
    }
}
