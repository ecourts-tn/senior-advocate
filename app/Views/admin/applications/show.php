<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <h2 class="h4 mb-1"><?= esc($app['application_no'] ?? 'Application #' . $app['id']) ?></h2>
        <?= sad_status_badge($app['status']) ?>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/applications/' . $app['id'] . '/pdf') ?>" class="btn btn-outline-danger" target="_blank">PDF</a>
        <a href="<?= base_url('admin/applications') ?>" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card card-mhc mb-3">
            <div class="card-header">Applicant details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <strong>Name:</strong> <?= esc(trim(($app['title'] ?? '') . ' ' . ($app['full_name'] ?? ''))) ?><br>
                        <strong>DOB / Age:</strong> <?= esc($app['date_of_birth'] ?? '—') ?> / <?= esc($app['age_years'] ?? '—') ?> yrs<br>
                        <strong>Email / Mobile:</strong> <?= esc($app['email'] ?? '') ?> / <?= esc($app['mobile'] ?? '') ?><br>
                        <strong>Enrolment:</strong> <?= esc($app['enrolment_number'] ?? '—') ?> (<?= esc($app['enrolment_date'] ?? '') ?>)<br>
                        <strong>Bar Council:</strong> <?= esc($app['bar_council'] ?? '—') ?><br>
                        <strong>Practice:</strong> <?= (int) ($app['practice_years'] ?? 0) ?>y <?= (int) ($app['practice_months'] ?? 0) ?>m · Income ₹<?= esc($app['net_income_lakhs'] ?? '—') ?> L<br>
                        <strong>Bar Assoc.:</strong> <?= sad_bool_label($app['is_bar_association_member'] ?? null) ?>
                        <?= ! empty($app['bar_association_name']) ? ' — ' . esc($app['bar_association_name']) : '' ?>
                    </div>
                    <div class="col-md-4">
                        <div class="review-identity">
                            <div class="review-identity-item">
                                <span class="review-label">Photograph</span>
                                <?php if (! empty($app['photo_path'])): ?>
                                    <a href="<?= base_url('files/application/' . $app['id'] . '/photo') ?>" target="_blank" rel="noopener">
                                        <img src="<?= base_url('files/application/' . $app['id'] . '/photo') ?>"
                                             class="photo-preview" width="120" height="150"
                                             alt="Passport photograph of <?= esc(trim(($app['title'] ?? '') . ' ' . ($app['full_name'] ?? 'applicant'))) ?>">
                                    </a>
                                <?php else: ?>
                                    <div class="photo-preview d-flex align-items-center justify-content-center text-muted small">No photo</div>
                                <?php endif; ?>
                            </div>
                            <div class="review-identity-item">
                                <span class="review-label">Signature</span>
                                <?php if (! empty($app['signature_path'])): ?>
                                    <a href="<?= base_url('files/application/' . $app['id'] . '/signature') ?>" target="_blank" rel="noopener">
                                        <img src="<?= base_url('files/application/' . $app['id'] . '/signature') ?>"
                                             class="sig-preview" width="180" height="64"
                                             alt="Signature of applicant">
                                    </a>
                                <?php else: ?>
                                    <div class="sig-preview d-flex align-items-center justify-content-center text-muted small">No signature</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6"><strong>Office:</strong><br><?= nl2br(esc($app['address_office'] ?? '')) ?></div>
                    <div class="col-md-6"><strong>Residence:</strong><br><?= nl2br(esc($app['address_residence'] ?? '')) ?></div>
                    <div class="col-12"><strong>Qualifications:</strong><br><?= nl2br(esc($app['qualifications'] ?? '')) ?></div>
                    <div class="col-md-6"><strong>Reported judgments:</strong> SC <?= (int) $app['reported_sc'] ?>, HC <?= (int) $app['reported_hc'] ?>, Dist <?= (int) $app['reported_district'] ?></div>
                    <div class="col-md-6"><strong>Unreported:</strong> SC <?= (int) $app['unreported_sc'] ?>, HC <?= (int) $app['unreported_hc'] ?>, Dist <?= (int) $app['unreported_district'] ?></div>
                    <div class="col-md-6"><strong>Pro Bono / Amicus:</strong> <?= (int) $app['pro_bono_total'] ?> / <?= (int) $app['amicus_total'] ?></div>
                    <div class="col-md-6"><strong>First generation:</strong> <?= sad_bool_label($app['is_first_generation'] ?? null) ?></div>
                    <div class="col-md-6"><strong>Nature of practice:</strong><br><?= nl2br(esc($app['nature_of_practice'] ?? '—')) ?></div>
                    <div class="col-md-6"><strong>Field of law:</strong><br><?= nl2br(esc($app['field_of_law'] ?? '—')) ?></div>
                    <div class="col-md-6"><strong>FIR:</strong> <?= sad_bool_label($app['fir_lodged'] ?? null) ?> <?= esc($app['fir_details'] ?? '') ?></div>
                    <div class="col-md-6"><strong>Criminal case:</strong> <?= sad_bool_label($app['criminal_case_party'] ?? null) ?> <?= esc($app['criminal_case_details'] ?? '') ?></div>
                    <div class="col-md-6"><strong>Bar Council proceedings:</strong> <?= sad_bool_label($app['bar_council_proceedings'] ?? null) ?> <?= esc($app['bar_council_details'] ?? '') ?></div>
                    <div class="col-md-6"><strong>Health:</strong> <?= esc($app['general_health'] ?? '—') ?></div>
                </div>
            </div>
        </div>

        <div class="card card-mhc mb-3">
            <div class="card-header">Attached documents</div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <?php
                    $docs = [
                        'enrolment_cert' => 'Enrolment Certificate',
                        'format_l1' => 'L-1',
                        'format_l2' => 'L-2',
                        'format_l3i' => 'L-3(i)',
                        'format_l3ii' => 'L-3(ii)',
                        'format_l4' => 'L-4',
                    ];
                    foreach ($docs as $k => $lab):
                        $col = $k . '_path';
                        if ($k === 'enrolment_cert') {
                            $col = 'enrolment_cert_path';
                        }
                    ?>
                        <?php if (! empty($app[$col])): ?>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('admin/applications/' . $app['id'] . '/file/' . $k) ?>"><?= esc($lab) ?></a>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border"><?= esc($lab) ?>: missing</span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if (! empty($l1)): ?>
        <div class="card card-mhc mb-3">
            <div class="card-header">Format L-1 entries</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>#</th><th>Court</th><th>Case / Citation</th><th>Cause Title</th><th>Decided</th></tr></thead>
                    <tbody>
                    <?php foreach ($l1 as $r): ?>
                        <tr>
                            <td><?= (int) $r['s_no'] ?></td>
                            <td><?= esc($r['court_name'] ?: $r['court_level']) ?></td>
                            <td><?= esc($r['case_number']) ?><br><small><?= esc($r['citation']) ?></small></td>
                            <td><?= esc($r['cause_title']) ?></td>
                            <td><?= esc($r['decided_on']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <div class="card card-mhc mb-3">
            <div class="card-header">Review workflow</div>
            <div class="card-body">
                <?= form_open('admin/applications/' . $app['id'] . '/status') ?>
                <div class="mb-3">
                    <label class="form-label">Update status</label>
                    <select name="status" class="form-select" required>
                        <?php foreach ($statuses as $k => $lab): if ($k === 'draft') {
                            continue;
                        } ?>
                            <option value="<?= esc($k) ?>" <?= $app['status'] === $k ? 'selected' : '' ?>><?= esc($lab) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="4" placeholder="Review remarks / reasons for return or rejection"><?= esc($app['review_remarks'] ?? '') ?></textarea>
                </div>
                <button class="btn btn-mhc w-100">Save status</button>
                <?= form_close() ?>
            </div>
        </div>

        <div class="card card-mhc">
            <div class="card-header">Status history</div>
            <ul class="list-group list-group-flush">
                <?php if (empty($history)): ?>
                    <li class="list-group-item text-muted">No history</li>
                <?php else: foreach ($history as $h): ?>
                    <li class="list-group-item">
                        <div class="small text-muted"><?= esc($h['created_at']) ?></div>
                        <div><strong><?= esc($h['from_status'] ?? '—') ?></strong> → <strong><?= esc($h['to_status']) ?></strong></div>
                        <?php if ($h['remarks']): ?><div class="small"><?= esc($h['remarks']) ?></div><?php endif; ?>
                    </li>
                <?php endforeach; endif; ?>
            </ul>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
