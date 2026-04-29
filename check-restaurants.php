<?php
/**
 * ====================================================================
 * فحص وإضافة جدول المطاعم - restaurants
 * ====================================================================
 */

$host = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
$port = getenv('MYSQLPORT') ?: '3306';
$dbname = getenv('MYSQLDATABASE') ?: 'railway';
$username = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: '';

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فحص قاعدة البيانات</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            direction: rtl;
        }
        .container {
            max-width: 900px;
            margin: 30px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 { font-size: 2em; margin-bottom: 10px; }
        .content { padding: 40px; }
        .box {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 5px solid;
        }
        .success { background: #e8f5e9; border-color: #4CAF50; color: #2E7D32; }
        .error { background: #ffebee; border-color: #f44336; color: #c62828; }
        .warning { background: #fff3e0; border-color: #ff9800; color: #e65100; }
        .info { background: #e3f2fd; border-color: #2196F3; color: #1976D2; }
        h3 { margin-bottom: 15px; font-size: 1.3em; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
            color: #333;
        }
        tr:hover { background: #f9f9f9; }
        code {
            background: #f5f5f5;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            color: #c62828;
            font-size: 0.9em;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 10px 5px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        ul { margin: 15px 0 15px 20px; line-height: 1.8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 فحص قاعدة البيانات</h1>
            <p>التحقق من الجداول وإضافة المفقود</p>
        </div>
        <div class="content">

<?php

// ===================================================================
// الاتصال
// ===================================================================
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo '<div class="box success">';
    echo '<h3>✅ تم الاتصال بقاعدة البيانات بنجاح</h3>';
    echo '<p>الخادم: <code>' . htmlspecialchars($host) . ':' . htmlspecialchars($port) . '</code></p>';
    echo '<p>قاعدة البيانات: <code>' . htmlspecialchars($dbname) . '</code></p>';
    echo '</div>';
    
} catch(PDOException $e) {
    echo '<div class="box error">';
    echo '<h3>❌ فشل الاتصال</h3>';
    echo '<p><strong>الخطأ:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
    echo '</div></div></body></html>';
    exit;
}

// ===================================================================
// عرض جميع الجداول الموجودة
// ===================================================================
echo '<div class="box info">';
echo '<h3>📋 الجداول الموجودة في قاعدة البيانات:</h3>';

try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo '<p>⚠️ لا توجد جداول في قاعدة البيانات!</p>';
    } else {
        echo '<table>';
        echo '<thead><tr><th>#</th><th>اسم الجدول</th><th>الحالة</th></tr></thead>';
        echo '<tbody>';
        
        $requiredTables = ['restaurants', 'orders', 'order_items', 'users', 'admins'];
        
        foreach ($tables as $index => $table) {
            $isRequired = in_array($table, $requiredTables);
            $status = $isRequired ? '✅ مطلوب' : 'ℹ️ موجود';
            echo '<tr>';
            echo '<td>' . ($index + 1) . '</td>';
            echo '<td><code>' . htmlspecialchars($table) . '</code></td>';
            echo '<td>' . $status . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
        echo '<p><strong>إجمالي الجداول:</strong> ' . count($tables) . '</p>';
    }
} catch(PDOException $e) {
    echo '<p class="error">❌ خطأ: ' . htmlspecialchars($e->getMessage()) . '</p>';
}

echo '</div>';

// ===================================================================
// التحقق من جدول restaurants
// ===================================================================
$restaurantExists = false;
$restaurantHasData = false;

try {
    $check = $pdo->query("SHOW TABLES LIKE 'restaurants'")->fetch();
    $restaurantExists = !empty($check);
    
    if ($restaurantExists) {
        $count = $pdo->query("SELECT COUNT(*) FROM restaurants")->fetchColumn();
        $restaurantHasData = $count > 0;
        
        echo '<div class="box success">';
        echo '<h3>✅ جدول restaurants موجود</h3>';
        echo '<p><strong>عدد المطاعم:</strong> ' . $count . '</p>';
        
        if ($restaurantHasData) {
            echo '<p>✅ الجدول يحتوي على بيانات</p>';
            
            // عرض بعض البيانات
            $sample = $pdo->query("SELECT id, name, type FROM restaurants LIMIT 5")->fetchAll();
            if ($sample) {
                echo '<table>';
                echo '<thead><tr><th>ID</th><th>الاسم</th><th>النوع</th></tr></thead>';
                echo '<tbody>';
                foreach ($sample as $row) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['name']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['type']) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            }
        } else {
            echo '<p class="warning">⚠️ الجدول فارغ - لا توجد مطاعم</p>';
        }
        
        echo '</div>';
    }
    
} catch(PDOException $e) {
    $restaurantExists = false;
}

// ===================================================================
// إضافة جدول restaurants إذا لم يكن موجوداً
// ===================================================================
if (!$restaurantExists) {
    echo '<div class="box warning">';
    echo '<h3>⚠️ جدول restaurants غير موجود</h3>';
    echo '<p>سيتم إنشاء الجدول الآن...</p>';
    echo '</div>';
    
    $createTableSQL = "
    CREATE TABLE `restaurants` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(200) NOT NULL,
        `type` varchar(100) NOT NULL,
        `description` varchar(1000) NOT NULL,
        `minimum_charge` decimal(18,2) NOT NULL DEFAULT 50.00,
        `image_url` varchar(500) NOT NULL,
        `logo_url` varchar(500) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    try {
        $pdo->exec($createTableSQL);
        
        echo '<div class="box success">';
        echo '<h3>✅ تم إنشاء جدول restaurants بنجاح!</h3>';
        echo '</div>';
        
        $restaurantExists = true;
        
    } catch(PDOException $e) {
        echo '<div class="box error">';
        echo '<h3>❌ فشل إنشاء الجدول</h3>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div>';
    }
}

// ===================================================================
// إضافة بيانات المطاعم إذا كان الجدول فارغاً
// ===================================================================
if ($restaurantExists && !$restaurantHasData) {
    echo '<div class="box info">';
    echo '<h3>📝 إضافة بيانات المطاعم...</h3>';
    echo '</div>';
    
    $restaurants = [
        [1, 'برنش آند كيك', 'عالمي', 'مطعم عالمي يقدم تجربة فريدة بنكهات أوروبية.', 50.00, 'https://s3.ticketmx.com/uploads/images/dc3da05925e3b013dd31db803aee61002b84c3a5.jpg', 'https://s3.ticketmx.com/uploads/images/57bde77de2eab09de36472cb45af748ebd0f883a.jpeg'],
        [2, 'كوفا للحلويات', 'إيطالي', 'من أقدم محلات الحلويات في إيطاليا.', 50.00, 'https://s3.ticketmx.com/uploads/images/c334f0cd19a6d2c6a4dcafd629b171bf52dfa477.jpeg', 'https://s3.ticketmx.com/uploads/images/12dde91a2e1d0516d29ea50b4bce8e5428a166bc.jpeg'],
        [3, 'لونق تشيم', 'تايلندي', 'مطعم تايلندي حاصل على نجمة ميشلان.', 50.00, 'https://s3.ticketmx.com/uploads/images/4662cdb0543f37db5dd9dd8160c0ffba6958b026.jpg', 'https://s3.ticketmx.com/uploads/images/681bd55fd60c18086636e068378b27c80f591401.png'],
        [4, 'سموير', 'عربي', 'مطبخ عربي معاصر بنكهات مبتكرة.', 100.00, 'https://s3.ticketmx.com/uploads/images/1cd3c84939397f11560c2182bb849082c8b7780f.jpg', 'https://s3.ticketmx.com/uploads/images/171328c841175e3868d895bd0596476a2e3d657d.jpeg'],
        [5, 'Maiz', 'سعودي', 'مطبخ سعودي معاصر.', 50.00, 'https://s3.ticketmx.com/uploads/images/18f0cd54ac1189d4d194483aafa81b19a1d3ea53.jpg', 'https://s3.ticketmx.com/uploads/images/2b8446f4a015ab2248a60c6d7aae31e5148b7b69.jpeg'],
        [6, 'Sarabeth\'s', 'أمريكي', 'مطعم أمريكي كلاسيكي.', 50.00, 'https://s3.ticketmx.com/uploads/images/e2b212c3e3e995fec6aab280de9222e036ea5548.jpg', 'https://s3.ticketmx.com/uploads/images/6fdd2c48d28d6ae34e943cb991f3f4ac70aba1e3.jpeg'],
        [7, 'Villa Mamas', 'بحريني', 'نكهات بحرينية تقليدية.', 50.00, 'https://s3.ticketmx.com/uploads/images/8ac4478b64d9249e8ea05d819ef894716efcf890.jpg', 'https://s3.ticketmx.com/uploads/images/d61080bc423e0e9f8a2e168a4966729002b7a7e4.png'],
        [8, 'Angelina', 'فرنسي', 'مطعم فرنسي راقي.', 100.00, 'https://s3.ticketmx.com/uploads/images/62d35c7e8a573ce2e3c2f58fef5bfefb5bd89b0c.jpg', 'https://s3.ticketmx.com/uploads/images/025684d674038de717849ab7648ac07d9e758380.jpeg'],
        [9, 'Sum+Things', 'عالمي', 'تجربة طعام عالمية مبتكرة.', 50.00, 'https://s3.ticketmx.com/uploads/images/708d2b1f002656fa349b6e1bac06423516b9c940.jpg', 'https://s3.ticketmx.com/uploads/images/0ccb0c4936c6f8f2bc582de782f586009a79dcb3.jpeg'],
        [10, 'Flamingo Room', 'أوروبي', 'مطعم أوروبي فاخر.', 50.00, 'https://s3.ticketmx.com/uploads/images/6d48728583b18cb8fcd457a955d4de5ecef627e4.jpeg', 'https://s3.ticketmx.com/uploads/images/4e6f28ff2f9e5b6ec454f69466108ee0d11cca0f.jpeg'],
        [11, 'Takya', 'سعودي', 'مطعم سعودي عصري.', 100.00, 'https://s3.ticketmx.com/uploads/images/3c2475d677b483d98054db4b2056199aa65d6d89.jpg', 'https://s3.ticketmx.com/uploads/images/d0439724baefb87c36ab9686e0b0c4e47a8df8ff.jpg'],
        [12, 'Altopiano', 'إيطالي', 'نكهات إيطالية أصيلة.', 50.00, 'https://s3.ticketmx.com/uploads/images/e3ec2ab6a7849e584da4a00fb41ef0acdb9d3560.jpg', 'https://s3.ticketmx.com/uploads/images/f140c9e5e0c441879d2c2d00a42dc1b0a1f87a51.jpg'],
        [13, 'African Lounge', 'أفريقي', 'مطعم أفريقي فاخر.', 150.00, 'https://s3.ticketmx.com/uploads/images/82b7017ee1f5e2aeeeb1976f6b70e2c72681c9ef.png', 'https://s3.ticketmx.com/uploads/images/86bf1bacf103acc43409d31b2f39892951546ff2.png'],
        [14, 'MAISON ASSOULINE', 'عالمي', 'تجربة فاخرة ومميزة.', 50.00, 'https://s3.ticketmx.com/uploads/images/513905dc523d74baeae65ca18e304ec12a101745.jpeg', 'https://s3.ticketmx.com/uploads/images/f7db7cb9aba48e752438edbb8fb33db4a760dedf.jpeg'],
        [15, 'Dolce and Gabbana Caffe', 'إيطالي', 'مقهى فاخر بطابع إيطالي.', 1.00, 'https://s3.ticketmx.com/uploads/images/efdf8102069e93691cdf9874e3a7a68209876169.jpg', 'https://s3.ticketmx.com/uploads/images/c363428069a78389c5fc6e6a254e67bee14192a1.jpg'],
        [16, 'LIZA', 'عالمي', 'مطعم بطابع عالمي.', 50.00, 'https://s3.ticketmx.com/uploads/images/db19a6d6edbff851dda08f1ba06b59e935f09640.jpg', 'https://s3.ticketmx.com/uploads/images/17fc57d1db0f816371d6c1ef1d6287110a47ef64.png']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO restaurants (id, name, type, description, minimum_charge, image_url, logo_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $success = 0;
    $failed = 0;
    
    foreach ($restaurants as $restaurant) {
        try {
            $stmt->execute($restaurant);
            $success++;
        } catch(PDOException $e) {
            $failed++;
        }
    }
    
    echo '<div class="box success">';
    echo '<h3>✅ تمت إضافة البيانات</h3>';
    echo '<p><strong>نجح:</strong> ' . $success . ' مطعم</p>';
    if ($failed > 0) {
        echo '<p><strong>فشل:</strong> ' . $failed . ' مطعم</p>';
    }
    echo '</div>';
}

// ===================================================================
// النتيجة النهائية
// ===================================================================
echo '<div class="box info">';
echo '<h3>📊 الملخص النهائي</h3>';
echo '<ul>';
echo '<li>✅ الاتصال بقاعدة البيانات: <strong>نجح</strong></li>';
echo '<li>' . ($restaurantExists ? '✅' : '❌') . ' جدول restaurants: <strong>' . ($restaurantExists ? 'موجود' : 'غير موجود') . '</strong></li>';
echo '<li>' . ($restaurantHasData ? '✅' : '⚠️') . ' بيانات المطاعم: <strong>' . ($restaurantHasData ? 'متوفرة' : 'غير متوفرة') . '</strong></li>';
echo '</ul>';

echo '<hr style="margin: 20px 0;">';

echo '<h3>🔗 الخطوات التالية:</h3>';
echo '<ul>';
echo '<li>✅ احذف هذا الملف بعد الاستخدام</li>';
echo '<li>🌐 افتح صفحة المطاعم: <a href="restaurants.php" class="btn">restaurants.php</a></li>';
echo '<li>🏠 العودة للرئيسية: <a href="index.php" class="btn">index.php</a></li>';
echo '</ul>';

echo '</div>';

?>

        </div>
    </div>
</body>
</html>
