<?php
// بدء الجلسة (لجلب السلة وبيانات المستخدم إن وجدت)
session_start();

// TODO: قم بتعديل بيانات الاتصال بقاعدة البيانات الخاصة بك هنا
$host = 'localhost';
$db   = 'your_database_name';
$user = 'your_database_user';
$pass = 'your_database_password';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = null;
$db_error = "";
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // يمكنك تفعيل طباعة الخطأ أثناء التطوير
    // $db_error = "خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage();
}

$errors = [];
$success_order_id = null;

// معالجة البيانات عند إرسال النموذج (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $state     = trim($_POST['state'] ?? '');
    $city      = trim($_POST['city'] ?? '');
    $zip       = trim($_POST['zip'] ?? '');
    $address   = trim($_POST['address'] ?? '');

    // التحقق من الحقول الأساسية
    if (empty($full_name) || empty($email) || empty($phone) || empty($state) || empty($city) || empty($address)) {
        $errors[] = "الرجاء ملء جميع الحقول الإلزامية.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "البريد الإلكتروني غير صالح.";
    }

    // إذا لم تكن هناك أخطاء، قم بحفظ الطلب
    if (empty($errors)) {
        try {
            if ($pdo) {
                $pdo->beginTransaction();
                
                // 1. إدخال بيانات الطلب في جدول الطلبات (orders) - تأكد أن الجداول لديك مطابقة
                $stmt = $pdo->prepare("INSERT INTO orders (full_name, email, phone, state, city, zip, address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$full_name, $email, $phone, $state, $city, $zip, $address]);
                $success_order_id = $pdo->lastInsertId();

                // 2. تفريغ السلة بعد نجاح الطلب
                unset($_SESSION['cart']);

                $pdo->commit();
            } else {
                // محاكاة نجاح الطلب في حال لم يتم ربط قاعدة البيانات بعد
                $success_order_id = rand(10000, 99999);
            }
        } catch (\Exception $e) {
            if ($pdo && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = "حدث خطأ أثناء حفظ الطلب: " . $e->getMessage();
        }
    }
}

// محاكاة سلة التسوق (يمكنك استبدالها بجلب البيانات من $_SESSION['cart'] أو قاعدة البيانات الخاصة بك)
// مثال لمنتج افتراضي في حال السلة فارغة للعرض
$cart_items = $_SESSION['cart'] ?? [
    [
        'name' => 'Portal Durable sports & fitness',
        'price' => 79.73,
        'quantity' => 1,
        'image' => ''
    ]
];

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 0; // شحن مجاني
$total = $subtotal + $shipping;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إتمام الطلب - Velora</title>
    <style>
        /* التنسيقات العامة والمتجاوبة */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            color: #111;
            direction: rtl;
            text-align: right;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button, input, select, textarea {
            font-family: inherit;
        }

        .navbar {
            background: #fff;
            border-bottom: 1px solid #eee;
            padding: 15px 5%;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-container {
            max-width: 1200px;
            margin: auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            flex-wrap: wrap;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .nav-links {
            list-style: none;
            display: flex;
            gap: 20px;
        }

        .nav-links a {
            font-size: 14px;
            transition: .3s;
        }

        .nav-links a:hover {
            color: #777;
        }

        .checkout-header {
            background: #111;
            color: #fff;
            text-align: center;
            padding: 50px 15px;
        }

        .checkout-header p {
            color: #aaa;
            letter-spacing: 2px;
            font-size: 11px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .checkout-header h1 {
            font-size: 32px;
        }

        .checkout-section {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 15px;
        }

        /* التخطيط: في الموبايل بيانات التوصيل فوق وملخص الطلب تحت بدون أي تداخل */
        .checkout-layout {
            display: flex;
            flex-direction: column-reverse; 
            gap: 20px;
        }

        @media (min-width: 900px) {
            .checkout-layout {
                display: grid;
                grid-template-columns: 1.6fr 1fr;
                flex-direction: row;
                gap: 30px;
            }
        }

        .checkout-box,
        .order-summary,
        .success-box {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 25px rgba(0,0,0,.05);
        }

        @media (min-width: 900px) {
            .checkout-box, .order-summary {
                padding: 30px;
            }
        }

        .checkout-box h2,
        .order-summary h2 {
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #eee;
        }

        /* الحقول والنماذج متجاوبة تماماً */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr; /* عمود واحد في الموبايل لمنع التداخل */
            gap: 15px;
        }

        @media (min-width: 600px) {
            .form-grid {
                grid-template-columns: 1fr 1fr; /* عمودين للشاشات الواسعة */
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-size: 13px;
            font-weight: bold;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            outline: none;
            font-size: 14px;
            background: #fff;
            transition: .3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #111;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .order-summary {
            position: static;
        }

        @media (min-width: 900px) {
            .order-summary {
                position: sticky;
                top: 90px;
                align-self: start;
            }
        }

        .order-product {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .order-product-image {
            width: 60px;
            height: 70px;
            background: #f4f4f4;
            border-radius: 6px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .order-product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .order-product-info {
            flex: 1;
        }

        .order-product-info h3 {
            font-size: 13px;
            margin-bottom: 4px;
        }

        .order-product-info p {
            color: #888;
            font-size: 11px;
        }

        .order-product-price {
            font-size: 13px;
            font-weight: bold;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            color: #666;
            font-size: 13px;
        }

        .summary-total {
            border-top: 1px solid #eee;
            margin-top: 10px;
            padding-top: 15px;
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: bold;
            color: #111;
        }

        .place-order {
            width: 100%;
            border: none;
            background: #111;
            color: #fff;
            padding: 15px;
            border-radius: 6px;
            margin-top: 20px;
            cursor: pointer;
            font-size: 15px;
            transition: background 0.3s;
        }

        .place-order:hover {
            background: #333;
        }

        .errors {
            background: #fff1f1;
            border: 1px solid #f0caca;
            color: #a00000;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success-box {
            text-align: center;
            padding: 50px 20px;
            max-width: 600px;
            margin: 50px auto;
        }

        .success-box h2 {
            margin-bottom: 10px;
        }

        .success-box p {
            color: #777;
            margin-bottom: 20px;
        }

        .order-number {
            display: inline-block;
            background: #f5f5f5;
            padding: 10px 15px;
            border-radius: 6px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .footer {
            background: #0b0b0b;
            color: #fff;
            padding: 30px 15px;
            text-align: center;
            margin-top: 50px;
        }

        .footer p {
            color: #777;
            font-size: 12px;
        }
    </style>
</head>
<body>

    <!-- شريط التنقل -->
    <header class="navbar">
        <div class="navbar-container">
            <div class="logo">Velora</div>
            <ul class="nav-links">
                <li><a href="index.php">الرئيسية</a></li>
                <li><a href="products.php">المنتجات</a></li>
                <li><a href="cart.php">السلة</a></li>
            </ul>
            <div class="nav-actions">🛒</div>
        </div>
    </header>

    <!-- رأس الصفحة -->
    <section class="checkout-header">
        <p>Velora Checkout</p>
        <h1>إتمام الطلب</h1>
    </section>

    <!-- محتوى صفحة الدفع -->
    <section class="checkout-section">
        
        <?php if ($success_order_id): ?>
            <!-- رسالة النجاح عند إتمام الطلب بنجاح -->
            <div class="success-box">
                <div style="font-size: 50px; margin-bottom: 15px;">✅</div>
                <h2>شكراً لك! تم استلام طلبك بنجاح</h2>
                <p>نقوم الآن بمعالجة طلبك وسيتم التواصل معك قريباً لتأكيد الشحن.</p>
                <div class="order-number">رقم الطلب: #<?php echo htmlspecialchars($success_order_id); ?></div>
                <br>
                <a href="index.php" class="place-order" style="display: inline-block; text-decoration: none; width: auto; padding: 12px 30px;">العودة للرئيسية</a>
            </div>
        <?php else: ?>

            <!-- إظهار الأخطاء إن وجدت -->
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="checkout-layout">
                
                <!-- نموذج بيانات التوصيل -->
                <div class="checkout-box">
                    <h2>بيانات التوصيل</h2>
                    <form action="" method="POST">
                        <div class="form-grid">
                            <div class="form-group full">
                                <label>الاسم بالكامل</label>
                                <input type="text" name="full_name" placeholder="اكتب اسمك بالكامل" value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                            </div>
                            
                            <div class="form-group full">
                                <label>البريد الإلكتروني</label>
                                <input type="email" name="email" placeholder="example@domain.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>رقم الهاتف</label>
                                <input type="text" name="phone" placeholder="01xxxxxxxxx" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>المحافظة</label>
                                <select name="state" required>
                                    <option value="">اختر المحافظة</option>
                                    <option value="Cairo" <?php echo (($_POST['state'] ?? '') === 'Cairo') ? 'selected' : ''; ?>>القاهرة</option>
                                    <option value="Giza" <?php echo (($_POST['state'] ?? '') === 'Giza') ? 'selected' : ''; ?>>الجيزة</option>
                                    <option value="Alex" <?php echo (($_POST['state'] ?? '') === 'Alex') ? 'selected' : ''; ?>>الإسكندرية</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>المدينة</label>
                                <input type="text" name="city" placeholder="اكتب المدينة" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label>الرمز البريدي ZIP</label>
                                <input type="text" name="zip" placeholder="الرمز البريدي" value="<?php echo htmlspecialchars($_POST['zip'] ?? ''); ?>">
                            </div>

                            <div class="form-group full">
                                <label>العنوان بالتفصيل</label>
                                <textarea name="address" placeholder="اسم الشارع، رقم الحلة، الشقة..." required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <button type="submit" class="place-order">تأكيد الطلب</button>
                    </form>
                </div>

                <!-- ملخص الطلب الديناميكي من الـ PHP -->
                <div class="order-summary">
                    <h2>ملخص الطلب</h2>
                    
                    <?php foreach ($cart_items as $item): ?>
                    <div class="order-product">
                        <div class="order-product-image">
                            <img src="<?php echo htmlspecialchars($item['image'] ?? ''); ?>" alt="منتج">
                        </div>
                        <div class="order-product-info">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p>الكمية: <?php echo intval($item['quantity']); ?></p>
                        </div>
                        <div class="order-product-price"><?php echo number_format($item['price'] * $item['quantity'], 2); ?> EGP</div>
                    </div>
                    <?php endforeach; ?>

                    <div class="summary-row">
                        <span>المجموع الفرعي</span>
                        <span><?php echo number_format($subtotal, 2); ?> EGP</span>
                    </div>
                    <div class="summary-row">
                        <span>الشحن</span>
                        <span>مجاني</span>
                    </div>
                    <div class="summary-total">
                        <span>الإجمالي الكلي</span>
                        <span><?php echo number_format($total, 2); ?> EGP</span>
                    </div>
                </div>

            </div>
        <?php endif; ?>

    </section>

    <!-- التذييل -->
    <footer class="footer">
        <p>جميع حقوق الطبع والنشر محفوظة © Velora</p>
    </footer>

</body>
</html>
