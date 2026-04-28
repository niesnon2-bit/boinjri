<?php
declare(strict_types=1);
require_once __DIR__ . '/init.php';

if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'الرجاء إدخال البريد وكلمة المرور';
    } else {
        $admin = $User->adminLogin($username, $password);

        if ($admin) {
            $email = (string) ($admin->email ?? $username);
            $_SESSION['admin_id'] = (int) $admin->id;
            $_SESSION['admin_email'] = $email;
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = (string) ($admin->username ?? $email);
            $_SESSION['admin_full_name'] = (string) ($admin->full_name ?? $email);

            header('Location: index.php');
            exit;
        }
        $error = 'البريد أو كلمة المرور غير صحيحة';
    }
}

$pageTitle = 'تسجيل الدخول - لوحة التحكم';
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            font-family: "Cairo", sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .login-header i { font-size: 4rem; margin-bottom: 15px; }

        .login-header h2 { font-size: 1.8rem; font-weight: 700; margin: 0; }
        .login-header p { margin: 10px 0 0; opacity: 0.9; font-size: 0.95rem; }

        .login-body { padding: 40px 30px; }
        .form-group { margin-bottom: 25px; }
        .form-label {
            font-weight: 600; color: #374151; margin-bottom: 10px;
            display: flex; align-items: center; gap: 8px; font-size: 0.95rem;
        }
        .form-control {
            border: 2px solid #e5e7eb; border-radius: 12px; padding: 14px 18px;
            font-size: 1rem; font-weight: 500;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            outline: none;
        }
        .input-icon { position: relative; }
        .input-icon i {
            position: absolute; left: 18px; top: 50%;
            transform: translateY(-50%); color: #9ca3af; font-size: 1.1rem;
        }
        .input-icon .form-control { padding-left: 50px; }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white; border: none; border-radius: 12px; padding: 15px;
            font-size: 1.1rem; font-weight: 700; width: 100%; cursor: pointer;
        }

        .alert { border-radius: 12px; padding: 15px 18px; margin-bottom: 20px; }
        .alert-danger { background: #fee2e2; color: #991b1b; }
        .login-footer {
            text-align: center; padding: 20px 30px; background: #f9fafb;
            color: #6b7280; font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <i class="fas fa-shield-halved"></i>
            <h2>لوحة التحكم</h2>
            <p>تسجيل دخول الإدارة — جدول <code>admins</code> وقاعدة البيانات المعرّفة في <code>dashboard/config.php</code></p>
        </div>

        <div class="login-body">
            <?php if ($error !== ''): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="form-group">
                    <label class="form-label" for="loginEmail">
                        <i class="fas fa-envelope"></i> البريد الإلكتروني (للإدارة)
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-user-circle"></i>
                        <input type="email" name="username" id="loginEmail" class="form-control"
                            placeholder="admin@site.com" required autocomplete="username" autofocus
                            value="<?php echo htmlspecialchars((string) ($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">
                        <i class="fas fa-lock"></i> كلمة المرور
                    </label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="password" class="form-control"
                            placeholder="••••••••" required autocomplete="current-password">
                    </div>
                </div>

                <div class="remember-me mb-3" style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="remember" id="remember" style="width: 18px; height: 18px;">
                    <label for="remember" class="m-0">تذكرني (جلسة الخادم)</label>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> تسجيل الدخول
                </button>
            </form>
        </div>

        <div class="login-footer">
            <p class="mb-0"><a href="<?php echo htmlspecialchars(url('admin/login.php'), ENT_QUOTES, 'UTF-8'); ?>">دخول الإدارة (admin)</a> ·
            <a href="<?php echo htmlspecialchars(url('index.php'), ENT_QUOTES, 'UTF-8'); ?>">الموقع</a></p>
        </div>
    </div>
</body>
</html>
