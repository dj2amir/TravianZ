<?php
// TravianZ - DO NOT REMOVE COPYRIGHT NOTICE!
include("Templates/Plus/pmenu.tpl");

$zpEnabled  = defined('ZARINPAL_MERCHANT_ID') && ZARINPAL_MERCHANT_ID !== '';
$rialToToman = function($rial) { return number_format($rial / 10); }; // Convert Rials to Tomans for display

$packages = [
    ['id' => 110,  'key' => 'A', 'img' => 'Travian_paket_a.jpg', 'gold' => defined('PLUS_PACKAGE_A_GOLD') ? PLUS_PACKAGE_A_GOLD : 60,   'rial' => defined('ZP_PACKAGE_A_RIAL') ? ZP_PACKAGE_A_RIAL : 199000],
    ['id' => 111,  'key' => 'B', 'img' => 'Travian_paket_b.jpg', 'gold' => defined('PLUS_PACKAGE_B_GOLD') ? PLUS_PACKAGE_B_GOLD : 120,  'rial' => defined('ZP_PACKAGE_B_RIAL') ? ZP_PACKAGE_B_RIAL : 499000],
    ['id' => 112,  'key' => 'C', 'img' => 'Travian_paket_c.jpg', 'gold' => defined('PLUS_PACKAGE_C_GOLD') ? PLUS_PACKAGE_C_GOLD : 360,  'rial' => defined('ZP_PACKAGE_C_RIAL') ? ZP_PACKAGE_C_RIAL : 999000],
    ['id' => 113,  'key' => 'D', 'img' => 'Travian_paket_d.jpg', 'gold' => defined('PLUS_PACKAGE_D_GOLD') ? PLUS_PACKAGE_D_GOLD : 1000, 'rial' => defined('ZP_PACKAGE_D_RIAL') ? ZP_PACKAGE_D_RIAL : 1999000],
    ['id' => 3110, 'key' => 'E', 'img' => 'Travian_paket_e.jpg', 'gold' => defined('PLUS_PACKAGE_E_GOLD') ? PLUS_PACKAGE_E_GOLD : 2000, 'rial' => defined('ZP_PACKAGE_E_RIAL') ? ZP_PACKAGE_E_RIAL : 4999000],
];
?>

<style>
.products-wrapper { display:flex; flex-wrap:wrap; gap:12px; justify-content:center; margin:16px 0; }
.product-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; width:180px; text-align:center; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.06); transition:transform .15s,box-shadow .15s; }
.product-card:hover { transform:translateY(-3px); box-shadow:0 6px 20px rgba(0,0,0,.1); }
.product-card .pc-header { background:#0f172a; color:#f59e0b; padding:10px; font-weight:700; font-size:15px; }
.product-card .pc-img { padding:12px; }
.product-card .pc-img img { max-width:120px; height:auto; border-radius:8px; }
.product-card .pc-gold { color:#16a34a; font-weight:700; font-size:18px; padding:6px; }
.product-card .pc-price { color:#334155; font-size:14px; padding:4px; font-weight:500; }
.product-card .pc-price .toman { font-size:11px; color:#64748b; }
.product-card .pc-btn { padding:10px; }
.product-card .pc-btn a { display:block; background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; text-decoration:none; padding:10px; border-radius:8px; font-weight:700; font-size:14px; transition:filter .15s; }
.product-card .pc-btn a:hover { filter:brightness(1.1); }
.zp-disabled { opacity:.5; pointer-events:none; }
.zp-disabled .pc-btn a { background:#94a3b8; cursor:not-allowed; }
@media(max-width:600px){ .product-card{width:150px} }
</style>

<table class="rate_details lang_ltr lang_de" cellpadding="1" cellspacing="1">
	<thead><tr><th colspan="2"><?php echo defined('GOLD_SHOP') ? GOLD_SHOP : 'Gold Shop'; ?></th></tr></thead>
	<tbody>
		<tr>
			<td class="pic"><img src="img/bezahlung/Travian_verdienen.jpg" style="width:99px;height:99px;" alt="Gold Shop" /><div><?php echo defined('GOLD_SHOP') ? GOLD_SHOP : 'Gold Shop'; ?></div></td>
			<td class="desc">
				<?php if ($zpEnabled): ?>
				<b style="color:#d97706;">💰 <?php echo defined('TZ_ZARINPAL_TITLE') ? TZ_ZARINPAL_TITLE : 'Zarinpal Payment'; ?></b><br><br>
				<?php echo defined('TZ_ZARINPAL_DESC') ? TZ_ZARINPAL_DESC : 'Select your package below and pay securely via Zarinpal. Gold will be credited automatically after payment.'; ?><br><br>
				<?php else: ?>
				For gold purchases, please contact the server administrator.<br><br>
				<?php endif; ?>
				<b><?php echo defined('TZ_USERNAME') ? TZ_USERNAME : 'Username'; ?><br>
				<?php echo defined('PAYMENT_METHOD') ? PAYMENT_METHOD : 'Payment Method'; ?><br>
				<?php echo defined('TZ_ORDERED_PACKAGE') ? TZ_ORDERED_PACKAGE : 'Ordered Package'; ?></b>
			</td>
		</tr>
	</tbody>
</table>

<div class="products-wrapper">
<?php foreach($packages as $p): ?>
    <div class="product-card <?= !$zpEnabled ? 'zp-disabled' : '' ?>">
        <div class="pc-header"><?= htmlspecialchars(SERVER_NAME) ?> <?= $p['key'] ?></div>
        <div class="pc-img"><a href="<?= $zpEnabled ? 'plus1.php?id=' . $p['id'] . '&zp=1' : '#' ?>"><img src="img/bezahlung/<?= $p['img'] ?>" alt="Package <?= $p['key'] ?>" /></a></div>
        <div class="pc-gold"><?= $p['gold'] ?> Gold</div>
        <div class="pc-price"><?= $rialToToman($p['rial']) ?> <span class="toman">Toman</span></div>
        <div class="pc-btn">
            <a href="<?= $zpEnabled ? 'plus1.php?id=' . $p['id'] . '&zp=1' : '#' ?>">
                <?= $zpEnabled ? '🛒 Buy Now' : 'Unavailable' ?>
            </a>
        </div>
    </div>
<?php endforeach; ?>
</div>
<div style="text-align:center;padding:10px;font-style:italic;font-size:11px;color:#dc2626;"><b>Non-refundable. Gold is credited automatically after successful payment.</b></div>
</div>