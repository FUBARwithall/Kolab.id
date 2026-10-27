<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    protected $helpers = ['url', 'html'];

    /**
     * @param CLIRequest|IncomingRequest $request
     *
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);
    }

    /**
     * Tampilkan halaman offline 503 yang ramah ketika service API artikel tidak dapat dihubungi.
     *
     * @param string $message
     * @param string $title
     * @return string
     */
    protected function renderOffline(
        string $message = 'Artikel sedang tidak dapat dimuat. Silakan coba beberapa saat lagi.',
        string $title = 'Layanan Sedang Mengalami Kendala — KOLAB.ID'
    ): string {
        $this->response->setStatusCode(503);
        $this->response->setHeader('Retry-After', '60');

        $data = [
            'offline_title'   => $title,
            'offline_message' => $message,
        ];

        return view('errors/offline', $data);
    }
}
