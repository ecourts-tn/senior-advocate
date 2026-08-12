<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 14px; text-align: center; margin: 0 0 4px; text-transform: uppercase; }
        h2 { font-size: 12px; text-align: center; margin: 0 0 12px; font-weight: normal; }
        .header { text-align: center; border-bottom: 2px solid #0b1f3a; padding-bottom: 8px; margin-bottom: 12px; }
        .meta { text-align: right; margin-bottom: 10px; font-size: 10px; }
        table.form { width: 100%; border-collapse: collapse; }
        table.form td { border: 1px solid #333; padding: 5px 6px; vertical-align: top; }
        table.form td.label { width: 38%; background: #f3f0ea; font-weight: bold; }
        .section { margin-top: 12px; font-weight: bold; font-size: 12px; color: #0b1f3a; }
        .decl { margin-top: 14px; border: 1px solid #333; padding: 8px; }
        .small { font-size: 9px; color: #444; }
        .photo { width: 90px; height: 110px; border: 1px solid #333; }
        .sig { max-height: 50px; max-width: 160px; }
        .page-break { page-break-before: always; }
        .annexure-head { margin: 0 0 10px; }
        .annexure-right { text-align: right; font-size: 10px; margin-bottom: 6px; }
        .annexure-title { text-align: center; font-weight: bold; font-size: 12px; text-transform: uppercase; margin: 4px 0; }
        .annexure-sub { text-align: center; font-size: 10px; margin: 0 0 10px; }
        .court-level-title { font-weight: bold; font-size: 11px; margin: 12px 0 6px; text-decoration: underline; }
        table.annex { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9px; }
        table.annex th, table.annex td { border: 1px solid #333; padding: 4px 5px; vertical-align: top; text-align: left; }
        table.annex th { background: #f3f0ea; font-weight: bold; }
        table.annex .c-sno { width: 28px; text-align: center; }
        table.annex .muted { color: #666; font-style: italic; }
        .empty-note { font-size: 10px; color: #666; margin: 4px 0 10px; }
    </style>
</head>
<body>
<?php
$ageAsOnLabel = ssa_age_as_on_label($app ?? null);
$l1   = $l1 ?? [];
$l2   = $l2 ?? [];
$l3pb = $l3pb ?? [];
$l3am = $l3am ?? [];
$l4   = $l4 ?? [];

$courtLevelLabels = [
    'madras_hc'         => 'In matters before Madras High Court',
    'supreme_other_hc'  => 'In matters before Supreme Court and Other High Courts',
    'district_tribunal' => 'In matters before District Courts / Labour Courts or Tribunals',
];

/**
 * Group L-1 / L-2 rows by court_level, preserving order.
 *
 * @param list<array<string,mixed>> $rows
 * @return array<string, list<array<string,mixed>>>
 */
$groupByCourtLevel = static function (array $rows) use ($courtLevelLabels): array {
    $grouped = array_fill_keys(array_keys($courtLevelLabels), []);
    foreach ($rows as $row) {
        $level = (string) ($row['court_level'] ?? 'madras_hc');
        if (! array_key_exists($level, $grouped)) {
            $grouped[$level] = [];
        }
        $grouped[$level][] = $row;
    }

    return $grouped;
};

$l1Grouped = $groupByCourtLevel($l1);
$l2Grouped = $groupByCourtLevel($l2);
?>
<div class="header">
    <div class="small">HIGH COURT OF MADRAS</div>
    <h1>Application-cum-Consent Letter</h1>
    <h2>for Designation of Senior Advocate [For Advocates]</h2>
    <div class="small">[Rules for Designation of Senior Advocates by the High Court of Madras, 2026]</div>
</div>

<div class="meta">
    Application No.: <strong><?= esc($app['application_no'] ?? 'DRAFT') ?></strong><br>
    Submitted: <?= esc(ssa_format_datetime($app['submitted_at'] ?? null)) ?>
</div>

<table class="form">
    <tr>
        <td class="label">1. Name of the Applicant-Advocate</td>
        <td><?= esc(trim(($app['title'] ?? '') . ' ' . ($app['full_name'] ?? ''))) ?></td>
        <td rowspan="5" style="width:100px;text-align:center;">
            <?php
            if (! empty($app['photo_path'])) {
                $p = WRITEPATH . 'uploads/' . $app['photo_path'];
                if (is_file($p)) {
                    $data = base64_encode(file_get_contents($p));
                    echo '<img class="photo" src="data:image/jpeg;base64,' . $data . '" alt="Photo">';
                } else {
                    echo '<div class="small">Recent Passport Size Colour Photograph</div>';
                }
            } else {
                echo '<div class="small">Recent Passport Size Colour Photograph</div>';
            }
            ?>
        </td>
    </tr>
    <tr>
        <td class="label">2. Date of Birth</td>
        <td><?= esc(ssa_format_date($app['date_of_birth'] ?? null)) ?></td>
    </tr>
    <tr>
        <td class="label">3. Age (as on <?= esc($ageAsOnLabel) ?>)</td>
        <td>
            <?= esc($app['age_years'] ?? '—') ?> Years
            <?= esc($app['age_months'] ?? '—') ?> Months
            <?= esc($app['age_days'] ?? '—') ?> Days
        </td>
    </tr>
    <tr>
        <td class="label">4. Address in Full — (i) Office</td>
        <td><?= nl2br(esc($app['address_office'] ?? '')) ?></td>
    </tr>
    <tr>
        <td class="label">4. Address in Full — (ii) Residence</td>
        <td><?= nl2br(esc($app['address_residence'] ?? '')) ?></td>
    </tr>
    <tr>
        <td class="label">5. Contact Details</td>
        <td colspan="2">
            (i) Landline: <?= esc($app['phone_landline'] ?? '—') ?><br>
            (ii) Mobile: <?= esc($app['mobile'] ?? '') ?><br>
            (iii) Email: <?= esc($app['email'] ?? '') ?>
        </td>
    </tr>
    <tr>
        <td class="label">6. Educational / Professional Qualifications</td>
        <td colspan="2"><?= nl2br(esc($app['qualifications'] ?? '')) ?></td>
    </tr>
    <tr>
        <td class="label">7. (i) Date, Month and Year of Enrolment as an Advocate</td>
        <td colspan="2"><?= esc(ssa_format_date($app['enrolment_date'] ?? null)) ?></td>
    </tr>
    <tr>
        <td class="label">7. (ii) Enrolment Number</td>
        <td colspan="2"><?= esc($app['enrolment_number'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">7. (iii) Bar Council where registered (Copy of Enrolment Certificate to be attached)</td>
        <td colspan="2"><?= esc($app['bar_council'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">7. (iv) Number of years of practice from the date of enrolment (as on <?= esc($ageAsOnLabel) ?>)</td>
        <td colspan="2">
            <?= (int) ($app['practice_years'] ?? 0) ?> Years
            <?= (int) ($app['practice_months'] ?? 0) ?> Months
        </td>
    </tr>
    <tr>
        <td class="label">7. (v) Net Professional Income per annum (in Lakhs of Rs) [Only earnings through practice as Advocate]</td>
        <td colspan="2"><?= esc($app['net_income_lakhs'] ?? '—') ?></td>
    </tr>
    <tr>
        <td class="label">8. Whether the applicant is a member of any bar association attached to a specific court</td>
        <td colspan="2">
            <?= ssa_bool_label($app['is_bar_association_member'] ?? null) ?>
            <?php if (! empty($app['bar_association_name'])): ?>
                <br>Name of Bar Association: <?= esc($app['bar_association_name']) ?>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td class="label">9. Number of Reported Judgments (excluding orders that do not lay down any principle of law): Format L-1</td>
        <td colspan="2">
            Supreme Court: <?= (int) ($app['reported_sc'] ?? 0) ?><br>
            High Court: <?= (int) ($app['reported_hc'] ?? 0) ?><br>
            District Court / Labour Court and Tribunals: <?= (int) ($app['reported_district'] ?? 0) ?>
        </td>
    </tr>
    <tr>
        <td class="label">10. Number of Unreported Judgments (excluding orders that do not lay down any principle of law): Format L-2</td>
        <td colspan="2">
            Supreme Court: <?= (int) ($app['unreported_sc'] ?? 0) ?><br>
            High Court: <?= (int) ($app['unreported_hc'] ?? 0) ?><br>
            District / Labour Court and Tribunals: <?= (int) ($app['unreported_district'] ?? 0) ?>
        </td>
    </tr>
    <tr>
        <td class="label">11. Pro Bono / Amicus Curiae work Format L-3(i), Format L-3(ii)</td>
        <td colspan="2">
            Total Pro Bono cases: <?= (int) ($app['pro_bono_total'] ?? 0) ?><br>
            Total Amicus Curiae cases: <?= (int) ($app['amicus_total'] ?? 0) ?>
        </td>
    </tr>
    <tr>
        <td class="label">12. Whether the applicant is first-generation lawyer</td>
        <td colspan="2"><?= ssa_bool_label($app['is_first_generation'] ?? null) ?></td>
    </tr>
    <tr>
        <td class="label">13. Academic Articles/Books published, experience of Teaching Assignments in the field of law, Guest Lectures delivered in law schools or professional institutions connected with law: Format L-4</td>
        <td colspan="2">
            No. of Academic Articles: <?= (int) ($app['academic_articles_count'] ?? 0) ?><br>
            No. of Academic Books: <?= (int) ($app['academic_books_count'] ?? 0) ?><br>
            No. of Teaching Assignments: <?= (int) ($app['teaching_assignments_count'] ?? 0) ?><br>
            No. of Guest Lectures: <?= (int) ($app['guest_lectures_count'] ?? 0) ?>
        </td>
    </tr>
    <tr>
        <td class="label">14. Courts where the applicant is practicing / has practiced</td>
        <td colspan="2">
            <?php
            $courts = $app['courts_practiced'] ?? [];
            if (is_string($courts)) {
                $courts = json_decode($courts, true) ?: [];
            }
            if (empty($courts)) {
                echo '—';
            } else {
                foreach ($courts as $c) {
                    echo esc(($c['court'] ?? '') . ' (' . ssa_format_period(is_array($c) ? $c : []) . ')') . '<br>';
                }
            }
            ?>
            <br><strong>Cumulative experience:</strong>
            <?= (int) ($app['cumulative_exp_years'] ?? 0) ?> Years
            <?= (int) ($app['cumulative_exp_months'] ?? 0) ?> Months
        </td>
    </tr>
    <tr>
        <td class="label">15. Tribunals, where the applicant has specialized practice</td>
        <td colspan="2">
            <?php
            $tribunals = $app['tribunals_practiced'] ?? [];
            if (is_string($tribunals)) {
                $tribunals = json_decode($tribunals, true) ?: [];
            }
            if (empty($tribunals)) {
                echo '—';
            } else {
                foreach ($tribunals as $t) {
                    echo esc(($t['tribunal'] ?? '') . ' (' . ssa_format_period(is_array($t) ? $t : []) . ')') . '<br>';
                }
            }
            ?>
        </td>
    </tr>
    <tr>
        <td class="label">16. Nature of practice (e.g. Civil, Criminal, Constitutional, Taxation, Labour, Company, Service, etc.)</td>
        <td colspan="2"><?= nl2br(esc($app['nature_of_practice'] ?? '')) ?></td>
    </tr>
    <tr>
        <td class="label">17. Field of Law — domain expertise … in which the applicant has specialization/expertise</td>
        <td colspan="2"><?= nl2br(esc($app['field_of_law'] ?? '')) ?></td>
    </tr>
    <tr>
        <td class="label">18. Whether the applicant has applied earlier to the Madras High Court for designation; If so, date of the application &amp; current status thereof</td>
        <td colspan="2"><?= ssa_bool_label($app['applied_mhc_earlier'] ?? null) ?>
            <?php if (! empty($app['applied_mhc_date'])): ?> <?= esc(ssa_format_date($app['applied_mhc_date'])) ?><?php endif; ?>
            <?php if (! empty($app['applied_mhc_status'])): ?> <?= esc($app['applied_mhc_status']) ?><?php endif; ?></td>
    </tr>
    <tr>
        <td class="label">19. Whether the applicant has applied earlier to the Supreme Court, or any other High Court; if so, date of the application and details thereof</td>
        <td colspan="2"><?= ssa_bool_label($app['applied_other_court'] ?? null) ?>
            <?php if (! empty($app['applied_other_date'])): ?> <?= esc(ssa_format_date($app['applied_other_date'])) ?><?php endif; ?>
            <?php if (! empty($app['applied_other_details'])): ?> <?= esc($app['applied_other_details']) ?><?php endif; ?></td>
    </tr>
    <tr>
        <td class="label">20. Whether any FIR has ever been lodged against the applicant; if so, details thereof</td>
        <td colspan="2"><?= ssa_bool_label($app['fir_lodged'] ?? null) ?> <?= esc($app['fir_details'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">21. Whether the applicant is a party to any criminal case; if so, details thereof</td>
        <td colspan="2"><?= ssa_bool_label($app['criminal_case_party'] ?? null) ?> <?= esc($app['criminal_case_details'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">22. Whether any proceedings were initiated or are pending against the applicant before Bar Council of India or State Bar Council; if so, details thereof</td>
        <td colspan="2"><?= ssa_bool_label($app['bar_council_proceedings'] ?? null) ?> <?= esc($app['bar_council_details'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">23. General State of Health</td>
        <td colspan="2"><?= esc($app['general_health'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">24. Any other information</td>
        <td colspan="2"><?= nl2br(esc($app['other_information'] ?? '')) ?></td>
    </tr>
</table>

<div class="decl">
    <strong>DECLARATION</strong>
    <p>I <strong><?= esc($app['declaration_name'] ?? $app['full_name'] ?? '') ?></strong> hereby give consent for being designated as Senior Advocate.</p>
    <p class="small">I hereby declare that the information furnished above is true and correct to the best of my knowledge and belief.
        No material information is concealed or suppressed therefrom. I understand that furnishing of false information or
        suppression of any factual information would render me unfit from being designated as Senior Advocate.</p>
    <p class="small">I undertake that if my application is accepted I will strictly adhere to the code of conduct applicable under the
        Advocates Act and Bar Council Act, as well as these Rules, and shall not do any act which directly or indirectly violates any of the above, either in letter or in spirit.</p>
    <table style="width:100%;margin-top:16px;">
        <tr>
            <td>Date: <?= esc(ssa_format_date($app['declaration_date'] ?? $app['submitted_at'] ?? date('Y-m-d'))) ?></td>
            <td style="text-align:right;">
                <?php
                if (! empty($app['signature_path'])) {
                    $s = WRITEPATH . 'uploads/' . $app['signature_path'];
                    if (is_file($s)) {
                        $data = base64_encode(file_get_contents($s));
                        echo '<img class="sig" src="data:image/jpeg;base64,' . $data . '" alt="Signature"><br>';
                    }
                }
                ?>
                Signature of the Applicant-Advocate
            </td>
        </tr>
    </table>
</div>

<?php /* ─── Format L-1 to L-4 annexures (prescribed table formats) ─── */ ?>

<div class="page-break"></div>

<!-- Format L-1 -->
<div class="annexure-head">
    <div class="annexure-right">
        <strong>Format L-1</strong><br>
        (See Sl. No.9 of the application)
    </div>
    <div class="annexure-title">As Arguing Counsel</div>
    <div class="annexure-sub">List of Reported Judgments (excluding orders not laying down any principle of law)</div>
</div>

<?php if (empty($l1)): ?>
    <p class="empty-note">No Format L-1 entries furnished.</p>
<?php else: ?>
    <?php foreach ($courtLevelLabels as $level => $levelTitle): ?>
        <?php $rows = $l1Grouped[$level] ?? []; ?>
        <div class="court-level-title"><?= esc($levelTitle) ?></div>
        <?php if (empty($rows)): ?>
            <p class="empty-note">No entries under this category.</p>
        <?php else: ?>
            <table class="annex">
                <thead>
                <tr>
                    <th class="c-sno">S.No.</th>
                    <?php if ($level !== 'madras_hc'): ?>
                        <th><?= $level === 'district_tribunal' ? 'Court / Tribunal(s)' : 'Court(s)' ?></th>
                    <?php endif; ?>
                    <th><?= $level === 'madras_hc' ? 'Case Number' : 'Citation / Case Number' ?></th>
                    <th>Cause Title and Subject Matter</th>
                    <th>Decided on</th>
                    <th>Legal formulation advanced by the applicant</th>
                </tr>
                </thead>
                <tbody>
                <?php $n = 0; foreach ($rows as $r): $n++; ?>
                    <tr>
                        <td class="c-sno"><?= (int) ($r['s_no'] ?? $n) ?></td>
                        <?php if ($level !== 'madras_hc'): ?>
                            <td><?= esc($r['court_name'] ?? '') ?></td>
                        <?php endif; ?>
                        <td>
                            <?php
                            $case = trim((string) ($r['case_number'] ?? ''));
                            $cite = trim((string) ($r['citation'] ?? ''));
                            if ($case !== '' && $cite !== '' && $case !== $cite) {
                                echo esc($cite) . '<br>' . esc($case);
                            } else {
                                echo esc($cite !== '' ? $cite : $case);
                            }
                            ?>
                        </td>
                        <td><?= nl2br(esc($r['cause_title'] ?? '')) ?></td>
                        <td><?= esc(ssa_format_date($r['decided_on'] ?? null)) ?></td>
                        <td><?= nl2br(esc($r['legal_formulation'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<div class="page-break"></div>

<!-- Format L-2 -->
<div class="annexure-head">
    <div class="annexure-right">
        <strong>Format L-2</strong><br>
        (See Sl. No.10 of the application)
    </div>
    <div class="annexure-title">As Arguing Counsel</div>
    <div class="annexure-sub">List of Unreported Judgments (excluding orders not laying down any principle of law)</div>
</div>

<?php if (empty($l2)): ?>
    <p class="empty-note">No Format L-2 entries furnished.</p>
<?php else: ?>
    <?php foreach ($courtLevelLabels as $level => $levelTitle): ?>
        <?php $rows = $l2Grouped[$level] ?? []; ?>
        <div class="court-level-title"><?= esc($levelTitle) ?></div>
        <?php if (empty($rows)): ?>
            <p class="empty-note">No entries under this category.</p>
        <?php else: ?>
            <table class="annex">
                <thead>
                <tr>
                    <th class="c-sno">S.No.</th>
                    <?php if ($level !== 'madras_hc'): ?>
                        <th><?= $level === 'district_tribunal' ? 'Court / Tribunal(s)' : 'Court(s)' ?></th>
                    <?php endif; ?>
                    <th><?= $level === 'madras_hc' ? 'Case Number' : 'Citation / Case Number' ?></th>
                    <th>Cause Title and Subject Matter</th>
                    <th>Decided on</th>
                    <th>Legal formulation advanced by the applicant</th>
                </tr>
                </thead>
                <tbody>
                <?php $n = 0; foreach ($rows as $r): $n++; ?>
                    <tr>
                        <td class="c-sno"><?= (int) ($r['s_no'] ?? $n) ?></td>
                        <?php if ($level !== 'madras_hc'): ?>
                            <td><?= esc($r['court_name'] ?? '') ?></td>
                        <?php endif; ?>
                        <td>
                            <?php
                            $case = trim((string) ($r['case_number'] ?? ''));
                            $cite = trim((string) ($r['citation'] ?? ''));
                            if ($case !== '' && $cite !== '' && $case !== $cite) {
                                echo esc($cite) . '<br>' . esc($case);
                            } else {
                                echo esc($cite !== '' ? $cite : $case);
                            }
                            ?>
                        </td>
                        <td><?= nl2br(esc($r['cause_title'] ?? '')) ?></td>
                        <td><?= esc(ssa_format_date($r['decided_on'] ?? null)) ?></td>
                        <td><?= nl2br(esc($r['legal_formulation'] ?? '')) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>

<div class="page-break"></div>

<!-- Format L-3(i) -->
<div class="annexure-head">
    <div class="annexure-right">
        <strong>Format L-3 (i)</strong><br>
        (See Sl. No.11 of the application)
    </div>
    <div class="annexure-title">List of matters in which appeared as Pro-Bono</div>
</div>

<?php if (empty($l3pb)): ?>
    <p class="empty-note">No Format L-3(i) entries furnished.</p>
<?php else: ?>
    <table class="annex">
        <thead>
        <tr>
            <th class="c-sno">S.No.</th>
            <th>Court(s) / Tribunal(s)</th>
            <th>Citation / Case Number</th>
            <th>Cause Title</th>
            <th>Decided on</th>
            <th>Describe manner in which society was sought to be benefited by the litigation</th>
        </tr>
        </thead>
        <tbody>
        <?php $n = 0; foreach ($l3pb as $r): $n++; ?>
            <tr>
                <td class="c-sno"><?= (int) ($r['s_no'] ?? $n) ?></td>
                <td><?= esc($r['court_tribunal'] ?? '') ?></td>
                <td><?= esc($r['case_number'] ?? '') ?></td>
                <td><?= nl2br(esc($r['cause_title'] ?? '')) ?></td>
                <td><?= esc(ssa_format_date($r['decided_on'] ?? null)) ?></td>
                <td><?= nl2br(esc($r['society_benefit'] ?? '')) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<div style="margin-top:18px;"></div>

<!-- Format L-3(ii) -->
<div class="annexure-head">
    <div class="annexure-right">
        <strong>Format L-3 (ii)</strong><br>
        (See Sl. No.11 of the application)
    </div>
    <div class="annexure-title">List of matters in which appeared as Amicus Curiae</div>
</div>

<?php if (empty($l3am)): ?>
    <p class="empty-note">No Format L-3(ii) entries furnished.</p>
<?php else: ?>
    <table class="annex">
        <thead>
        <tr>
            <th class="c-sno">S.No.</th>
            <th>Court(s) / Tribunal(s)</th>
            <th>Citation / Case Number</th>
            <th>Cause Title</th>
            <th>Decided on</th>
            <th>Reportable / Unreportable</th>
        </tr>
        </thead>
        <tbody>
        <?php $n = 0; foreach ($l3am as $r): $n++; ?>
            <tr>
                <td class="c-sno"><?= (int) ($r['s_no'] ?? $n) ?></td>
                <td><?= esc($r['court_tribunal'] ?? '') ?></td>
                <td><?= esc($r['case_number'] ?? '') ?></td>
                <td><?= nl2br(esc($r['cause_title'] ?? '')) ?></td>
                <td><?= esc(ssa_format_date($r['decided_on'] ?? null)) ?></td>
                <td><?= esc($r['reportable'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<div class="page-break"></div>

<!-- Format L-4 -->
<div class="annexure-head">
    <div class="annexure-right">
        <strong>Format L-4</strong><br>
        (See Sl. No.13 of the application)
    </div>
    <div class="annexure-sub" style="text-align:left; margin-top:8px;">
        Details of academic articles/books published, experience of teaching assignments in the field of law,
        guest lectures delivered in law schools or professional institutions connected with law.
    </div>
</div>

<?php if (empty($l4)): ?>
    <p class="empty-note">No Format L-4 entries furnished.</p>
<?php else: ?>
    <table class="annex">
        <thead>
        <tr>
            <th class="c-sno" rowspan="2" style="vertical-align:middle;">S.No.</th>
            <th colspan="2" style="text-align:center;">Topic of published academic Articles/Books</th>
            <th colspan="2" style="text-align:center;">Experience details in law schools or professional institutions (with names) connected with law</th>
            <th rowspan="2" style="vertical-align:middle;">Any other relevant details</th>
        </tr>
        <tr>
            <th>Articles</th>
            <th>Books</th>
            <th>Teaching Assignment(s)</th>
            <th>Guest Lectures</th>
        </tr>
        </thead>
        <tbody>
        <?php $n = 0; foreach ($l4 as $r): $n++;
            $articles = trim((string) ($r['articles'] ?? ''));
            $books    = trim((string) ($r['books'] ?? ''));
            // Legacy rows stored a single combined topic.
            if ($articles === '' && $books === '' && ! empty($r['topic'])) {
                $articles = (string) $r['topic'];
            }
        ?>
            <tr>
                <td class="c-sno"><?= (int) ($r['s_no'] ?? $n) ?></td>
                <td><?= nl2br(esc($articles)) ?></td>
                <td><?= nl2br(esc($books)) ?></td>
                <td><?= nl2br(esc($r['teaching_assignment'] ?? '')) ?></td>
                <td><?= nl2br(esc($r['guest_lectures'] ?? '')) ?></td>
                <td><?= nl2br(esc($r['other_details'] ?? '')) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<p class="small" style="margin-top:14px;">Generated by Madras High Court SSA Portal · <?= date('d-m-Y h:i A') ?></p>
</body>
</html>
