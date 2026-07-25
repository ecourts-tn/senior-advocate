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
        table.form td.label { width: 32%; background: #f3f0ea; font-weight: bold; }
        .section { margin-top: 12px; font-weight: bold; font-size: 12px; color: #0b1f3a; }
        .decl { margin-top: 14px; border: 1px solid #333; padding: 8px; }
        .small { font-size: 9px; color: #444; }
        .photo { width: 90px; height: 110px; border: 1px solid #333; }
        .sig { max-height: 50px; max-width: 160px; }
    </style>
</head>
<body>
<div class="header">
    <div class="small">HIGH COURT OF MADRAS</div>
    <h1>Application-cum-Consent Letter</h1>
    <h2>for Designation of Senior Advocate [For Advocates]</h2>
    <div class="small">Rules for Designation of Senior Advocates by the High Court of Madras, 2026</div>
</div>

<div class="meta">
    Application No.: <strong><?= esc($app['application_no'] ?? 'DRAFT') ?></strong><br>
    Status: <?= esc($app['status'] ?? '') ?><br>
    Submitted: <?= esc($app['submitted_at'] ?? '—') ?>
</div>

<table class="form">
    <tr>
        <td class="label">1. Name of the Applicant-Advocate</td>
        <td><?= esc(trim(($app['title'] ?? '') . ' ' . ($app['full_name'] ?? ''))) ?></td>
        <td rowspan="4" style="width:100px;text-align:center;">
            <?php
            if (! empty($app['photo_path'])) {
                $p = WRITEPATH . 'uploads/' . $app['photo_path'];
                if (is_file($p)) {
                    $data = base64_encode(file_get_contents($p));
                    echo '<img class="photo" src="data:image/jpeg;base64,' . $data . '" alt="Photo">';
                } else {
                    echo '<div class="small">Photo</div>';
                }
            } else {
                echo '<div class="small">Photo</div>';
            }
            ?>
        </td>
    </tr>
    <tr>
        <td class="label">2. Date of Birth / Age (as on 01.01.2026)</td>
        <td><?= esc($app['date_of_birth'] ?? '') ?> / <?= esc($app['age_years'] ?? '—') ?> years</td>
    </tr>
    <tr>
        <td class="label">3. Address — Office</td>
        <td><?= nl2br(esc($app['address_office'] ?? '')) ?></td>
    </tr>
    <tr>
        <td class="label">3. Address — Residence</td>
        <td><?= nl2br(esc($app['address_residence'] ?? '')) ?></td>
    </tr>
    <tr>
        <td class="label">4. Contact</td>
        <td colspan="2">Landline: <?= esc($app['phone_landline'] ?? '—') ?> |
            Mobile: <?= esc($app['mobile'] ?? '') ?> |
            Email: <?= esc($app['email'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">5. Educational / Professional Qualifications</td>
        <td colspan="2"><?= nl2br(esc($app['qualifications'] ?? '')) ?></td>
    </tr>
    <tr>
        <td class="label">6. Enrolment</td>
        <td colspan="2">
            Date: <?= esc($app['enrolment_date'] ?? '') ?> |
            Number: <?= esc($app['enrolment_number'] ?? '') ?> |
            Bar Council: <?= esc($app['bar_council'] ?? '') ?><br>
            Practice: <?= (int) ($app['practice_years'] ?? 0) ?> years <?= (int) ($app['practice_months'] ?? 0) ?> months
        </td>
    </tr>
    <tr>
        <td class="label">7. Net Professional Income (₹ Lakhs p.a.)</td>
        <td colspan="2"><?= esc($app['net_income_lakhs'] ?? '—') ?></td>
    </tr>
    <tr>
        <td class="label">8. Bar Association member</td>
        <td colspan="2"><?= sad_bool_label($app['is_bar_association_member'] ?? null) ?>
            <?= ! empty($app['bar_association_name']) ? ' — ' . esc($app['bar_association_name']) : '' ?></td>
    </tr>
    <tr>
        <td class="label">9. Reported judgments (L-1)</td>
        <td colspan="2">SC: <?= (int) ($app['reported_sc'] ?? 0) ?> |
            HC: <?= (int) ($app['reported_hc'] ?? 0) ?> |
            District/Tribunals: <?= (int) ($app['reported_district'] ?? 0) ?></td>
    </tr>
    <tr>
        <td class="label">10. Unreported judgments (L-2)</td>
        <td colspan="2">SC: <?= (int) ($app['unreported_sc'] ?? 0) ?> |
            HC: <?= (int) ($app['unreported_hc'] ?? 0) ?> |
            District/Tribunals: <?= (int) ($app['unreported_district'] ?? 0) ?></td>
    </tr>
    <tr>
        <td class="label">11. Pro Bono / Amicus Curiae</td>
        <td colspan="2">Pro Bono: <?= (int) ($app['pro_bono_total'] ?? 0) ?> |
            Amicus: <?= (int) ($app['amicus_total'] ?? 0) ?></td>
    </tr>
    <tr>
        <td class="label">12. First-generation lawyer</td>
        <td colspan="2"><?= sad_bool_label($app['is_first_generation'] ?? null) ?></td>
    </tr>
    <tr>
        <td class="label">13. Academic work (L-4)</td>
        <td colspan="2">Articles: <?= (int) ($app['academic_articles_count'] ?? 0) ?> |
            Books: <?= (int) ($app['academic_books_count'] ?? 0) ?> |
            Teaching: <?= (int) ($app['teaching_assignments_count'] ?? 0) ?> |
            Guest lectures: <?= (int) ($app['guest_lectures_count'] ?? 0) ?></td>
    </tr>
    <tr>
        <td class="label">14. Courts practiced</td>
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
                    echo esc(($c['court'] ?? '') . ' (' . ($c['from'] ?? '') . ' – ' . ($c['to'] ?? '') . ')') . '<br>';
                }
            }
            ?>
        </td>
    </tr>
    <tr>
        <td class="label">15. Tribunals</td>
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
                    echo esc(($t['tribunal'] ?? '') . ' (' . ($t['from'] ?? '') . ' – ' . ($t['to'] ?? '') . ')') . '<br>';
                }
            }
            ?>
        </td>
    </tr>
    <tr>
        <td class="label">16. Nature of practice</td>
        <td colspan="2"><?= nl2br(esc($app['nature_of_practice'] ?? '')) ?></td>
    </tr>
    <tr>
        <td class="label">17. Field of law / domain expertise</td>
        <td colspan="2"><?= nl2br(esc($app['field_of_law'] ?? '')) ?></td>
    </tr>
    <tr>
        <td class="label">18. Earlier MHC application</td>
        <td colspan="2"><?= sad_bool_label($app['applied_mhc_earlier'] ?? null) ?>
            <?= esc($app['applied_mhc_date'] ?? '') ?> <?= esc($app['applied_mhc_status'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">19. Other Court applications</td>
        <td colspan="2"><?= sad_bool_label($app['applied_other_court'] ?? null) ?> <?= esc($app['applied_other_details'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">20. FIR lodged</td>
        <td colspan="2"><?= sad_bool_label($app['fir_lodged'] ?? null) ?> <?= esc($app['fir_details'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">21. Party to criminal case</td>
        <td colspan="2"><?= sad_bool_label($app['criminal_case_party'] ?? null) ?> <?= esc($app['criminal_case_details'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">22. Bar Council proceedings</td>
        <td colspan="2"><?= sad_bool_label($app['bar_council_proceedings'] ?? null) ?> <?= esc($app['bar_council_details'] ?? '') ?></td>
    </tr>
    <tr>
        <td class="label">23. General state of health</td>
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
        Advocates Act and Bar Council Act, as well as these Rules.</p>
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

<p class="small" style="margin-top:10px;">Generated by Madras High Court SAD Portal · <?= date('d-m-Y H:i') ?></p>
</body>
</html>
