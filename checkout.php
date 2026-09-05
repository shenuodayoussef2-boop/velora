<?php

session_start();

require_once "db.php";

/* =========================================
   INITIALIZE CART
========================================= */

if (
    !isset($_SESSION["cart"])
    ||
    !is_array($_SESSION["cart"])
) {

    $_SESSION["cart"] = [];

}


/* =========================================
   GET CART PRODUCTS
========================================= */

$cartProducts = [];

$subtotal = 0;

$totalQuantity = 0;


if (!empty($_SESSION["cart"])) {

    $productIds = array_keys($_SESSION["cart"]);

    $productIds = array_map("intval", $productIds);

    $productIds = array_filter($productIds);


    if (!empty($productIds)) {

        $placeholders = implode(
            ",",
            array_fill(
                0,
                count($productIds),
                "?"
            )
        );


        $stmt = $pdo->prepare(
            "
            SELECT *
            FROM products
            WHERE id IN ($placeholders)
            "
        );


        $stmt->execute($productIds);


        $products = $stmt->fetchAll(
            PDO::FETCH_ASSOC
        );


        foreach ($products as $product) {

            $id = (int)$product["id"];


            $quantity = (int)(
                $_SESSION["cart"][$id]
                ??
                0
            );


            $stock = (int)(
                $product["stock"]
                ??
                0
            );


            if (
                $quantity <= 0
                ||
                $stock <= 0
            ) {

                continue;

            }


            if ($quantity > $stock) {

                $quantity = $stock;

                $_SESSION["cart"][$id] = $quantity;

            }


            /* =================================
                PRODUCT PRICE
            ================================= */

            if (
                isset($product["sale_price"])
                &&
                $product["sale_price"] !== null
                &&
                $product["sale_price"] !== ""
                &&
                (float)$product["sale_price"] > 0
            ) {

                $unitPrice = (float)$product["sale_price"];

            } else {

                $unitPrice = (float)$product["price"];

            }


            $itemTotal =
                $unitPrice * $quantity;


            $product["cart_quantity"] = $quantity;

            $product["unit_price"] = $unitPrice;

            $product["item_total"] = $itemTotal;


            $cartProducts[] = $product;


            $subtotal += $itemTotal;

            $totalQuantity += $quantity;

        }

    }

}


/* =========================================
   SHIPPING
========================================= */

$shipping = 0;


/* =========================================
   GRAND TOTAL
========================================= */

$grandTotal =
    $subtotal + $shipping;


/* =========================================
   VARIABLES
========================================= */

$errors = [];

$success = false;

$orderId = null;


/* =========================================
   CHECKOUT FORM
========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {


    /* =====================================
        CUSTOMER DATA
    ================================     */

    $fullName = trim(
        $_POST["full_name"] ?? ""
    );

    $email = trim(
        $_POST["email"] ?? ""
    );


    $phone = trim(
        $_POST["phone"] ?? ""
    );


    $address = trim(
        $_POST["address"] ?? ""
    );


    $city = trim(
        $_POST["city"] ?? ""
    );


    $governorate = trim(
        $_POST["governorate"] ?? ""
    );


    $zipCode = trim(
        $_POST["zip_code"] ?? ""
    );


    $paymentMethod = trim(
        $_POST["payment_method"] ?? ""
    );


    /* =====================================
        PAYMENT DATA
    ================================     */

    $cardNumber = trim(
        $_POST["card_number"] ?? ""
    );


    $cardExpiry = trim(
        $_POST["card_expiry"] ?? ""
    );


    $cardCvv = trim(
        $_POST["card_cvv"] ?? ""
    );


    $walletPhone = trim(
        $_POST["wallet_phone"] ?? ""
    );


    /* =====================================
        VALIDATION
    ================================     */

    if (empty($cartProducts)) {

        $errors[] =
            "السلة فارغة، لا يمكنك إتمام الطلب.";

    }


    if ($fullName === "") {

        $errors[] =
            "من فضلك أدخل الاسم بالكامل.";

    }

    if ($email === "") {

        $errors[] =
            "من فضلك أدخل البريد الإلكتروني.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] =
            "البريد الإلكتروني غير صحيح.";

    }


    if ($phone === "") {

        $errors[] =
            "من فضلك أدخل رقم الهاتف.";

    }

    elseif (
        !preg_match(
            '/^[0-9+\-\s]{8,20}$/',
            $phone
        )
    ) {

        $errors[] =
            "رقم الهاتف غير صحيح.";

    }


    if ($governorate === "") {

        $errors[] =
            "من فضلك اختر المحافظة.";

    }


    if ($city === "") {

        $errors[] =
            "من فضلك أدخل المدينة.";

    }


    if ($zipCode === "") {

        $errors[] =
            "من فضلك أدخل الرمز البريدي.";

    }


    if ($address === "") {

        $errors[] =
            "من فضلك أدخل العنوان.";

    }


    /* =====================================
        PAYMENT METHOD
    ================================     */

    $allowedPayments = [

        "cod",

        "bank",

        "wallet"

    ];


    if (
        !in_array(
            $paymentMethod,
            $allowedPayments,
            true
        )
    ) {

        $errors[] =
            "من فضلك اختر طريقة الدفع.";

    }


    /* =====================================
        BANK VALIDATION
    ================================     */

    if ($paymentMethod === "bank") {


        if ($cardNumber === "") {

            $errors[] =
                "من فضلك أدخل رقم البطاقة.";

        }

        elseif (
            !preg_match(
                '/^[0-9\s]{13,23}$/',
                $cardNumber
            )
        ) {

            $errors[] =
                "رقم البطاقة غير صحيح.";

        }


        if ($cardExpiry === "") {

            $errors[] =
                "من فضلك أدخل تاريخ انتهاء البطاقة.";

        }


        if ($cardCvv === "") {

            $errors[] =
                "من فضلك أدخل CVV.";

        }

        elseif (
            !preg_match(
                '/^[0-9]{3,4}$/',
                $cardCvv
            )
        ) {

            $errors[] =
                "CVV غير صحيح.";

        }

    }


    /* =====================================
        WALLET VALIDATION
    ================================     */

    if ($paymentMethod === "wallet") {


        if ($walletPhone === "") {

            $errors[] =
                "من فضلك أدخل رقم الهاتف المرتبط بالمحفظة.";

        }

        elseif (
            !preg_match(
                '/^[0-9+\-\s]{8,20}$/',
                $walletPhone
            )
        ) {

            $errors[] =
                "رقم المحفظة غير صحيح.";

        }

    }


    /* =====================================
        SAVE ORDER
    ================================     */

    if (empty($errors)) {

        try {


            /* =============================
               START TRANSACTION
            ============================= */

            $pdo->beginTransaction();


            /* =============================
               CHECK STOCK AGAIN
            ============================= */

            foreach (
                $cartProducts
                as $product
            ) {


                $stockCheck = $pdo->prepare(
                    "
                    SELECT stock
                    FROM products
                    WHERE id = ?
                    FOR UPDATE
                    "
                );


                $stockCheck->execute([

                    (int)$product["id"]

                ]);


                $currentProduct =
                    $stockCheck->fetch(
                        PDO::FETCH_ASSOC
                    );


                if (!$currentProduct) {

                    throw new Exception(
                        "أحد المنتجات لم يعد متوفرًا."
                    );

                }


                $currentStock =
                    (int)$currentProduct["stock"];


                $requestedQuantity =
                    (int)$product["cart_quantity"];


                if (
                    $currentStock
                    <
                    $requestedQuantity
                ) {

                    throw new Exception(
                        "الكمية المطلوبة من المنتج "
                        .
                        $product["name"]
                        .
                        " لم تعد متوفرة في المخزون."
                    );

                }

            }


            /* =============================
               INSERT ORDER
            ============================= */

            $stmt = $pdo->prepare(
                "
                INSERT INTO orders (

                    user_id,

                    full_name,

                    email,

                    phone,

                    governorate,

                    city,

                    address,

                    zip_code,

                    payment_method,

                    subtotal,

                    shipping,

                    total,

                    status

                )

                VALUES (

                    ?,

                    ?,

                    ?,

                    ?,

                    ?,

                    ?,

                    ?,

                    ?,

                    ?,

                    ?,

                    ?,

                    ?,

                    ?

                )
                "
            );

            // تحديد الـ user_id إن وجد، وإلا إرسال null للزائر
            $userId = isset($_SESSION["user_id"]) ? (int)$_SESSION["user_id"] : null;

            $stmt->execute([

                $userId,

                $fullName,

                $email,

                $phone,

                $governorate,

                $city,

                $address,

                $zipCode,

                $paymentMethod,

                $subtotal,

                $shipping,

                $grandTotal,

                "جديد"

            ]);


            /* =============================
               GET ORDER ID
            ============================= */

            $orderId =
                (int)$pdo->lastInsertId();


            /* =============================
               INSERT ORDER ITEMS
            ============================= */

            $itemStmt = $pdo->prepare(
                "
                INSERT INTO order_items (

                    order_id,

                    product_id,

                    product_name,

                    product_price,

                    quantity,

                    total

                )

                VALUES (

                    ?,

                    ?,

                    ?,

                    ?,

                    ?,

                    ?

                )
                "
            );


            /* =============================
               UPDATE STOCK
            ============================= */

            $stockStmt = $pdo->prepare(
                "
                UPDATE products

                SET stock = stock - ?

                WHERE id = ?
                "
            );


            /* =============================
               SAVE ITEMS
            ============================= */

            foreach (
                $cartProducts
                as $product
            ) {


                $itemStmt->execute([

                    $orderId,

                    (int)$product["id"],

                    $product["name"],

                    $product["unit_price"],

                    (int)$product["cart_quantity"],

                    $product["item_total"]

                ]);


                $stockStmt->execute([

                    (int)$product["cart_quantity"],

                    (int)$product["id"]

                ]);

            }


            /* =============================
               COMMIT
            ============================= */

            $pdo->commit();


            /* =============================
               CLEAR CART
            ============================= */

            $_SESSION["cart"] = [];


            /* =============================
               SAVE ORDER INFO
            ============================= */

            $_SESSION["last_order_id"] =
                $orderId;


            $_SESSION["last_order_total"] =
                $grandTotal;


            /* =============================
               SUCCESS
            ============================= */

            $success = true;


        }

        catch (Exception $e) {


            if ($pdo->inTransaction()) {

                $pdo->rollBack();

            }


            $errors[] =
                $e->getMessage();

        }

    }

}

?>

<!DOCTYPE html>

<html lang="ar" dir="rtl">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Velora - Checkout</title>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
>

<style>
/* التنسيقات العامة */
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

.checkout-header {
    background: #111;
    color: #fff;
    text-align: center;
    padding: 40px 15px;
}

.checkout-header p {
    color: #aaa;
    font-size: 11px;
    margin-bottom: 6px;
    text-transform: uppercase;
}

.checkout-header h1 {
    font-size: 28px;
}

.checkout-section {
    max-width: 1200px;
    margin: 20px auto;
    padding: 0 10px;
}

/* تخطيط الصفحة: الهواتف فوق بعضها، الشاشات الكبيرة جنب بعض */
.checkout-layout {
    display: flex;
    flex-direction: column-reverse; /* في الموبايل، نموذج البيانات يظهر أولاً وملخص الطلب تحته */
    gap: 20px;
}

@media (min-width: 900px) {
    .checkout-layout {
        display: grid;
        grid-template-columns: 1.6fr 1fr;
        flex-direction: row;
    }
}

.checkout-box,
.order-summary {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,.05);
}

.checkout-box h2,
.order-summary h2 {
    font-size: 18px;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

/* شبكة النماذج - اجبارها على النزول تحت بعض في الموبايل */
.form-grid {
    display: grid;
    grid-template-columns: 1fr; /* عمود واحد في الموبايل لمنع أي تداخل نهائياً */
    gap: 15px;
}

@media (min-width: 600px) {
    .form-grid {
        grid-template-columns: 1fr 1fr; /* عمودين في الشاشات الواسعة */
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
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #111;
}

/* ملخص الطلب */
.order-product {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.order-product-image {
    width: 50px;
    height: 60px;
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

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    color: #666;
    font-size: 13px;
}

.summary-total {
    border-top: 1px solid #eee;
    margin-top: 8px;
    padding-top: 12px;
    display: flex;
    justify-content: space-between;
    font-size: 16px;
    font-weight: bold;
    color: #111;
}

.place-order {
    width: 100%;
    border: none;
    background: #111;
    color: #fff;
    padding: 14px;
    border-radius: 6px;
    margin-top: 15px;
    cursor: pointer;
    font-size: 15px;
}
</style>
</head>

<body>

<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="logo">Velora</a>
        <ul class="nav-links">
            <li><a href="index.php">الرئيسية</a></li>
            <li><a href="index.php#products">المنتجات</a></li>
            <li><a href="cart.php">السلة</a></li>
        </ul>
        <div class="nav-actions">
            <a href="index.php"><i class="fa-solid fa-house"></i></a>
            <a href="cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
        </div>
    </div>
</nav>

<header class="checkout-header">
    <p>VELORA CHECKOUT</p>
    <h1>إتمام الطلب</h1>
</header>

<?php if ($success): ?>

<section class="checkout-section">
    <div class="success-box">
        <div class="success-icon"><i class="fa-solid fa-check"></i></div>
        <h2>تم تأكيد طلبك بنجاح 🎉</h2>
        <p>شكرًا لتسوقك من Velora. تم تسجيل طلبك بنجاح.</p>
        <div class="order-number">رقم الطلب: #<?= (int)$orderId ?></div>
        <p>الإجمالي: <strong><?= number_format($grandTotal, 2) ?> EGP</strong></p>
        <a href="index.php" class="success-btn">العودة للمتجر</a>
    </div>
</section>

<?php elseif (empty($cartProducts)): ?>

<section class="checkout-section">
    <div class="success-box">
        <div class="success-icon"><i class="fa-solid fa-cart-shopping"></i></div>
        <h2>السلة فارغة</h2>
        <p>أضف منتجًا إلى السلة أولًا ثم انتقل لإتمام الطلب.</p>
        <a href="index.php#products" class="success-btn">ابدأ التسوق</a>
    </div>
</section>

<?php else: ?>

<section class="checkout-section">
    <div class="checkout-layout">

        <div class="checkout-box">
            <h2>بيانات التوصيل</h2>

            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="checkout.php" id="checkoutForm">

                <div class="form-grid">

                    <div class="form-group full">
                        <label>الاسم بالكامل</label>
                        <input type="text" name="full_name" placeholder="اكتب اسمك بالكامل" value="<?= htmlspecialchars($_POST["full_name"] ?? "") ?>" required>
                    </div>

                    <div class="form-group full">
                        <label>البريد الإلكتروني</label>
                        <input type="email" name="email" placeholder="example@domain.com" value="<?= htmlspecialchars($_POST["email"] ?? "") ?>" required>
                    </div>

                    <div class="form-group">
                        <label>رقم الهاتف</label>
                        <input type="tel" name="phone" placeholder="01xxxxxxxxx" value="<?= htmlspecialchars($_POST["phone"] ?? "") ?>" required>
                    </div>

                    <div class="form-group">
                        <label>المحافظة</label>
                        <select name="governorate" required>
                            <option value="">اختر المحافظة</option>
                            <?php
                            $governorates = [
                                "القاهرة", "الجيزة", "الإسكندرية", "القليوبية", "الدقهلية", "الشرقية", "الغربية", "المنوفية", "البحيرة", "كفر الشيخ", "دمياط", "بورسعيد", "الإسماعيلية", "السويس", "الفيوم", "بني سويف", "المنيا", "أسيوط", "سوهاج", "قنا", "الأقصر", "أسوان", "البحر الأحمر", "الوادي الجديد", "مطروح", "شمال سيناء", "جنوب سيناء"
                            ];
                            foreach ($governorates as $gov):
                            ?>
                                <option value="<?= htmlspecialchars($gov) ?>" <?= (($_POST["governorate"] ?? "") === $gov) ? "selected" : "" ?>><?= htmlspecialchars($gov) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>المدينة</label>
                        <input type="text" name="city" placeholder="اكتب المدينة" value="<?= htmlspecialchars($_POST["city"] ?? "") ?>" required>
                    </div>

                    <div class="form-group">
                        <label>الرمز البريدي ZIP</label>
                        <input type="text" name="zip_code" placeholder="مثال: 11511" value="<?= htmlspecialchars($_POST["zip_code"] ?? "") ?>" required>
                    </div>

                    <div class="form-group full">
                        <label>العنوان بالتفصيل</label>
                        <textarea name="address" placeholder="اسم الشارع، رقم العمارة، الدور، الشقة..." required><?= htmlspecialchars($_POST["address"] ?? "") ?></textarea>
                    </div>

                </div>

                <h2 class="payment-title">طريقة الدفع</h2>

                <div class="payment-options">
                    <div class="payment-option">
                        <label>
                            <input type="radio" name="payment_method" value="cod" <?= (($_POST["payment_method"] ?? "cod") === "cod") ? "checked" : "" ?> required>
                            <i class="fa-solid fa-money-bill"></i>
                            الدفع عند الاستلام
                        </label>
                    </div>
                </div>

                <button type="submit" class="place-order">إتمام الطلب</button>

            </form>
        </div>

        <div class="order-summary">
            <h2>ملخص الطلب</h2>
            <?php foreach ($cartProducts as $p): ?>
                <div class="order-product">
                    <div class="order-product-info">
                        <h3><?= htmlspecialchars($p["name"]) ?></h3>
                        <p>الكمية: <?= $p["cart_quantity"] ?></p>
                    </div>
                    <div class="order-product-price"><?= number_format($p["item_total"], 2) ?> EGP</div>
                </div>
            <?php endforeach; ?>

            <div class="summary-row">
                <span>المجموع الفرعي</span>
                <span><?= number_format($subtotal, 2) ?> EGP</span>
            </div>
            <div class="summary-row">
                <span>الشحن</span>
                <span>مجاني</span>
            </div>
            <div class="summary-total">
                <span>الإجمالي الكلي</span>
                <span><?= number_format($grandTotal, 2) ?> EGP</span>
            </div>
        </div>

    </div>
</section>

<?php endif; ?>

<footer class="footer">
    <p>&copy; <?= date("Y") ?> Velora. جميع الحقوق محفوظة.</p>
</footer>

</body>
</html>
