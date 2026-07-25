<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Please log in to continue.');
        }

        if (! empty($arguments)) {
            $role = $session->get('role');
            if (! in_array($role, $arguments, true)) {
                return redirect()->to('/')->with('error', 'You are not authorised to access that page.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
