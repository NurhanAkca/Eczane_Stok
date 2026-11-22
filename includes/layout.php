<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function renderHeader(string $title): void
{
    $user = currentUser();
    $currentPage = basename((string) ($_SERVER['PHP_SELF'] ?? ''));
    $navClass = static function (string $page) use ($currentPage): string {
        if ($currentPage === $page) {
            return 'block px-3 py-2 rounded bg-indigo-600 text-white font-semibold shadow hover:bg-indigo-500';
        }

        return 'block px-3 py-2 rounded bg-slate-800 hover:bg-slate-700';
    };
    ?>
<!DOCTYPE html>
<html lang="<?= currentLang() === 'en' ? 'en' : 'tr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> - <?= e(t('app_brand')) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-slate-100 min-h-screen">
<div class="min-h-screen flex">
    <?php if ($user): ?>
        <aside class="w-72 bg-slate-900 text-slate-100 p-5 hidden lg:block">
            <h1 class="text-xl font-bold mb-8"><?= e(t('app_brand')) ?></h1>
            <nav class="space-y-2 text-sm">
                <a class="<?= $navClass('dashboard.php') ?>" href="dashboard.php"><?= e(t('nav_dashboard')) ?></a>
                <a class="<?= $navClass('medicines.php') ?>" href="medicines.php"><?= e(t('nav_medicines')) ?></a>
                <a class="<?= $navClass('stock_movements.php') ?>" href="stock_movements.php"><?= e(t('nav_stock_movements')) ?></a>
                <a class="<?= $navClass('expiring.php') ?>" href="expiring.php"><?= e(t('nav_expiring')) ?></a>
                <a class="<?= $navClass('reports.php') ?>" href="reports.php"><?= e(t('nav_reports')) ?></a>
                <a class="<?= $navClass('monthly_analysis.php') ?>" href="monthly_analysis.php"><?= e(t('nav_monthly')) ?></a>
                <?php if (($user['role'] ?? '') === 'admin'): ?>
                    <a class="<?= $navClass('users.php') ?>" href="users.php"><?= e(t('nav_users')) ?></a>
                <?php endif; ?>
                <a class="block px-3 py-2 rounded bg-rose-600 hover:bg-rose-500 mt-6" href="logout.php"><?= e(t('nav_logout')) ?></a>
            </nav>
        </aside>
    <?php endif; ?>

    <main class="flex-1 p-4 md:p-8">
        <div class="max-w-7xl mx-auto">
            <div class="flex justify-between items-center mb-6 gap-4 flex-wrap">
                <h2 class="text-2xl font-semibold text-slate-800"><?= e($title) ?></h2>
                <div class="flex items-center gap-4 flex-wrap">
                    <div class="flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs text-slate-600 shadow-sm">
                        <a href="<?= e(langUrl('tr')) ?>" class="px-2 py-0.5 rounded <?= currentLang() === 'tr' ? 'bg-slate-900 text-white font-semibold' : 'hover:bg-slate-100' ?>"><?= e(t('lang_tr')) ?></a>
                        <span class="text-slate-300">|</span>
                        <a href="<?= e(langUrl('en')) ?>" class="px-2 py-0.5 rounded <?= currentLang() === 'en' ? 'bg-slate-900 text-white font-semibold' : 'hover:bg-slate-100' ?>"><?= e(t('lang_en')) ?></a>
                    </div>
                    <?php if ($user): ?>
                        <?php
                        $roleKey = ($user['role'] ?? '') === 'admin' ? 'role_admin' : 'role_pharmacist';
                        ?>
                        <span class="text-sm text-slate-600"><?= e($user['full_name']) ?> (<?= e(t($roleKey)) ?>)</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php foreach (getFlashMessages() as $flash): ?>
                <div class="mb-4 px-4 py-3 rounded text-sm <?= $flash['type'] === 'success' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' ?>">
                    <?= e($flash['message']) ?>
                </div>
            <?php endforeach; ?>
<?php
}

function renderFooter(): void
{
    ?>
        </div>
    </main>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
<?php
}
