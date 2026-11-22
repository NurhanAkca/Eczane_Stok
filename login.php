<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

if (currentUser()) {
    header('Location: dashboard.php');
    exit;
}

if (isPost()) {
    validateCsrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        setFlash('error', t('flash_login_fields'));
    } elseif (login($username, $password)) {
        setFlash('success', t('flash_login_success'));
        header('Location: dashboard.php');
        exit;
    } else {
        setFlash('error', t('flash_login_invalid'));
    }
}

$oldUsername = trim((string) ($_POST['username'] ?? ''));

renderHeader(t('title_login'));
?>
<div class="mx-auto mt-6 md:mt-12 max-w-5xl">
    <div class="relative overflow-hidden rounded-3xl bg-white/85 p-2 shadow-2xl ring-1 ring-slate-200 backdrop-blur">
        <div class="pointer-events-none absolute -left-24 -top-24 h-52 w-52 rounded-full bg-indigo-200/50 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-20 h-52 w-52 rounded-full bg-sky-200/50 blur-3xl"></div>

        <div class="relative grid gap-6 md:grid-cols-2">
            <div class="hidden rounded-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-indigo-900 p-8 text-slate-100 md:flex md:flex-col md:justify-between">
            <div>
                <p class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-xs tracking-wide text-slate-100 ring-1 ring-white/20">
                    <?= e(t('login_hero_badge')) ?>
                </p>
                <h3 class="mt-5 text-2xl font-semibold leading-tight"><?= e(t('login_hero_title')) ?></h3>
                <p class="mt-3 text-sm leading-6 text-slate-300"><?= e(t('login_hero_text')) ?></p>
            </div>
            <div class="rounded-xl bg-white/10 p-4 text-xs text-slate-200 ring-1 ring-white/20">
                <p class="font-semibold text-slate-100"><?= e(t('login_info_title')) ?></p>
                <p class="mt-1"><?= e(t('login_info_text')) ?></p>
            </div>
        </div>

            <div class="rounded-2xl bg-white p-6 md:p-8">
                <div class="mb-6">
                    <h3 class="text-2xl font-semibold text-slate-800"><?= e(t('login_heading')) ?></h3>
                    <p class="mt-1 text-sm text-slate-500"><?= e(t('login_subtitle')) ?></p>
                </div>

                <form method="post" class="space-y-4">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700"><?= e(t('login_username')) ?></label>
                    <input
                        name="username"
                        class="w-full rounded-xl border border-slate-300 px-3 py-2.5 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                        placeholder="<?= e(t('login_username_ph')) ?>"
                        value="<?= e($oldUsername) ?>"
                        autocomplete="username"
                        autofocus
                        required
                        maxlength="60"
                    >
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700"><?= e(t('login_password')) ?></label>
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="w-full rounded-xl border border-slate-300 px-3 py-2.5 pr-24 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200"
                            placeholder="<?= e(t('login_password_ph')) ?>"
                            autocomplete="current-password"
                            required
                        >
                        <button
                            type="button"
                            data-toggle-password
                            data-target="#password"
                            data-label-show="<?= e(t('login_show')) ?>"
                            data-label-hide="<?= e(t('login_hide')) ?>"
                            data-aria-show="<?= e(t('login_aria_show')) ?>"
                            data-aria-hide="<?= e(t('login_aria_hide')) ?>"
                            class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg px-3 py-1.5 text-xs font-medium text-slate-600 transition hover:bg-slate-100 hover:text-slate-800"
                            aria-label="<?= e(t('login_aria_show')) ?>"
                            aria-pressed="false"
                        >
                            <?= e(t('login_show')) ?>
                        </button>
                    </div>
                </div>

                <button class="w-full rounded-xl bg-slate-900 py-2.5 font-medium text-white transition hover:bg-slate-700">
                    <?= e(t('login_submit')) ?>
                </button>
                </form>

                <p class="mt-4 text-center text-xs text-slate-400"><?= e(t('login_footer')) ?></p>
            </div>
        </div>
    </div>
</div>
<?php renderFooter(); ?>
