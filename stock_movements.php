<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
requireLogin();

$db = db();

if (isPost()) {
    validateCsrf();
    $medicineId = (int) ($_POST['medicine_id'] ?? 0);
    $movementType = (string) ($_POST['movement_type'] ?? '');
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
    $note = trim((string) ($_POST['note'] ?? ''));

    if (!in_array($movementType, ['IN', 'OUT'], true) || $medicineId <= 0) {
        setFlash('error', t('stock_flash_invalid'));
        header('Location: stock_movements.php');
        exit;
    }

    try {
        $db->beginTransaction();
        $stmt = $db->prepare('SELECT stock FROM medicines WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $medicineId]);
        $medicine = $stmt->fetch();

        if (!$medicine) {
            throw new RuntimeException(t('stock_err_not_found'));
        }

        $currentStock = (int) $medicine['stock'];
        $newStock = $movementType === 'IN' ? $currentStock + $quantity : $currentStock - $quantity;

        if ($newStock < 0) {
            throw new RuntimeException(t('stock_err_insufficient'));
        }

        $stmt = $db->prepare(
            'INSERT INTO stock_movements (medicine_id, movement_type, quantity, note, user_id) 
             VALUES (:medicine_id, :movement_type, :quantity, :note, :user_id)'
        );
        $stmt->execute([
            'medicine_id' => $medicineId,
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'note' => $note,
            'user_id' => (int) currentUser()['id'],
        ]);

        $stmt = $db->prepare('UPDATE medicines SET stock = :stock WHERE id = :id');
        $stmt->execute(['stock' => $newStock, 'id' => $medicineId]);

        $db->commit();
        setFlash('success', t('stock_flash_saved'));
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        setFlash('error', $e->getMessage());
    }

    header('Location: stock_movements.php');
    exit;
}

$medicineOptions = $db->query('SELECT id, name, stock FROM medicines ORDER BY name')->fetchAll();
$movements = $db->query(
    'SELECT sm.*, m.name AS medicine_name, u.full_name
     FROM stock_movements sm
     INNER JOIN medicines m ON m.id = sm.medicine_id
     INNER JOIN users u ON u.id = sm.user_id
     ORDER BY sm.movement_date DESC, sm.id DESC
     LIMIT 100'
)->fetchAll();

renderHeader(t('title_stock'));
?>
<div class="grid lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl p-5 shadow">
        <h3 class="text-lg font-semibold mb-4"><?= e(t('stock_new')) ?></h3>
        <form method="post" class="space-y-3">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <select name="medicine_id" class="w-full border rounded-lg px-3 py-2" required>
                <option value=""><?= e(t('stock_select_med')) ?></option>
                <?php foreach ($medicineOptions as $item): ?>
                    <option value="<?= (int) $item['id'] ?>">
                        <?= e($item['name']) ?> (<?= e(tf('stock_level', (int) $item['stock'])) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="movement_type" class="w-full border rounded-lg px-3 py-2" required>
                <option value="IN"><?= e(t('stock_opt_in')) ?></option>
                <option value="OUT"><?= e(t('stock_opt_out')) ?></option>
            </select>
            <input name="quantity" type="number" min="1" class="w-full border rounded-lg px-3 py-2" placeholder="<?= e(t('stock_ph_qty')) ?>" required>
            <textarea name="note" class="w-full border rounded-lg px-3 py-2" rows="3" placeholder="<?= e(t('stock_ph_note')) ?>"></textarea>
            <button class="w-full bg-slate-900 text-white rounded-lg py-2"><?= e(t('stock_save')) ?></button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl p-5 shadow">
        <h3 class="text-lg font-semibold mb-4"><?= e(t('stock_history')) ?></h3>
        <div class="overflow-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left border-b">
                        <th class="p-2"><?= e(t('stock_th_date')) ?></th>
                        <th class="p-2"><?= e(t('stock_th_medicine')) ?></th>
                        <th class="p-2"><?= e(t('stock_th_type')) ?></th>
                        <th class="p-2"><?= e(t('dash_th_qty')) ?></th>
                        <th class="p-2"><?= e(t('stock_th_user')) ?></th>
                        <th class="p-2"><?= e(t('stock_th_note')) ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($movements as $row): ?>
                    <tr class="border-b">
                        <td class="p-2"><?= e($row['movement_date']) ?></td>
                        <td class="p-2"><?= e($row['medicine_name']) ?></td>
                        <td class="p-2">
                            <span class="text-xs px-2 py-1 rounded <?= $row['movement_type'] === 'IN' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                                <?= e($row['movement_type']) ?>
                            </span>
                        </td>
                        <td class="p-2"><?= (int) $row['quantity'] ?></td>
                        <td class="p-2"><?= e($row['full_name']) ?></td>
                        <td class="p-2"><?= e((string) $row['note']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php renderFooter(); ?>
