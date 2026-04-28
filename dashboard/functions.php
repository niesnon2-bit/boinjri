<?php
//Functions file
//Application Name
$app_name = 'airlines';

//----------------------------------------------
//Database connection data
$host_name  = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
$username   = getenv('MYSQLUSER') ?: 'root';
$password   = getenv('MYSQLPASSWORD') ?: 'mihiGRVvutPwuuyPkXLQGSoYRWAhwGH';
$db_name    = getenv('MYSQLDATABASE') ?: 'railway';

//----------------------------------------------
//Connect to database
$db_connection = mysqli_connect($host_name, $username, $password);

// Check connection
if (!$db_connection) {
    die("❌ فشل الاتصال بقاعدة البيانات: " . mysqli_connect_error());
}

//----------------------------------------------
//Use Database
$use_db = 'USE ' . $db_name;
if (!mysqli_query($db_connection, $use_db)) {
    echo "يرجى تعديل معلومات الاتصال بقاعدة البيانات";
    die();
}

//----------------------------------------------
//Create Tables If Not Exist Any Table
$count = 'SELECT count(*) AS total FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = "' . $db_name . '"';
$result = mysqli_query($db_connection, $count);
$r = @mysqli_fetch_assoc($result);

if ($r['total'] < 1) {
    $create_tbl_users = "CREATE TABLE users(
        id INT(99) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
        user_name TEXT NOT NULL,
        code TEXT NOT NULL,
        approve TEXT NOT NULL,
        password TEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $services = "CREATE TABLE services(
        id INT(99) UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY,
        region TEXT NOT NULL,
        services TEXT NOT NULL,
        player TEXT NOT NULL,
        duration TEXT NOT NULL,
        gender TEXT NOT NULL,
        payment TEXT NOT NULL,
        the_date TEXT NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    mysqli_query($db_connection, $create_tbl_users); //Create users Table
    mysqli_query($db_connection, $services); //Create services Table
    
    echo "✅ تم إنشاء الجداول بنجاح!";
}
//----------------------------------------------
?>
