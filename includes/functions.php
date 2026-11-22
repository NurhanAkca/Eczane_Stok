<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/lang.php';

initLocale();

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function isPost(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validateCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (!$token || !$sessionToken || !hash_equals($sessionToken, $token)) {
        http_response_code(403);
        exit(t('csrf_failed'));
    }
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function getFlashMessages(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireLogin(): void
{
    if (!currentUser()) {
        setFlash('error', t('flash_login_required'));
        header('Location: login.php');
        exit;
    }
}

function requireRole(string $role): void
{
    $user = currentUser();
    if (!$user || ($user['role'] ?? '') !== $role) {
        http_response_code(403);
        exit(t('forbidden'));
    }
}

function login(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT id, full_name, username, password_hash, role FROM users WHERE username = :username AND is_active = 1');
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    $isValid = password_verify($password, $user['password_hash']);

    // Ilk kurulum kolayligi: SQL dosyasindaki varsayilan hesaplar
    // plain text olarak geldiyse ilk giriste guvenli hash'e cevrilir.
    if (!$isValid && in_array($username, ['admin', 'eczaci'], true) && hash_equals((string) $user['password_hash'], $password)) {
        $isValid = true;
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $update = db()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $update->execute(['hash' => $newHash, 'id' => (int) $user['id']]);
    }

    if (!$isValid) {
        return false;
    }

    session_regenerate_id(true);
    unset($user['password_hash']);
    $_SESSION['user'] = $user;
    return true;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
