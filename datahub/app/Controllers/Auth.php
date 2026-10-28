<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * 1. Hub Pemilihan Portal (Style Landing Page)
     * Menampilkan 3 kartu dengan tombol menuju halaman login masing-masing portal
     */
    public function loginHub()
    {
        $session = session();
        if ($session->get('is_logged_in')) {
            return $this->redirectBasedOnRole($session->get('roles'));
        }

        $data = [
            'active'           => 'login',
            'meta_title'       => 'Pilih Portal Masuk — KOLAB.ID',
            'meta_description' => 'Single sign-on terpadu untuk Campaign Management, KOL Data Hub, dan Portal Creator KOLAB.ID.',
            'canonical'        => base_url('login'),
        ];

        return view('landing/layout/header', $data)
            . view('auth/login_hub', $data)
            . view('landing/layout/footer', $data);
    }

    /**
     * Alias untuk login default
     */
    public function login()
    {
        return $this->loginHub();
    }

    /**
     * 2a. Form Login Campaign Management (Style Adel AdminLTE)
     */
    public function loginCampaign()
    {
        $session = session();
        if ($session->get('is_logged_in')) {
            return redirect()->to(base_url('campaign'));
        }

        $data = [
            'portal'         => 'campaign',
            'themeColor'     => 'primary',
            'icon'           => 'fas fa-bullhorn',
            'badgeText'      => 'CAMPAIGN PORTAL',
            'portalTitle'    => 'Campaign Management',
            'portalSubtitle' => 'Masuk untuk kelola brief, review draft, sampel & invoice',
            'formAction'     => base_url('campaign/login'),
            'demoEmail'      => 'admin@kolab.id',
            'demoPass'       => 'admin123',
            'demoRole'       => 'Admin Internal',
        ];

        return view('auth/adel_login', $data);
    }

    /**
     * 2b. Form Login KOL Data Hub (Style Adel AdminLTE)
     */
    public function loginDatahub()
    {
        $session = session();
        $currentUser = null;

        if ($session->get('is_logged_in')) {
            $roles = (array) ($session->get('roles') ?? []);
            if (in_array('admin', $roles, true) || in_array('kol', $roles, true) || in_array('leader', $roles, true) || in_array('staff', $roles, true)) {
                $currentUser = [
                    'name'  => $session->get('name') ?? 'Pengguna',
                    'email' => $session->get('email') ?? '',
                    'role'  => in_array('admin', $roles, true) ? 'Superadmin' : (in_array('kol', $roles, true) ? 'Talent KOL' : 'Internal Staff'),
                ];
            } else {
                $session->remove(['user_id', 'name', 'email', 'roles', 'portal', 'is_logged_in']);
            }
        }

        $data = [
            'portal'         => 'datahub',
            'themeColor'     => 'success',
            'icon'           => 'fas fa-database',
            'badgeText'      => 'DATAHUB PORTAL',
            'portalTitle'    => 'KOL Data Hub',
            'portalSubtitle' => 'Database Intelijen KOL, Scraper, Rate Card & Market Benchmark',
            'formAction'     => '/datahub/login',
            'currentUser'    => $currentUser,
            'demoEmail'      => 'admin@kolab.id',
            'demoPass'       => 'admin123',
            'demoRole'       => 'Superadmin (Full Datahub Access)',
            'demoAccounts'   => [
                [
                    'role'  => 'Superadmin',
                    'email' => 'admin@kolab.id',
                    'pass'  => 'admin123',
                    'icon'  => 'fas fa-user-shield text-danger',
                    'desc'  => 'Akses Penuh: Scraper, CRUD KOL, Rate Card, Settings',
                ],
                [
                    'role'  => 'KOL / Talent Creator',
                    'email' => 'kol@kolab.id',
                    'pass'  => 'admin123',
                    'icon'  => 'fas fa-user-astronaut text-warning',
                    'desc'  => 'Akses Terbatas: Direktori KOL, Benchmark Rate, Profil Saya',
                ],
            ],
        ];

        return view('auth/adel_login', $data);
    }

    public function loginKol()
    {
        return redirect()->to(base_url('login'));
    }

    /**
     * Process login submission from specific portal login form
     */
    public function attemptLogin()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[5]',
            'portal'   => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email    = (string) $this->request->getPost('email');
        $password = (string) $this->request->getPost('password');

        $user = $this->userModel->authenticate($email, $password);

        if (!$user) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Email atau kata sandi tidak cocok, atau akun Anda belum aktif.');
        }

        $roles   = (array) ($user['roles'] ?? []);
        $isAdmin = in_array('admin', $roles, true);
        $isKol   = in_array('kol', $roles, true);
        $isStaffOrLeader = in_array('staff', $roles, true) || in_array('leader', $roles, true);

        // RBAC Datahub: Khusus Superadmin dan Talent KOL
        if ($isStaffOrLeader && !$isAdmin) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Akun ' . esc($email) . ' adalah akun operasional Campaign (Staff/Leader). Silakan masuk melalui Campaign Portal di /campaign/login.');
        }

        if (!$isAdmin && !$isKol) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Akun ' . esc($email) . ' tidak memiliki izin untuk mengakses KOL Data Hub.');
        }

        // Set session
        session()->set([
            'user_id'      => (int) $user['id'],
            'name'         => $user['name'],
            'email'        => $user['email'],
            'roles'        => $roles,
            'avatar'       => $user['avatar'] ?? null,
            'portal'       => 'datahub',
            'is_logged_in' => true,
        ]);

        return redirect()->to(base_url('dashboard'));
    }

    /**
     * Logout
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('login'))->with('success', 'Anda telah berhasil keluar.');
    }

    /**
     * Fallback redirect helper based on assigned roles
     */
    protected function redirectBasedOnRole(array $roles)
    {
        return redirect()->to(base_url('dashboard'));
    }
}
