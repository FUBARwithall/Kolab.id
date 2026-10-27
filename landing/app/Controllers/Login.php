<?php

namespace App\Controllers;

use Config\Portal;

class Login extends BaseController
{
    public function index(): string
    {
        $portal = config(Portal::class);

        $data = [
            'active'           => 'login',
            'meta_title'       => 'Masuk — KOLAB.ID',
            'meta_description' => 'Pilih portal KOLAB.ID: Campaign Management atau KOL Datahub.',
            'og_type'          => 'website',
            'og_image'         => base_url('assets/images/hero-mockup.png'),
            'canonical'        => base_url('login'),
            'campaign_url'     => $portal->campaignUrl,
            'datahub_url'      => $portal->datahubUrl,
            'contact_url'      => $portal->contactUrl,
        ];

        return view('layout/header', $data)
            . view('login/index', $data)
            . view('layout/footer', $data);
    }
}
