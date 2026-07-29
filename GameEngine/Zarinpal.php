<?php

#################################################################################
##  Filename       : Zarinpal.php                                              ##
##  Type           : Payment Gateway Integration                               ##
## --------------------------------------------------------------------------- ##
##  Purpose        : Zarinpal payment gateway (Iranian payment processor)      ##
##                   Full automatic flow: request → redirect → verify → credit ##
## --------------------------------------------------------------------------- ##
##  API Version    : Zarinpal REST API v4                                      ##
##  Sandbox        : Any 36-char string as merchant_id                         ##
##  Production     : Real merchant_id from zarinpal.com panel                  ##
#################################################################################

class Zarinpal
{
    /** @var string Merchant ID (36 chars, UUID format) */
    private $merchantId;

    /** @var bool Sandbox mode */
    private $sandbox;

    /** @var string Base URL for API requests */
    private $apiBase;

    /** @var string StartPay redirect URL */
    private $startPayUrl;

    /** @var array Last API response */
    private $lastResponse = [];

    /** @var string Last error message */
    private $lastError = '';

    /**
     * @param string|null $merchantId Merchant ID. If null, reads from ZARINPAL_MERCHANT_ID config.
     * @param bool|null   $sandbox    Sandbox mode. If null, reads from ZARINPAL_SANDBOX config.
     */
    public function __construct($merchantId = null, $sandbox = null)
    {
        $this->merchantId = $merchantId ?? (defined('ZARINPAL_MERCHANT_ID') ? ZARINPAL_MERCHANT_ID : '');
        $this->sandbox    = $sandbox    ?? (defined('ZARINPAL_SANDBOX')    ? (bool)ZARINPAL_SANDBOX    : true);

        if ($this->sandbox) {
            $this->apiBase    = 'https://sandbox.zarinpal.com/pg/v4/payment/';
            $this->startPayUrl = 'https://sandbox.zarinpal.com/pg/StartPay/';
        } else {
            $this->apiBase     = 'https://payment.zarinpal.com/pg/v4/payment/';
            $this->startPayUrl  = 'https://payment.zarinpal.com/pg/StartPay/';
        }
    }

    /**
     * Send a payment request to Zarinpal and get Authority code.
     *
     * @param  int    $amount      Amount in Rials (e.g., 100000 for 10,000 Tomans = 100,000 Rials).
     * @param  string $description Transaction description (max 500 chars).
     * @param  string $callbackUrl Full URL where Zarinpal redirects after payment.
     * @param  array  $metadata    Optional: mobile, email, order_id.
     * @return array{success:bool, authority:string, url:string}|array{success:bool, error:string}
     */
    public function request($amount, $description, $callbackUrl, $metadata = [])
    {
        if (empty($this->merchantId)) {
            $this->lastError = 'Zarinpal Merchant ID is not configured.';
            return ['success' => false, 'error' => $this->lastError];
        }

        $data = [
            'merchant_id'  => $this->merchantId,
            'amount'       => (int)$amount,
            'description'  => mb_substr($description, 0, 500),
            'callback_url' => $callbackUrl,
        ];

        if (!empty($metadata)) {
            $data['metadata'] = $metadata;
        }

        $result = $this->apiCall('request.json', $data);

        if (!$result) {
            return ['success' => false, 'error' => $this->lastError ?: 'API connection failed'];
        }

        $code = $result['data']['code'] ?? 0;

        if ($code === 100 && !empty($result['data']['authority'])) {
            $authority = $result['data']['authority'];
            $this->lastError = '';
            return [
                'success'   => true,
                'authority' => $authority,
                'url'       => $this->startPayUrl . $authority,
            ];
        }

        $errorCode = $result['errors']['code'] ?? $code;
        $errorMsg  = $result['errors']['message'] ?? ($result['data']['message'] ?? 'Unknown error');
        $this->lastError = "Zarinpal Error ($errorCode): $errorMsg";
        return ['success' => false, 'error' => $this->lastError];
    }

    /**
     * Verify a payment after user returns from Zarinpal.
     *
     * @param  int    $amount    Original amount in Rials (must match request).
     * @param  string $authority The Authority code from callback.
     * @return array{success:bool, refId:int, cardPan:string}
     */
    public function verify($amount, $authority)
    {
        if (empty($this->merchantId)) {
            $this->lastError = 'Zarinpal Merchant ID is not configured.';
            return ['success' => false, 'error' => $this->lastError];
        }

        $data = [
            'merchant_id' => $this->merchantId,
            'amount'      => (int)$amount,
            'authority'   => $authority,
        ];

        $result = $this->apiCall('verify.json', $data);

        if (!$result) {
            return ['success' => false, 'error' => $this->lastError ?: 'API verification failed'];
        }

        $code = $result['data']['code'] ?? 0;

        if ($code === 100 || $code === 101) {
            $this->lastError = '';
            return [
                'success' => true,
                'refId'   => (int)($result['data']['ref_id'] ?? 0),
                'cardPan' => (string)($result['data']['card_pan'] ?? ''),
            ];
        }

        $errorCode = $result['errors']['code'] ?? $code;
        $errorMsg  = $result['errors']['message'] ?? ($result['data']['message'] ?? 'Unknown error');
        $this->lastError = "Zarinpal Verify Error ($errorCode): $errorMsg";
        return ['success' => false, 'error' => $this->lastError];
    }

    /**
     * Get the last error message.
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Get the StartPay redirect URL for a given authority.
     * @param  string $authority
     * @return string
     */
    public function getPayUrl($authority)
    {
        return $this->startPayUrl . $authority;
    }

    /**
     * Make an API call to Zarinpal.
     *
     * @param  string $endpoint E.g., 'request.json' or 'verify.json'.
     * @param  array  $data     Request payload.
     * @return array|null       Decoded JSON or null on failure.
     */
    private function apiCall($endpoint, array $data)
    {
        $ch = curl_init($this->apiBase . $endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($data, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            $this->lastError = "cURL Error: $curlErr";
            return null;
        }

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            $this->lastError = "Invalid API response (not JSON): " . substr($response, 0, 200);
            return null;
        }

        $this->lastResponse = $decoded;
        return $decoded;
    }

    /**
     * Get the last raw API response.
     * @return array
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }
}
