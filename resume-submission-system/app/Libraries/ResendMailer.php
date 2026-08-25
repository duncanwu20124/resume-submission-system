<?php

namespace App\Libraries;

class ResendMailer
{
    private const API_URL = 'https://api.resend.com/emails';

    private ?string $apiKey;
    private string $fromEmail;
    private string $fromName;

    public function __construct()
    {
        $this->apiKey    = env('RESEND_API_KEY');
        $this->fromEmail = env('RESEND_FROM_EMAIL') ?? 'onboarding@resend.com';
        $this->fromName  = env('RESEND_FROM_NAME') ?? 'Resume Submission System';
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Send a plain-text email through the Resend API.
     */
    public function send(string $to, string $subject, string $text): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $payload = json_encode([
            'from'    => "{$this->fromName} <{$this->fromEmail}>",
            'to'      => [$to],
            'subject' => $subject,
            'text'    => $text,
        ]);

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            // Windows PHP builds often ship without a configured CA bundle,
            // which makes cURL reject any HTTPS certificate. Point it at a
            // bundled Mozilla CA bundle instead of disabling verification.
            CURLOPT_CAINFO         => WRITEPATH . 'certs/cacert.pem',
        ]);

        $response  = curl_exec($ch);
        $curlError = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            log_message('error', 'Resend email sending failed (cURL): ' . $curlError);
            return false;
        }

        if ($statusCode < 200 || $statusCode >= 300) {
            log_message('error', 'Resend email sending failed (HTTP ' . $statusCode . '): ' . $response);
            return false;
        }

        return true;
    }
}
