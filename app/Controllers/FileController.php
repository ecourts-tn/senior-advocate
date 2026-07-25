<?php

namespace App\Controllers;

use App\Libraries\UploadService;
use App\Models\ApplicationModel;

/**
 * Secure file serving for applicants (own files) and admins.
 */
class FileController extends BaseController
{
    public function application(int $id, string $type)
    {
        $userId = (int) session()->get('user_id');
        $role   = session()->get('role');
        $app    = model(ApplicationModel::class)->find($id);

        if (! $app) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (! in_array($role, ['admin', 'reviewer'], true) && (int) $app['user_id'] !== $userId) {
            return redirect()->to('/')->with('error', 'Access denied.');
        }

        $map = [
            'photo'          => 'photo_path',
            'signature'      => 'signature_path',
            'enrolment_cert' => 'enrolment_cert_path',
            'format_l1'      => 'format_l1_path',
            'format_l2'      => 'format_l2_path',
            'format_l3i'     => 'format_l3i_path',
            'format_l3ii'    => 'format_l3ii_path',
            'format_l4'      => 'format_l4_path',
        ];

        if (! isset($map[$type]) || empty($app[$map[$type]])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $abs = (new UploadService())->absolutePath($app[$map[$type]]);
        if (! is_file($abs)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $mime = mime_content_type($abs) ?: 'application/octet-stream';

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($abs) . '"')
            ->setBody(file_get_contents($abs));
    }
}
