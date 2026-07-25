<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title">Application-cum-Consent Letter</h1>
    <p class="page-subtitle">Step 5 of 7 — Practice Domain (Sl. No. 14–17)</p>
</div>
<?= $this->include('applicant/application/_stepper') ?>

<div class="card card-mhc">
    <div class="card-body">
        <?= form_open('applicant/application/step/5') ?>

        <div class="section-title">14. Courts where the applicant is practicing / has practiced</div>
        <div class="d-flex justify-content-end mb-2">
            <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="#courtRows">+ Add court</button>
        </div>
        <div id="courtRows" data-rows>
            <?php
            $courts = ! empty($app['courts_practiced']) ? $app['courts_practiced'] : [
                ['court' => 'Supreme Court of India', 'from' => '', 'to' => ''],
                ['court' => 'High Court(s) / District / Trial Court(s)', 'from' => '', 'to' => ''],
            ];
            foreach ($courts as $i => $c):
            ?>
                <div class="row g-2 mb-2 dynamic-row" <?= $i === 0 ? 'data-row-template' : '' ?>>
                    <div class="col-md-6">
                        <input type="text" name="court_name[]" class="form-control" placeholder="Court" value="<?= esc($c['court'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="court_from[]" class="form-control" placeholder="From" value="<?= esc($c['from'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="court_to[]" class="form-control" placeholder="To" value="<?= esc($c['to'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger w-100" data-remove-row>&times;</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="section-title mt-4">15. Tribunals — specialized practice</div>
        <div class="d-flex justify-content-end mb-2">
            <button type="button" class="btn btn-sm btn-outline-primary" data-add-row="#tribRows">+ Add tribunal</button>
        </div>
        <div id="tribRows" data-rows>
            <?php
            $tribunals = ! empty($app['tribunals_practiced']) ? $app['tribunals_practiced'] : [['tribunal' => '', 'from' => '', 'to' => '']];
            foreach ($tribunals as $i => $t):
            ?>
                <div class="row g-2 mb-2 dynamic-row" <?= $i === 0 ? 'data-row-template' : '' ?>>
                    <div class="col-md-6">
                        <input type="text" name="tribunal_name[]" class="form-control" placeholder="Tribunal" value="<?= esc($t['tribunal'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="tribunal_from[]" class="form-control" placeholder="From" value="<?= esc($t['from'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="tribunal_to[]" class="form-control" placeholder="To" value="<?= esc($t['to'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger w-100" data-remove-row>&times;</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="section-title mt-4">16. Nature of practice</div>
        <textarea name="nature_of_practice" class="form-control" rows="3"
                  placeholder="e.g. Civil, Criminal, Constitutional, Taxation, Labour, Company, Service, etc."><?= esc($app['nature_of_practice'] ?? '') ?></textarea>

        <div class="section-title mt-4">17. Field of Law — domain expertise</div>
        <textarea name="field_of_law" class="form-control" rows="3"
                  placeholder="e.g. Constitutional law, Criminal law, Arbitration, Corporate law, Family law, Human Rights, PIL, International law, law relating to women, etc."><?= esc($app['field_of_law'] ?? '') ?></textarea>

        <?= $this->include('applicant/application/_form_nav') ?>
        <?= form_close() ?>
    </div>
</div>

<?= $this->endSection() ?>
