<?php
declare(strict_types=1);

if (defined('BUJAIRI_DASHBOARD_INIT')) {
    return;
}
define('BUJAIRI_DASHBOARD_INIT', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** 1) إعدادات الاتصال — فقط من dashboard/config.php */
require_once __DIR__ . '/config.php';

/** 2) دوال/اتصال mysqli إن وُجد (مثل $db_connection) */
if (is_readable(__DIR__ . '/functions.php')) {
    require_once __DIR__ . '/functions.php';
}

/** 3) PDO للوحة التحكم (نفس بيانات config) — لكلاس User و DB:: */
$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . (defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4');
$GLOBALS['dashboard_pdo'] = new PDO(
    $dsn,
    DB_USER,
    DB_PASSWORD,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);

/**
 * @return PDO
 */
function dashboard_pdo(): PDO
{
    if (!isset($GLOBALS['dashboard_pdo']) || !$GLOBALS['dashboard_pdo'] instanceof PDO) {
        throw new RuntimeException('قاعدة بيانات لوحة التحكم غير جاهزة.');
    }
    return $GLOBALS['dashboard_pdo'];
}

require_once __DIR__ . '/classes/db.php';
DB::useProjectPdo(dashboard_pdo());

require_once __DIR__ . '/functions2.php';
if (function_exists('debug_mode')) {
    $dbg = defined('DEBUG') && constant('DEBUG');
    debug_mode($dbg);
}

require_once __DIR__ . '/classes/core.php';
require_once __DIR__ . '/classes/user.php';
require_once __DIR__ . '/public-core.php';

$Core = new Core();
$User = new User();

// تحميل دوال المشروع الرئيسي للتوجيه (اختياري) بدون الاعتماد على config/config.php
$root = dirname(__DIR__);

if (!defined('APP_BASE')) {
    define('APP_BASE', '/');
}
if (!defined('APP_NAME')) {
    define('APP_NAME', 'مطاعم البجيري');
}
if (!defined('BUJAIRI_LOADED')) {
    define('BUJAIRI_LOADED', true);
}

if (!function_exists('url')) {
    $maybeFunctions = __DIR__ . '/functions.php';
    if (is_readable($maybeFunctions)) {
        require_once $maybeFunctions;
    } else {
        function url(string $path = ''): string
        {
            $path = ltrim($path, '/');
            return rtrim(APP_BASE, '/') . ($path !== '' ? '/' . $path : '/');
        }
    }
}

// تم نقل كل وظائف العامة إلى dashboard/public-core.php

