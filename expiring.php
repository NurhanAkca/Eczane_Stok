<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
requireLogin();

$days = max(1, (int) ($_GET['days'] ?? 60));
$stmt = db()->prepare(
    'SELECT name, barcode, stock, expiry_date, DATEDIFF(expiry_date, CURDATE()) AS remaining_days
     FROM medicines
     WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
     ORDER BY expiry_date ASC'
);
$stmt->bindValue(':days', $days, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll();

renderHeader(t('title_expiring'));
?>
<div class="bg-white rounded-xl p-5 shadow">
    <form method="get" class="flex gap-3 items-end mb-5">
        <input type="hidden" name="lang" value="<?= e(currentLang()) ?>">
        <div>
            <label class="block text-sm text-slate-600 mb-1"><?= e(t('exp_days_label')) ?></label>
            <input type="number" name="days" value="<?= $days ?>" min="1" class="border rounded-lg px-3 py-2">
        </div>
        <button class="bg-slate-900 text-white rounded-lg px-4 py-2"><?= e(t('exp_list_btn')) ?></button>
    </form>

    <div class="overflow-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b text-left">
                    <th class="p-2"><?= e(t('exp_th_medicine')) ?></th>
                    <th class="p-2"><?= e(t('exp_th_barcode')) ?></th>
                    <th class="p-2"><?= e(t('exp_th_stock')) ?></th>
                    <th class="p-2"><?= e(t('exp_th_exp')) ?></th>
                    <th class="p-2"><?= e(t('exp_th_remaining')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr class="border-b">
                        <td class="p-2"><?= e($item['name']) ?></td>
                        <td class="p-2"><?= e($item['barcode']) ?></td>
                        <td class="p-2"><?= (int) $item['stock'] ?></td>
                        <td class="p-2"><?= e($item['expiry_date']) ?></td>
                        <td class="p-2 <?= (int) $item['remaining_days'] <= 15 ? 'text-rose-600 font-semibold' : '' ?>">
                            <?= (int) $item['remaining_days'] ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php renderFooter(); ?>
