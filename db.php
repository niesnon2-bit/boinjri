<?php
declare(strict_types=1);

// عدّل القيم حسب استضافتك/قاعدة البيانات
define('APP_BASE', '');

define('DB_HOST', getenv('MYSQLHOST') ?: 'mysql.railway.internal');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'railway');
define('DB_USER', getenv('MYSQLUSER') ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: 'mihiGRVvutPwuuyPkXLQGSoYRWAhwGH');
define('DB_CHARSET', 'utf8mb4');

