<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
requireLogin();

$db = db();
$criticalLimit = 15;

$categoryReport = $db->query(
    'SELECT category, COUNT(*) AS medicine_count, SUM(stock) AS total_stock, SUM(stock * unit_price) AS total_value
     FROM medicines
     GROUP BY category
     ORDER BY total_value DESC'
)->fetchAll();

$movementSummary = $db->query(
    "SELECT DATE(movement_date) AS movement_day,
            SUM(CASE WHEN movement_type='IN' THEN quantity ELSE 0 END) AS total_in,
            SUM(CASE WHEN movement_type='OUT' THEN quantity ELSE 0 END) AS total_out
     FROM stock_movements
     WHERE movement_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
     GROUP BY DATE(movement_date)
     ORDER BY movement_day DESC"
)->fetchAll();

$criticalItems = $db->prepare('SELECT name, stock FROM medicines WHERE stock <= :limit ORDER BY stock ASC');
$criticalItems->execute(['limit' => $criticalLimit]);
$criticalItems = $criticalItems->fetchAll();

renderHeader(t('title_reports'));
?>
<div class="grid xl:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl p-5 shadow">
        <h3 class="text-lg font-semibold mb-4"><?= e(t('rep_cat_value')) ?></h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b text-left">
                    <th class="p-2"><?= e(t('rep_th_category')) ?></th>
                    <th class="p-2"><?= e(t('rep_th_sku_count')) ?></th>
                    <th class="p-2"><?= e(t('rep_th_total_stock')) ?></th>
                    <th class="p-2"><?= e(t('rep_th_total_value')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categoryReport as $row): ?>
                    <tr class="border-b">
                        <td class="p-2"><?= e($row['category']) ?></td>
                        <td class="p-2"><?= (int) $row['medicine_count'] ?></td>
                        <td class="p-2"><?= (int) $row['total_stock'] ?></td>
                        <td class="p-2"><?= number_format((float) $row['total_value'], 2, ',', '.') ?> TL</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl p-5 shadow">
        <h3 class="text-lg font-semibold mb-4"><?= e(t('rep_critical_list')) ?></h3>
        <ul class="space-y-2 text-sm">
            <?php foreach ($criticalItems as $item): ?>
                <li class="flex justify-between border rounded p-2">
                    <span><?= e($item['name']) ?></span>
                    <span class="text-rose-600 font-semibold"><?= (int) $item['stock'] ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<div class="bg-white rounded-xl p-5 shadow mt-6">
    <h3 class="text-lg font-semibold mb-4"><?= e(t('rep_movement_30')) ?></h3>
    <div class="overflow-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b text-left">
                    <th class="p-2"><?= e(t('dash_th_date')) ?></th>
                    <th class="p-2"><?= e(t('rep_th_total_in')) ?></th>
                    <th class="p-2"><?= e(t('rep_th_total_out')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($movementSummary as $row): ?>
                    <tr class="border-b">
                        <td class="p-2"><?= e($row['movement_day']) ?></td>
                        <td class="p-2 text-emerald-700"><?= (int) $row['total_in'] ?></td>
                        <td class="p-2 text-rose-700"><?= (int) $row['total_out'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php renderFooter(); ?>
