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
    </style>
</head>
<body>
<?php $ageAsOnLabel = ssa_age_as_on_label($app ?? null); ?>
<div class="header">
    <div class="small">HIGH COURT OF MADRAS</div>
    <h1>Application-cum-Consent Letter</h1>
    <h2>for Designation of Senior Advocate [For Advocates]</h2>
    <div class="small">[Rules for Designation of Senior Advocates by the High Court of Madras, 2026]</div>
</div>

<div class="meta">
    Application No.: <strong><?= esc($app['application_no'] ?? 'DRAFT') ?></strong><br>
    Submitted: <?= esc($app['submitted_at'] ?? '—') ?>
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
        <td><?= esc($app['date_of_birth'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">Age as on <?= esc($ageAsOnLabel) ?></td>
        <td>
            <?= esc($app['age_years'] ?? '—') ?> Years
            <?= esc($app['age_months'] ?? '—') ?> Months
            <?= esc($app['age_days'] ?? '—') ?> Days
        </td>
    </tr>
    <tr>
        <td class="label">Address in Full — Office</td>
        <td><?= nl2br(esc($app['address_office'] ?? '')) ?></td>
    </tr>
    <tr>
        <td class="label">Address in Full — Residence</td>
        <td><?= nl2br(esc($app['address_residence'] ?? '')) ?></td>
    </tr>
    <tr>
        <td class="label">Contact Details</td>
        <td colspan="2">
            Landline: <?= esc($app['phone_landline'] ?? '—') ?><br>
            Mobile: <?= esc($app['mobile'] ?? '') ?><br>
            Email: <?= esc($app['email'] ?? '') ?>
        </td>
    </tr>
    <tr>
        <td class="label">5. Educational / Professional Qualifications</td>
        <td colspan="2"><?= nl2br(esc($app['qualifications'] ?? '')) ?></td>
    </tr>
    <tr>
        <td class="label">Date, Month and Year of Enrolment as an Advocate</td>
        <td colspan="2"><?= esc($app['enrolment_date'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">Enrolment Number</td>
        <td colspan="2"><?= esc($app['enrolment_number'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">Bar Council where registered (Copy of Enrolment Certificate to be attached)</td>
        <td colspan="2"><?= esc($app['bar_council'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">Number of years of practice from the date of enrolment (as on <?= esc($ageAsOnLabel) ?>)</td>
        <td colspan="2">
            <?= (int) ($app['practice_years'] ?? 0) ?> Years
            <?= (int) ($app['practice_months'] ?? 0) ?> Months
        </td>
    </tr>
    <tr>
        <td class="label">Net Professional Income per annum (in Lakhs of Rs) [Only earnings through practice as Advocate]</td>
        <td colspan="2"><?= esc($app['net_income_lakhs'] ?? '—') ?></td>
    </tr>
    <tr>
        <td class="label">Whether the applicant is a member of any bar association attached to a specific court</td>
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
            $fmtPeriod = static function (array $row): string {
                $from = $row['from_date'] ?? $row['from'] ?? '';
                $to   = $row['to_date'] ?? $row['to'] ?? '';
                $from = $from !== '' && $from !== null ? (string) $from : '—';
                $to   = $to !== '' && $to !== null ? (string) $to : 'present';

                return $from . ' – ' . $to;
            };
            $courts = $app['courts_practiced'] ?? [];
            if (is_string($courts)) {
                $courts = json_decode($courts, true) ?: [];
            }
            if (empty($courts)) {
                echo '—';
            } else {
                foreach ($courts as $c) {
                    echo esc(($c['court'] ?? '') . ' (' . $fmtPeriod($c) . ')') . '<br>';
                }
            }
            ?>
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
                    echo esc(($t['tribunal'] ?? '') . ' (' . $fmtPeriod($t) . ')') . '<br>';
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
            <?= esc($app['applied_mhc_date'] ?? '') ?> <?= esc($app['applied_mhc_status'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">19. Whether the applicant has applied earlier to the Supreme Court, or any other High Court; if so, date of the application and details thereof</td>
        <td colspan="2"><?= ssa_bool_label($app['applied_other_court'] ?? null) ?>
            <?= esc($app['applied_other_date'] ?? '') ?> <?= esc($app['applied_other_details'] ?? '') ?></td>
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
            <td>Date: <?= esc($app['declaration_date'] ?? $app['submitted_at'] ?? date('Y-m-d')) ?></td>
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

<p class="small" style="margin-top:10px;">Generated by Madras High Court SSA Portal · <?= date('d-m-Y h:i A') ?></p>
</body>
</html>
