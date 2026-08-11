<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Controllers\Concerns\ListQuery;
use App\Models\AuditLogModel;
use App\Models\FormLookupOptionModel;

/**
 * Admin CRUD for application form dropdown options.
 */
class FormLookupController extends BaseController
{
    use ListQuery;

    public function index()
    {
        $model = model(FormLookupOptionModel::class);
        if ((int) $model->countAllResults() === 0) {
            $model->ensureDefaults();
        }

        $filters  = $this->listQueryParams();
        $q        = $filters['q'];
        $perPage  = $filters['perPage'];
        $category = trim((string) ($this->request->getGet('category') ?? ''));
        if ($category !== '' && ! array_key_exists($category, FormLookupOptionModel::CATEGORIES)) {
            $category = '';
        }

        if ($category !== '') {
            $model->where('category', $category);
        }
        if ($q !== '') {
            $model->groupStart()
                ->like('label', $q, 'both', null, true)
                ->orLike('category', $q, 'both', null, true)
                ->groupEnd();
        }

        $model->orderBy('category', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('label', 'ASC');

        $rows  = $model->paginate($perPage, 'default', $filters['page']);
        $pager = $model->pager;
        $pager->setPath('admin/lookups');
        $pager->only(['q', 'per_page', 'category']);

        return view('admin/lookups/index', [
            'title'          => 'Form dropdown options',
            'options'        => $rows,
            'q'              => $q,
            'category'       => $category,
            'categories'     => FormLookupOptionModel::CATEGORIES,
            'perPage'        => $perPage,
            'page'           => (int) ($pager->getCurrentPage('default') ?: $filters['page']),
            'total'          => (int) $pager->getTotal('default'),
            'allowedPerPage' => $filters['allowedPerPage'],
            'pager'          => $pager,
            'hasActiveFilters' => $category !== '',
        ]);
    }

    public function create()
    {
        $category = trim((string) ($this->request->getGet('category') ?? ''));
        if ($category !== '' && ! array_key_exists($category, FormLookupOptionModel::CATEGORIES)) {
            $category = '';
        }

        return view('admin/lookups/form', [
            'title'      => 'Add dropdown option',
            'option'     => null,
            'categories' => FormLookupOptionModel::CATEGORIES,
            'category'   => $category,
            'isEdit'     => false,
        ]);
    }

    public function store()
    {
        $rules = [
            'category'   => 'required|in_list[' . implode(',', array_keys(FormLookupOptionModel::CATEGORIES)) . ']',
            'label'      => 'required|min_length[1]|max_length[255]',
            'sort_order' => 'permit_empty|integer',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $label    = trim((string) $this->request->getPost('label'));
        $category = (string) $this->request->getPost('category');

        if (strcasecmp($label, FormLookupOptionModel::OTHERS_LABEL) === 0) {
            return redirect()->back()->withInput()
                ->with('error', '"Others" is built into every dropdown automatically — do not add it as an option.');
        }

        $model  = model(FormLookupOptionModel::class);
        $exists = $model->where('category', $category)->where('label', $label)->first();
        if ($exists) {
            return redirect()->back()->withInput()
                ->with('error', 'That option already exists in this category.');
        }

        $userId = (int) session()->get('user_id');
        $id     = $model->insert([
            'category'   => $category,
            'label'      => $label,
            'sort_order' => (int) ($this->request->getPost('sort_order') ?? 0),
            'is_active'  => (bool) $this->request->getPost('is_active'),
            'updated_by' => $userId > 0 ? $userId : null,
        ], true);

        model(AuditLogModel::class)->log(
            'form_lookup_created',
            $userId,
            null,
            ['id' => $id, 'category' => $category, 'label' => $label],
            'form_lookup_option',
            (int) $id
        );

        return redirect()->to('/admin/lookups?category=' . rawurlencode($category))
            ->with('success', 'Dropdown option added.');
    }

    public function edit(int $id)
    {
        $model = model(FormLookupOptionModel::class);
        $row   = $model->find($id);
        if (! $row) {
            return redirect()->to('/admin/lookups')->with('error', 'Option not found.');
        }

        return view('admin/lookups/form', [
            'title'      => 'Edit dropdown option',
            'option'     => $row,
            'categories' => FormLookupOptionModel::CATEGORIES,
            'category'   => $row['category'],
            'isEdit'     => true,
        ]);
    }

    public function update(int $id)
    {
        $model = model(FormLookupOptionModel::class);
        $row   = $model->find($id);
        if (! $row) {
            return redirect()->to('/admin/lookups')->with('error', 'Option not found.');
        }

        $rules = [
            'category'   => 'required|in_list[' . implode(',', array_keys(FormLookupOptionModel::CATEGORIES)) . ']',
            'label'      => 'required|min_length[1]|max_length[255]',
            'sort_order' => 'permit_empty|integer',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $label    = trim((string) $this->request->getPost('label'));
        $category = (string) $this->request->getPost('category');

        if (strcasecmp($label, FormLookupOptionModel::OTHERS_LABEL) === 0) {
            return redirect()->back()->withInput()
                ->with('error', '"Others" is built into every dropdown automatically — do not add it as an option.');
        }

        $dup = $model->where('category', $category)
            ->where('label', $label)
            ->where('id !=', $id)
            ->first();
        if ($dup) {
            return redirect()->back()->withInput()
                ->with('error', 'That option already exists in this category.');
        }

        $userId = (int) session()->get('user_id');
        $model->update($id, [
            'category'   => $category,
            'label'      => $label,
            'sort_order' => (int) ($this->request->getPost('sort_order') ?? 0),
            'is_active'  => (bool) $this->request->getPost('is_active'),
            'updated_by' => $userId > 0 ? $userId : null,
        ]);

        model(AuditLogModel::class)->log(
            'form_lookup_updated',
            $userId,
            null,
            ['id' => $id, 'category' => $category, 'label' => $label],
            'form_lookup_option',
            $id
        );

        return redirect()->to('/admin/lookups?category=' . rawurlencode($category))
            ->with('success', 'Dropdown option updated.');
    }

    public function delete(int $id)
    {
        $model = model(FormLookupOptionModel::class);
        $row   = $model->find($id);
        if (! $row) {
            return redirect()->to('/admin/lookups')->with('error', 'Option not found.');
        }

        $model->delete($id);

        model(AuditLogModel::class)->log(
            'form_lookup_deleted',
            (int) session()->get('user_id'),
            null,
            ['id' => $id, 'category' => $row['category'], 'label' => $row['label']],
            'form_lookup_option',
            $id
        );

        return redirect()->to('/admin/lookups?category=' . rawurlencode((string) $row['category']))
            ->with('success', 'Dropdown option deleted.');
    }

    /**
     * Re-seed missing default labels without overwriting admin edits.
     */
    public function seedDefaults()
    {
        model(FormLookupOptionModel::class)->ensureDefaults();

        model(AuditLogModel::class)->log(
            'form_lookup_seeded',
            (int) session()->get('user_id'),
            null,
            []
        );

        return redirect()->to('/admin/lookups')
            ->with('success', 'Default options ensured (existing labels left unchanged).');
    }
}
