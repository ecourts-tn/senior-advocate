<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ApplicationSettingModel;

class ApplicationSettingsController extends BaseController
{
    protected ApplicationSettingModel $settingsModel;

    public function __construct()
    {
        $this->settingsModel = model(ApplicationSettingModel::class);
    }

    /**
     * Display application settings.
     */
    public function index()
    {
        $cycleYear = (int) date('Y');

        $settings = $this->settingsModel
            ->where('cycle_year', $cycleYear)
            ->first();

        /*
         * If settings do not exist for the current cycle,
         * provide default values for the form.
         */
        if (!$settings) {
            $settings = [
                'cycle_year'             => $cycleYear,
                'application_start_date' => '',
                'application_last_date'  => '',
                'is_active'              => false,
            ];
        }

        return view('admin/settings/application_setting', [
            'title'    => 'Application Settings',
            'settings' => $settings,
        ]);
    }

    /**
     * Save application settings.
     */
    public function save()
    {
        $cycleYear = (int) $this->request->getPost('cycle_year');

        $startDate = trim(
            (string) $this->request->getPost('application_start_date')
        );

        $lastDate = trim(
            (string) $this->request->getPost('application_last_date')
        );

        $isActive = $this->request->getPost('is_active') === '1';

        /*
         * Validation rules.
         */
        $rules = [
            'cycle_year' => [
                'label' => 'Cycle year',
                'rules' => 'required|integer|greater_than_equal_to[2000]|less_than_equal_to[2100]',
            ],
            'application_start_date' => [
                'label' => 'Application start date',
                'rules' => 'required|valid_date[Y-m-d]',
            ],
            'application_last_date' => [
                'label' => 'Application last date',
                'rules' => 'required|valid_date[Y-m-d]',
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()
                ->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        /*
         * Make sure the last date is not before
         * the application start date.
         */
        if ($lastDate < $startDate) {
            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Application last date cannot be earlier than the application start date.'
                );
        }

        /*
         * Check whether settings already exist
         * for this cycle.
         */
        $existing = $this->settingsModel
            ->where('cycle_year', $cycleYear)
            ->first();

        $data = [
            'cycle_year'             => $cycleYear,
            'application_start_date' => $startDate,
            'application_last_date'  => $lastDate,
            'is_active'              => $isActive,
        ];

        if ($existing) {

            $this->settingsModel->update(
                $existing['id'],
                $data
            );

            $message = 'Application settings updated successfully.';

        } else {

            $this->settingsModel->insert($data);

            $message = 'Application settings created successfully.';
        }

        return redirect()
            ->to('/admin/application-settings')
            ->with('success', $message);
    }
}

