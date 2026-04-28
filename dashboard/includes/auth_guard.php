<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/init.php';
if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
