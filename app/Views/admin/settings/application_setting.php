```php
<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Application Settings</h1>
            <p class="text-muted mb-0">
                Configure the application submission period for the designation cycle.
            </p>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i>
            <?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                <?php foreach ((array) session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>


    <div class="row">

        <!-- Settings Form -->
        <div class="col-lg-8">

            <div class="card card-mhc mb-3">

                <div class="card-header">
                    <h5 class="mb-0">
                        Application Submission Period
                    </h5>
                </div>

                <div class="card-body">

                    <?= form_open('/admin/application-settings/save') ?>

                    <p class="small text-muted mb-4">
                        Configure the dates during which applicants can submit
                        applications for the designation cycle.
                    </p>

                    <div class="row g-3">

                        <!-- Cycle Year -->
                        <div class="col-md-6">

                            <label class="form-label" for="cycle_year">
                                Cycle Year
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="number"
                                name="cycle_year"
                                id="cycle_year"
                                class="form-control"
                                min="2000"
                                max="2100"
                                value="<?= esc(
                                    old(
                                        'cycle_year',
                                        $settings['cycle_year'] ?? date('Y')
                                    )
                                ) ?>"
                                required
                            >

                            <div class="form-text">
                                Designation cycle year.
                            </div>

                        </div>


                        <!-- Start Date -->
                        <div class="col-md-6">

                            <label
                                class="form-label"
                                for="application_start_date"
                            >
                                Application Start Date
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="date"
                                name="application_start_date"
                                id="application_start_date"
                                class="form-control"
                                value="<?= esc(
                                    old(
                                        'application_start_date',
                                        $settings['application_start_date'] ?? ''
                                    )
                                ) ?>"
                                required
                            >

                            <div class="form-text">
                                Applicants can start new applications from this date.
                            </div>

                        </div>


                        <!-- Last Date -->
                        <div class="col-md-6">

                            <label
                                class="form-label"
                                for="application_last_date"
                            >
                                Last Date for Application
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="date"
                                name="application_last_date"
                                id="application_last_date"
                                class="form-control"
                                value="<?= esc(
                                    old(
                                        'application_last_date',
                                        $settings['application_last_date'] ?? ''
                                    )
                                ) ?>"
                                required
                            >

                            <div class="form-text">
                                Applicants cannot submit applications after this date.
                            </div>

                        </div>


                        <!-- Active -->
                        <div class="col-md-6">

                            <label class="form-label d-block">
                                Application Status
                            </label>

                            <div class="form-check form-switch mt-2">

                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    name="is_active"
                                    value="1"
                                    id="applicationActive"
                                    <?= ! empty($settings['is_active'])
                                        && $settings['is_active'] !== '0'
                                        ? 'checked'
                                        : '' ?>
                                >

                                <label
                                    class="form-check-label"
                                    for="applicationActive"
                                >
                                    Enable application submission
                                </label>

                            </div>

                            <div class="form-text">
                                Disable this option to manually close applications.
                            </div>

                        </div>

                    </div>


                    <hr class="my-4">


                    <h2 class="h6 text-uppercase text-muted mb-3">
                        Submission Rules
                    </h2>

                    <div class="alert alert-info small mb-0">

                        <div class="d-flex">

                            <i class="bi bi-info-circle me-2"></i>

                            <div>
                                <strong>Application deadline</strong>

                                <p class="mb-0 mt-1">
                                    The application period is controlled by the
                                    administrator. The system checks the configured
                                    dates on the server when an applicant starts
                                    an application and again when the applicant
                                    submits the final application.
                                </p>
                            </div>

                        </div>

                    </div>


                    <div class="mt-4">

                        <button
                            type="submit"
                            class="btn btn-mhc"
                        >
                            <i class="bi bi-save me-1"></i>
                            Save Settings
                        </button>

                    </div>

                    <?= form_close() ?>

                </div>
            </div>

        </div>


        <!-- Current Status -->
        <div class="col-lg-4">

            <?php
            $today = date('Y-m-d');

            $startDate = $settings['application_start_date'] ?? null;
            $lastDate  = $settings['application_last_date'] ?? null;

            $isActive = ! empty($settings['is_active'])
                && $settings['is_active'] !== '0';

            $applicationOpen =
                $isActive
                && ! empty($startDate)
                && ! empty($lastDate)
                && $today >= $startDate
                && $today <= $lastDate;

            $applicationNotStarted =
                $isActive
                && ! empty($startDate)
                && $today < $startDate;
            ?>


            <div class="card card-mhc mb-3">

                <div class="card-header">
                    Current Status
                </div>

                <div class="card-body">

                    <?php if ($applicationOpen): ?>

                        <span class="badge bg-success mb-3">
                            <i class="bi bi-check-circle me-1"></i>
                            Application is OPEN
                        </span>

                    <?php elseif ($applicationNotStarted): ?>

                        <span class="badge bg-warning text-dark mb-3">
                            <i class="bi bi-clock me-1"></i>
                            Application NOT YET OPEN
                        </span>

                    <?php else: ?>

                        <span class="badge bg-secondary mb-3">
                            <i class="bi bi-lock me-1"></i>
                            Application is CLOSED
                        </span>

                    <?php endif; ?>


                    <ul class="small mb-0 ps-3">

                        <li class="mb-2">
                            Cycle Year:
                            <strong>
                                <?= esc($settings['cycle_year'] ?? '—') ?>
                            </strong>
                        </li>

                        <li class="mb-2">
                            Start Date:
                            <strong>
                                <?= ! empty($startDate)
                                    ? esc(date('d-m-Y', strtotime($startDate)))
                                    : '—' ?>
                            </strong>
                        </li>

                        <li class="mb-2">
                            Last Date:
                            <strong>
                                <?= ! empty($lastDate)
                                    ? esc(date('d-m-Y', strtotime($lastDate)))
                                    : '—' ?>
                            </strong>
                        </li>

                        <li>
                            Enabled:
                            <strong>
                                <?= $isActive ? 'Yes' : 'No' ?>
                            </strong>
                        </li>

                    </ul>

                </div>
            </div>


            <div class="warning-box">

                <strong class="d-block mb-2">
                    <i class="bi bi-shield-check me-1"></i>
                    How it works
                </strong>

                <p class="small mb-2">
                    Applicants can start a new application only when the
                    current date falls between the configured start date
                    and last date.
                </p>

                <p class="small mb-2">
                    The final submission also checks the deadline on the
                    server.
                </p>

                <p class="small mb-0">
                    Therefore, changing the last date here immediately
                    affects application submission.
                </p>

            </div>

        </div>

    </div>

</div>

<?= $this->endSection() ?>
```
