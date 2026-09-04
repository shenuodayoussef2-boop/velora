<?php

session_start();

require_once "db.php";


/* =========================================
   CHECK LOGIN
========================================= */

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");

    exit;

}


$userId = (int) $_SESSION["user_id"];


/* =========================================
   GET USER ORDERS
========================================= */

$stmt = $pdo->prepare("
    SELECT
        id,
        full_name,
        governorate,
        city,
        payment_method,
        subtotal,
        shipping,
        total,
        status,
        created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY id DESC
");

$stmt->execute([
    $userId
]);

$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        Velora - طلباتي
    </title>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <style>

/* =========================================
   GLOBAL
========================================= */

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
}

a {
    text-decoration: none;
    color: inherit;
}


/* =========================================
   HEADER
========================================= */

.header {

    background: #fff;

    border-bottom: 1px solid #eee;

    padding: 22px 5%;
}

.header-container {

    max-width: 1200px;

    margin: auto;

    display: flex;

    align-items: center;

    justify-content: space-between;
}

.logo {

    font-size: 28px;

    font-weight: bold;
}

.back-store {

    display: flex;

    align-items: center;

    gap: 8px;

    color: #555;

    font-size: 14px;

    transition: .3s;
}

.back-store:hover {
    color: #111;
}


/* =========================================
   PAGE
========================================= */

.orders-page {

    width: 90%;

    max-width: 1100px;

    margin: 60px auto;
}

.page-title {

    margin-bottom: 30px;
}

.page-title p {

    color: #888;

    font-size: 12px;

    letter-spacing: 2px;

    margin-bottom: 8px;
}

.page-title h1 {

    font-size: 34px;
}


/* =========================================
   EMPTY
========================================= */

.empty-orders {

    background: #fff;

    padding: 70px 20px;

    text-align: center;

    border-radius: 12px;

    box-shadow:
        0 8px 30px rgba(0,0,0,.05);
}

.empty-orders i {

    font-size: 45px;

    color: #bbb;

    margin-bottom: 20px;
}

.empty-orders h2 {

    font-size: 22px;

    margin-bottom: 10px;
}

.empty-orders p {

    color: #888;

    font-size: 14px;

    margin-bottom: 25px;
}

.shop-btn {

    display: inline-block;

    background: #111;

    color: #fff;

    padding: 13px 25px;

    font-size: 13px;

    border-radius: 6px;
}


/* =========================================
   ORDER CARD
========================================= */

.order-card {

    background: #fff;

    border-radius: 12px;

    margin-bottom: 20px;

    padding: 25px;

    box-shadow:
        0 8px 30px rgba(0,0,0,.05);
}


/* =========================================
   ORDER TOP
========================================= */

.order-top {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding-bottom: 20px;

    border-bottom: 1px solid #eee;
}

.order-number {

    font-size: 18px;

    font-weight: bold;
}

.order-date {

    color: #888;

    font-size: 12px;

    margin-top: 7px;
}


/* =========================================
   STATUS
========================================= */

.status {

    padding: 7px 13px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: bold;
}

.status-pending {

    background: #fff4d6;

    color: #8a6500;
}

.status-processing {

    background: #e8f1ff;

    color: #1855a0;
}

.status-shipped {

    background: #eee8ff;

    color: #6336a0;
}

.status-completed {

    background: #e4f8e9;

    color: #197235;
}

.status-cancelled {

    background: #ffe7e7;

    color: #a00000;
}


/* =========================================
   ORDER DETAILS
========================================= */

.order-details {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 20px;

    padding: 22px 0;
}

.detail-box {

    background: #f8f8f8;

    padding: 15px;

    border-radius: 7px;
}

.detail-box span {

    display: block;

    color: #888;

    font-size: 11px;

    margin-bottom: 7px;
}

.detail-box strong {

    font-size: 14px;
}


/* =========================================
   TOTAL
========================================= */

.order-bottom {

    border-top: 1px solid #eee;

    padding-top: 20px;

    display: flex;

    align-items: center;

    justify-content: space-between;
}

.total-label {

    color: #888;

    font-size: 13px;
}

.total-price {

    font-size: 20px;

    font-weight: bold;
}


/* =========================================
   BACK ACCOUNT
========================================= */

.account-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    margin-top: 25px;

    color: #555;

    font-size: 13px;
}

.account-btn:hover {
    color: #111;
}


/* =========================================
   RESPONSIVE
========================================= */

@media (max-width: 700px) {

    .orders-page {
        margin: 35px auto;
    }

    .page-title h1 {
        font-size: 28px;
    }

    .order-top {
        align-items: flex-start;
        flex-direction: column;
    }

    .order-details {
        grid-template-columns: 1fr;
    }

    .order-bottom {
        flex-direction: column;

        align-items: flex-start;

        gap: 10px;
    }

}

    </style>

</head>

<body>


<!-- =========================================
     HEADER
========================================= -->

<header class="header">

    <div class="header-container">

        <a
            href="index.php"
            class="logo"
        >
            Velora
        </a>

        <a
            href="index.php"
            class="back-store"
        >

            <i class="fa-solid fa-arrow-right"></i>

            العودة للمتجر

        </a>

    </div>

</header>


<!-- =========================================
     ORDERS
========================================= -->

<main class="orders-page">


    <div class="page-title">

        <p>
            MY ORDERS
        </p>

        <h1>
            طلباتي
        </h1>

    </div>


    <?php if (empty($orders)): ?>


        <!-- EMPTY ORDERS -->

        <div class="empty-orders">

            <i class="fa-solid fa-box-open"></i>

            <h2>
                لا توجد طلبات حتى الآن
            </h2>

            <p>
                عندما تقوم بإتمام أول طلب لك سيظهر هنا.
            </p>

            <a
                href="index.php#products"
                class="shop-btn"
            >

                ابدأ التسوق

            </a>

        </div>


    <?php else: ?>


        <?php foreach ($orders as $order): ?>


            <div class="order-card">


                <!-- ORDER TOP -->

                <div class="order-top">


                    <div>

                        <div class="order-number">

                            طلب #<?= (int) $order["id"] ?>

                        </div>

                        <div class="order-date">

                            <?= htmlspecialchars(
                                date(
                                    "Y-m-d H:i",
                                    strtotime($order["created_at"])
                                )
                            ) ?>

                        </div>

                    </div>


                    <?php

                    $status =
                        strtolower(
                            trim(
                                $order["status"]
                            )
                        );

                    $statusClass =
                        "status-pending";

                    if (
                        in_array(
                            $status,
                            ["processing", "قيد التجهيز"]
                        )
                    ) {

                        $statusClass =
                            "status-processing";

                    }

                    elseif (
                        in_array(
                            $status,
                            ["shipped", "تم الشحن"]
                        )
                    ) {

                        $statusClass =
                            "status-shipped";

                    }

                    elseif (
                        in_array(
                            $status,
                            ["completed", "complete", "تم التسليم"]
                        )
                    ) {

                        $statusClass =
                            "status-completed";

                    }

                    elseif (
                        in_array(
                            $status,
                            ["cancelled", "canceled", "ملغي"]
                        )
                    ) {

                        $statusClass =
                            "status-cancelled";

                    }

                    ?>


                    <span
                        class="status <?= $statusClass ?>"
                    >

                        <?= htmlspecialchars(
                            $order["status"]
                        ) ?>

                    </span>


                </div>


                <!-- DETAILS -->

                <div class="order-details">


                    <div class="detail-box">

                        <span>
                            الاسم
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $order["full_name"]
                            ) ?>

                        </strong>

                    </div>


                    <div class="detail-box">

                        <span>
                            المحافظة
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $order["governorate"] ?? "-"
                            ) ?>

                        </strong>

                    </div>


                    <div class="detail-box">

                        <span>
                            المدينة
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $order["city"] ?? "-"
                            ) ?>

                        </strong>

                    </div>


                    <div class="detail-box">

                        <span>
                            طريقة الدفع
                        </span>

                        <strong>

                            <?= htmlspecialchars(
                                $order["payment_method"] ?? "-"
                            ) ?>

                        </strong>

                    </div>


                    <div class="detail-box">

                        <span>
                            المنتجات
                        </span>

                        <strong>

                            <?= number_format(
                                (float) $order["subtotal"],
                                2
                            ) ?>

                            EGP

                        </strong>

                    </div>


                    <div class="detail-box">

                        <span>
                            الشحن
                        </span>

                        <strong>

                            <?= number_format(
                                (float) $order["shipping"],
                                2
                            ) ?>

                            EGP

                        </strong>

                    </div>


                </div>


                <!-- TOTAL -->

                <div class="order-bottom">


                    <span class="total-label">

                        إجمالي الطلب

                    </span>


                    <strong class="total-price">

                        <?= number_format(
                            (float) $order["total"],
                            2
                        ) ?>

                        EGP

                    </strong>


                </div>


            </div>


        <?php endforeach; ?>


    <?php endif; ?>


    <a
        href="account.php"
        class="account-btn"
    >

        <i class="fa-solid fa-arrow-right"></i>

        العودة إلى حسابي

    </a>


</main>


</body>

</html>