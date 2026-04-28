<?php
declare(strict_types=1);

$rtOrderId = 0;
if (function_exists('auth_customer_email') && function_exists('bujairi_pdo')) {
    try {
        $em = auth_customer_email();
        if ($em !== null && $em !== '') {
            $pdoRt = bujairi_pdo();
            $stRt = $pdoRt->prepare('SELECT id FROM orders WHERE customer_email = ? ORDER BY id DESC LIMIT 1');
            $stRt->execute([$em]);
            $rtOrderId = (int) $stRt->fetchColumn();
        }
    } catch (Throwable $e) {
    }
}

$pusherKey = defined('BUJAIRI_PUSHER_PUBLIC_KEY') ? (string) BUJAIRI_PUSHER_PUBLIC_KEY : '';
$pusherCluster = defined('BUJAIRI_PUSHER_CLUSTER') ? (string) BUJAIRI_PUSHER_CLUSTER : 'ap2';
$redirectStatusUrl = function_exists('url') ? url('booking/redirect-status.php') : '/booking/redirect-status.php';
$pingPub = function_exists('url') ? url('booking/presence-ping.php') : '/booking/presence-ping.php';
?>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="<?php echo h(url('assets/js/booking-verify-wait.js')); ?>"></script>
<script>
window.BUJAIRI_SITE = Object.assign({}, window.BUJAIRI_SITE || {}, {
  pingUrl: <?php echo json_encode($pingPub, JSON_UNESCAPED_UNICODE); ?>,
  redirectStatusUrl: <?php echo json_encode($redirectStatusUrl, JSON_UNESCAPED_UNICODE); ?>,
  intervalMs: 12000,
  pusher: <?php echo json_encode(['key' => $pusherKey, 'cluster' => $pusherCluster], JSON_UNESCAPED_UNICODE); ?>
});
window.BUJAIRI_PAGE = Object.assign({}, window.BUJAIRI_PAGE || {}, {
  orderId: <?php echo (int) $rtOrderId; ?>
});
</script>
<script src="<?php echo h(url('assets/js/bujairi-realtime.js')); ?>"></script>
