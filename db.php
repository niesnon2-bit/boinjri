<?php
declare(strict_types=1);

// عدّل القيم حسب استضافتك/قاعدة البيانات
define('APP_BASE', '');

define('DB_HOST', 'mysql.railway.internal:3306');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'railway');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: 'mihiGRVvutPwuuyPkXLQGSoYRWAhwGH');
define('DB_CHARSET', 'utf8mb4');

