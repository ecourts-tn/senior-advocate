<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
    <div>
        <h2 class="h4 mb-1"><?= esc($app['application_no'] ?? 'Application #' . $app['id']) ?></h2>
        <?= ssa_status_badge($app['status']) ?>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= base_url('admin/applications/' . $app['id'] . '/pdf') ?>" class="btn btn-outline-danger" target="_blank">PDF</a>
        <a href="<?= base_url('admin/applications') ?>" class="btn btn-outline-secondary">Back</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <?php $ageAsOnLabel = ssa_age_as_on_label($app ?? null); ?>
        <div class="card card-mhc mb-3">
            <div class="card-header">Applicant details</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="mb-2">
                            <strong>1. Name of the Applicant-Advocate</strong><br>
                            <?= esc(trim(($app['title'] ?? '') . ' ' . ($app['full_name'] ?? ''))) ?>
                        </div>
                        <?php if (! empty($app['notification_id'])): ?>
                            <?php
                            $nRow = null;
                            try {
                                $nRow = model(\App\Models\DesignationNotificationModel::class)->find((int) $app['notification_id']);
                            } catch (\Throwable $e) {
                                $nRow = null;
                            }
                            ?>
                            <div class="mb-2">
                                <strong>Notification:</strong>
                                <?php if ($nRow): ?>
                                    <a href="<?= base_url('admin/applications?notification_id=' . (int) $nRow['id']) ?>">
                                        <?= esc($nRow['notification_number'] ?? '') ?>
                                    </a>
                                    <?php if (! empty($nRow['notification_date'])): ?>
                                        <span class="text-muted">(<?= esc(date('d-m-Y', strtotime($nRow['notification_date']))) ?>)</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    #<?= (int) $app['notification_id'] ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <div class="mb-2">
                            <strong>2. Date of Birth</strong><br>
                            <?= esc(ssa_format_date($app['date_of_birth'] ?? null)) ?>
                        </div>
                        <div class="mb-2">
                            <strong>Age as on <?= esc($ageAsOnLabel) ?></strong><br>
                            <?= esc($app['age_years'] ?? '—') ?> Years
                            <?= esc($app['age_months'] ?? '—') ?> Months
                            <?= esc($app['age_days'] ?? '—') ?> Days
                        </div>
                        <div class="mb-2">
                            <strong>Address in Full — Office</strong><br>
                            <?= nl2br(esc($app['address_office'] ?? '—')) ?>
                        </div>
                        <div class="mb-2">
                            <strong>Address in Full — Residence</strong><br>
                            <?= nl2br(esc($app['address_residence'] ?? '—')) ?>
                        </div>
                        <div class="mb-2">
                            <strong>Contact Details</strong><br>
                            Landline: <?= esc($app['phone_landline'] ?? '—') ?><br>
                            Mobile: <?= esc($app['mobile'] ?? '—') ?><br>
                            Email: <?= esc($app['email'] ?? '—') ?>
                        </div>
                        <div class="mb-2">
                            <strong>5. Educational / Professional Qualifications</strong><br>
                            <?= nl2br(esc($app['qualifications'] ?? '—')) ?>
                        </div>
                        <div class="mb-2">
                            <strong>Date, Month and Year of Enrolment as an Advocate</strong><br>
                            <?= esc(ssa_format_date($app['enrolment_date'] ?? null)) ?>
                        </div>
                        <div class="mb-2">
                            <strong>Enrolment Number</strong><br>
                            <?= esc($app['enrolment_number'] ?? '—') ?>
                        </div>
                        <div class="mb-2">
                            <strong>Bar Council where registered (Copy of Enrolment Certificate to be attached)</strong><br>
                            <?= esc($app['bar_council'] ?? '—') ?>
                        </div>
                        <div class="mb-2">
                            <strong>Number of years of practice from the date of enrolment (as on <?= esc($ageAsOnLabel) ?>)</strong><br>
                            <?= (int) ($app['practice_years'] ?? 0) ?> Years
                            <?= (int) ($app['practice_months'] ?? 0) ?> Months
                        </div>
                        <div class="mb-2">
                            <strong>Net Professional Income per annum (in Lakhs of Rs) [Only earnings through practice as Advocate]</strong><br>
                            <?= esc($app['net_income_lakhs'] ?? '—') ?>
                        </div>
                        <div class="mb-0">
                            <strong>Whether the applicant is a member of any bar association attached to a specific court</strong><br>
                            <?= ssa_bool_label($app['is_bar_association_member'] ?? null) ?>
                            <?php if (! empty($app['bar_association_name'])): ?>
                                <br><strong>Name of Bar Association:</strong> <?= esc($app['bar_association_name']) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="review-identity">
                            <div class="review-identity-item">
                                <span class="review-label">Recent Passport Size Colour Photograph</span>
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
                    <div class="col-md-6">
                        <strong>9. Number of Reported Judgments (excluding orders that do not lay down any principle of law): Format L-1</strong><br>
                        Supreme Court: <?= (int) ($app['reported_sc'] ?? 0) ?><br>
                        High Court: <?= (int) ($app['reported_hc'] ?? 0) ?><br>
                        District Court / Labour Court and Tribunals: <?= (int) ($app['reported_district'] ?? 0) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>10. Number of Unreported Judgments (excluding orders that do not lay down any principle of law): Format L-2</strong><br>
                        Supreme Court: <?= (int) ($app['unreported_sc'] ?? 0) ?><br>
                        High Court: <?= (int) ($app['unreported_hc'] ?? 0) ?><br>
                        District / Labour Court and Tribunals: <?= (int) ($app['unreported_district'] ?? 0) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>11. Pro Bono / Amicus Curiae work Format L-3(i), Format L-3(ii)</strong><br>
                        Total Pro Bono cases: <?= (int) ($app['pro_bono_total'] ?? 0) ?><br>
                        Total Amicus Curiae cases: <?= (int) ($app['amicus_total'] ?? 0) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>12. Whether the applicant is first-generation lawyer</strong><br>
                        <?= ssa_bool_label($app['is_first_generation'] ?? null) ?>
                    </div>
                    <div class="col-12">
                        <strong>13. Academic Articles/Books published, experience of Teaching Assignments in the field of law, Guest Lectures delivered in law schools or professional institutions connected with law: Format L-4</strong><br>
                        No. of Academic Articles: <?= (int) ($app['academic_articles_count'] ?? 0) ?><br>
                        No. of Academic Books: <?= (int) ($app['academic_books_count'] ?? 0) ?><br>
                        No. of Teaching Assignments: <?= (int) ($app['teaching_assignments_count'] ?? 0) ?><br>
                        No. of Guest Lectures: <?= (int) ($app['guest_lectures_count'] ?? 0) ?>
                    </div>
                    <div class="col-12">
                        <strong>14. Courts where the applicant is practicing / has practiced</strong><br>
                        <?php
                        $courts = $app['courts_practiced'] ?? [];
                        if (is_string($courts)) {
                            $courts = json_decode($courts, true) ?: [];
                        }
                        if (empty($courts)):
                        ?>
                            <span class="text-muted">—</span>
                        <?php else: ?>
                            <ul class="mb-1 ps-3">
                                <?php foreach ($courts as $c): ?>
                                    <li>
                                        <?= esc($c['court'] ?? '—') ?>
                                        <span class="text-muted">(<?= esc(ssa_format_period(is_array($c) ? $c : [])) ?>)</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <div class="mt-1">
                            <strong>Cumulative experience (from courts practiced):</strong>
                            <?= (int) ($app['cumulative_exp_years'] ?? 0) ?> Years
                            <?= (int) ($app['cumulative_exp_months'] ?? 0) ?> Months
                        </div>
                    </div>
                    <div class="col-12">
                        <strong>15. Tribunals, where the applicant has specialized practice: (Applicable to those practising before Tribunals)</strong><br>
                        <?php
                        $tribunals = $app['tribunals_practiced'] ?? [];
                        if (is_string($tribunals)) {
                            $tribunals = json_decode($tribunals, true) ?: [];
                        }
                        if (empty($tribunals)):
                        ?>
                            <span class="text-muted">—</span>
                        <?php else: ?>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($tribunals as $t): ?>
                                    <li>
                                        <?= esc($t['tribunal'] ?? '—') ?>
                                        <span class="text-muted">(<?= esc(ssa_format_period(is_array($t) ? $t : [])) ?>)</span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <strong>16. Nature of practice (e.g. Civil, Criminal, Constitutional, Taxation, Labour, Company, Service, etc.)</strong><br>
                        <?= nl2br(esc($app['nature_of_practice'] ?? '—')) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>17. Field of Law — domain expertise (such as, Consitutional Law, Inter-State Water Disputes, Criminal Law, Arbitration Law, Corportate Law, Family Law, Human Righsts, Public Interest Litigation, International Law, law relating to women ) in which the applicant has specialization/expertise</strong><br>
                        <?= nl2br(esc($app['field_of_law'] ?? '—')) ?>
                    </div>
                    <div class="col-md-6">
                        <strong>20. Whether any FIR has ever been lodged against the applicant; if so, details thereof</strong><br>
                        <?= ssa_bool_label($app['fir_lodged'] ?? null) ?>
                        <?php if (! empty($app['fir_details'])): ?><br><?= nl2br(esc($app['fir_details'])) ?><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <strong>21. Whether the applicant is a party to any criminal case; if so, details thereof</strong><br>
                        <?= ssa_bool_label($app['criminal_case_party'] ?? null) ?>
                        <?php if (! empty($app['criminal_case_details'])): ?><br><?= nl2br(esc($app['criminal_case_details'])) ?><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <strong>22. Whether any proceedings were initiated or are pending against the applicant before Bar Council of India or State Bar Council; if so, details thereof</strong><br>
                        <?= ssa_bool_label($app['bar_council_proceedings'] ?? null) ?>
                        <?php if (! empty($app['bar_council_details'])): ?><br><?= nl2br(esc($app['bar_council_details'])) ?><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <strong>23. General State of Health</strong><br>
                        <?= esc($app['general_health'] ?? '—') ?>
                    </div>
                    <?php if (! empty($app['other_information'])): ?>
                        <div class="col-12">
                            <strong>24. Any other information</strong><br>
                            <?= nl2br(esc($app['other_information'])) ?>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-6">
                        <strong>18. Whether the applicant has applied earlier to the Madras High Court for designation; If so, date of the application &amp; current status thereof</strong><br>
                        <?= ssa_bool_label($app['applied_mhc_earlier'] ?? null) ?>
                        <?php if (! empty($app['applied_mhc_date'])): ?>
                            <br>Date: <?= esc(ssa_format_date($app['applied_mhc_date'])) ?>
                        <?php endif; ?>
                        <?php if (! empty($app['applied_mhc_status'])): ?>
                            <br>Details: <?= esc($app['applied_mhc_status']) ?>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <strong>19. Whether the applicant has applied earlier to the Supreme Court, or any other High Court; if so, date of the application and details thereof</strong><br>
                        <?= ssa_bool_label($app['applied_other_court'] ?? null) ?>
                        <?php if (! empty($app['applied_other_date'])): ?>
                            <br>Date: <?= esc(ssa_format_date($app['applied_other_date'])) ?>
                        <?php endif; ?>
                        <?php if (! empty($app['applied_other_details'])): ?>
                            <br>Details: <?= esc($app['applied_other_details']) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Structured format data entered on Steps 3–4 (not PDF uploads).
        $l1   = $l1 ?? [];
        $l2   = $l2 ?? [];
        $l3pb = $l3pb ?? [];
        $l3am = $l3am ?? [];
        $l4   = $l4 ?? [];
        $formatEntryCounts = [
            'L-1'     => count($l1),
            'L-2'     => count($l2),
            'L-3(i)'  => count($l3pb),
            'L-3(ii)' => count($l3am),
            'L-4'     => count($l4),
        ];
        ?>

        <div class="card card-mhc mb-3">
            <div class="card-header">Format entries (from application form)</div>
            <div class="card-body">
                <p class="small text-muted mb-2">Data entered for Formats L-1 to L-4 during the online application (Steps 3–4).</p>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($formatEntryCounts as $lab => $cnt): ?>
                        <?php if ($cnt > 0): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle">
                                <?= esc($lab) ?>: <?= (int) $cnt ?> entr<?= $cnt === 1 ? 'y' : 'ies' ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border"><?= esc($lab) ?>: none</span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card card-mhc mb-3">
            <div class="card-header">Attached PDF documents</div>
            <div class="card-body">
                <p class="small text-muted mb-2">Optional PDF uploads from Step 7 (separate from the online format entries above).</p>
                <div class="d-flex flex-wrap gap-2">
                    <?php
                    $docs = [
                        'enrolment_cert' => 'Enrolment Certificate',
                        'age_proof'      => 'Age proof',
                        'education_qual' => 'Educational qualifications document',
                        'format_l1'      => 'Format L-1 (Reported Judgments)',
                        'format_l2'      => 'Format L-2 (Unreported Judgments)',
                        'format_l3i'     => 'Format L-3(i) Pro Bono',
                        'format_l3ii'    => 'Format L-3(ii) Amicus Curiae',
                        'format_l4'      => 'Format L-4 Academic',
                    ];
                    foreach ($docs as $k => $lab):
                        $col = $k . '_path';
                    ?>
                        <?php if (! empty($app[$col])): ?>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= base_url('admin/applications/' . $app['id'] . '/file/' . $k) ?>"><?= esc($lab) ?></a>
                        <?php else: ?>
                            <span class="badge bg-light text-dark border"><?= esc($lab) ?>: not uploaded<?= $k === 'education_qual' ? ' (optional)' : '' ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if (! empty($l1)): ?>
        <div class="card card-mhc mb-3">
            <div class="card-header">Format L-1 entries (Reported judgments)</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>#</th><th>Court</th><th>Case Number / Citation</th><th>Cause Title</th><th>Decided</th></tr></thead>
                    <tbody>
                    <?php foreach ($l1 as $r): ?>
                        <tr>
                            <td><?= (int) $r['s_no'] ?></td>
                            <td><?= esc($r['court_name'] ?: $r['court_level']) ?></td>
                            <td><?= esc($r['case_number']) ?><br><small><?= esc($r['citation']) ?></small></td>
                            <td><?= esc($r['cause_title']) ?></td>
                            <td><?= esc(ssa_format_date($r['decided_on'] ?? null)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if (! empty($l2)): ?>
        <div class="card card-mhc mb-3">
            <div class="card-header">Format L-2 entries (Unreported judgments)</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>#</th><th>Court</th><th>Case Number / Citation (If any)</th><th>Cause Title</th><th>Decided</th></tr></thead>
                    <tbody>
                    <?php foreach ($l2 as $r): ?>
                        <tr>
                            <td><?= (int) $r['s_no'] ?></td>
                            <td><?= esc($r['court_name'] ?: $r['court_level']) ?></td>
                            <td><?= esc($r['case_number']) ?></td>
                            <td><?= esc($r['cause_title']) ?></td>
                            <td><?= esc(ssa_format_date($r['decided_on'] ?? null)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if (! empty($l3pb)): ?>
        <div class="card card-mhc mb-3">
            <div class="card-header">Format L-3(i) entries (Pro bono)</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>#</th><th>Court / Tribunal</th><th>Case Number / Citation</th><th>Cause Title</th><th>Decided</th><th>Society benefit</th></tr></thead>
                    <tbody>
                    <?php foreach ($l3pb as $r): ?>
                        <tr>
                            <td><?= (int) $r['s_no'] ?></td>
                            <td><?= esc($r['court_tribunal'] ?? '') ?></td>
                            <td><?= esc($r['case_number'] ?? '') ?></td>
                            <td><?= esc($r['cause_title'] ?? '') ?></td>
                            <td><?= esc(ssa_format_date($r['decided_on'] ?? null)) ?></td>
                            <td><?= esc($r['society_benefit'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if (! empty($l3am)): ?>
        <div class="card card-mhc mb-3">
            <div class="card-header">Format L-3(ii) entries (Amicus curiae)</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>#</th><th>Court / Tribunal</th><th>Case Number / Citation</th><th>Cause Title</th><th>Decided</th><th>Reportable</th></tr></thead>
                    <tbody>
                    <?php foreach ($l3am as $r): ?>
                        <tr>
                            <td><?= (int) $r['s_no'] ?></td>
                            <td><?= esc($r['court_tribunal'] ?? '') ?></td>
                            <td><?= esc($r['case_number'] ?? '') ?></td>
                            <td><?= esc($r['cause_title'] ?? '') ?></td>
                            <td><?= esc(ssa_format_date($r['decided_on'] ?? null)) ?></td>
                            <td><?= esc($r['reportable'] ?? '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if (! empty($l4)): ?>
        <div class="card card-mhc mb-3">
            <div class="card-header">Format L-4 entries (Academic articles/books &amp; experience)</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th rowspan="2" class="align-middle">#</th>
                            <th colspan="2" class="text-center">Topic of published academic articles/books</th>
                            <th colspan="2" class="text-center">Experience details in law schools or professional institutions (with names) connected with law</th>
                            <th rowspan="2" class="align-middle">Any other relevant details</th>
                        </tr>
                        <tr>
                            <th>Articles</th>
                            <th>Books</th>
                            <th>Teaching Assignment(s)</th>
                            <th>Guest Lectures</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($l4 as $r):
                        $articles = trim((string) ($r['articles'] ?? ''));
                        $books    = trim((string) ($r['books'] ?? ''));
                        if ($articles === '' && $books === '' && ! empty($r['topic'])) {
                            $articles = (string) $r['topic'];
                        }
                    ?>
                        <tr>
                            <td><?= (int) $r['s_no'] ?></td>
                            <td><?= nl2br(esc($articles)) ?></td>
                            <td><?= nl2br(esc($books)) ?></td>
                            <td><?= nl2br(esc($r['teaching_assignment'] ?? '')) ?></td>
                            <td><?= nl2br(esc($r['guest_lectures'] ?? '')) ?></td>
                            <td><?= nl2br(esc($r['other_details'] ?? '')) ?></td>
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
            <div class="card-header">Application tatus</div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted small d-block mb-1">Current status</span>
                    <?= ssa_status_badge($app['status']) ?>
                    <?php if (! empty($app['reviewed_at'])): ?>
                        <div class="small text-muted mt-1">Last action: <?= esc(ssa_format_datetime($app['reviewed_at'])) ?></div>
                    <?php endif; ?>
                </div>

                <?php if (! empty($app['review_remarks'])): ?>
                    <div class="alert alert-light border small mb-3">
                        <strong>Latest remarks:</strong><br>
                        <?= nl2br(esc($app['review_remarks'])) ?>
                    </div>
                <?php endif; ?>

                <?php if (($app['status'] ?? '') === 'draft'): ?>
                    <div class="alert alert-secondary small mb-0">
                        Draft applications are not in the classification queue.
                    </div>
                <?php elseif (($role ?? '') === 'admin'): ?>
                    <a href="<?= base_url('admin/applications/status') ?>" class="btn btn-mhc w-100">
                        <i class="bi bi-ui-checks me-1"></i> Go to Update status
                    </a>
                <?php else: ?>
                    <div class="alert alert-secondary small mb-0">
                        Only an <strong>administrator</strong> can update application status.
                    </div>
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
                        <div class="small text-muted"><?= esc(ssa_format_datetime($h['created_at'] ?? null)) ?></div>
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
