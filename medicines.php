<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
requireLogin();

$db = db();
$criticalLimit = 15;

if (isPost()) {
    validateCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        $barcode = trim((string) ($_POST['barcode'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));
        $stock = max(0, (int) ($_POST['stock'] ?? 0));
        $unit_price = max(0, (float) ($_POST['unit_price'] ?? 0));
        $expiry_date = (string) ($_POST['expiry_date'] ?? '');

        if ($name === '' || $barcode === '' || $category === '' || $expiry_date === '') {
            setFlash('error', t('med_flash_fields'));
        } else {
            try {
                if ($action === 'create') {
                    $stmt = $db->prepare(
                        'INSERT INTO medicines (name, barcode, category, stock, unit_price, expiry_date) 
                         VALUES (:name, :barcode, :category, :stock, :unit_price, :expiry_date)'
                    );
                    $stmt->execute(compact('name', 'barcode', 'category', 'stock', 'unit_price', 'expiry_date'));
                    setFlash('success', t('med_flash_added'));
                } else {
                    $stmt = $db->prepare(
                        'UPDATE medicines SET name=:name, barcode=:barcode, category=:category, stock=:stock, unit_price=:unit_price, expiry_date=:expiry_date 
                         WHERE id=:id'
                    );
                    $stmt->execute(compact('id', 'name', 'barcode', 'category', 'stock', 'unit_price', 'expiry_date'));
                    setFlash('success', t('med_flash_updated'));
                }
            } catch (PDOException $e) {
                setFlash('error', t('med_flash_dup'));
            }
        }
    }

    if ($action === 'delete') {
        requireRole('admin');
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $db->prepare('DELETE FROM medicines WHERE id=:id');
        $stmt->execute(['id' => $id]);
        setFlash('success', t('med_flash_deleted'));
    }

    header('Location: medicines.php');
    exit;
}

$search = trim((string) ($_GET['search'] ?? ''));
$categoryFilter = trim((string) ($_GET['category'] ?? ''));
$editId = max(0, (int) ($_GET['edit'] ?? 0));

$medQuery = static function (array $overrides = []) use ($search, $categoryFilter): string {
    $params = array_merge([
        'lang' => currentLang(),
        'search' => $search,
        'category' => $categoryFilter,
    ], $overrides);
    if (($params['search'] ?? '') === '') {
        unset($params['search']);
    }
    if (($params['category'] ?? '') === '') {
        unset($params['category']);
    }

    return http_build_query($params);
};

$sql = 'SELECT * FROM medicines WHERE 1=1';
$params = [];

if ($search !== '') {
    $sql .= ' AND (name LIKE :search OR barcode LIKE :search)';
    $params['search'] = '%' . $search . '%';
}
if ($categoryFilter !== '') {
    $sql .= ' AND category = :category';
    $params['category'] = $categoryFilter;
}
$sql .= ' ORDER BY name ASC';

$stmt = $db->prepare($sql);
$stmt->execute($params);
$medicines = $stmt->fetchAll();
$categories = $db->query('SELECT DISTINCT category FROM medicines ORDER BY category')->fetchAll();

$editMedicine = null;
if ($editId > 0) {
    $editStmt = $db->prepare('SELECT * FROM medicines WHERE id = :id LIMIT 1');
    $editStmt->execute(['id' => $editId]);
    $editMedicine = $editStmt->fetch() ?: null;
}

renderHeader(t('title_medicines'));
?>
<div class="bg-white rounded-xl p-5 shadow mb-6">
    <h3 class="text-lg font-semibold mb-4"><?= e(t('med_filter_title')) ?></h3>
    <form method="get" class="grid md:grid-cols-3 gap-3">
        <input type="hidden" name="lang" value="<?= e(currentLang()) ?>">
        <input name="search" value="<?= e($search) ?>" class="border rounded-lg px-3 py-2" placeholder="<?= e(t('med_search_ph')) ?>">
        <select name="category" class="border rounded-lg px-3 py-2">
            <option value=""><?= e(t('med_all_categories')) ?></option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= e($category['category']) ?>" <?= $categoryFilter === $category['category'] ? 'selected' : '' ?>>
                    <?= e($category['category']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button class="bg-slate-900 text-white rounded-lg py-2"><?= e(t('med_filter_btn')) ?></button>
    </form>
</div>

<div class="grid xl:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl p-5 shadow">
        <h3 class="text-lg font-semibold mb-4"><?= e(t('med_new_title')) ?></h3>
        <form method="post" class="space-y-3">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="action" value="create">
            <input name="name" class="w-full border rounded-lg px-3 py-2" placeholder="<?= e(t('med_ph_name')) ?>" required>
            <input name="barcode" class="w-full border rounded-lg px-3 py-2" placeholder="<?= e(t('med_ph_barcode')) ?>" required>
            <input name="category" class="w-full border rounded-lg px-3 py-2" placeholder="<?= e(t('med_ph_category')) ?>" required>
            <input name="stock" type="number" min="0" class="w-full border rounded-lg px-3 py-2" placeholder="<?= e(t('med_ph_stock')) ?>" required>
            <input name="unit_price" type="number" step="0.01" min="0" class="w-full border rounded-lg px-3 py-2" placeholder="<?= e(t('med_ph_price')) ?>" required>
            <input name="expiry_date" type="date" class="w-full border rounded-lg px-3 py-2" required>
            <button class="w-full bg-emerald-600 text-white rounded-lg py-2"><?= e(t('med_save')) ?></button>
        </form>
    </div>

    <div class="xl:col-span-2 bg-white rounded-xl p-5 shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold"><?= e(t('med_list_title')) ?></h3>
            <span class="text-xs text-slate-500"><?= e(tf('med_records', count($medicines))) ?></span>
        </div>
        <div class="overflow-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left">
                        <th class="p-2"><?= e(t('dash_th_medicine')) ?></th>
                        <th class="p-2"><?= e(t('med_th_category')) ?></th>
                        <th class="p-2"><?= e(t('med_th_stock')) ?></th>
                        <th class="p-2"><?= e(t('med_th_price')) ?></th>
                        <th class="p-2"><?= e(t('med_th_exp')) ?></th>
                        <th class="p-2"><?= e(t('med_th_action')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medicines as $item): ?>
                        <tr class="border-b align-top <?= $editMedicine && (int) $editMedicine['id'] === (int) $item['id'] ? 'bg-indigo-50' : '' ?>">
                            <td class="p-2">
                                <div class="font-medium"><?= e($item['name']) ?></div>
                                <div class="text-xs text-slate-500"><?= e($item['barcode']) ?></div>
                            </td>
                            <td class="p-2"><?= e($item['category']) ?></td>
                            <td class="p-2">
                                <span class="<?= (int) $item['stock'] <= $criticalLimit ? 'text-rose-600 font-semibold' : '' ?>">
                                    <?= (int) $item['stock'] ?>
                                </span>
                            </td>
                            <td class="p-2"><?= number_format((float) $item['unit_price'], 2, ',', '.') ?> TL</td>
                            <td class="p-2"><?= e($item['expiry_date']) ?></td>
                            <td class="p-2">
                                <a
                                    href="medicines.php?<?= e($medQuery(['edit' => (int) $item['id']])) ?>"
                                    class="inline-block text-xs bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-500"
                                >
                                    <?= e(t('med_edit')) ?>
                                </a>
                                <?php if ((currentUser()['role'] ?? '') === 'admin'): ?>
                                    <form method="post" onsubmit="return confirm('<?= e(t('med_delete_confirm')) ?>')" class="mt-2">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                        <button class="text-rose-600 text-xs"><?= e(t('med_delete')) ?></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($editMedicine): ?>
    <div class="fixed inset-0 z-40 bg-slate-900/50"></div>
    <div class="fixed inset-0 z-50 p-4 md:p-8 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl border border-indigo-100">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-indigo-700"><?= e(t('med_modal_title')) ?></h3>
                <a href="medicines.php?<?= e($medQuery()) ?>" class="text-sm text-slate-600 hover:text-slate-800"><?= e(t('med_close')) ?></a>
            </div>
            <form method="post" class="p-6 grid md:grid-cols-2 gap-3">
                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= (int) $editMedicine['id'] ?>">

                <div>
                    <label class="block text-xs text-slate-600 mb-1"><?= e(t('med_label_name')) ?></label>
                    <input name="name" class="w-full border rounded-lg px-3 py-2" value="<?= e($editMedicine['name']) ?>" required>
                </div>
                <div>
                    <label class="block text-xs text-slate-600 mb-1"><?= e(t('med_label_barcode')) ?></label>
                    <input name="barcode" class="w-full border rounded-lg px-3 py-2" value="<?= e($editMedicine['barcode']) ?>" required>
                </div>
                <div>
                    <label class="block text-xs text-slate-600 mb-1"><?= e(t('med_label_category')) ?></label>
                    <input name="category" class="w-full border rounded-lg px-3 py-2" value="<?= e($editMedicine['category']) ?>" required>
                </div>
                <div>
                    <label class="block text-xs text-slate-600 mb-1"><?= e(t('med_label_stock')) ?></label>
                    <input name="stock" type="number" min="0" class="w-full border rounded-lg px-3 py-2" value="<?= (int) $editMedicine['stock'] ?>" required>
                </div>
                <div>
                    <label class="block text-xs text-slate-600 mb-1"><?= e(t('med_label_price')) ?></label>
                    <input name="unit_price" type="number" step="0.01" min="0" class="w-full border rounded-lg px-3 py-2" value="<?= e((string) $editMedicine['unit_price']) ?>" required>
                </div>
                <div>
                    <label class="block text-xs text-slate-600 mb-1"><?= e(t('med_label_exp')) ?></label>
                    <input name="expiry_date" type="date" class="w-full border rounded-lg px-3 py-2" value="<?= e($editMedicine['expiry_date']) ?>" required>
                </div>

                <div class="md:col-span-2 flex gap-2 pt-2">
                    <button class="bg-indigo-600 text-white rounded-lg px-4 py-2 hover:bg-indigo-500"><?= e(t('med_save_changes')) ?></button>
                    <a href="medicines.php?<?= e($medQuery()) ?>" class="border rounded-lg px-4 py-2 hover:bg-slate-50"><?= e(t('med_cancel')) ?></a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
<?php renderFooter(); ?>
