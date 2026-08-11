<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Concerns\ListQuery;
use App\Models\AuditLogModel;
use App\Models\MasterRegistry;

/**
 * Admin Master Management — separate CRUD against individual master tables.
 */
class MasterController extends BaseController
{
    use ListQuery;

    /** @var array<string, array{label: string, icon: string, description: string}> */
    public const MASTERS = [
        'qualification' => [
            'label'       => 'Educational qualifications',
            'icon'        => 'bi-mortarboard',
            'description' => 'Degrees and professional qualifications (application Step 1).',
            'table'       => 'master_qualifications',
        ],
        'court' => [
            'label'       => 'Courts',
            'icon'        => 'bi-building',
            'description' => 'Courts practiced (Sl. No. 14).',
            'table'       => 'master_courts',
        ],
        'tribunal' => [
            'label'       => 'Tribunals',
            'icon'        => 'bi-bank',
            'description' => 'Tribunals with specialized practice (Sl. No. 15).',
            'table'       => 'master_tribunals',
        ],
        'nature_of_practice' => [
            'label'       => 'Nature of practice',
            'icon'        => 'bi-briefcase',
            'description' => 'Nature of practice options (Sl. No. 16).',
            'table'       => 'master_nature_of_practice',
        ],
        'field_of_law' => [
            'label'       => 'Field of law',
            'icon'        => 'bi-journal-bookmark',
            'description' => 'Domain expertise / field of law (Sl. No. 17).',
            'table'       => 'master_fields_of_law',
        ],
    ];

    public function hub()
    {
        MasterRegistry::ensureAllDefaults();

        $cards = [];
        foreach (self::MASTERS as $key => $meta) {
            $model  = MasterRegistry::model($key);
            $total  = (int) $model->countAllResults();
            $active = (int) model(MasterRegistry::modelClass($key))
                ->where('is_active', true)
                ->countAllResults();
            $cards[$key] = array_merge($meta, [
                'key'    => $key,
                'total'  => $total,
                'active' => $active,
            ]);
        }

        return view('admin/masters/hub', [
            'title' => 'Master management',
            'cards' => $cards,
        ]);
    }

    public function index(string $category)
    {
        $meta  = $this->requireMaster($category);
        $model = MasterRegistry::model($category);
        $model->ensureDefaults();

        $filters = $this->listQueryParams();
        $q       = $filters['q'];
        $perPage = $filters['perPage'];

        if ($q !== '') {
            $model->like('label', $q, 'both', null, true);
        }
        $model->orderBy('sort_order', 'ASC')->orderBy('label', 'ASC');

        $rows  = $model->paginate($perPage, 'default', $filters['page']);
        $pager = $model->pager;
        $pager->setPath('admin/masters/' . $category);
        $pager->only(['q', 'per_page']);

        return view('admin/masters/index', [
            'title'          => $meta['label'],
            'meta'           => $meta,
            'category'       => $category,
            'masters'        => self::MASTERS,
            'options'        => $rows,
            'q'              => $q,
            'perPage'        => $perPage,
            'page'           => (int) ($pager->getCurrentPage('default') ?: $filters['page']),
            'total'          => (int) $pager->getTotal('default'),
            'allowedPerPage' => $filters['allowedPerPage'],
            'pager'          => $pager,
        ]);
    }

    public function create(string $category)
    {
        $meta = $this->requireMaster($category);

        return view('admin/masters/form', [
            'title'    => 'Add — ' . $meta['label'],
            'meta'     => $meta,
            'category' => $category,
            'masters'  => self::MASTERS,
            'option'   => null,
            'isEdit'   => false,
        ]);
    }

    public function store(string $category)
    {
        $meta  = $this->requireMaster($category);
        $model = MasterRegistry::model($category);

        $rules = [
            'label'      => 'required|min_length[1]|max_length[255]',
            'sort_order' => 'permit_empty|integer',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $label = trim((string) $this->request->getPost('label'));
        if (strcasecmp($label, MasterRegistry::OTHERS_LABEL) === 0) {
            return redirect()->back()->withInput()
                ->with('error', '"Others" is built into every dropdown automatically — do not add it as a master value.');
        }

        if ($model->where('label', $label)->first()) {
            return redirect()->back()->withInput()
                ->with('error', 'That value already exists in this master list.');
        }

        $userId = (int) session()->get('user_id');
        $id     = $model->insert([
            'label'      => $label,
            'sort_order' => (int) ($this->request->getPost('sort_order') ?? 0),
            'is_active'  => (bool) $this->request->getPost('is_active'),
            'updated_by' => $userId > 0 ? $userId : null,
        ], true);

        model(AuditLogModel::class)->log(
            'master_created',
            $userId,
            null,
            ['id' => $id, 'master' => $category, 'table' => $meta['table'], 'label' => $label],
            $meta['table'],
            (int) $id
        );

        return redirect()->to('/admin/masters/' . $category)
            ->with('success', 'Master value added.');
    }

    public function edit(string $category, int $id)
    {
        $meta  = $this->requireMaster($category);
        $model = MasterRegistry::model($category);
        $row   = $model->find($id);
        if (! $row) {
            return redirect()->to('/admin/masters/' . $category)->with('error', 'Master value not found.');
        }

        return view('admin/masters/form', [
            'title'    => 'Edit — ' . $meta['label'],
            'meta'     => $meta,
            'category' => $category,
            'masters'  => self::MASTERS,
            'option'   => $row,
            'isEdit'   => true,
        ]);
    }

    public function update(string $category, int $id)
    {
        $meta  = $this->requireMaster($category);
        $model = MasterRegistry::model($category);
        $row   = $model->find($id);
        if (! $row) {
            return redirect()->to('/admin/masters/' . $category)->with('error', 'Master value not found.');
        }

        $rules = [
            'label'      => 'required|min_length[1]|max_length[255]',
            'sort_order' => 'permit_empty|integer',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $label = trim((string) $this->request->getPost('label'));
        if (strcasecmp($label, MasterRegistry::OTHERS_LABEL) === 0) {
            return redirect()->back()->withInput()
                ->with('error', '"Others" is built into every dropdown automatically — do not add it as a master value.');
        }

        $dup = $model->where('label', $label)->where('id !=', $id)->first();
        if ($dup) {
            return redirect()->back()->withInput()
                ->with('error', 'That value already exists in this master list.');
        }

        $userId = (int) session()->get('user_id');
        $model->update($id, [
            'label'      => $label,
            'sort_order' => (int) ($this->request->getPost('sort_order') ?? 0),
            'is_active'  => (bool) $this->request->getPost('is_active'),
            'updated_by' => $userId > 0 ? $userId : null,
        ]);

        model(AuditLogModel::class)->log(
            'master_updated',
            $userId,
            null,
            ['id' => $id, 'master' => $category, 'table' => $meta['table'], 'label' => $label],
            $meta['table'],
            $id
        );

        return redirect()->to('/admin/masters/' . $category)
            ->with('success', 'Master value updated.');
    }

    public function delete(string $category, int $id)
    {
        $meta  = $this->requireMaster($category);
        $model = MasterRegistry::model($category);
        $row   = $model->find($id);
        if (! $row) {
            return redirect()->to('/admin/masters/' . $category)->with('error', 'Master value not found.');
        }

        $model->delete($id);

        model(AuditLogModel::class)->log(
            'master_deleted',
            (int) session()->get('user_id'),
            null,
            ['id' => $id, 'master' => $category, 'table' => $meta['table'], 'label' => $row['label']],
            $meta['table'],
            $id
        );

        return redirect()->to('/admin/masters/' . $category)
            ->with('success', 'Master value deleted.');
    }

    public function seedDefaults(?string $category = null)
    {
        if ($category !== null && $category !== '') {
            $this->requireMaster($category);
            MasterRegistry::model($category)->ensureDefaults();
        } else {
            MasterRegistry::ensureAllDefaults();
        }

        model(AuditLogModel::class)->log(
            'master_seeded',
            (int) session()->get('user_id'),
            null,
            ['master' => $category]
        );

        $to = $category ? '/admin/masters/' . $category : '/admin/masters';

        return redirect()->to($to)
            ->with('success', 'Default master values ensured (existing rows left unchanged).');
    }

    /**
     * @return array{label: string, icon: string, description: string, table: string}
     */
    private function requireMaster(string $category): array
    {
        if (! array_key_exists($category, self::MASTERS) || MasterRegistry::modelClass($category) === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Unknown master type.');
        }

        return self::MASTERS[$category];
    }
}
