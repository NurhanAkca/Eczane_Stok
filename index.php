<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

if (currentUser()) {
    header('Location: dashboard.php');
    exit;
}

header('Location: login.php');
