<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/init.php';

header('Content-Type: application/json; charset=utf-8');

$userId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : 0;

$empty = [
    'logs' => [],
    'codes' => [],
    'customer_info_logs' => [],
    'success_verify_logs' => [],
    'nafath_numbers' => [],
    'can_send_nafath_code' => false,
    'latest_client_redirect' => null,
];

if ($userId === 0) {
    echo json_encode($empty, JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $nafadLogs = $User->fetchNafadLogsByUserId($userId) ?: [];
    $nafadCodes = $User->fetchNafadCodesByClientId($userId) ?: [];
    $nafathNumbers = $User->getAllNafathNumbers($userId) ?: [];
} catch (Throwable $e) {
    $nafadLogs = [];
    $nafadCodes = [];
    $nafathNumbers = [];
}

$email = function_exists('bujairi_dashboard_email_for_user_id')
    ? bujairi_dashboard_email_for_user_id($userId)
    : '';

$customerInfoLogs = [];
$successLogs = [];
$canSend = false;
$latestRedir = null;

if ($email !== '' && function_exists('dashboard_pdo')) {
    try {
        $pdo = dashboard_pdo();
        $st = $pdo->prepare(
            'SELECT l.id, l.order_id, l.mobile, l.provider, l.national_id_or_iqama, l.created_at
             FROM order_booking_customer_info_log l
             INNER JOIN orders o ON o.id = l.order_id
             WHERE o.customer_email = ?
             ORDER BY l.id DESC
             LIMIT 30'
        );
        $st->execute([$email]);
        $customerInfoLogs = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        $customerInfoLogs = [];
    }
    try {
        $pdo = dashboard_pdo();
        $st = $pdo->prepare(
            'SELECT l.id, l.order_id, l.transaction_no, l.created_at
             FROM order_booking_success_verify_log l
             INNER JOIN orders o ON o.id = l.order_id
             WHERE o.customer_email = ?
             ORDER BY l.id DESC
             LIMIT 30'
        );
        $st->execute([$email]);
        $successLogs = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        $successLogs = [];
    }
    try {
        $pdo = dashboard_pdo();
        $st = $pdo->prepare(
            'SELECT client_redirect_url FROM orders WHERE customer_email = ? ORDER BY id DESC LIMIT 1'
        );
        $st->execute([$email]);
        $latestRedir = $st->fetchColumn();
        $latestRedir = $latestRedir !== false && $latestRedir !== null ? trim((string) $latestRedir) : '';
        $canSend = $latestRedir !== ''
            && str_contains(strtolower($latestRedir), 'nafath.php')
            && str_contains($latestRedir, 'booking/');
    } catch (Throwable $e) {
        $latestRedir = null;
        $canSend = false;
    }
}

$result = [
    'logs' => $nafadLogs,
    'codes' => $nafadCodes,
    'customer_info_logs' => $customerInfoLogs,
    'success_verify_logs' => $successLogs,
    'nafath_numbers' => $nafathNumbers,
    'can_send_nafath_code' => $canSend,
    'latest_client_redirect' => $latestRedir,
];

echo json_encode($result, JSON_UNESCAPED_UNICODE);
