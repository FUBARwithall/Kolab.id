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

        // Whitelist public login routes
        $path = trim($request->getUri()->getPath(), '/');
        if (strpos($path, 'login') !== false) {
            return;
        }

        // 1. Check if user is logged in
        if (!$session->get('is_logged_in') || !$session->get('user_id')) {
            return redirect()->to(base_url('login'))->with('error', 'Silakan masuk ke portal KOL Data Hub terlebih dahulu.');
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
                // If staff or leader attempts to enter Datahub, redirect to Campaign
                if (in_array('staff', $userRoles, true) || in_array('leader', $userRoles, true)) {
                    return redirect()->to(root_url('campaign/dashboard'))->with('error', 'Portal KOL Data Hub khusus untuk Talent KOL dan Superadmin. Silakan kelola operasional di Campaign Portal.');
                }
                return redirect()->to(base_url('dashboard'))->with('error', 'Akses dibatasi. Fitur ini memerlukan hak akses Superadmin.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed
    }
}
