<?php

namespace App\Libraries;

/**
 * WahaClient
 *
 * Client library for interacting with WhatsApp HTTP API (WAHA).
 * Sends automated notifications to KOLs for brief updates, draft reviews,
 * shipment tracking, and fee disbursements.
 */
class WahaClient
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $defaultSession;
    protected int $connectTimeout;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl        = rtrim(env('waha.baseUrl', 'http://localhost:3000'), '/');
        $this->apiKey         = (string) env('waha.apiKey', '');
        $this->defaultSession = (string) env('waha.session', 'default');
        $this->connectTimeout = (int) env('waha.connectTimeout', 3);
        $this->timeout        = (int) env('waha.timeout', 8);
    }

    /**
     * Normalize Indonesian phone number into WhatsApp chatId (e.g. 628123456789@c.us)
     */
    public static function formatChatId(string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($clean, '08')) {
            $clean = '628' . substr($clean, 2);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }

        if (!str_ends_with($clean, '@c.us')) {
            $clean .= '@c.us';
        }

        return $clean;
    }

    /**
     * Send standard text message
     */
    public function sendMessage(string $phone, string $message): array
    {
        $chatId = self::formatChatId($phone);
        $payload = [
            'chatId'  => $chatId,
            'text'    => $message,
            'session' => $this->defaultSession,
        ];

        return $this->request('/api/sendText', 'POST', $payload);
    }

    /**
     * Template 1: Pemberitahuan Brief Konten Siap
     */
    public function notifyBriefReady(string $phone, string $kolName, string $campaignTitle, string $taskTitle): array
    {
        $msg = "Halo *{$kolName}*! 👋\n\n";
        $msg .= "Brief konten untuk kampanye *{$campaignTitle}* ({$taskTitle}) telah selesai dibuat dan disetujui.\n\n";
        $msg .= "Silakan buka portal KOLAB.ID untuk membaca panduan storyline, do's & don'ts, dan referensi video.\n\n";
        $msg .= "Terima kasih!\n_Tim KOLAB.ID_";

        return $this->sendMessage($phone, $msg);
    }

    /**
     * Template 2: Permintaan Revisi Draft Video
     */
    public function notifyDraftRevision(string $phone, string $kolName, string $campaignTitle, string $taskTitle, string $notes): array
    {
        $msg = "Halo *{$kolName}*! 👋\n\n";
        $msg .= "Tim telah selesai mereview draft video untuk *{$campaignTitle}* ({$taskTitle}).\n\n";
        $msg .= "Ada beberapa catatan revisi yang perlu disesuaikan:\n";
        $msg .= "📝 _\"{$notes}\"_\n\n";
        $msg .= "Silakan unggah video hasil revisi di portal KOLAB.ID. Terima kasih!\n_Tim KOLAB.ID_";

        return $this->sendMessage($phone, $msg);
    }

    /**
     * Template 3: Draft Video Disetujui (Approved)
     */
    public function notifyDraftApproved(string $phone, string $kolName, string $campaignTitle, string $taskTitle): array
    {
        $msg = "Halo *{$kolName}*! 🎉\n\n";
        $msg .= "Kabar gembira! Draft video untuk kampanye *{$campaignTitle}* ({$taskTitle}) telah *DISETUJUI (APPROVED)* oleh tim.\n\n";
        $msg .= "Video Anda siap tayang sesuai jadwal posting. Jangan lupa kirimkan link postingan live setelah video tayang ya!\n\n";
        $msg .= "Terima kasih atas karya luar biasanya!\n_Tim KOLAB.ID_";

        return $this->sendMessage($phone, $msg);
    }

    /**
     * Template 4: Pengiriman Sampel Produk Dispatched
     */
    public function notifyShipment(string $phone, string $kolName, string $courier, string $resi): array
    {
        $msg = "Halo *{$kolName}*! 📦\n\n";
        $msg .= "Sampel produk untuk keperluan konten telah dikirimkan via *{$courier}*.\n";
        $msg .= "Nomor Resi: *{$resi}*\n\n";
        $msg .= "Mohon konfirmasi jika paket telah tiba ya. Selamat berkarya!\n_Tim Logistik KOLAB.ID_";

        return $this->sendMessage($phone, $msg);
    }

    /**
     * Template 5: Pencairan Dana / Invoice Terbayar
     */
    public function notifyPayment(string $phone, string $kolName, float $amount, string $invNumber): array
    {
        $formattedAmount = number_format($amount, 0, ',', '.');
        $msg = "Halo *{$kolName}*! 💸\n\n";
        $msg .= "Pembayaran kompensasi SOW Anda untuk invoice *{$invNumber}* sebesar *Rp {$formattedAmount}* telah berhasil ditransfer ke rekening Anda.\n\n";
        $msg .= "Terima kasih atas kerjasamanya yang luar biasa. Sampai jumpa di kampanye berikutnya!\n_Tim Keuangan KOLAB.ID_";

        return $this->sendMessage($phone, $msg);
    }

    /**
     * Internal cURL Request Executor
     */
    protected function request(string $endpoint, string $method = 'POST', ?array $data = null): array
    {
        $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
        $ch  = curl_init();

        $headers = ['Content-Type: application/json'];
        if (!empty($this->apiKey)) {
            $headers[] = 'X-Api-Key: ' . $this->apiKey;
        }

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => $headers,
        ];

        if (strtoupper($method) === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($data ?? []);
        }

        curl_setopt_array($ch, $options);
        $response  = curl_exec($ch);
        $httpCode  = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return [
                'success'   => false,
                'http_code' => $httpCode,
                'error'     => 'WAHA service connection failed: ' . $curlError,
            ];
        }

        return [
            'success'   => ($httpCode >= 200 && $httpCode < 300),
            'http_code' => $httpCode,
            'data'      => json_decode($response, true),
        ];
    }
}
