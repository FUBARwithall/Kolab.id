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
        $currentUser = null;

        // Jika sudah login dengan role campaign yang valid, siapkan info user
        if ($session->get('is_logged_in')) {
            $roles = (array) ($session->get('roles') ?? []);
            if (in_array('admin', $roles, true) || in_array('leader', $roles, true) || in_array('staff', $roles, true)) {
                $currentUser = [
                    'name'  => $session->get('name') ?? 'Pengguna',
                    'email' => $session->get('email') ?? '',
                    'role'  => in_array('admin', $roles, true) ? 'Superadmin' : (in_array('leader', $roles, true) ? 'Leader KOL' : 'Staff Operational'),
                ];
            } else {
                // Sesi bukan role campaign (misal KOL), bersihkan agar bisa login akun internal
                $session->remove(['user_id', 'name', 'email', 'roles', 'portal', 'is_logged_in']);
            }
        }

        $data = [
            'portal'         => 'campaign',
            'themeColor'     => 'primary',
            'icon'           => 'fas fa-bullhorn',
            'badgeText'      => 'CAMPAIGN PORTAL',
            'portalTitle'    => 'Campaign Management',
            'portalSubtitle' => 'Kelola kampanye, brief, review video draft, logistik & invoice',
            'formAction'     => '/campaign/login',
            'currentUser'    => $currentUser,
            'demoEmail'      => 'leader@kolab.id',
            'demoPass'       => 'admin123',
            'demoRole'       => 'Leader KOL Manager',
            'demoAccounts'   => [
                [
                    'role'  => 'Leader KOL Manager',
                    'email' => 'leader@kolab.id',
                    'pass'  => 'admin123',
                    'icon'  => 'fas fa-user-tie text-primary',
                    'desc'  => 'Akses Strategis: Approval Brief, Kontrak, Invoicing & Budget',
                ],
                [
                    'role'  => 'Staff Operational Specialist',
                    'email' => 'staff@kolab.id',
                    'pass'  => 'admin123',
                    'icon'  => 'fas fa-user-cog text-info',
                    'desc'  => 'Akses Operasional: Review Draft Video, Logistik & Pengiriman Sampel',
                ],
                [
                    'role'  => 'Superadmin',
                    'email' => 'admin@kolab.id',
                    'pass'  => 'admin123',
                    'icon'  => 'fas fa-user-shield text-danger',
                    'desc'  => 'Akses Penuh: Seluruh Modul Campaign & Developer Tools',
                ],
            ],
        ];

        return view('auth/adel_login', $data);
    }

    /**
     * 2b. Form Login KOL Data Hub (Style Adel AdminLTE)
     */
    public function loginDatahub()
    {
        return redirect()->to(root_url('datahub/login'));
    }

    /**
     * 2c. Form Login Portal Creator / KOL (Style Adel AdminLTE)
     */
    public function loginKol()
    {
        return redirect()->to(root_url('datahub/login'));
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
        $portal   = strtolower((string) ($this->request->getPost('portal') ?? 'campaign'));

        $user = $this->userModel->authenticate($email, $password);

        if (!$user) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Email atau kata sandi tidak cocok, atau akun Anda belum aktif.');
        }

        $roles    = (array) ($user['roles'] ?? []);
        $isAdmin  = in_array('admin', $roles, true);
        $isLeader = in_array('leader', $roles, true);
        $isStaff  = in_array('staff', $roles, true);
        $isKol    = in_array('kol', $roles, true);

        // Validasi hak akses role terhadap portal Campaign
        if ($isKol && !$isAdmin && !$isLeader && !$isStaff) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Akun Talent KOL tidak dapat mengakses Campaign Management. Silakan masuk melalui portal KOL Data Hub.');
        }

        $targetUrl = base_url('dashboard');

        // Set session
        session()->set([
            'user_id'      => (int) $user['id'],
            'name'         => $user['name'],
            'email'        => $user['email'],
            'roles'        => $roles,
            'avatar'       => $user['avatar'] ?? null,
            'portal'       => $portal,
            'is_logged_in' => true,
        ]);

        return redirect()->to($targetUrl);
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
        if (in_array('kol', $roles, true) && !in_array('admin', $roles, true)) {
            return redirect()->to(root_url('datahub/dashboard'));
        }

        $portal = $this->request->getGet('portal') ?? $this->request->getPost('portal');
        if ($portal === 'datahub') {
            return redirect()->to(root_url('datahub/dashboard'));
        }

        return redirect()->to(base_url('dashboard'));
    }
}
