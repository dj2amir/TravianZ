<?php
include_once("GameEngine/Generator.php");
$start_timer = $generator->pageLoadTimeStart();

use App\Utils\AccessLogger;

include_once("GameEngine/Village.php");
AccessLogger::logRequest();

if(isset($_GET['newdid'])) {
	$_SESSION['wid'] = $_GET['newdid'];
	header("Location: ".$_SERVER['PHP_SELF']);
	exit;
}
else {
	$building->procBuild($_GET);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><?php echo SERVER_NAME ?> - PLUS Packages</title>
    <link rel="shortcut icon" href="favicon.ico"/>
	<meta http-equiv="cache-control" content="max-age=0" />
	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="expires" content="0" />
	<meta http-equiv="imagetoolbar" content="no" />
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<script src="mt-full.js?0faab" type="text/javascript"></script>
	<script src="unx.js?f4b7h" type="text/javascript"></script>
	<script src="new.js?0faab" type="text/javascript"></script>
	<link href="<?php echo GP_LOCATE; ?>lang/en/lang.css?f4b7d" rel="stylesheet" type="text/css" />
	<link href="<?php echo GP_LOCATE; ?>lang/en/compact.css?f4b7i" rel="stylesheet" type="text/css" />
	<?php
	// GP_LOCATE contine deja pachetul efectiv: alegerea jucatorului cand
	// e permisa si valida, altfel pachetul serverului (vezi config.php).
	echo "
	<link href='".GP_LOCATE."travian.css?e21d2' rel='stylesheet' type='text/css' />
	<link href='".GP_LOCATE."lang/en/lang.css?e21d2' rel='stylesheet' type='text/css' />";
	?>
	<script type="text/javascript">

		window.addEvent('domready', start);
	</script>
</head>


<body class="v35 ie ie8">
<div class="wrapper">
<img style="filter:chroma();" src="img/x.gif" id="msfilter" alt="" />
<div id="dynamic_header">
	</div>
<?php include("Templates/header.tpl"); ?>
<div id="mid">
<?php include("Templates/menu.tpl"); ?>
<?php
if(isset($_GET['id'])) {
$id = $_GET['id'];
} else {
$id = "";
}

// --- ZARINPAL PAYMENT INITIATION ---
// Packages: id => [gold, price_in_rials, label]
$zp_packages = [
    110  => [  'gold' => PLUS_PACKAGE_A_GOLD, 'rial' => defined('ZP_PACKAGE_A_RIAL') ? ZP_PACKAGE_A_RIAL : 199000,  'label' => 'Package A' ],
    111  => [  'gold' => PLUS_PACKAGE_B_GOLD, 'rial' => defined('ZP_PACKAGE_B_RIAL') ? ZP_PACKAGE_B_RIAL : 499000,  'label' => 'Package B' ],
    112  => [  'gold' => PLUS_PACKAGE_C_GOLD, 'rial' => defined('ZP_PACKAGE_C_RIAL') ? ZP_PACKAGE_C_RIAL : 999000,  'label' => 'Package C' ],
    113  => [  'gold' => PLUS_PACKAGE_D_GOLD, 'rial' => defined('ZP_PACKAGE_D_RIAL') ? ZP_PACKAGE_D_RIAL : 1999000, 'label' => 'Package D' ],
    3110 => [ 'gold' => PLUS_PACKAGE_E_GOLD, 'rial' => defined('ZP_PACKAGE_E_RIAL') ? ZP_PACKAGE_E_RIAL : 4999000, 'label' => 'Package E' ],
];

if (isset($_GET['zp']) && isset($zp_packages[$id])) {
    $pkg  = $zp_packages[$id];
    $uid  = (int)($session->uid ?? 0);
    $user = $uid > 0 ? $database->getUserArray($uid, 1) : null;
    $desc = SERVER_NAME . ' - ' . $pkg['label'] . ' (' . $pkg['gold'] . ' Gold)';
    $cb   = rtrim(HOMEPAGE, '/') . '/zarinpal_callback.php';

    require_once __DIR__ . '/GameEngine/Zarinpal.php';
    $zp = new Zarinpal();
    $req = $zp->request($pkg['rial'], $desc, $cb, [
        'mobile'   => $user['email'] ?? '',
        'order_id' => $uid . '-' . $id . '-' . time(),
    ]);

    if ($req['success']) {
        $_SESSION['zp_tx_' . $req['authority']] = [
            'uid'     => $uid,
            'amount'  => $pkg['rial'],
            'gold'    => $pkg['gold'],
            'package' => $pkg['label'],
        ];
        header('Location: ' . $req['url']);
        exit;
    }
    // Fall through to show error
    $zp_error = $zp->getLastError();
}
if ($id == 110) {
include("Templates/Plus/110.tpl");
}
if ($id == 111) {
include("Templates/Plus/111.tpl");
}
if ($id == 112) {
include("Templates/Plus/112.tpl");
}
if ($id == 113) {
include("Templates/Plus/113.tpl");
}
if ($id == 114) {
include("Templates/Plus/114.tpl");
}
if ($id == 116) {
include("Templates/Plus/116.tpl");
}
if ($id == 3110) {
include("Templates/Plus/3110.tpl");
}
?>

<div class="footer-stopper"></div>
<div class="clear"></div>

<?php
include("Templates/footer.tpl");
include("Templates/res.tpl");
?>
<div id="stime">
<div id="ltime">
<div id="ltimeWrap">
<?php echo CALCULATED_IN;?> <b><?php
echo round(($generator->pageLoadTimeEnd()-$start_timer)*1000);
?></b> ms

<br /><?php echo SERVER_TIME;?> <span id="tp1" class="b"><?php echo date('H:i:s'); ?></span>
</div>
	</div>
</div>

<div id="ce"></div>
</body>
</html>
