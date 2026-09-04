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


/* =========================================
   GET USER
========================================= */

$stmt = $pdo->prepare("
    SELECT
        id,
        name,
        email,
        provider,
        avatar,
        created_at
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([
    $_SESSION["user_id"]
]);

$user = $stmt->fetch();


/* =========================================
   USER NOT FOUND
========================================= */

if (!$user) {

    session_unset();
    session_destroy();

    header("Location: login.php");

    exit;

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
        Velora - حسابي
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

    font-size: 14px;

    color: #555;

    transition: .3s;
}

.back-store:hover {
    color: #111;
}


/* =========================================
   PAGE
========================================= */

.account-page {

    width: 90%;

    max-width: 1000px;

    margin: 60px auto;
}


/* =========================================
   WELCOME
========================================= */

.account-title {

    margin-bottom: 30px;
}

.account-title p {

    color: #888;

    font-size: 13px;

    margin-bottom: 8px;
}

.account-title h1 {

    font-size: 34px;
}


/* =========================================
   PROFILE
========================================= */

.profile-card {

    background: #fff;

    padding: 35px;

    border-radius: 12px;

    box-shadow:
        0 8px 30px rgba(0,0,0,.06);

    display: flex;

    align-items: center;

    gap: 25px;

    margin-bottom: 25px;
}

.avatar {

    width: 80px;

    height: 80px;

    border-radius: 50%;

    background: #111;

    color: #fff;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 30px;

    overflow: hidden;

    flex-shrink: 0;
}

.avatar img {

    width: 100%;

    height: 100%;

    object-fit: cover;
}

.profile-info h2 {

    font-size: 23px;

    margin-bottom: 8px;
}

.profile-info p {

    color: #777;

    font-size: 14px;

    margin-bottom: 8px;
}

.provider {

    display: inline-block;

    background: #f1f1f1;

    padding: 6px 10px;

    border-radius: 5px;

    font-size: 11px;

    color: #666;
}


/* =========================================
   ACTIONS
========================================= */

.actions {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 20px;
}

.action-card {

    background: #fff;

    padding: 30px 20px;

    border-radius: 10px;

    text-align: center;

    border: 1px solid #eee;

    transition: .3s;
}

.action-card:hover {

    transform: translateY(-4px);

    box-shadow:
        0 8px 25px rgba(0,0,0,.07);
}

.action-icon {

    width: 55px;

    height: 55px;

    border-radius: 50%;

    background: #f4f4f4;

    display: flex;

    align-items: center;

    justify-content: center;

    margin: 0 auto 15px;
}

.action-icon i {
    font-size: 20px;
}

.action-card h3 {

    font-size: 16px;

    margin-bottom: 8px;
}

.action-card p {

    color: #888;

    font-size: 12px;

    line-height: 1.6;
}


/* =========================================
   LOGOUT
========================================= */

.logout {

    margin-top: 30px;

    text-align: center;
}

.logout a {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    color: #b00000;

    border: 1px solid #e5caca;

    padding: 12px 22px;

    border-radius: 7px;

    font-size: 13px;

    transition: .3s;
}

.logout a:hover {

    background: #fff1f1;
}


/* =========================================
   RESPONSIVE
========================================= */

@media (max-width: 700px) {

    .account-page {

        margin: 35px auto;
    }

    .profile-card {

        flex-direction: column;

        text-align: center;

        padding: 30px 20px;
    }

    .actions {

        grid-template-columns: 1fr;
    }

    .account-title h1 {

        font-size: 28px;
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
     ACCOUNT
========================================= -->

<main class="account-page">


    <div class="account-title">

        <p>
            MY ACCOUNT
        </p>

        <h1>
            حسابي
        </h1>

    </div>


    <!-- PROFILE -->

    <div class="profile-card">


        <div class="avatar">

            <?php if (!empty($user["avatar"])): ?>

                <img
                    src="<?= htmlspecialchars($user["avatar"]) ?>"
                    alt="Profile"
                >

            <?php else: ?>

                <i class="fa-solid fa-user"></i>

            <?php endif; ?>

        </div>


        <div class="profile-info">

            <h2>

                <?= htmlspecialchars(
                    $user["name"]
                ) ?>

            </h2>

            <p>

                <?= htmlspecialchars(
                    $user["email"]
                ) ?>

            </p>


            <?php if ($user["provider"] === "google"): ?>

                <span class="provider">

                    <i class="fa-brands fa-google"></i>

                    تم التسجيل باستخدام Google

                </span>

            <?php elseif ($user["provider"] === "facebook"): ?>

                <span class="provider">

                    <i class="fa-brands fa-facebook"></i>

                    تم التسجيل باستخدام Facebook

                </span>

            <?php else: ?>

                <span class="provider">

                    <i class="fa-solid fa-envelope"></i>

                    حساب عادي

                </span>

            <?php endif; ?>

        </div>


    </div>


    <!-- ACTIONS -->

    <div class="actions">


        <!-- ORDERS -->

        <a
            href="orders.php"
            class="action-card"
        >

            <div class="action-icon">

                <i class="fa-solid fa-box"></i>

            </div>

            <h3>
                طلباتي
            </h3>

            <p>
                عرض ومتابعة جميع طلباتك
            </p>

        </a>


        <!-- CART -->

        <a
            href="cart.php"
            class="action-card"
        >

            <div class="action-icon">

                <i class="fa-solid fa-cart-shopping"></i>

            </div>

            <h3>
                السلة
            </h3>

            <p>
                عرض المنتجات الموجودة في السلة
            </p>

        </a>


        <!-- SETTINGS -->

        <a
            href="#"
            class="action-card"
        >

            <div class="action-icon">

                <i class="fa-solid fa-gear"></i>

            </div>

            <h3>
                إعدادات الحساب
            </h3>

            <p>
                تعديل بيانات حسابك
            </p>

        </a>


    </div>


    <!-- LOGOUT -->

    <div class="logout">

        <a href="logout.php">

            <i class="fa-solid fa-right-from-bracket"></i>

            تسجيل الخروج

        </a>

    </div>


</main>


</body>

</html>