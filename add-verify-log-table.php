<?php
/**
 * ====================================================================
 * إضافة جدول order_booking_success_verify_log
 * ====================================================================
 * 
 * هذا الجدول ضروري لعرض رموز التحقق في مودال نفاذ
 */

$host = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
$port = getenv('MYSQLPORT') ?: '3306';
$dbname = getenv('MYSQLDATABASE') ?: 'railway';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إضافة جدول رموز التحقق</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            direction: rtl;
        }
        .container {
            max-width: 700px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content { padding: 40px; }
        .box {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 5px solid;
        }
        .success { background: #e8f5e9; border-color: #4CAF50; color: #2E7D32; }
        .error { background: #ffebee; border-color: #f44336; color: #c62828; }
        .info { background: #e3f2fd; border-color: #2196F3; color: #1976D2; }
        h3 { margin-bottom: 10px; }
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            color: #c62828;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔧 إضافة جدول رموز التحقق</h1>
            <p>order_booking_success_verify_log</p>
        </div>
        <div class="content">
<?php

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo '<div class="box success">';
    echo '<h3>✅ تم الاتصال بقاعدة البيانات</h3>';
    echo '</div>';
    
} catch(PDOException $e) {
    echo '<div class="box error">';
    echo '<h3>❌ فشل الاتصال</h3>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
    echo '</div></div></body></html>';
    exit;
}

$sql = "
CREATE TABLE IF NOT EXISTS `order_booking_success_verify_log` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id` bigint(20) UNSIGNED NOT NULL,
    `transaction_no` varchar(50) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `order_id_idx` (`order_id`),
    KEY `created_at_idx` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
";

try {
    $pdo->exec($sql);
    
    echo '<div class="box success">';
    echo '<h3>🎉 تم إنشاء الجدول بنجاح!</h3>';
    echo '<p>الجدول: <code>order_booking_success_verify_log</code></p>';
    echo '<p><strong>الخطوات التالية:</strong></p>';
    echo '<ul>';
    echo '<li>✅ احذف هذا الملف فوراً</li>';
    echo '<li>🔄 افتح لوحة التحكم وجرب مودال نفاذ</li>';
    echo '<li>✅ يجب أن تظهر رموز التحقق الآن</li>';
    echo '</ul>';
    echo '</div>';
    
} catch(PDOException $e) {
    echo '<div class="box error">';
    echo '<h3>❌ فشل إنشاء الجدول</h3>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
}

echo '<div class="box info">';
echo '<h3>📝 ملاحظة</h3>';
echo '<p>هذا الجدول يخزن رموز التحقق (Transaction Code) من صفحة النجاح.</p>';
echo '<p style="color: #c62828; font-weight: bold; margin-top: 15px;">⚠️ احذف هذا الملف بعد الاستخدام!</p>';
echo '</div>';

?>
        </div>
    </div>
</body>
</html>
