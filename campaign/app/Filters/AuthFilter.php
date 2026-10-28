<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     *
     * @param array|null $arguments Allowed roles (e.g. ['admin', 'brand', 'kol'])
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Whitelist public API endpoints and login routes
        $path = trim($request->getUri()->getPath(), '/');
        if (strpos($path, 'articles/feed') !== false || strpos($path, 'login') !== false) {
            return;
        }

        // 1. Check if user is logged in
        if (!$session->get('is_logged_in') || !$session->get('user_id')) {
            return redirect()->to('/campaign/login')->with('error', 'Silakan masuk ke portal Campaign terlebih dahulu.');
        }

        // 2. If specific roles are required, check user roles
        if (!empty($arguments)) {
            $userRoles = (array) $session->get('roles');

            // Admin always has access to everything
            if (in_array('admin', $userRoles, true)) {
                return;
            }

            // Check if user has at least one of the required roles
            $hasAccess = false;
            foreach ($arguments as $requiredRole) {
                if (in_array($requiredRole, $userRoles, true)) {
                    $hasAccess = true;
                    break;
                }
            }

            if (!$hasAccess) {
                if (in_array('kol', $userRoles, true)) {
                    return redirect()->to('/datahub/dashboard')->with('error', 'Akun Anda terdaftar sebagai Talent KOL. Silakan akses portal KOL Data Hub.');
                }
                return redirect()->to('/campaign/login')->with('error', 'Akses dibatasi. Modul ini memerlukan otorisasi Leader KOL / Superadmin.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed
    }
}
