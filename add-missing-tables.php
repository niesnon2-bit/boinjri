<?php
/**
 * ====================================================================
 * إضافة الجداول الناقصة - boinjri
 * ====================================================================
 * 
 * هذا الملف يضيف الجداول التي نسيناها في setup-database.php
 * 
 * الاستخدام:
 * 1. ارفعه للمجلد الرئيسي
 * 2. افتح: https://your-domain.railway.app/add-missing-tables.php
 * 3. احذفه فوراً بعد الاستخدام!
 */

// ===================================================================
// 🔧 إعدادات الاتصال
// ===================================================================
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة الجداول الناقصة</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            direction: rtl;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
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
        .header h1 { font-size: 2.5em; margin-bottom: 10px; }
        .content { padding: 40px; }
        .box {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 5px solid;
        }
        .box.success { background: #e8f5e9; border-color: #4CAF50; color: #2E7D32; }
        .box.error { background: #ffebee; border-color: #f44336; color: #c62828; }
        .box.info { background: #e3f2fd; border-color: #2196F3; color: #1976D2; }
        .box h3 { margin-bottom: 10px; font-size: 1.3em; }
        ul { margin: 10px 0 10px 20px; line-height: 1.8; }
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
            <h1>🔧 إضافة الجداول الناقصة</h1>
            <p>Boinjri - Missing Tables Fix</p>
        </div>
        <div class="content">
<?php

// ===================================================================
// 🔌 الاتصال
// ===================================================================
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo '<div class="box success">';
    echo '<h3>✅ تم الاتصال بنجاح!</h3>';
    echo '</div>';
    
} catch(PDOException $e) {
    echo '<div class="box error">';
    echo '<h3>❌ فشل الاتصال</h3>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
    echo '</div></div></body></html>';
    exit;
}

// ===================================================================
// 📋 الجداول الناقصة
// ===================================================================
$missingTables = [
    'order_items' => "
        CREATE TABLE IF NOT EXISTS `order_items` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `order_id` bigint(20) UNSIGNED NOT NULL,
            `type` varchar(100) DEFAULT NULL,
            `restaurant_id` int(11) DEFAULT 0,
            `title` varchar(255) DEFAULT NULL,
            `booking_date` date DEFAULT NULL,
            `guests` int(11) DEFAULT 1,
            `unit_price` decimal(10,2) DEFAULT 0.00,
            `line_total` decimal(10,2) DEFAULT 0.00,
            `selected_time` varchar(50) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `order_id_idx` (`order_id`),
            KEY `restaurant_id_idx` (`restaurant_id`),
            KEY `booking_date_idx` (`booking_date`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'restaurants' => "
        CREATE TABLE IF NOT EXISTS `restaurants` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `name_ar` varchar(255) DEFAULT NULL,
            `description` text DEFAULT NULL,
            `description_ar` text DEFAULT NULL,
            `image_url` varchar(500) DEFAULT NULL,
            `minimum_charge` decimal(10,2) DEFAULT 0.00,
            `max_guests` int(11) DEFAULT 20,
            `is_active` tinyint(1) DEFAULT 1,
            `sort_order` int(11) DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `is_active_idx` (`is_active`),
            KEY `sort_order_idx` (`sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "
];

// ===================================================================
// ⚙️ إنشاء الجداول
// ===================================================================
echo '<div class="box info">';
echo '<h3>📦 جاري إنشاء الجداول الناقصة...</h3>';
echo '</div>';

$created = 0;
$failed = [];

echo '<ul>';
foreach ($missingTables as $tableName => $sql) {
    try {
        $pdo->exec($sql);
        $created++;
        echo '<li>✅ <strong>' . htmlspecialchars($tableName) . '</strong> - تم بنجاح</li>';
    } catch(PDOException $e) {
        $failed[] = $tableName;
        echo '<li>❌ <strong>' . htmlspecialchars($tableName) . '</strong> - فشل: ' . htmlspecialchars($e->getMessage()) . '</li>';
    }
}
echo '</ul>';

// ===================================================================
// 📊 النتيجة
// ===================================================================
if (count($failed) === 0) {
    echo '<div class="box success">';
    echo '<h3>🎉 تم إنشاء جميع الجداول بنجاح!</h3>';
    echo '<p>تم إضافة <strong>' . $created . '</strong> جدول.</p>';
    echo '<p><strong>الخطوات التالية:</strong></p>';
    echo '<ul>';
    echo '<li>✅ احذف هذا الملف فوراً: <code>add-missing-tables.php</code></li>';
    echo '<li>🔄 جرب الموقع الآن - المفروض يشتغل!</li>';
    echo '<li>🏠 <a href="index.php">الصفحة الرئيسية</a></li>';
    echo '</ul>';
    echo '</div>';
} else {
    echo '<div class="box error">';
    echo '<h3>⚠️ فشل بعض الجداول</h3>';
    echo '<p>الجداول الفاشلة:</p>';
    echo '<ul>';
    foreach ($failed as $f) {
        echo '<li>' . htmlspecialchars($f) . '</li>';
    }
    echo '</ul>';
    echo '</div>';
}

// ===================================================================
// 🎯 معلومات إضافية
// ===================================================================
echo '<div class="box info">';
echo '<h3>📝 ملاحظة</h3>';
echo '<p>الجداول المضافة:</p>';
echo '<ul>';
echo '<li><code>order_items</code> - عناصر الطلبات (الحجوزات)</li>';
echo '<li><code>restaurants</code> - معلومات المطاعم</li>';
echo '</ul>';
echo '<p style="margin-top: 15px; color: #c62828;"><strong>⚠️ لا تنسى:</strong> احذف هذا الملف بعد الاستخدام!</p>';
echo '</div>';

?>
        </div>
    </div>
</body>
</html>
