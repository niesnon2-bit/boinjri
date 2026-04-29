<?php
/**
 * ====================================================================
 * تشخيص مشكلة restaurants.php
 * ====================================================================
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تشخيص المشكلة</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            direction: rtl;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
        h2 { color: #667eea; margin-top: 30px; }
        .success { background: #e8f5e9; padding: 15px; border-radius: 5px; color: #2e7d32; margin: 10px 0; }
        .error { background: #ffebee; padding: 15px; border-radius: 5px; color: #c62828; margin: 10px 0; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; color: #1976d2; margin: 10px 0; }
        code { background: #f5f5f5; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: right; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 تشخيص مشكلة restaurants.php</h1>

<?php

// ===================================================================
// 1. التحقق من ملف db.php
// ===================================================================
echo '<h2>1️⃣ فحص ملف db.php</h2>';

if (file_exists(__DIR__ . '/db.php')) {
    echo '<div class="success">✅ ملف db.php موجود</div>';
    require __DIR__ . '/db.php';
    
    echo '<table>';
    echo '<tr><th>المتغير</th><th>القيمة</th></tr>';
    echo '<tr><td>DB_HOST</td><td><code>' . (defined('DB_HOST') ? DB_HOST : 'غير معرّف') . '</code></td></tr>';
    echo '<tr><td>DB_NAME</td><td><code>' . (defined('DB_NAME') ? DB_NAME : 'غير معرّف') . '</code></td></tr>';
    echo '<tr><td>DB_USER</td><td><code>' . (defined('DB_USER') ? DB_USER : 'غير معرّف') . '</code></td></tr>';
    echo '<tr><td>DB_PASS</td><td><code>' . (defined('DB_PASS') ? (strlen(DB_PASS) > 0 ? '***موجود***' : 'فارغ!') : 'غير معرّف') . '</code></td></tr>';
    echo '<tr><td>DB_CHARSET</td><td><code>' . (defined('DB_CHARSET') ? DB_CHARSET : 'غير معرّف') . '</code></td></tr>';
    echo '</table>';
} else {
    echo '<div class="error">❌ ملف db.php غير موجود!</div>';
    exit;
}

// ===================================================================
// 2. التحقق من ملف bootstrap.php
// ===================================================================
echo '<h2>2️⃣ فحص ملف bootstrap.php</h2>';

if (file_exists(__DIR__ . '/bootstrap.php')) {
    echo '<div class="success">✅ ملف bootstrap.php موجود</div>';
    require __DIR__ . '/bootstrap.php';
    
    if (function_exists('bujairi_pdo')) {
        echo '<div class="success">✅ دالة bujairi_pdo() موجودة</div>';
    } else {
        echo '<div class="error">❌ دالة bujairi_pdo() غير موجودة!</div>';
    }
} else {
    echo '<div class="error">❌ ملف bootstrap.php غير موجود!</div>';
    exit;
}

// ===================================================================
// 3. محاولة الاتصال
// ===================================================================
echo '<h2>3️⃣ محاولة الاتصال بقاعدة البيانات</h2>';

try {
    // محاولة 1: باستخدام db.php
    echo '<div class="info">📡 محاولة الاتصال باستخدام إعدادات db.php...</div>';
    
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    echo '<p>DSN: <code>' . htmlspecialchars($dsn) . '</code></p>';
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo '<div class="success">✅ الاتصال نجح!</div>';
    
} catch(PDOException $e) {
    echo '<div class="error">❌ فشل الاتصال (محاولة 1)</div>';
    echo '<p><strong>الخطأ:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    
    // محاولة 2: مع PORT
    try {
        echo '<div class="info">📡 محاولة ثانية مع PORT...</div>';
        
        $port = getenv('MYSQLPORT') ?: '3306';
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . $port . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        echo '<p>DSN: <code>' . htmlspecialchars($dsn) . '</code></p>';
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        echo '<div class="success">✅ الاتصال نجح مع PORT!</div>';
        echo '<div class="info">💡 <strong>الحل:</strong> يجب إضافة PORT في ملف db.php</div>';
        
    } catch(PDOException $e) {
        echo '<div class="error">❌ فشل الاتصال (محاولة 2)</div>';
        echo '<p><strong>الخطأ:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        exit;
    }
}

// ===================================================================
// 4. فحص جدول restaurants
// ===================================================================
echo '<h2>4️⃣ فحص جدول restaurants</h2>';

try {
    $check = $pdo->query("SHOW TABLES LIKE 'restaurants'")->fetch();
    
    if ($check) {
        echo '<div class="success">✅ جدول restaurants موجود</div>';
        
        $count = $pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn();
        echo '<p><strong>عدد المطاعم:</strong> ' . $count . '</p>';
        
        if ($count > 0) {
            echo '<div class="success">✅ يوجد بيانات في الجدول</div>';
            
            $restaurants = $pdo->query("SELECT id, name, type FROM restaurants LIMIT 5")->fetchAll();
            echo '<table>';
            echo '<thead><tr><th>ID</th><th>الاسم</th><th>النوع</th></tr></thead>';
            echo '<tbody>';
            foreach ($restaurants as $row) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                echo '<td>' . htmlspecialchars($row['type']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<div class="error">⚠️ الجدول فارغ!</div>';
        }
        
    } else {
        echo '<div class="error">❌ جدول restaurants غير موجود!</div>';
    }
    
} catch(PDOException $e) {
    echo '<div class="error">❌ خطأ في الاستعلام</div>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
}

// ===================================================================
// 5. اختبار bujairi_pdo()
// ===================================================================
echo '<h2>5️⃣ اختبار دالة bujairi_pdo()</h2>';

try {
    $testPdo = bujairi_pdo();
    echo '<div class="success">✅ دالة bujairi_pdo() تعمل</div>';
    
    $restaurants = $testPdo->query("SELECT * FROM restaurants ORDER BY id ASC")->fetchAll();
    echo '<p><strong>عدد المطاعم:</strong> ' . count($restaurants) . '</p>';
    
} catch(Throwable $e) {
    echo '<div class="error">❌ دالة bujairi_pdo() فشلت</div>';
    echo '<p><strong>الخطأ:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
}

// ===================================================================
// 6. الحل
// ===================================================================
echo '<h2>🎯 الحل</h2>';

echo '<div class="info">';
echo '<p><strong>إذا نجح الاتصال في "محاولة ثانية مع PORT":</strong></p>';
echo '<ol>';
echo '<li>افتح ملف <code>db.php</code></li>';
echo '<li>عدّل السطر الخاص بـ DB_HOST ليصبح:</li>';
echo '</ol>';
echo '<pre style="background:#f5f5f5;padding:15px;border-radius:5px;overflow:auto;">define(\'DB_HOST\', (getenv(\'MYSQLHOST\') ?: \'mysql.railway.internal\') . \':\' . (getenv(\'MYSQLPORT\') ?: \'3306\'));</pre>';
echo '<p>أو ببساطة:</p>';
echo '<pre style="background:#f5f5f5;padding:15px;border-radius:5px;overflow:auto;">define(\'DB_HOST\', \'mysql.railway.internal:3306\');</pre>';
echo '</div>';

echo '<div class="success">';
echo '<h3>✅ بعد التعديل:</h3>';
echo '<ol>';
echo '<li>احذف هذا الملف</li>';
echo '<li>افتح <a href="restaurants.php" style="color:#1976d2;font-weight:bold;">restaurants.php</a></li>';
echo '<li>يجب أن تظهر المطاعم!</li>';
echo '</ol>';
echo '</div>';

?>

    </div>
</body>
</html>
