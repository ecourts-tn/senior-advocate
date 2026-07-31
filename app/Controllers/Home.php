<?php

namespace App\Controllers;

use Config\Site;

class Home extends BaseController
{
    public function index()
    {
        // Home page temporarily disabled — send visitors to login / their dashboard.
        if (session()->get('user_id')) {
            if (is_admin_role()) {
                return redirect()->to('/admin');
            }

            return redirect()->to('/applicant/dashboard');
        }

        return redirect()->to('/login');
    }

    public function instructions()
    {
        return view('instructions', [
            'title' => 'Instructions for Applicants',
        ]);
    }

    /**
     * GIGW mandatory policy / information pages.
     */
    public function policy(string $slug = '')
    {
        /** @var Site $site */
        $site = config(Site::class);
        $slug = strtolower(trim($slug));

        if ($slug === '' || ! isset($site->policies[$slug])) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $policy = $site->policies[$slug];

        return view('policies/show', [
            'title'      => $policy['title'],
            'policy'     => $policy,
            'slug'       => $slug,
            'policies'   => $site->policies,
            'lastUpdated'=> $site->lastUpdated,
            'site'       => $site,
        ]);
    }
}
