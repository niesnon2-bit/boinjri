<?php
require 'dashboard/functions.php';

echo "<h2>استيراد قاعدة البيانات</h2>";

// قراءة ملف SQL
$sql_file = 'sql/install.sql'; // ضع اسم ملف SQL هنا

if (!file_exists($sql_file)) {
    die("❌ الملف غير موجود: $sql_file");
}

$sql = file_get_contents($sql_file);

if (empty($sql)) {
    die("❌ الملف فارغ!");
}

try {
    // تنفيذ SQL
    $db_connection->exec($sql);
    echo "✅ <b>تم استيراد قاعدة البيانات بنجاح!</b><br>";
    
    // عرض الجداول
    $tables = $db_connection->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<br>عدد الجداول: " . count($tables) . "<br>";
    echo "<br>الجداول:<br>";
    foreach ($tables as $table) {
        echo "- $table<br>";
    }
    
} catch(PDOException $e) {
    echo "❌ <b>خطأ:</b> " . $e->getMessage();
}
?>
