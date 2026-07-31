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
                        <strong>DOB / Age:</strong> <?= esc($app['date_of_birth'] ?? '—') ?> /
                        <?= esc($app['age_years'] ?? '—') ?> yrs
                        <?= esc($app['age_months'] ?? '—') ?> mo<br>
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
                    <div class="col-md-6"><strong>Office Address:</strong><br><?= nl2br(esc($app['address_office'] ?? '')) ?></div>
                    <div class="col-md-6"><strong>Residential Address:</strong><br><?= nl2br(esc($app['address_residence'] ?? '')) ?></div>
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
            <div class="card-header">Decision</div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    <strong>Flow:</strong> Applicant submits → Admin accepts or rejects with remarks.
                </p>
                <div class="mb-3">
                    <span class="text-muted small d-block mb-1">Current status</span>
                    <?= sad_status_badge($app['status']) ?>
                    <?php if (! empty($app['reviewed_at'])): ?>
                        <div class="small text-muted mt-1">Last action: <?= esc($app['reviewed_at']) ?></div>
                    <?php endif; ?>
                </div>

                <?php if (! empty($app['review_remarks'])): ?>
                    <div class="alert alert-light border small mb-3">
                        <strong>Latest remarks:</strong><br>
                        <?= nl2br(esc($app['review_remarks'])) ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($actions)): ?>
                    <div class="alert alert-secondary small mb-0">
                        <?php if (in_array($app['status'], ['approved', 'rejected'], true)): ?>
                            This application is closed. No further actions are available.
                        <?php elseif ($app['status'] === 'returned'): ?>
                            Returned to the advocate for correction. Waiting for resubmission.
                        <?php elseif ($app['status'] === 'draft'): ?>
                            Draft applications are not in the decision queue.
                        <?php elseif (($role ?? '') !== 'admin'): ?>
                            Only an <strong>administrator</strong> can accept or reject applications.
                        <?php else: ?>
                            No actions available at this status.
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?= form_open('admin/applications/' . $app['id'] . '/status') ?>
                    <div class="mb-3">
                        <label class="form-label" for="remarks">Remarks <span class="text-danger">*</span></label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="4" required
                                  placeholder="Required when accepting or rejecting."><?= esc(old('remarks', '')) ?></textarea>
                        <div class="form-text">These remarks are recorded and shared with the applicant on decision.</div>
                    </div>
                    <div class="d-grid gap-2">
                        <?php
                        $btnClass = [
                            'approve' => 'btn-success',
                            'reject'  => 'btn-danger',
                        ];
                        foreach ($actions as $key => $meta):
                            $class   = $btnClass[$key] ?? 'btn-outline-secondary';
                            $confirm = "return confirm('Confirm: " . esc($meta['label'], 'js') . "?');";
                        ?>
                            <button type="submit" name="action" value="<?= esc($key) ?>"
                                    class="btn <?= esc($class) ?>"
                                    onclick="<?= $confirm ?>">
                                <?= esc($meta['label']) ?>
                                <span class="small opacity-75">(remarks required)</span>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <?= form_close() ?>
                <?php endif; ?>
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
                        <div>
                            <strong><?= esc($statuses[$h['from_status']] ?? ($h['from_status'] ?? '—')) ?></strong>
                            →
                            <strong><?= esc($statuses[$h['to_status']] ?? $h['to_status']) ?></strong>
                        </div>
                        <?php if ($h['remarks']): ?><div class="small"><?= esc($h['remarks']) ?></div><?php endif; ?>
                    </li>
                <?php endforeach; endif; ?>
            </ul>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
