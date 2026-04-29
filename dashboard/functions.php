<?php
//Functions file
//Application Name
$app_name = 'airlines';

//----------------------------------------------
//Database connection data using environment variables
$host_name  = getenv('MYSQLHOST');
$username   = getenv('MYSQLUSER');
$password   = getenv('MYSQLPASSWORD');
$db_name    = getenv('MYSQLDATABASE');
$port       = getenv('MYSQLPORT') ?: '3306';

//----------------------------------------------
// الاتصال بقاعدة البيانات باستخدام PDO بدلاً من mysqli
try {
    $db_connection = new PDO(
        "mysql:host=$host_name;port=$port;dbname=$db_name;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // ✅ احذف هذا السطر
    // echo "✅ تم الاتصال بقاعدة البيانات بنجاح!<br>";
    
} catch(PDOException $e) {
    // في حالة الخطأ، سجّل الخطأ في ملف log بدلاً من طباعته
    error_log("Database connection error: " . $e->getMessage());
    die("خطأ في الاتصال بقاعدة البيانات. يرجى المحاولة لاحقاً.");
}

//----------------------------------------------
// إنشاء الجداول إذا لم تكن موجودة
try {
    // التحقق من عدد الجداول
    $stmt = $db_connection->prepare("SELECT COUNT(*) AS total FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = ?");
    $stmt->execute([$db_name]);
    $result = $stmt->fetch();
    
    if ($result['total'] < 1) {
        // إنشاء جدول users
        $create_tbl_users = "CREATE TABLE IF NOT EXISTS users(
            id INT(99) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
            user_name TEXT NOT NULL,
            code TEXT NOT NULL,
            approve TEXT NOT NULL,
            password TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        // إنشاء جدول services
        $services = "CREATE TABLE IF NOT EXISTS services(
            id INT(99) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
            region TEXT NOT NULL,
            services TEXT NOT NULL,
            player TEXT NOT NULL,
            duration TEXT NOT NULL,
            gender TEXT NOT NULL,
            payment TEXT NOT NULL,
            the_date TEXT NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $db_connection->exec($create_tbl_users);
        $db_connection->exec($services);
        
        // ✅ احذف هذا السطر
        // echo "✅ تم إنشاء الجداول بنجاح!<br>";
    }
    
} catch(PDOException $e) {
    // سجّل الخطأ في ملف log
    error_log("Table creation error: " . $e->getMessage());
    // ✅ احذف هذا السطر
    // echo "⚠️ خطأ في إنشاء الجداول: " . $e->getMessage() . "<br>";
}
//----------------------------------------------
