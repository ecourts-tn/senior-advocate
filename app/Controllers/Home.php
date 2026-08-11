<?php

namespace App\Controllers;

use App\Libraries\UploadService;
use App\Models\DesignationNotificationModel;
use Config\Site;

class Home extends BaseController
{
    public function index()
    {
        // Home page temporarily disabled — send visitors to login / their dashboard.
        if (session()->get('user_id')) {
            if (is_admin_role()) {
                return redirect()->to('/admin');
            }

            return redirect()->to('/applicant/dashboard');
        }

        return redirect()->to('/login');
    }

    public function instructions()
    {
        $portalNotifications = [];
        try {
            $portalNotifications = model(DesignationNotificationModel::class)->withDocuments(10);
        } catch (\Throwable $e) {
            $portalNotifications = [];
        }

        return view('instructions', [
            'title'               => 'Instructions for Applicants',
            'portalNotifications' => $portalNotifications,
        ]);
    }

    /**
     * Public list of official notification PDFs published on the portal.
     */
    public function notifications()
    {
        $rows = [];
        try {
            $rows = model(DesignationNotificationModel::class)->withDocuments(100);
        } catch (\Throwable $e) {
            $rows = [];
        }

        return view('portal_notifications', [
            'title'         => 'Notifications',
            'notifications' => $rows,
        ]);
    }

    /**
     * Stream a published notification PDF (public).
     */
    public function notificationDocument(int $id)
    {
        $row = model(DesignationNotificationModel::class)->find($id);
        if (! $row || empty($row['document_path'])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $abs = (new UploadService())->absolutePath($row['document_path']);
        if (! is_file($abs) || ! is_readable($abs)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $mime = mime_content_type($abs) ?: 'application/pdf';
        $name = 'notification_' . preg_replace(
            '/[^A-Za-z0-9._-]+/',
            '_',
            (string) ($row['notification_number'] ?? $id)
        ) . '.pdf';
        $size  = filesize($abs);
        $mtime = filemtime($abs) ?: time();

        return $this->response
            ->setStatusCode(200)
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $name . '"')
            ->setHeader('Content-Length', (string) $size)
            ->setHeader('Cache-Control', 'public, max-age=3600')
            ->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', $mtime) . ' GMT')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody((string) file_get_contents($abs));
    }

    /**
     * Official Rules for Designation of Senior Advocates, 2026 (view / download PDF).
     */
    public function rules()
    {
        /** @var Site $site */
        $site = config(Site::class);
        $path = FCPATH . $site->rulesPdfPath;
        $ver  = is_file($path) ? (string) filemtime($path) : (string) time();

        // Inline stream URL (correct Content-Type) for reliable browser embedding
        $embedUrl = site_url('rules/view') . '?v=' . rawurlencode($ver);

        return view('rules', [
            'title'         => $site->rulesTitle,
            'site'          => $site,
            'rulesPdfUrl'   => base_url($site->rulesPdfPath) . '?v=' . rawurlencode($ver),
            'rulesEmbedUrl' => $embedUrl,
            'lastUpdated'   => $site->lastUpdated,
        ]);
    }

    /**
     * Stream the rules PDF for in-browser viewing (inline, not attachment).
     */
    public function rulesView()
    {
        return $this->streamRulesPdf(false);
    }

    /**
     * Force-download the official rules PDF.
     */
    public function rulesDownload()
    {
        return $this->streamRulesPdf(true);
    }

    /**
     * @param bool $asAttachment true = download, false = inline display
     */
    private function streamRulesPdf(bool $asAttachment)
    {
        /** @var Site $site */
        $site = config(Site::class);
        $path = FCPATH . $site->rulesPdfPath;

        if (! is_file($path) || ! is_readable($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Rules PDF not found.');
        }

        $filename = 'Rules-for-Designation-of-Senior-Advocates-2026.pdf';
        $size     = filesize($path);
        $mtime    = filemtime($path) ?: time();
        $disposition = ($asAttachment ? 'attachment' : 'inline') . '; filename="' . $filename . '"';

        return $this->response
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', $disposition)
            ->setHeader('Content-Length', (string) $size)
            ->setHeader('Accept-Ranges', 'bytes')
            ->setHeader('Cache-Control', 'public, max-age=86400')
            ->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', $mtime) . ' GMT')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody((string) file_get_contents($path));
    }

    /**
     * GIGW mandatory policy / information pages.
     */
    public function policy(string $slug = '')
    {
        /** @var Site $site */
        $site = config(Site::class);
        $slug = strtolower(trim($slug));

        if ($slug === '' || ! isset($site->policies[$slug])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $policy = $site->policies[$slug];

        return view('policies/show', [
            'title'      => $policy['title'],
            'policy'     => $policy,
            'slug'       => $slug,
            'policies'   => $site->policies,
            'lastUpdated'=> $site->lastUpdated,
            'site'       => $site,
        ]);
    }
}
