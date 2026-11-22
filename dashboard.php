<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
requireLogin();

$db = db();
$criticalLimit = 15;

$totalMedicine = (int) $db->query('SELECT COUNT(*) FROM medicines')->fetchColumn();
$totalStock = (int) $db->query('SELECT COALESCE(SUM(stock), 0) FROM medicines')->fetchColumn();
$stmtCritical = $db->prepare('SELECT COUNT(*) FROM medicines WHERE stock <= :limit');
$stmtCritical->execute(['limit' => $criticalLimit]);
$criticalCount = (int) $stmtCritical->fetchColumn();

$stmtExpire = $db->prepare('SELECT COUNT(*) FROM medicines WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY)');
$stmtExpire->execute();
$expiringSoon = (int) $stmtExpire->fetchColumn();

$latestMovements = $db->query(
    'SELECT sm.id, sm.movement_type, sm.quantity, sm.movement_date, m.name
     FROM stock_movements sm
     INNER JOIN medicines m ON m.id = sm.medicine_id
     ORDER BY sm.movement_date DESC, sm.id DESC
     LIMIT 10'
)->fetchAll();

renderHeader(t('title_dashboard'));
?>
<div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-xl p-5 shadow">
        <p class="text-sm text-slate-500"><?= e(t('dash_total_medicines')) ?></p>
        <p class="text-3xl font-bold text-slate-800"><?= $totalMedicine ?></p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow">
        <p class="text-sm text-slate-500"><?= e(t('dash_total_stock')) ?></p>
        <p class="text-3xl font-bold text-slate-800"><?= $totalStock ?></p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow">
        <p class="text-sm text-slate-500"><?= e(tf('dash_critical_stock', $criticalLimit)) ?></p>
        <p class="text-3xl font-bold text-amber-600"><?= $criticalCount ?></p>
    </div>
    <div class="bg-white rounded-xl p-5 shadow">
        <p class="text-sm text-slate-500"><?= e(t('dash_expiring_60')) ?></p>
        <p class="text-3xl font-bold text-rose-600"><?= $expiringSoon ?></p>
    </div>
</div>

<div class="bg-white rounded-xl p-5 shadow">
    <h3 class="text-lg font-semibold mb-4"><?= e(t('dash_recent_movements')) ?></h3>
    <div class="overflow-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left border-b">
                    <th class="p-2"><?= e(t('dash_th_medicine')) ?></th>
                    <th class="p-2"><?= e(t('dash_th_movement')) ?></th>
                    <th class="p-2"><?= e(t('dash_th_qty')) ?></th>
                    <th class="p-2"><?= e(t('dash_th_date')) ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($latestMovements as $row): ?>
                <tr class="border-b">
                    <td class="p-2"><?= e($row['name']) ?></td>
                    <td class="p-2">
                        <span class="px-2 py-1 rounded text-xs <?= $row['movement_type'] === 'IN' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                            <?= e($row['movement_type']) ?>
                        </span>
                    </td>
                    <td class="p-2"><?= (int) $row['quantity'] ?></td>
                    <td class="p-2"><?= e($row['movement_date']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php renderFooter(); ?>
