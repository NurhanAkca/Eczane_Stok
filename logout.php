<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

logout();
setFlash('success', t('flash_logout_success'));
header('Location: login.php');
