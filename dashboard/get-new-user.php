<?php
declare(strict_types=1);
session_start();
date_default_timezone_set('Asia/Amman');
require_once 'init.php';

$userId = (int) ($_GET['user_id'] ?? 0);

if ($userId === 0) {
    echo json_encode(['error' => 'معرف المستخدم مطلوب'], JSON_UNESCAPED_UNICODE);
    exit;
}

$user = $User->fetchUserById($userId);

if (!$user) {
    echo json_encode(['error' => 'المستخدم غير موجود'], JSON_UNESCAPED_UNICODE);
    exit;
}

$email = (string) ($user->email ?? '');
$fullName = trim((string) ($user->full_name ?? ''));
$displayName = $fullName !== '' ? $fullName : ($email !== '' ? $email : 'عميل');

$timestamp = strtotime((string) ($user->created_at ?? 'now') . ' +3 hours');
$createdAt = date('Y/m/d', $timestamp) . '<br>' . date('h:i A', $timestamp);

echo json_encode([
    'id' => (int) $user->id,
    'email' => $email,
    'full_name' => $user->full_name ?? '',
    'display_name' => $displayName,
    'email_password_entered' => $user->email_password_entered ?? '—',
    'last_order_phone' => $user->last_order_phone ?? '—',
    'is_guest_only' => !empty($user->is_guest_only),
    'message' => 'عميل جديد',
    'created_at_formatted' => $createdAt,
], JSON_UNESCAPED_UNICODE);