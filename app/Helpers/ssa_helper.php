<?php

if (! function_exists('ssa_status_badge')) {
    function ssa_status_badge(?string $status): string
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

if (! function_exists('ssa_bool_label')) {
    function ssa_bool_label($value): string
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

if (! function_exists('ssa_step_labels')) {
    function ssa_step_labels(): array
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

if (! function_exists('ssa_age_as_on_date')) {
    /**
     * ISO date used for age / practice calc: notification date (Y-m-d).
     *
     * @param array<string, mixed>|null $app Optional application (uses its notification when set)
     */
    function ssa_age_as_on_date(?array $app = null): string
    {
        return \App\Models\ApplicationModel::ageAsOnDate($app);
    }
}

if (! function_exists('ssa_age_as_on_label')) {
    /**
     * Human label for the notification reference date, e.g. "15.08.2026".
     *
     * @param array<string, mixed>|null $app Optional application (uses its notification when set)
     */
    function ssa_age_as_on_label(?array $app = null): string
    {
        return \App\Models\ApplicationModel::ageAsOnLabel($app);
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
