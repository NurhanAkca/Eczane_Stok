<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';
requireLogin();
requireRole('admin');

$db = db();

if (isPost()) {
    validateCsrf();
    $action = (string) ($_POST['action'] ?? '');

    if ($action === 'create') {
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $username = trim((string) ($_POST['username'] ?? ''));
        $role = (string) ($_POST['role'] ?? 'pharmacist');
        $password = (string) ($_POST['password'] ?? '');

        if ($fullName === '' || $username === '' || $password === '' || !in_array($role, ['admin', 'pharmacist'], true)) {
            setFlash('error', t('user_flash_bad'));
        } else {
            try {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare('INSERT INTO users (full_name, username, password_hash, role, is_active) VALUES (:full_name, :username, :hash, :role, 1)');
                $stmt->execute([
                    'full_name' => $fullName,
                    'username' => $username,
                    'hash' => $hash,
                    'role' => $role,
                ]);
                setFlash('success', t('user_flash_added'));
            } catch (PDOException $e) {
                setFlash('error', t('user_flash_dup'));
            }
        }
    }

    if ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $db->prepare('UPDATE users SET is_active = IF(is_active=1,0,1) WHERE id=:id');
        $stmt->execute(['id' => $id]);
        setFlash('success', t('user_flash_toggle'));
    }

    header('Location: users.php');
    exit;
}

$users = $db->query('SELECT id, full_name, username, role, is_active, created_at FROM users ORDER BY created_at DESC')->fetchAll();

renderHeader(t('title_users'));
?>
<div class="grid lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl p-5 shadow">
        <h3 class="text-lg font-semibold mb-4"><?= e(t('user_new_title')) ?></h3>
        <form method="post" class="space-y-3">
            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
            <input type="hidden" name="action" value="create">
            <input name="full_name" class="w-full border rounded-lg px-3 py-2" placeholder="<?= e(t('user_ph_fullname')) ?>" required>
            <input name="username" class="w-full border rounded-lg px-3 py-2" placeholder="<?= e(t('user_ph_username')) ?>" required>
            <select name="role" class="w-full border rounded-lg px-3 py-2" required>
                <option value="pharmacist"><?= e(t('role_pharmacist')) ?></option>
                <option value="admin"><?= e(t('role_admin')) ?></option>
            </select>
            <input name="password" type="password" class="w-full border rounded-lg px-3 py-2" placeholder="<?= e(t('user_ph_password')) ?>" required>
            <button class="w-full bg-slate-900 text-white rounded-lg py-2"><?= e(t('user_add')) ?></button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl p-5 shadow">
        <h3 class="text-lg font-semibold mb-4"><?= e(t('user_list_title')) ?></h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b text-left">
                    <th class="p-2"><?= e(t('user_th_fullname')) ?></th>
                    <th class="p-2"><?= e(t('user_th_username')) ?></th>
                    <th class="p-2"><?= e(t('user_th_role')) ?></th>
                    <th class="p-2"><?= e(t('user_th_status')) ?></th>
                    <th class="p-2"><?= e(t('med_th_action')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr class="border-b">
                        <td class="p-2"><?= e($user['full_name']) ?></td>
                        <td class="p-2"><?= e($user['username']) ?></td>
                        <td class="p-2"><?= e(($user['role'] ?? '') === 'admin' ? t('role_admin') : t('role_pharmacist')) ?></td>
                        <td class="p-2"><?= (int) $user['is_active'] === 1 ? e(t('status_active')) : e(t('status_inactive')) ?></td>
                        <td class="p-2">
                            <form method="post">
                                <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                <button class="text-indigo-600 text-xs"><?= e(t('user_toggle')) ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php renderFooter(); ?>
