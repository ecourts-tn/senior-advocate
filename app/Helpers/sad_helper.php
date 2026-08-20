<?php

if (! function_exists('sad_status_badge')) {
    function sad_status_badge(?string $status): string
    {
        $map = [
            'draft'            => 'secondary',
            'submitted'        => 'primary',
            'under_review'     => 'info',
            'pending_approval' => 'dark',
            'approved'         => 'success',
            'listed'           => 'success',
            'waitlisted'       => 'warning',
            'rejected'         => 'danger',
            'returned'         => 'warning',
        ];
        $label = \App\Models\ApplicationModel::STATUSES[$status] ?? ucfirst((string) $status);
        $class = $map[$status] ?? 'secondary';

        return '<span class="badge bg-' . $class . '">' . esc($label) . '</span>';
    }
}

if (! function_exists('sad_bool_label')) {
    function sad_bool_label($value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }
        // PostgreSQL may return t/f
        if ($value === true || $value === 't' || $value === '1' || $value === 1 || $value === 'true') {
            return 'Yes';
        }
        if ($value === false || $value === 'f' || $value === '0' || $value === 0 || $value === 'false') {
            return 'No';
        }

        return (string) $value;
    }
}

if (! function_exists('sad_step_labels')) {
    function sad_step_labels(): array
    {
        return [
            1 => 'Personal Details',
            2 => 'Enrolment & Practice',
            3 => 'Judgments (L-1 / L-2)',
            4 => 'Pro Bono & Academic',
            5 => 'Practice Domain',
            6 => 'Declarations',
            7 => 'Uploads & Submit',
        ];
    }
}

if (! function_exists('sad_age_as_on_date')) {
    /**
     * ISO date used for age / practice calc: 01 January of the cycle year.
     */
    function sad_age_as_on_date(): string
    {
        return \App\Models\ApplicationModel::ageAsOnDate();
    }
}

if (! function_exists('sad_age_as_on_label')) {
    /**
     * Human label for age reference date, e.g. "01.01.2026".
     */
    function sad_age_as_on_label(): string
    {
        return \App\Models\ApplicationModel::ageAsOnLabel();
    }
}

if (! function_exists('current_user')) {
    function current_user(): ?array
    {
        $session = session();
        if (! $session->get('user_id')) {
            return null;
        }

        return [
            'id'    => $session->get('user_id'),
            'name'  => $session->get('name'),
            'email' => $session->get('email'),
            'role'  => $session->get('role'),
        ];
    }
}

if (! function_exists('is_admin_role')) {
    /**
     * Staff roles that use the admin UI (not applicants).
     */
    function is_admin_role(): bool
    {
        return in_array(session()->get('role'), ['admin', 'reviewer', 'approver'], true);
    }
}

if (! function_exists('can_review_applications')) {
    /**
     * Temporarily: only admin decides applications (reviewer path disabled).
     */
    function can_review_applications(): bool
    {
        return session()->get('role') === 'admin';
    }
}

if (! function_exists('can_approve_applications')) {
    /**
     * Temporarily: only admin decides applications (approver path disabled).
     */
    function can_approve_applications(): bool
    {
        return session()->get('role') === 'admin';
    }
}
