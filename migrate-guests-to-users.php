<?php
require 'bootstrap.php';

echo "<h2>نقل guest_logins إلى جدول users</h2>";

try {
    $pdo = bujairi_pdo();
    
    // جلب جميع guest_logins
    $guests = $pdo->query("SELECT * FROM guest_logins ORDER BY id ASC")->fetchAll(PDO::FETCH_OBJ);
    
    if (empty($guests)) {
        echo "❌ لا توجد بيانات guest_logins<br>";
        exit;
    }
    
    echo "وجدت " . count($guests) . " مستخدم في guest_logins<br><br>";
    
    $migrated = 0;
    $skipped = 0;
    
    foreach ($guests as $guest) {
        // التحقق من وجود المستخدم في users
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$guest->email]);
        
        if ($check->fetch()) {
            echo "⏭️ تم تخطي: {$guest->email} (موجود مسبقاً في users)<br>";
            $skipped++;
            continue;
        }
        
        // إضافة المستخدم إلى جدول users
        $insert = $pdo->prepare("
            INSERT INTO users (email, password_hash, created_at, updated_at, full_name, is_guest_migrated) 
            VALUES (?, ?, ?, NOW(), ?, 1)
        ");
        
        $insert->execute([
            $guest->email,
            '', // password_hash فارغ لأن كلمة المرور موجودة في password_entered
            $guest->created_at,
            $guest->email // استخدام البريد كاسم مؤقت
        ]);
        
        echo "✅ تم نقل: {$guest->email}<br>";
        $migrated++;
    }
    
    echo "<br><hr><br>";
    echo "<b>النتيجة:</b><br>";
    echo "✅ تم نقل: $migrated مستخدم<br>";
    echo "⏭️ تم تخطي: $skipped مستخدم (موجود مسبقاً)<br>";
    echo "<br>";
    echo "🎉 <b style='color:green'>العملية مكتملة!</b><br>";
    echo "<br>";
    echo "<a href='dashboard/'>فتح لوحة التحكم</a><br>";
    
} catch(PDOException $e) {
    echo "❌ <b>خطأ:</b> " . $e->getMessage();
}
