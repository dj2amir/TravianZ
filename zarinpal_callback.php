<?php

#################################################################################
##  Filename       : zarinpal_callback.php                                     ##
##  Type           : Payment Callback Handler                                  ##
## --------------------------------------------------------------------------- ##
##  Purpose        : Receives redirect from Zarinpal after payment, verifies   ##
##                   the transaction, and credits gold to the player.          ##
## --------------------------------------------------------------------------- ##
##  Flow           : 1. Read Authority + Status from GET params                ##
##                   2. If Status=OK, verify via Zarinpal API                   ##
##                   3. If verified, credit gold to user + log transaction      ##
##                   4. Redirect to a2b2.php with success/failure message      ##
#################################################################################

session_start();

// Bootstrap
$autoprefix = '';
for ($i = 0; $i < 5; $i++) {
    $autoprefix = str_repeat('../', $i);
    if (file_exists($autoprefix . 'autoloader.php')) { break; }
}
require_once $autoprefix . 'autoloader.php';
require_once $autoprefix . 'GameEngine/config.php';
require_once $autoprefix . 'GameEngine/Database.php';
require_once $autoprefix . 'GameEngine/Zarinpal.php';

$authority = $_GET['Authority'] ?? '';
$status    = $_GET['Status']    ?? '';

// Redirect to game if no authority
if (empty($authority)) {
    header('Location: ' . HOMEPAGE);
    exit;
}

// Get stored transaction info from session
$txKey       = 'zp_tx_' . $authority;
$txData      = $_SESSION[$txKey] ?? null;
$uid         = (int)($txData['uid']     ?? ($_SESSION['id'] ?? 0));
$amountRials = (int)($txData['amount']  ?? 0);
$goldAmount  = (int)($txData['gold']    ?? 0);
$packageLabel = $txData['package'] ?? 'Gold Package';

// If user cancelled or failed
if ($status !== 'OK') {
    unset($_SESSION[$txKey]);
    header('Location: ' . HOMEPAGE . 'a2b2.php?zp=cancelled');
    exit;
}

// Verify with Zarinpal
$zp = new Zarinpal();
$verify = $zp->verify($amountRials, $authority);

if (!$verify['success']) {
    // Log failed verification
    $err = $zp->getLastError();
    if ($uid > 0 && function_exists('logZarinpalTransaction')) {
        logZarinpalTransaction($uid, $amountRials, $goldAmount, $authority, 'FAILED', $err, $packageLabel);
    }
    unset($_SESSION[$txKey]);
    header('Location: ' . HOMEPAGE . 'a2b2.php?zp=failed&msg=' . urlencode($err));
    exit;
}

$refId   = $verify['refId'];
$cardPan = $verify['cardPan'];

// Prevent double-credit: check if this refId was already processed
$existing = mysqli_query($database->dblink,
    "SELECT id FROM " . TB_PREFIX . "zarinpal_transactions WHERE ref_id = $refId LIMIT 1"
);

if ($existing && mysqli_num_rows($existing) > 0) {
    // Already processed - just redirect to success page
    unset($_SESSION[$txKey]);
    header('Location: ' . HOMEPAGE . 'a2b2.php?zp=success&ref=' . $refId);
    exit;
}

// Credit gold to user
if ($uid > 0 && $goldAmount > 0) {
    // Log Zarinpal transaction FIRST (so it's recorded even if gold credit fails)
    $escUid     = (int)$uid;
    $escAmount  = (int)$amountRials;
    $escGold    = (int)$goldAmount;
    $escAuth    = mysqli_real_escape_string($database->dblink, $authority);
    $escRef     = (int)$refId;
    $escCard    = mysqli_real_escape_string($database->dblink, $cardPan);
    $escLabel   = mysqli_real_escape_string($database->dblink, $packageLabel);
    $escTime    = time();

    mysqli_query($database->dblink,
        "INSERT INTO " . TB_PREFIX . "zarinpal_transactions 
         (uid, amount_rial, gold, authority, ref_id, card_pan, package_label, status, created_at)
         VALUES ($escUid, $escAmount, $escGold, '$escAuth', $escRef, '$escCard', '$escLabel', 'SUCCESS', $escTime)"
    );

    // Add gold
    mysqli_query($database->dblink,
        "UPDATE " . TB_PREFIX . "users SET gold = gold + $goldAmount WHERE id = $uid"
    );

    // Log in gold_fin_log
    $details = "Zarinpal - $packageLabel - Ref: $refId - Card: $cardPan";
    $database->addGoldFinLog(0, $uid, 'Gold Purchase', $goldAmount, $details);
}

// Clean up session
unset($_SESSION[$txKey]);

// Redirect to success page in game
header('Location: ' . HOMEPAGE . 'a2b2.php?zp=success&ref=' . $refId);
exit;
