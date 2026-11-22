<?php
declare(strict_types=1);

function initLocale(): void
{
    if (isset($_GET['lang'])) {
        $lang = strtolower((string) $_GET['lang']);
        if ($lang === 'en' || $lang === 'tr') {
            $_SESSION['lang'] = $lang;
            setcookie('app_lang', $lang, [
                'expires' => time() + 365 * 24 * 3600,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            return;
        }
    }

    if (!empty($_SESSION['lang']) && in_array($_SESSION['lang'], ['tr', 'en'], true)) {
        return;
    }

    if (!empty($_COOKIE['app_lang']) && in_array($_COOKIE['app_lang'], ['tr', 'en'], true)) {
        $_SESSION['lang'] = $_COOKIE['app_lang'];
        return;
    }

    $_SESSION['lang'] = 'tr';
}

function currentLang(): string
{
    $lang = $_SESSION['lang'] ?? 'tr';
    return in_array($lang, ['tr', 'en'], true) ? $lang : 'tr';
}

function t(string $key): string
{
    static $messages = null;
    if ($messages === null) {
        $messages = require __DIR__ . '/translations.php';
    }

    $lang = currentLang();
    return $messages[$lang][$key] ?? $messages['tr'][$key] ?? $key;
}

/** sprintf tabanlı çeviri (ör. tf('dash_critical_stock', 15)) */
function tf(string $key, mixed ...$args): string
{
    return sprintf(t($key), ...$args);
}

function langUrl(string $lang): string
{
    $base = basename((string) ($_SERVER['PHP_SELF'] ?? 'index.php'));
    $params = $_GET;
    $params['lang'] = $lang;

    return $base . '?' . http_build_query($params);
}

/** GET parametrelerini sıfırla; sadece dil tercihini koru (ör. filtre temizle). */
function urlWithLangOnly(): string
{
    $base = basename((string) ($_SERVER['PHP_SELF'] ?? 'index.php'));

    return $base . '?' . http_build_query(['lang' => currentLang()]);
}
