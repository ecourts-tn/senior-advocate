<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Concerns\ListQuery;
use App\Libraries\UploadService;
use App\Models\AuditLogModel;
use App\Models\DesignationNotificationModel;

class DesignationNotificationController extends BaseController
{
    use ListQuery;

    public function index()
    {
        $model   = model(DesignationNotificationModel::class);
        $filters = $this->listQueryParams();
        $q       = $filters['q'];
        $perPage = $filters['perPage'];

        if ($q !== '') {
            $model->groupStart()
                ->like('notification_number', $q, 'both', null, true)
                ->orLike('title', $q, 'both', null, true)
                ->orLike('remarks', $q, 'both', null, true)
                ->groupEnd();
        }

        $activeOnly = (string) ($this->request->getGet('active') ?? '');
        if ($activeOnly === '1') {
            $model->where('is_active', true);
        } elseif ($activeOnly === '0') {
            $model->where('is_active', false);
        } else {
            $activeOnly = '';
        }

        $model->orderBy('notification_date', 'DESC')
            ->orderBy('id', 'DESC');

        $rows  = $model->paginate($perPage, 'default', $filters['page']);
        $pager = $model->pager;
        $pager->setPath('admin/notifications');
        $pager->only(['q', 'per_page', 'active']);

        $counts = model(DesignationNotificationModel::class)->applicationCounts();

        return view('admin/designation_notifications/index', [
            'title'            => 'Notifications',
            'notifications'    => $rows,
            'q'                => $q,
            'perPage'          => $perPage,
            'page'             => (int) ($pager->getCurrentPage('default') ?: $filters['page']),
            'total'            => (int) $pager->getTotal('default'),
            'allowedPerPage'   => $filters['allowedPerPage'],
            'pager'            => $pager,
            'activeOnly'       => $activeOnly,
            'appCounts'        => $counts,
            'hasActiveFilters' => $q !== '' || $activeOnly !== '',
        ]);
    }

    public function create()
    {
        return view('admin/designation_notifications/form', [
            'title'        => 'Add notification',
            'notification' => null,
            'isEdit'       => false,
        ]);
    }

    public function store()
    {
        $rules = $this->validationRules();
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = $this->payloadFromRequest();
        $dateErr = DesignationNotificationModel::validateAdminDates($payload);
        if ($dateErr !== null) {
            return redirect()->back()->withInput()->with('error', $dateErr);
        }

        $model = model(DesignationNotificationModel::class);
        $id    = (int) $model->insert($payload, true);

        $upload = $this->handleDocumentUpload($id, null);
        if ($upload['error'] !== null) {
            return redirect()->to('/admin/notifications/' . $id . '/edit')
                ->with('error', $upload['error'] . ' Notification was saved without a document.');
        }

        if (! empty($payload['is_active'])) {
            $model->deactivateOthers($id);
        }

        model(AuditLogModel::class)->log(
            'designation_notification_created',
            (int) session()->get('user_id'),
            null,
            [
                'id'                  => $id,
                'notification_number' => $payload['notification_number'],
                'notification_date'   => $payload['notification_date'],
                'document_path'       => $upload['path'] ?? null,
            ],
            'designation_notification',
            $id
        );

        return redirect()->to('/admin/notifications')
            ->with('success', 'Notification created.');
    }

    public function edit(int $id)
    {
        $model = model(DesignationNotificationModel::class);
        $row   = $model->find($id);
        if (! $row) {
            return redirect()->to('/admin/notifications')
                ->with('error', 'Notification not found.');
        }

        return view('admin/designation_notifications/form', [
            'title'        => 'Edit notification',
            'notification' => $row,
            'isEdit'       => true,
        ]);
    }

    public function update(int $id)
    {
        $model = model(DesignationNotificationModel::class);
        $row   = $model->find($id);
        if (! $row) {
            return redirect()->to('/admin/notifications')
                ->with('error', 'Notification not found.');
        }

        $rules = $this->validationRules($id);
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = $this->payloadFromRequest(true);
        $dateErr = DesignationNotificationModel::validateAdminDates($payload);
        if ($dateErr !== null) {
            return redirect()->back()->withInput()->with('error', $dateErr);
        }

        $uploader   = new UploadService();
        $oldPath    = $row['document_path'] ?? null;
        $removeDoc  = (bool) $this->request->getPost('remove_document');
        $uploadPath = null;

        $file = $this->request->getFile('document');
        $hasNewFile = $file && $file->isValid() && ! $file->hasMoved() && $file->getError() !== UPLOAD_ERR_NO_FILE;

        if ($hasNewFile) {
            $result = $uploader->storeNotificationDocument($file, $id);
            if (! $result['ok']) {
                return redirect()->back()->withInput()->with('error', $result['error'] ?? 'Document upload failed.');
            }
            $uploadPath              = $result['path'];
            $payload['document_path'] = $uploadPath;
            if (! empty($oldPath) && $oldPath !== $uploadPath) {
                $uploader->deleteIfExists($oldPath);
            }
        } elseif ($removeDoc && ! empty($oldPath)) {
            $uploader->deleteIfExists($oldPath);
            $payload['document_path'] = null;
        }

        $model->update($id, $payload);

        if (! empty($payload['is_active'])) {
            $model->deactivateOthers($id);
        }

        model(AuditLogModel::class)->log(
            'designation_notification_updated',
            (int) session()->get('user_id'),
            null,
            [
                'id'                  => $id,
                'notification_number' => $payload['notification_number'],
                'is_active'           => $payload['is_active'],
                'document_path'       => $payload['document_path'] ?? $oldPath,
                'document_removed'    => $removeDoc && ! $hasNewFile,
            ],
            'designation_notification',
            $id
        );

        return redirect()->to('/admin/notifications')
            ->with('success', 'Notification updated.');
    }

    public function delete(int $id)
    {
        $model = model(DesignationNotificationModel::class);
        $row   = $model->find($id);
        if (! $row) {
            return redirect()->to('/admin/notifications')
                ->with('error', 'Notification not found.');
        }

        // Block delete when applications are linked
        $linked = (int) db_connect()->table('applications')
            ->where('notification_id', $id)
            ->where('deleted_at', null)
            ->countAllResults();

        if ($linked > 0) {
            return redirect()->to('/admin/notifications')
                ->with('error', 'Cannot delete: ' . $linked . ' application(s) are linked to this notification. Deactivate it instead.');
        }

        if (! empty($row['document_path'])) {
            (new UploadService())->deleteIfExists($row['document_path']);
        }

        $model->delete($id);

        model(AuditLogModel::class)->log(
            'designation_notification_deleted',
            (int) session()->get('user_id'),
            null,
            [
                'id'                  => $id,
                'notification_number' => $row['notification_number'] ?? '',
            ],
            'designation_notification',
            $id
        );

        return redirect()->to('/admin/notifications')
            ->with('success', 'Notification deleted.');
    }

    /**
     * Handle optional PDF upload after the notification row exists.
     *
     * @return array{path: ?string, error: ?string}
     */
    private function handleDocumentUpload(int $id, ?string $oldPath): array
    {
        $file = $this->request->getFile('document');
        if (! $file || ! $file->isValid() || $file->hasMoved() || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return ['path' => null, 'error' => null];
        }

        $uploader = new UploadService();
        $result   = $uploader->storeNotificationDocument($file, $id);
        if (! $result['ok']) {
            return ['path' => null, 'error' => $result['error'] ?? 'Document upload failed.'];
        }

        model(DesignationNotificationModel::class)->update($id, [
            'document_path' => $result['path'],
        ]);

        if (! empty($oldPath) && $oldPath !== $result['path']) {
            $uploader->deleteIfExists($oldPath);
        }

        return ['path' => $result['path'], 'error' => null];
    }

    /**
     * @return array<string, string>
     */
    private function validationRules(?int $id = null): array
    {
        $unique = $id === null
            ? 'is_unique[designation_notifications.notification_number]'
            : 'is_unique[designation_notifications.notification_number,id,' . $id . ']';

        return [
            'notification_number'    => 'required|min_length[2]|max_length[100]|' . $unique,
            'notification_date'      => 'required|valid_date[Y-m-d]',
            'title'                  => 'permit_empty|max_length[255]',
            'application_start_date' => 'required',
            'application_end_date'   => 'required',
            'edit_window_start_date' => 'permit_empty',
            'edit_window_end_date'   => 'permit_empty',
            'remarks'                => 'permit_empty|max_length[2000]',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromRequest(bool $isUpdate = false): array
    {
        $userId = (int) session()->get('user_id');

        $emptyToNull = static function (?string $v): ?string {
            $v = trim((string) $v);

            return $v === '' ? null : $v;
        };

        $payload = [
            'notification_number'    => trim((string) $this->request->getPost('notification_number')),
            'notification_date'      => trim((string) $this->request->getPost('notification_date')),
            'title'                  => $emptyToNull($this->request->getPost('title')),
            'application_start_date' => DesignationNotificationModel::normalizeDateTime(
                (string) $this->request->getPost('application_start_date')
            ),
            'application_end_date' => DesignationNotificationModel::normalizeDateTime(
                (string) $this->request->getPost('application_end_date')
            ),
            'edit_window_start_date' => DesignationNotificationModel::normalizeDateTime(
                (string) $this->request->getPost('edit_window_start_date')
            ),
            'edit_window_end_date' => DesignationNotificationModel::normalizeDateTime(
                (string) $this->request->getPost('edit_window_end_date')
            ),
            'is_active'  => (bool) $this->request->getPost('is_active'),
            'remarks'    => $emptyToNull($this->request->getPost('remarks')),
            'updated_by' => $userId > 0 ? $userId : null,
        ];

        if (! $isUpdate) {
            // Keep DB default for legacy frequency column (not shown in UI)
            $payload['frequency']  = DesignationNotificationModel::FREQUENCY_YEARLY;
            $payload['created_by'] = $userId > 0 ? $userId : null;
        }

        return $payload;
    }
}
