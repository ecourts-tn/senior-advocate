<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <h1 class="page-title">Application-cum-Consent Letter</h1>
    <p class="page-subtitle">Step 7 of 7 — Uploads, Declaration &amp; Submit</p>
</div>
<?= $this->include('applicant/application/_stepper') ?>

<div class="card card-mhc">
    <div class="card-body">
        <?= form_open_multipart('applicant/application/step/7', [
            'autocomplete'         => 'off',
            'data-prevent-bfcache' => '1',
            'class'                => 'application-step-form',
        ]) ?>

        <div class="section-title">Required uploads (strict validation)</div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="upload-preview-block">
                    <label class="form-label required" for="photoInput">Recent Passport Size Colour Photograph</label>
                    <div class="upload-preview-frame photo-frame<?= ! empty($app['photo_path']) ? ' has-preview' : '' ?>" id="photoPreviewBox">
                        <?php if (! empty($app['photo_path'])): ?>
                            <img src="<?= base_url('files/application/' . $app['id'] . '/photo') ?>"
                                 class="photo-preview" id="photoPreviewImg" alt="Passport photograph preview">
                            <div class="upload-preview-placeholder d-none" id="photoPlaceholder">
                                <i class="bi bi-person-bounding-box" aria-hidden="true"></i>
                                <span>Photo preview</span>
                            </div>
                        <?php else: ?>
                            <img src="" class="photo-preview d-none" id="photoPreviewImg" alt="Passport photograph preview">
                            <div class="upload-preview-placeholder" id="photoPlaceholder">
                                <i class="bi bi-person-bounding-box" aria-hidden="true"></i>
                                <span>Photo preview</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="photo" id="photoInput" class="form-control mt-2"
                           accept=".jpg,.jpeg,image/jpeg"
                           data-preview="#photoPreviewImg"
                           data-placeholder="#photoPlaceholder"
                           data-meta="#photoMeta"
                           data-min-kb="20"
                           data-max-kb="200">
                    <div class="form-text form-help-text">JPG/JPEG only · 20–200 KB</div>
                    <div class="upload-file-meta small" id="photoMeta" aria-live="polite">
                        <?php if (! empty($app['photo_path'])): ?>
                            <span class="text-success"><i class="bi bi-check-circle" aria-hidden="true"></i> Photo on file — choose a new file to replace</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="upload-preview-block">
                    <label class="form-label required" for="signatureInput">Signature</label>
                    <div class="upload-preview-frame sig-frame<?= ! empty($app['signature_path']) ? ' has-preview' : '' ?>" id="signaturePreviewBox">
                        <?php if (! empty($app['signature_path'])): ?>
                            <img src="<?= base_url('files/application/' . $app['id'] . '/signature') ?>"
                                 class="sig-preview" id="signaturePreviewImg" alt="Signature preview">
                            <div class="upload-preview-placeholder d-none" id="signaturePlaceholder">
                                <i class="bi bi-pen" aria-hidden="true"></i>
                                <span>Signature preview</span>
                            </div>
                        <?php else: ?>
                            <img src="" class="sig-preview d-none" id="signaturePreviewImg" alt="Signature preview">
                            <div class="upload-preview-placeholder" id="signaturePlaceholder">
                                <i class="bi bi-pen" aria-hidden="true"></i>
                                <span>Signature preview</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="signature" id="signatureInput" class="form-control mt-2"
                           accept=".jpg,.jpeg,image/jpeg"
                           data-preview="#signaturePreviewImg"
                           data-placeholder="#signaturePlaceholder"
                           data-meta="#signatureMeta"
                           data-min-kb="20"
                           data-max-kb="200">
                    <div class="form-text form-help-text">JPG/JPEG only · 20–200 KB</div>
                    <div class="upload-file-meta small" id="signatureMeta" aria-live="polite">
                        <?php if (! empty($app['signature_path'])): ?>
                            <span class="text-success"><i class="bi bi-check-circle" aria-hidden="true"></i> Signature on file — choose a new file to replace</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label required">Enrolment Certificate</label>
                <input type="file" name="enrolment_cert" class="form-control" accept=".pdf,application/pdf">
                <div class="form-text form-help-text">PDF · less than 5 MB <?= ! empty($app['enrolment_cert_path']) ? '· <span class="text-success">Uploaded</span>' : '' ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label required" for="ageProofInput">Age proof</label>
                <input type="file" name="age_proof" id="ageProofInput" class="form-control" accept=".pdf,application/pdf">
                <div class="form-text form-help-text">
                    PDF · less than 5 MB · Mandatory (e.g. birth certificate / SSLC mark sheet)
                    <?php if (! empty($app['age_proof_path'])): ?>
                        · <span class="text-success">Uploaded</span>
                        · <a href="<?= base_url('files/application/' . $app['id'] . '/age_proof') ?>" target="_blank" rel="noopener">View</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="educationQualInput">Educational qualifications document</label>
                <input type="file" name="education_qual" id="educationQualInput" class="form-control" accept=".pdf,application/pdf">
                <div class="form-text form-help-text">
                    PDF · less than 5 MB · Optional (supporting certificates / mark sheets)
                    <?php if (! empty($app['education_qual_path'])): ?>
                        · <span class="text-success">Uploaded</span>
                        · <a href="<?= base_url('files/application/' . $app['id'] . '/education_qual') ?>" target="_blank" rel="noopener">View</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Format L-1 (Reported Judgments)</label>
                <input type="file" name="format_l1" class="form-control" accept=".pdf,application/pdf">
                <div class="form-text form-help-text">PDF · less than 5 MB <?= ! empty($app['format_l1_path']) ? '· <span class="text-success">Uploaded</span>' : '' ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Format L-2 (Unreported Judgments)</label>
                <input type="file" name="format_l2" class="form-control" accept=".pdf,application/pdf">
                <div class="form-text form-help-text">PDF · less than 5 MB <?= ! empty($app['format_l2_path']) ? '· <span class="text-success">Uploaded</span>' : '' ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Format L-3(i) Pro Bono</label>
                <input type="file" name="format_l3i" class="form-control" accept=".pdf,application/pdf">
                <div class="form-text form-help-text">PDF · less than 5 MB <?= ! empty($app['format_l3i_path']) ? '· <span class="text-success">Uploaded</span>' : '' ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Format L-3(ii) Amicus Curiae</label>
                <input type="file" name="format_l3ii" class="form-control" accept=".pdf,application/pdf">
                <div class="form-text form-help-text">PDF · less than 5 MB <?= ! empty($app['format_l3ii_path']) ? '· <span class="text-success">Uploaded</span>' : '' ?></div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Format L-4 Academic</label>
                <input type="file" name="format_l4" class="form-control" accept=".pdf,application/pdf">
                <div class="form-text form-help-text">PDF · less than 5 MB <?= ! empty($app['format_l4_path']) ? '· <span class="text-success">Uploaded</span>' : '' ?></div>
            </div>
        </div>

        <div class="section-title mt-4">Declaration</div>
        <div class="declaration-box mb-3">
            <p>I
                <input type="text" name="declaration_name" class="form-control d-inline-block w-auto mx-1"
                       value="<?= esc($app['declaration_name'] ?? $app['full_name'] ?? '') ?>" required>
                hereby give consent for being designated as Senior Advocate.
            </p>
            <p class="small">
                I hereby declare that the information furnished above is true and correct to the best of my knowledge and belief.
                No material information is concealed or suppressed there from. I understand that furnishing of false information
                or suppression of any factual information would render me unfit from being designated as Senior Advocate.
            </p>
            <p class="small mb-0">
                I undertake that if my application is accepted, I will strictly adhere to the code of conduct applicable under the
                Advocates Act and Bar Council Act, as well as these Rules, and shall not do any act which directly or indirectly
                violates any of the above, either in letter or in spirit.
            </p>
        </div>

        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="declaration_accepted" value="1" id="decl"
                   data-reset-on-load="1" required>
            <label class="form-check-label" for="decl">I accept the above declaration.</label>
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="instructions_accepted" value="1" id="instr"
                   data-reset-on-load="1" required>
            <label class="form-check-label" for="instr">
                Yes, I have read the instructions carefully and understand that an error in this application cannot be
                subsequently rectified and may result in rejection of my application.
            </label>
        </div>

        <?= $this->include('applicant/application/_form_nav') ?>
        <?= form_close() ?>
    </div>
</div>

<?= $this->endSection() ?>
