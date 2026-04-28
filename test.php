<?php
// إيقاف عرض الأخطاء مؤقتاً
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 اختبار البيئة</h2>";

// 1. التحقق من PDO
if (extension_loaded('pdo')) {
    echo "✅ PDO مثبت<br>";
    $drivers = PDO::getAvailableDrivers();
    echo "Drivers: " . implode(', ', $drivers) . "<br>";
    
    if (in_array('mysql', $drivers)) {
        echo "✅ <b>PDO MySQL مثبت!</b><br><br>";
    } else {
        echo "❌ <b>PDO MySQL غير مثبت!</b><br><br>";
    }
} else {
    echo "❌ PDO غير مثبت<br><br>";
}

// 2. التحقق من المتغيرات البيئية
echo "<h3>المتغيرات البيئية:</h3>";
$vars = ['MYSQLHOST', 'MYSQLUSER', 'MYSQLPASSWORD', 'MYSQLDATABASE', 'MYSQLPORT'];
foreach ($vars as $var) {
    $value = getenv($var);
    if ($value) {
        echo "✅ $var = " . ($var === 'MYSQLPASSWORD' ? '***' : $value) . "<br>";
    } else {
        echo "❌ $var غير موجود<br>";
    }
}

// 3. محاولة الاتصال
echo "<br><h3>محاولة الاتصال:</h3>";
try {
    $host = getenv('MYSQLHOST');
    $user = getenv('MYSQLUSER');
    $pass = getenv('MYSQLPASSWORD');
    $db   = getenv('MYSQLDATABASE');
    $port = getenv('MYSQLPORT') ?: '3306';
    
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "✅ <b style='color:green'>نجح الاتصال بقاعدة البيانات!</b><br>";
    
    // عرض الجداول
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<br>الجداول الموجودة: " . (count($tables) > 0 ? implode(', ', $tables) : 'لا توجد جداول') . "<br>";
    
} catch(PDOException $e) {
    echo "❌ <b style='color:red'>فشل الاتصال:</b> " . $e->getMessage();
}
?>
