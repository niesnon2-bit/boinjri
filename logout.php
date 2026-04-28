<?php
declare(strict_types=1);
require __DIR__ . '/dashboard/init.php';
auth_logout();
redirect('index.php');
