<?php

namespace App\Controllers;

use App\Libraries\UploadService;
use App\Models\ApplicationModel;
use App\Models\DesignationNotificationModel;

/**
 * Secure file serving for applicants (own files) and admins.
 */
class FileController extends BaseController
{
    /**
     * Official designation notification PDF (authenticated users).
     */
    public function designationNotification(int $id)
    {
        $row = model(DesignationNotificationModel::class)->find($id);
        if (! $row || empty($row['document_path'])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $abs = (new UploadService())->absolutePath($row['document_path']);
        if (! is_file($abs)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $name = 'notification_' . preg_replace('/[^A-Za-z0-9._-]+/', '_', (string) ($row['notification_number'] ?? $id)) . '.pdf';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Content-Security-Policy', "default-src 'none'; object-src 'self'; script-src 'none'; sandbox")
            ->setHeader('Content-Disposition', 'inline; filename="' . $name . '"')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody((string) file_get_contents($abs));
    }

    public function application(int $id, string $type)
    {
        $userId = (int) session()->get('user_id');
        $role   = session()->get('role');
        $app    = model(ApplicationModel::class)->find($id);

        if (! $app) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (! in_array($role, ['admin', 'reviewer', 'approver'], true) && (int) $app['user_id'] !== $userId) {
            return redirect()->to('/')->with('error', 'Access denied.');
        }

        $map = [
            'photo'           => 'photo_path',
            'signature'       => 'signature_path',
            'enrolment_cert'  => 'enrolment_cert_path',
            'age_proof'       => 'age_proof_path',
            'education_qual'  => 'education_qual_path',
            'format_l1'       => 'format_l1_path',
            'format_l2'       => 'format_l2_path',
            'format_l3i'      => 'format_l3i_path',
            'format_l3ii'     => 'format_l3ii_path',
            'format_l4'       => 'format_l4_path',
        ];

        if (! isset($map[$type]) || empty($app[$map[$type]])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $abs = (new UploadService())->absolutePath($app[$map[$type]]);
        if (! is_file($abs)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Force safe Content-Type by field (do not trust client-supplied names or polyglot sniffing).
        $mime     = UploadService::contentTypeForField($type);
        $download = preg_replace('/[^A-Za-z0-9._-]+/', '_', basename($abs)) ?: ($type . '.bin');

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Content-Security-Policy', "default-src 'none'; img-src 'self'; object-src 'none'; script-src 'none'; sandbox")
            ->setHeader('Content-Disposition', 'inline; filename="' . $download . '"')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody((string) file_get_contents($abs));
    }
}
