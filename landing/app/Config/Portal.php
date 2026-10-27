<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Portal URLs used by the Login selector page.
 * Override values via .env without touching code.
 */
class Portal extends BaseConfig
{
    /**
     * Campaign management portal URL.
     * Override via .env: landing.campaignUrl = 'https://app.kolab.id'
     */
    public string $campaignUrl = 'http://localhost:8000';

    /**
     * Data Hub / analytics portal URL.
     * Override via .env: landing.datahubUrl = 'https://datahub.kolab.id'
     */
    public string $datahubUrl = 'http://localhost:8081';

    /**
     * Contact / mailto link.
     * Override via .env: landing.contactUrl = 'mailto:hello@kolab.id'
     */
    public string $contactUrl = 'mailto:hello@kolab.id';

    public function __construct()
    {
        parent::__construct();

        $this->campaignUrl = rtrim(
            (string) env('landing.campaignUrl', $this->campaignUrl),
            '/'
        );
        $this->datahubUrl = rtrim(
            (string) env('landing.datahubUrl', $this->datahubUrl),
            '/'
        );
        $this->contactUrl = (string) env('landing.contactUrl', $this->contactUrl);
    }
}
