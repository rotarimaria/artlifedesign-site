<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

// Se închide sesiunea de admin și se revine la login.
logoutAdmin();

header('Location: login.php');
exit;