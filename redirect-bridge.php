<?php
declare(strict_types=1);
require __DIR__ . '/dashboard/init.php';

$to = isset($_GET['to']) ? rawurldecode((string) $_GET['to']) : '';
$to = bujairi_normalize_internal_destination($to);
if ($to === '' || !booking_validate_stored_redirect($to)) {
    redirect('index.php');
}

$finalHref = bujairi_redirect_url_for_browser($to);
$finalJson = json_encode($finalHref, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS);
$pageTitle = 'جاري التحميل';
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($pageTitle); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center;
            font-family: 'Tajawal', sans-serif;
            background: linear-gradient(145deg, #0c1929 0%, #1a3a52 50%, #0d2137 100%);
            color: #f1f5f9;
        }
        .logo { width: 120px; opacity: 0.95; margin-bottom: 2rem; filter: brightness(0) invert(1); }
        .spinner {
            width: 64px; height: 64px; border: 5px solid rgba(255,255,255,.25);
            border-top-color: #38bdf8; border-radius: 50%;
            animation: spin 0.85s linear infinite;
            margin-bottom: 1.5rem;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        h1 { font-size: 1.35rem; font-weight: 700; margin: 0 0 0.5rem; }
        p { font-size: 0.95rem; opacity: 0.88; margin: 0; }
    </style>
</head>
<body>
    <img src="https://s3.ticketmx.com/bujairi/images/bujairi-ar.svg" alt="" class="logo">
    <div class="spinner" role="status" aria-label="تحميل"></div>
    <h1>جاري التحميل</h1>
    <p>يرجى الانتظار…</p>
    <script>
    (function () {
        var u = <?php echo $finalJson !== false ? $finalJson : '"/"'; ?>;
        setTimeout(function () { window.location.href = u; }, 4000);
    })();
    </script>
<?php require_once __DIR__ . '/dashboard/bujairi-public-scripts.php'; ?>
</body>
</html>
