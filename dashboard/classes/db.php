<?php
declare(strict_types=1);

if (!defined('BUJAIRI_LOADED') && !defined('BUJAIRI_DASHBOARD_INIT')) {
    exit;
}

/**
 * واجهة DB ثابتة (متوافقة مع classes/user.php) — الـ PDO يُمرَّر من dashboard/init.php.
 */
class DB
{
    private static ?\PDO $pdo = null;

    /** @var \PDOStatement|null */
    private static $stmt = null;

    private static int $lastResultCount = 0;

    public static function useProjectPdo(\PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    public static function query($query)
    {
        if (self::$pdo === null) {
            throw new RuntimeException('DB: useProjectPdo() قبل الاستدعاء.');
        }
        self::$stmt = self::$pdo->prepare($query);
    }

    public static function bind($param, $value, $type = null)
    {
        if (self::$stmt === null) {
            return;
        }
        $k = (string) $param;
        if ($k !== '' && $k[0] !== ':') {
            $k = ':' . ltrim($k, ':');
        }
        if ($type === null) {
            if (is_int($value)) {
                $tt = \PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $tt = \PDO::PARAM_BOOL;
            } elseif ($value === null) {
                $tt = \PDO::PARAM_NULL;
            } else {
                $tt = \PDO::PARAM_STR;
            }
        } else {
            $tt = (int) $type;
        }
        self::$stmt->bindValue($k, $value, $tt);
    }

    public static function execute()
    {
        if (self::$stmt === null) {
            return false;
        }
        $ok = (bool) self::$stmt->execute();
        if ($ok) {
            $n = self::$stmt->rowCount();
            self::$lastResultCount = $n > 0 ? (int) $n : 0;
        } else {
            self::$lastResultCount = 0;
        }
        return $ok;
    }

    public static function fetch()
    {
        if (self::$stmt === null) {
            return false;
        }
        self::execute();
        $row = self::$stmt->fetch(\PDO::FETCH_OBJ);
        self::$lastResultCount = $row ? 1 : 0;
        return $row;
    }

    public static function fetchAll()
    {
        if (self::$stmt === null) {
            return [];
        }
        self::execute();
        $all = self::$stmt->fetchAll(\PDO::FETCH_OBJ);
        self::$lastResultCount = is_array($all) ? count($all) : 0;
        return $all;
    }

    public static function rowCount()
    {
        return (int) self::$lastResultCount;
    }

    public static function lastInsertId()
    {
        if (self::$pdo === null) {
            return '0';
        }
        $id = self::$pdo->lastInsertId();
        return $id !== false ? (string) $id : '0';
    }
}
