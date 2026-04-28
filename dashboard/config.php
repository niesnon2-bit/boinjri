<?php
/** MySQL hostname */
define('DB_HOST', getenv('MYSQLHOST') ?: 'mysql.railway.internal');

/** MySQL database username */
define('DB_USER', getenv('MYSQLUSER') ?: 'root');

/** MySQL database password */
define('DB_PASSWORD', getenv('MYSQLPASSWORD') ?: 'mihiGRVvutPwuuyPkXLQGSoYRWAhwGH');

/** MySQL database name */
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'railway');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

define('CAN_REGISTER', 'none');
define('DEFAULT_ROLE', 'member');

// For development only!!
define('SECURE', false);
define('DEBUG', true);

$_bujairiP = dirname(__DIR__) . '/config/pusher.php';
if (is_readable($_bujairiP)) {
    require_once $_bujairiP;
}
unset($_bujairiP);
?>
