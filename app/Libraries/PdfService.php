<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Generate Application-cum-Consent Letter PDF matching official proforma.
 */
class PdfService
{
    public function generateApplicationPdf(array $app, array $extra = []): string
    {
        $html = view('pdf/application_form', [
            'app'    => $app,
            'extra'  => $extra,
            'l1'     => $extra['l1'] ?? [],
            'l2'     => $extra['l2'] ?? [],
            'l3pb'   => $extra['l3pb'] ?? [],
            'l3am'   => $extra['l3am'] ?? [],
            'l4'     => $extra['l4'] ?? [],
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'applications' . DIRECTORY_SEPARATOR . $app['id'];
        if (! is_dir($dir)) {
            mkdir($dir, 0750, true);
        }

        $filename = 'SAD_Application_' . ($app['application_no'] ? str_replace('/', '_', $app['application_no']) : $app['id']) . '.pdf';
        $fullPath = $dir . DIRECTORY_SEPARATOR . $filename;
        file_put_contents($fullPath, $dompdf->output());

        return 'applications/' . $app['id'] . '/' . $filename;
    }

    public function stream(string $relativePath, string $downloadName = 'application.pdf'): void
    {
        $abs = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        if (! is_file($abs)) {
            throw new \RuntimeException('PDF not found.');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $downloadName . '"');
        header('Content-Length: ' . filesize($abs));
        readfile($abs);
        exit;
    }
}
