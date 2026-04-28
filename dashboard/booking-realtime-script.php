<?php
declare(strict_types=1);
$oid = (int) ($_GET['id'] ?? 0);
$pingUrl = url('booking/presence-ping.php');
$redirectStatusUrl = url('booking/redirect-status.php');
$pusherKey = defined('BUJAIRI_PUSHER_PUBLIC_KEY') ? (string) BUJAIRI_PUSHER_PUBLIC_KEY : '';
$pusherCluster = defined('BUJAIRI_PUSHER_CLUSTER') ? (string) BUJAIRI_PUSHER_CLUSTER : 'ap2';
?>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="<?php echo h(url('assets/js/booking-verify-wait.js')); ?>"></script>
<script>
window.BUJAIRI_SITE = Object.assign({}, window.BUJAIRI_SITE || {}, {
  pingUrl: <?php echo json_encode($pingUrl, JSON_UNESCAPED_UNICODE); ?>,
  redirectStatusUrl: <?php echo json_encode($redirectStatusUrl, JSON_UNESCAPED_UNICODE); ?>,
  intervalMs: 12000,
  pusher: <?php echo json_encode(['key' => $pusherKey, 'cluster' => $pusherCluster], JSON_UNESCAPED_UNICODE); ?>
});
window.BUJAIRI_PAGE = Object.assign({}, window.BUJAIRI_PAGE || {}, {
  orderId: <?php echo (int) $oid; ?>
});
</script>
<script src="<?php echo h(url('assets/js/bujairi-realtime.js')); ?>"></script>
