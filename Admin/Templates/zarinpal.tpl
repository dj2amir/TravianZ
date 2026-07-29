<?php
#################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 ##
## --------------------------------------------------------------------------- ##
##  Filename       : zarinpal.tpl                                              ##
##  Type           : Admin Panel – Zarinpal Transaction Log                    ##
## --------------------------------------------------------------------------- ##
##  License        : TravianZ Project                                          ##
##  Copyright      : TravianZ (c) 2010-2026. All rights reserved.              ##
#################################################################################
if ($_SESSION['access'] < ADMIN) die("Access Denied: You are not Admin!");

$pageNum = isset($_GET['zp_page']) ? max(1, (int)$_GET['zp_page']) : 1;
$perPage = 25;
$offset  = ($pageNum - 1) * $perPage;

$totalQ = mysqli_query($database->dblink,
    "SELECT COUNT(*) AS cnt FROM `" . TB_PREFIX . "zarinpal_transactions`"
);
$total  = ($totalQ && ($trow = mysqli_fetch_assoc($totalQ))) ? (int)$trow['cnt'] : 0;
$pages  = max(1, (int)ceil($total / $perPage));

$listQ  = mysqli_query($database->dblink,
    "SELECT z.*, u.username
     FROM `" . TB_PREFIX . "zarinpal_transactions` z
     LEFT JOIN `" . TB_PREFIX . "users` u ON u.id = z.uid
     ORDER BY z.created_at DESC
     LIMIT $offset, $perPage"
);

$list = [];
if ($listQ) {
    while ($row = mysqli_fetch_assoc($listQ)) {
        $list[] = $row;
    }
}

// Stats summary
$statsQ = mysqli_query($database->dblink,
    "SELECT
        COUNT(*) AS total_tx,
        SUM(CASE WHEN status='SUCCESS' THEN 1 ELSE 0 END) AS success,
        SUM(CASE WHEN status='SUCCESS' THEN gold ELSE 0 END) AS total_gold,
        SUM(CASE WHEN status='SUCCESS' THEN amount_rial ELSE 0 END) AS total_rial
     FROM `" . TB_PREFIX . "zarinpal_transactions`"
);
$stats = ($statsQ) ? mysqli_fetch_assoc($statsQ) : ['total_tx' => 0, 'success' => 0, 'total_gold' => 0, 'total_rial' => 0];
?>
<style>
.zp-wrap{color:#e2e8f0;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-size:12px;padding:6px 4px 26px}
.zp-wrap h2{font-size:18px;margin:0 0 4px;color:#fff}
.zp-wrap h2 span{color:#f59e0b}
.zp-intro{color:#94a3b8;font-size:11px;margin:0 0 14px;max-width:860px}
.zp-cards{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:18px}
.zp-stat{background:#111827;border:1px solid #1f2937;border-radius:8px;padding:14px 18px;flex:1;min-width:120px;text-align:center}
.zp-stat .val{font-size:24px;font-weight:800}
.zp-stat .lbl{font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;margin-top:4px}
.zp-stat.success .val{color:#4ade80}
.zp-stat.failed .val{color:#f87171}
.zp-stat.gold .val{color:#facc15}
.zp-stat.rial .val{color:#93c5fd}
.zp-table{width:100%;border-collapse:collapse;background:#0b1220;border:1px solid #1f2937;border-radius:8px}
.zp-table th{background:#111827;text-align:left;padding:7px 8px;font-size:9px;text-transform:uppercase;letter-spacing:.3px;color:#94a3b8;border-bottom:1px solid #1f2937;white-space:nowrap}
.zp-table td{padding:6px 8px;border-bottom:1px solid #14203a;font-size:11px;vertical-align:middle}
.zp-table tr:hover td{background:#0f1a30}
.zp-table .card-num{font-family:monospace;direction:ltr;text-align:right}
.zp-table .num{font-variant-numeric:tabular-nums}
.zp-empty{padding:22px;text-align:center;color:#64748b}
.zp-status{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:bold;text-transform:uppercase}
.zp-badge-success{background:#14532d;color:#bbf7d0}
.zp-badge-failed{background:#7f1d1d;color:#fecaca}
.zp-badge-pending{background:#78350f;color:#fde68a}
.zp-pager{display:flex;gap:4px;justify-content:center;margin-top:14px}
.zp-pager a{display:inline-block;padding:5px 10px;background:#1f2937;color:#93c5fd;border-radius:5px;text-decoration:none;font-size:11px;font-weight:600}
.zp-pager a:hover{background:#f59e0b;color:#111827}
.zp-pager strong{padding:5px 10px;background:#f59e0b;color:#111827;border-radius:5px;font-size:11px}
.zp-scroll{overflow-x:auto}
</style>

<div class="zp-wrap">
    <h2><?php echo defined('ADMIN_ZARINPAL_TRANSACTIONS') ? ADMIN_ZARINPAL_TRANSACTIONS : 'Zarinpal Transactions'; ?> <span>Zarinpal</span></h2>
    <p class="zp-intro"><?php echo defined('TZ_ZARINPAL_TRANSACTIONS') ? TZ_ZARINPAL_TRANSACTIONS : 'Zarinpal Transaction Log'; ?> – <?php echo defined('TZ_ZARINPAL') ? TZ_ZARINPAL : 'Zarinpal'; ?></p>

    <!-- Test Connection Button -->
    <div id="zp-test-area" style="margin-bottom:14px;">
        <button id="zp-test-btn" onclick="zpTestConnection()" style="background:#1f2937;color:#93c5fd;border:1px solid #334155;border-radius:6px;padding:8px 16px;cursor:pointer;font-size:12px;font-weight:600;transition:all .15s;">
            <?php echo defined('ZP_TEST_CONNECTION') ? ZP_TEST_CONNECTION : '🔌 Test Connection'; ?>
        </button>
        <span style="color:#64748b;font-size:10px;margin-left:8px;"><?php echo defined('ZP_TEST_HINT') ? ZP_TEST_HINT : ''; ?></span>
        <div id="zp-test-result" style="margin-top:10px;"></div>
    </div>
    <script>
    function zpTestConnection() {
        var btn  = document.getElementById('zp-test-btn');
        var area = document.getElementById('zp-test-result');
        btn.disabled = true;
        btn.innerHTML = '<?php echo defined('ZP_TEST_CONNECTING') ? ZP_TEST_CONNECTING : '⏳ Testing...'; ?>';
        area.innerHTML = '';
        fetch('../GameEngine/Admin/Mods/zarinpalTest.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: '_csrf_token=<?php echo csrf_token(); ?>'
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            var html = '';
            if (d.ok) {
                html = '<div style="background:#14532d;border:1px solid #166534;border-radius:8px;padding:12px 16px;">'
                    + '<b style="color:#bbf7d0;"><?php echo defined('ZP_TEST_SUCCESS_TITLE') ? ZP_TEST_SUCCESS_TITLE : '✅ Connection Successful'; ?></b>'
                    + '<div style="color:#86efac;font-size:11px;margin-top:4px;">'
                    + '<?php echo defined('ZP_TEST_SUCCESS_DETAIL') ? ZP_TEST_SUCCESS_DETAIL : 'Zarinpal %s API responded in %d ms. Authority: %s'; ?>'
                        .replace('%s', d.mode).replace('%d', d.elapsed).replace('%s', '<code style="color:#fde68a;">' + d.authority + '</code>')
                    + '</div>'
                    + '<div style="color:#86efac;font-size:11px;margin-top:4px;">'
                    + '← ' + d.url
                    + '</div></div>';
            } else if (d.error) {
                html = '<div style="background:#7f1d1d;border:1px solid #991b1b;border-radius:8px;padding:12px 16px;">'
                    + '<b style="color:#fecaca;"><?php echo defined('ZP_TEST_FAILED_TITLE') ? ZP_TEST_FAILED_TITLE : '❌ Connection Failed'; ?></b>'
                    + '<div style="color:#fca5a5;font-size:11px;margin-top:4px;">'
                    + '<?php echo defined('ZP_TEST_FAILED_DETAIL') ? ZP_TEST_FAILED_DETAIL : '%s'; ?>'
                        .replace('%s', '<code style="color:#fca5a5;">' + (d.error || 'Unknown error') + '</code>')
                    + '</div>'
                    + '<div style="color:#94a3b8;font-size:10px;margin-top:4px;">'
                    + d.mode + ' · ' + d.elapsed + 'ms' + (d.merchant ? ' · Merchant: ' + d.merchant : '')
                    + '</div>';
                if (d.raw && d.raw.errors) {
                    html += '<div style="color:#94a3b8;font-size:10px;margin-top:4px;">'
                        + 'API Code: ' + (d.raw.errors.code || '?') + ' — ' + (d.raw.errors.message || '')
                        + '</div>';
                }
                html += '</div>';
            }
            area.innerHTML = html;
        })
        .catch(function(e) {
            area.innerHTML = '<div style="background:#7f1d1d;border:1px solid #991b1b;border-radius:8px;padding:12px 16px;color:#fecaca;"><b>Error:</b> ' + e.message + '</div>';
        })
        .finally(function() {
            btn.disabled = false;
            btn.innerHTML = '<?php echo defined('ZP_TEST_CONNECTION') ? ZP_TEST_CONNECTION : '🔌 Test Connection'; ?>';
        });
    }
    </script>

    <div class="zp-cards">
        <div class="zp-stat success">
            <div class="val"><?php echo number_format((int)$stats['success']); ?></div>
            <div class="lbl"><?php echo defined('ZP_STAT_SUCCESS') ? ZP_STAT_SUCCESS : 'Successful Payments'; ?></div>
        </div>
        <div class="zp-stat gold">
            <div class="val"><?php echo number_format((int)$stats['total_gold']); ?></div>
            <div class="lbl"><?php echo defined('ZP_STAT_GOLD_SOLD') ? ZP_STAT_GOLD_SOLD : 'Gold Sold'; ?></div>
        </div>
        <div class="zp-stat rial">
            <div class="val"><?php echo number_format((int)($stats['total_rial'] / 10)); ?></div>
            <div class="lbl"><?php echo defined('ZP_STAT_TOMANS') ? ZP_STAT_TOMANS : 'Tomans Received'; ?></div>
        </div>
        <div class="zp-stat">
            <div class="val" style="color:#e2e8f0;"><?php echo number_format((int)$stats['total_tx']); ?></div>
            <div class="lbl"><?php echo defined('ZP_STAT_TOTAL_TX') ? ZP_STAT_TOTAL_TX : 'Total Transactions'; ?></div>
        </div>
    </div>

    <?php if (empty($list)): ?>
        <div class="zp-empty"><?php echo defined('ZP_NO_TRANSACTIONS') ? ZP_NO_TRANSACTIONS : 'No transactions recorded yet.'; ?></div>
    <?php else: ?>
        <div class="zp-scroll">
            <table class="zp-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php echo defined('ZP_TABLE_PLAYER') ? ZP_TABLE_PLAYER : 'Player'; ?></th>
                        <th><?php echo defined('TZ_ZARINPAL_REF_ID') ? TZ_ZARINPAL_REF_ID : 'Ref ID'; ?></th>
                        <th><?php echo defined('TZ_ZARINPAL_AMOUNT') ? TZ_ZARINPAL_AMOUNT : 'Amount'; ?></th>
                        <th><?php echo defined('GOLD') ? GOLD : 'Gold'; ?></th>
                        <th><?php echo defined('TZ_ZARINPAL_CARD') ? TZ_ZARINPAL_CARD : 'Card'; ?></th>
                        <th><?php echo defined('ZP_TABLE_PACKAGE') ? ZP_TABLE_PACKAGE : 'Package'; ?></th>
                        <th><?php echo defined('ZP_TABLE_STATUS') ? ZP_TABLE_STATUS : 'Status'; ?></th>
                        <th><?php echo defined('TZ_ZARINPAL_DATE') ? TZ_ZARINPAL_DATE : 'Date'; ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($list as $tx): 
                    $badgeClass = 'zp-badge-pending';
                    if ($tx['status'] === 'SUCCESS') $badgeClass = 'zp-badge-success';
                    elseif ($tx['status'] === 'FAILED')  $badgeClass = 'zp-badge-failed';
                ?>
                    <tr>
                        <td class="num" style="color:#64748b;"><?php echo (int)$tx['id']; ?></td>
                        <td>
                            <?php if (!empty($tx['uid'])): ?>
                                <a href="admin.php?p=player&uid=<?php echo (int)$tx['uid']; ?>" style="color:#e2e8f0;text-decoration:none;">
                                    <?php echo e($tx['username'] ?? ('#' . $tx['uid'])); ?>
                                </a>
                            <?php else: ?>
                                <span style="color:#64748b;">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="card-num" style="color:#fde68a;"><?php echo !empty($tx['ref_id']) ? number_format((int)$tx['ref_id']) : '-'; ?></td>
                        <td class="card-num num"><?php echo number_format((int)($tx['amount_rial'] / 10)); ?> T</td>
                        <td class="num" style="color:#facc15;"><?php echo number_format((int)$tx['gold']); ?></td>
                        <td class="card-num" style="color:#94a3b8;"><?php echo !empty($tx['card_pan']) ? e($tx['card_pan']) : '-'; ?></td>
                        <td><?php echo e($tx['package_label'] ?? '-'); ?></td>
                        <td><span class="zp-status <?php echo $badgeClass; ?>"><?php echo e($tx['status']); ?></span></td>
                        <td class="num" style="color:#94a3b8;"><?php echo $tx['created_at'] ? date('Y-m-d H:i', (int)$tx['created_at']) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pages > 1): ?>
        <div class="zp-pager">
            <?php for ($p = 1; $p <= $pages; $p++): ?>
                <?php if ($p === $pageNum): ?>
                    <strong><?php echo $p; ?></strong>
                <?php else: ?>
                    <a href="?p=zarinpal&zp_page=<?php echo $p; ?>"><?php echo $p; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
