<?php

session_start();

require_once "db.php";

$orderId = (int) ($_GET["order"] ?? 0);

$order = null;

if ($orderId > 0) {

    $stmt = $pdo->prepare("
        SELECT *
        FROM orders
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$orderId]);

    $order = $stmt->fetch(PDO::FETCH_ASSOC);
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

<title>
    Velora - Order Success
</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {

    font-family: Arial, sans-serif;

    background: #f7f7f7;

    color: #111;

    min-height: 100vh;

    display: flex;

    align-items: center;

    justify-content: center;

}

.success-box {

    width: 90%;

    max-width: 600px;

    background: #fff;

    padding: 60px 30px;

    text-align: center;

    border-radius: 15px;

    box-shadow:
        0 10px 40px rgba(0,0,0,.07);

}

.success-icon {

    width: 80px;

    height: 80px;

    margin: 0 auto 25px;

    border-radius: 50%;

    background: #111;

    color: #fff;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 35px;

}

.success-box h1 {

    font-size: 30px;

    margin-bottom: 15px;

}

.success-box p {

    color: #777;

    line-height: 1.8;

    margin-bottom: 10px;

}

.order-number {

    font-weight: bold;

    color: #111;

    margin: 20px 0;

}

.home-btn {

    display: inline-block;

    background: #111;

    color: #fff;

    text-decoration: none;

    padding: 14px 25px;

    margin-top: 15px;

    border-radius: 6px;

}

</style>

</head>

<body>

<div class="success-box">

    <div class="success-icon">
        ✓
    </div>

    <h1>
        تم تأكيد طلبك 🎉
    </h1>

    <p>
        شكرًا لتسوقك من Velora.
    </p>

    <?php if ($order): ?>

        <p class="order-number">

            رقم الطلب:
            #<?= (int)$order["id"] ?>

        </p>

        <p>

            الإجمالي:
            <?= number_format(
                (float)$order["total"],
                2
            ) ?>

            EGP

        </p>

    <?php endif; ?>

    <a
        href="index.php"
        class="home-btn"
    >

        العودة للمتجر

    </a>

</div>

</body>

</html>