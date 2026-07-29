<?php
#################################################################################
##  Filename       : zarinpalTest.php                                          ##
##  Type           : Admin AJAX Handler – Test Zarinpal Connection             ##
## --------------------------------------------------------------------------- ##
##  Purpose        : Called via fetch() from the admin Zarinpal page.          ##
##                   Tests the Zarinpal API connection by sending a small      ##
##                   payment request and returning the result as JSON.         ##
#################################################################################

if (!isset($_SESSION)) session_start();
if (($_SESSION['access'] ?? 0) < 9) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// CSRF: verify token on POST
require_once __DIR__ . '/../csrf.php';
csrf_verify();

// Bootstrap
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../Zarinpal.php';

header('Content-Type: application/json; charset=utf-8');

$zp = new Zarinpal();

// Quick pre-checks
if (empty(ZARINPAL_MERCHANT_ID)) {
    echo json_encode([
        'ok'    => false,
        'error' => defined('TZ_ZARINPAL_MERCHANT_ID') ? TZ_ZARINPAL_MERCHANT_ID . ' not set.' : 'Merchant ID not configured.',
        'hint'  => 'Set ZARINPAL_MERCHANT_ID in Admin → Server Configuration → Extra Settings.',
    ]);
    exit;
}

$mode = ZARINPAL_SANDBOX ? 'Sandbox' : 'Production';

// Make a test request with 1000 Rials (100 Tomans)
$start  = microtime(true);
$result = $zp->request(
    1000,
    'TravianZ Connection Test — ' . SERVER_NAME,
    HOMEPAGE . 'zarinpal_callback.php',
    ['order_id' => 'connection-test-' . time()]
);
$elapsed = round((microtime(true) - $start) * 1000); // ms

if ($result['success']) {
    echo json_encode([
        'ok'        => true,
        'mode'      => $mode,
        'authority' => $result['authority'],
        'url'       => $result['url'],
        'elapsed'   => $elapsed,
        'merchant'  => substr(ZARINPAL_MERCHANT_ID, 0, 8) . '...',
    ]);
} else {
    $rawResponse = $zp->getLastResponse();
    echo json_encode([
        'ok'       => false,
        'mode'     => $mode,
        'error'    => $result['error'] ?? $zp->getLastError(),
        'elapsed'  => $elapsed,
        'raw'      => $rawResponse ?: null,
        'merchant' => substr(ZARINPAL_MERCHANT_ID, 0, 8) . '...',
    ]);
}
exit;
