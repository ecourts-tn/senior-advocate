<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Concerns\ListQuery;
use App\Models\AuditLogModel;
use App\Models\NotificationTemplateModel;

class NotificationTemplateController extends BaseController
{
    use ListQuery;

    public function index()
    {
        $model = model(NotificationTemplateModel::class);
        // Seed defaults only when the table is empty (delete stays permanent otherwise)
        if ((int) $model->countAllResults() === 0) {
            $model->ensureDefaults();
        }

        $filters = $this->listQueryParams();
        $q       = $filters['q'];
        $perPage = $filters['perPage'];

        if ($q !== '') {
            $model->groupStart()
                ->like('name', $q, 'both', null, true)
                ->orLike('event_key', $q, 'both', null, true)
                ->orLike('email_subject', $q, 'both', null, true)
                ->orLike('description', $q, 'both', null, true)
                ->groupEnd();
        }

        $model->orderBy('event_key', 'ASC');

        $rows  = $model->paginate($perPage, 'default', $filters['page']);
        $pager = $model->pager;
        $pager->setPath('admin/notifications');
        $pager->only(['q', 'per_page']);

        return view('admin/notifications/index', [
            'title'          => 'Notification templates',
            'templates'      => $rows,
            'q'              => $q,
            'perPage'        => $perPage,
            'page'           => (int) ($pager->getCurrentPage('default') ?: $filters['page']),
            'total'          => (int) $pager->getTotal('default'),
            'allowedPerPage' => $filters['allowedPerPage'],
            'pager'          => $pager,
            'events'         => NotificationTemplateModel::EVENTS,
        ]);
    }

    public function create()
    {
        $model = model(NotificationTemplateModel::class);

        $available = $this->availableEventKeys($model);
        if ($available === []) {
            return redirect()->to('/admin/notifications')
                ->with('error', 'All portal event templates already exist. Edit an existing one, or delete one to recreate it.');
        }

        return view('admin/notifications/form', [
            'title'        => 'Add notification template',
            'template'     => null,
            'available'    => $available,
            'placeholders' => NotificationTemplateModel::placeholderHelp(),
            'isEdit'       => false,
        ]);
    }

    public function store()
    {
        $model = model(NotificationTemplateModel::class);

        $available = $this->availableEventKeys($model);
        if ($available === []) {
            return redirect()->to('/admin/notifications')
                ->with('error', 'All portal event templates already exist. Edit an existing one instead.');
        }

        $rules = $this->validationRules(null, array_keys($available));
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = $this->payloadFromRequest();
        $id      = $model->insert($payload, true);

        model(AuditLogModel::class)->log(
            'notification_template_created',
            (int) session()->get('user_id'),
            null,
            [
                'id'        => $id,
                'event_key' => $payload['event_key'],
                'name'      => $payload['name'],
            ],
            'notification_template',
            (int) $id
        );

        return redirect()->to('/admin/notifications')
            ->with('success', 'Notification template created.');
    }

    public function edit(int $id)
    {
        $model = model(NotificationTemplateModel::class);
        $row   = $model->find($id);
        if (! $row) {
            return redirect()->to('/admin/notifications')->with('error', 'Template not found.');
        }

        return view('admin/notifications/form', [
            'title'        => 'Edit notification template',
            'template'     => $row,
            'available'    => NotificationTemplateModel::EVENTS,
            'placeholders' => NotificationTemplateModel::placeholderHelp(),
            'isEdit'       => true,
        ]);
    }

    public function update(int $id)
    {
        $model = model(NotificationTemplateModel::class);
        $row   = $model->find($id);
        if (! $row) {
            return redirect()->to('/admin/notifications')->with('error', 'Template not found.');
        }

        $rules = $this->validationRules($id, array_keys(NotificationTemplateModel::EVENTS));
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $payload = $this->payloadFromRequest(true);
        // Keep event_key stable on edit
        $payload['event_key'] = $row['event_key'];
        $model->update($id, $payload);

        model(AuditLogModel::class)->log(
            'notification_template_updated',
            (int) session()->get('user_id'),
            null,
            [
                'id'        => $id,
                'event_key' => $row['event_key'],
                'name'      => $payload['name'],
                'is_active' => $payload['is_active'],
            ],
            'notification_template',
            $id
        );

        return redirect()->to('/admin/notifications')
            ->with('success', 'Notification template updated.');
    }

    public function delete(int $id)
    {
        $model = model(NotificationTemplateModel::class);
        $row   = $model->find($id);
        if (! $row) {
            return redirect()->to('/admin/notifications')->with('error', 'Template not found.');
        }

        $model->delete($id);

        model(AuditLogModel::class)->log(
            'notification_template_deleted',
            (int) session()->get('user_id'),
            null,
            [
                'id'        => $id,
                'event_key' => $row['event_key'],
                'name'      => $row['name'],
            ],
            'notification_template',
            $id
        );

        return redirect()->to('/admin/notifications')
            ->with('success', 'Template deleted. You can re-create it or re-run the seeder for defaults.');
    }

    /**
     * Event keys that do not yet have a template row.
     *
     * @return array<string, string>
     */
    private function availableEventKeys(NotificationTemplateModel $model): array
    {
        $existing = $model->select('event_key')->findAll();
        $keys     = array_column($existing, 'event_key');
        $out      = [];
        foreach (NotificationTemplateModel::EVENTS as $key => $label) {
            if (! in_array($key, $keys, true)) {
                $out[$key] = $label;
            }
        }

        return $out;
    }

    /**
     * @param list<string> $allowedEvents
     *
     * @return array<string, string>
     */
    private function validationRules(?int $id, array $allowedEvents): array
    {
        $eventList = implode(',', $allowedEvents);
        $unique    = $id === null
            ? 'is_unique[notification_templates.event_key]'
            : 'is_unique[notification_templates.event_key,id,' . $id . ']';

        return [
            'event_key'     => 'required|in_list[' . $eventList . ']|' . $unique,
            'name'          => 'required|min_length[2]|max_length[150]',
            'description'   => 'permit_empty|max_length[500]',
            'email_subject' => 'required|max_length[500]',
            'email_body'    => 'permit_empty',
            'sms_body'      => 'permit_empty|max_length[500]',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromRequest(bool $isUpdate = false): array
    {
        $userId = (int) session()->get('user_id');

        $payload = [
            'event_key'     => (string) $this->request->getPost('event_key'),
            'name'          => trim((string) $this->request->getPost('name')),
            'description'   => trim((string) $this->request->getPost('description')) ?: null,
            'email_enabled' => (bool) $this->request->getPost('email_enabled'),
            'sms_enabled'   => (bool) $this->request->getPost('sms_enabled'),
            'email_subject' => trim((string) $this->request->getPost('email_subject')),
            'email_body'    => (string) $this->request->getPost('email_body'),
            'sms_body'      => trim((string) $this->request->getPost('sms_body')) ?: null,
            'is_active'     => (bool) $this->request->getPost('is_active'),
            'updated_by'    => $userId > 0 ? $userId : null,
        ];

        if ($isUpdate) {
            // event_key fixed in update()
            unset($payload['event_key']);
        }

        return $payload;
    }
}
