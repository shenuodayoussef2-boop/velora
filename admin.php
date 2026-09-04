<?php

require_once "db.php";


/* =========================================================
   HELPERS
========================================================= */

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}


/* =========================================================
   PRODUCT ACTIONS
========================================================= */

/* DELETE PRODUCT */

if (isset($_GET["delete"])) {

    $id = (int)($_GET["delete"] ?? 0);

    if ($id > 0) {

        $stmt = $pdo->prepare("
            DELETE FROM products
            WHERE id = ?
        ");

        $stmt->execute([$id]);
    }

    header("Location: admin.php#products");
    exit;
}


/* ADD PRODUCT */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["add_product"])
) {

    $name = trim($_POST["name"] ?? "");

    $price = (float)($_POST["price"] ?? 0);

    $category = trim($_POST["category"] ?? "");

    $image = trim($_POST["image"] ?? "");

    $salePrice =
        isset($_POST["sale_price"])
        && $_POST["sale_price"] !== ""
            ? (float)$_POST["sale_price"]
            : null;

    $stock = (int)($_POST["stock"] ?? 0);

    $featured =
        isset($_POST["featured"])
            ? 1
            : 0;


    if (
        $name !== ""
        && $price >= 0
        && $stock >= 0
    ) {

        $stmt = $pdo->prepare("
            INSERT INTO products
            (
                name,
                price,
                category,
                image,
                sale_price,
                stock,
                featured
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $name,
            $price,
            $category,
            $image,
            $salePrice,
            $stock,
            $featured
        ]);
    }

    header("Location: admin.php#products");
    exit;
}


/* EDIT PRODUCT */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["edit_product"])
) {

    $id = (int)($_POST["id"] ?? 0);

    $name = trim($_POST["name"] ?? "");

    $price = (float)($_POST["price"] ?? 0);

    $category = trim($_POST["category"] ?? "");

    $image = trim($_POST["image"] ?? "");

    $salePrice =
        isset($_POST["sale_price"])
        && $_POST["sale_price"] !== ""
            ? (float)$_POST["sale_price"]
            : null;

    $stock = (int)($_POST["stock"] ?? 0);

    $featured =
        isset($_POST["featured"])
            ? 1
            : 0;


    if (
        $id > 0
        && $name !== ""
        && $price >= 0
        && $stock >= 0
    ) {

        $stmt = $pdo->prepare("
            UPDATE products

            SET
                name = ?,
                price = ?,
                category = ?,
                image = ?,
                sale_price = ?,
                stock = ?,
                featured = ?

            WHERE id = ?
        ");

        $stmt->execute([
            $name,
            $price,
            $category,
            $image,
            $salePrice,
            $stock,
            $featured,
            $id
        ]);
    }

    header("Location: admin.php#products");
    exit;
}


/* =========================================================
   ORDER STATUS
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["update_order_status"])
) {

    $orderId =
        (int)($_POST["order_id"] ?? 0);

    $status =
        trim($_POST["status"] ?? "Pending");


    $allowedStatuses = [

        "Pending",
        "Processing",
        "Shipped",
        "Delivered",
        "Cancelled"

    ];


    if (
        $orderId > 0
        && in_array(
            $status,
            $allowedStatuses,
            true
        )
    ) {

        $stmt = $pdo->prepare("
            UPDATE orders

            SET status = ?

            WHERE id = ?
        ");

        $stmt->execute([
            $status,
            $orderId
        ]);
    }

    header("Location: admin.php#orders");
    exit;
}


/* =========================================================
   GET PRODUCTS
========================================================= */

$stmt = $pdo->query("
    SELECT *
    FROM products
    ORDER BY id DESC
");

$products =
    $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );


/* =========================================================
   EDIT PRODUCT
========================================================= */

$editProduct = null;


if (isset($_GET["edit"])) {

    $id =
        (int)($_GET["edit"] ?? 0);


    if ($id > 0) {

        $stmt = $pdo->prepare("
            SELECT *
            FROM products
            WHERE id = ?
        ");

        $stmt->execute([
            $id
        ]);

        $editProduct =
            $stmt->fetch(
                PDO::FETCH_ASSOC
            );
    }
}


/* =========================================================
   GET ORDERS
========================================================= */

$stmt = $pdo->query("
    SELECT *
    FROM orders
    ORDER BY id DESC
");

$orders =
    $stmt->fetchAll(
        PDO::FETCH_ASSOC
    );


/* =========================================================
   PRODUCT STATS
========================================================= */

$totalProducts =
    count($products);

$totalStock = 0;

$featuredCount = 0;

$lowStockCount = 0;


foreach ($products as $product) {

    $stock =
        (int)($product["stock"] ?? 0);


    $totalStock += $stock;


    if (
        (int)($product["featured"] ?? 0)
        === 1
    ) {

        $featuredCount++;
    }


    if ($stock <= 5) {

        $lowStockCount++;
    }
}


/* =========================================================
   ORDER STATS
========================================================= */

$totalOrders =
    count($orders);

$pendingOrders = 0;

$processingOrders = 0;

$deliveredOrders = 0;

$cancelledOrders = 0;

$totalSales = 0;


foreach ($orders as $order) {

    $status =
        $order["status"] ?? "";


    if ($status === "Pending") {

        $pendingOrders++;
    }


    if ($status === "Processing") {

        $processingOrders++;
    }


    if ($status === "Delivered") {

        $deliveredOrders++;

        $totalSales +=
            (float)($order["total"] ?? 0);
    }


    if ($status === "Cancelled") {

        $cancelledOrders++;
    }
}

?>

<!DOCTYPE html>

<html
    lang="ar"
    dir="rtl"
>

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Velora Admin Dashboard
</title>


<link
    rel="preconnect"
    href="https://fonts.googleapis.com"
>

<link
    rel="preconnect"
    href="https://fonts.gstatic.com"
    crossorigin
>

<link
    href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap"
    rel="stylesheet"
>


<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
>


<style>

/* =========================================================
   RESET
========================================================= */

* {

    margin: 0;

    padding: 0;

    box-sizing: border-box;
}


html {

    scroll-behavior: smooth;
}


body {

    font-family: "Cairo", Arial, sans-serif;

    background: #f5f6f8;

    color: #151515;

    line-height: 1.6;
}


button,
input,
select {

    font-family: inherit;
}


a {

    color: inherit;

    text-decoration: none;
}


/* =========================================================
   LAYOUT
========================================================= */

.dashboard {

    min-height: 100vh;

    display: flex;
}


/* =========================================================
   SIDEBAR
========================================================= */

.sidebar {

    position: fixed;

    top: 0;

    right: 0;

    width: 270px;

    height: 100vh;

    background: #111;

    color: #fff;

    padding: 28px 18px;

    z-index: 1000;

    overflow-y: auto;
}


.brand {

    display: flex;

    align-items: center;

    gap: 12px;

    padding: 8px 12px 30px;

    margin-bottom: 15px;

    border-bottom: 1px solid rgba(255,255,255,.08);
}


.brand-mark {

    width: 42px;

    height: 42px;

    border-radius: 12px;

    background: #fff;

    color: #111;

    display: flex;

    align-items: center;

    justify-content: center;

    font-weight: 800;

    font-size: 17px;
}


.brand-text strong {

    display: block;

    font-size: 21px;

    letter-spacing: .5px;
}


.brand-text span {

    display: block;

    color: #777;

    font-size: 11px;

    margin-top: -3px;
}


.menu-title {

    color: #666;

    font-size: 11px;

    font-weight: 700;

    padding: 14px 13px 9px;
}


.sidebar-link {

    display: flex;

    align-items: center;

    gap: 13px;

    min-height: 48px;

    padding: 11px 13px;

    margin-bottom: 5px;

    border-radius: 10px;

    color: #aaa;

    transition: .25s;
}


.sidebar-link i {

    width: 20px;

    text-align: center;

    font-size: 15px;
}


.sidebar-link:hover {

    background: #1d1d1d;

    color: #fff;
}


.sidebar-link.active {

    background: #fff;

    color: #111;

    font-weight: 700;
}


.sidebar-link .badge {

    margin-right: auto;

    min-width: 22px;

    height: 22px;

    padding: 0 6px;

    border-radius: 20px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #fff;

    color: #111;

    font-size: 10px;

    font-weight: 800;
}


.sidebar-footer {

    margin-top: 30px;

    padding: 15px 13px;

    border-top: 1px solid rgba(255,255,255,.08);

    color: #666;

    font-size: 11px;
}


/* =========================================================
   MAIN
========================================================= */

.main {

    width: calc(100% - 270px);

    margin-right: 270px;

    padding: 30px 35px 50px;
}


/* =========================================================
   TOPBAR
========================================================= */

.topbar {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    margin-bottom: 28px;
}


.page-title small {

    display: block;

    color: #999;

    font-size: 12px;

    margin-bottom: 2px;
}


.page-title h1 {

    font-size: 28px;

    font-weight: 800;

    letter-spacing: -.5px;
}


.topbar-actions {

    display: flex;

    align-items: center;

    gap: 10px;
}


.store-btn {

    display: inline-flex;

    align-items: center;

    gap: 9px;

    padding: 11px 17px;

    background: #111;

    color: #fff;

    border-radius: 9px;

    font-size: 13px;

    font-weight: 600;

    transition: .25s;
}


.store-btn:hover {

    background: #292929;

    transform: translateY(-1px);
}


/* =========================================================
   STATS
========================================================= */

.stats-grid {

    display: grid;

    grid-template-columns:
        repeat(5, 1fr);

    gap: 16px;

    margin-bottom: 28px;
}


.stat-card {

    background: #fff;

    border: 1px solid #ececef;

    border-radius: 15px;

    padding: 20px;

    transition: .25s;
}


.stat-card:hover {

    transform: translateY(-2px);

    box-shadow:
        0 12px 30px rgba(0,0,0,.06);
}


.stat-top {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 15px;
}


.stat-icon {

    width: 42px;

    height: 42px;

    border-radius: 11px;

    background: #f2f2f3;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 16px;
}


.stat-label {

    color: #888;

    font-size: 12px;
}


.stat-value {

    font-size: 27px;

    font-weight: 800;

    line-height: 1.1;
}


.stat-note {

    color: #999;

    font-size: 10px;

    margin-top: 8px;
}


/* =========================================================
   SECTION CARD
========================================================= */

.card {

    background: #fff;

    border: 1px solid #ececef;

    border-radius: 15px;

    padding: 24px;

    margin-bottom: 24px;

    box-shadow:
        0 4px 18px rgba(0,0,0,.025);
}


.card-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    margin-bottom: 20px;
}


.card-title {

    display: flex;

    align-items: center;

    gap: 11px;
}


.card-title-icon {

    width: 38px;

    height: 38px;

    border-radius: 10px;

    background: #f3f3f4;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 14px;
}


.card-title h2 {

    font-size: 18px;

    font-weight: 800;
}


.card-title p {

    color: #999;

    font-size: 10px;

    margin-top: -2px;
}


/* =========================================================
   FORM
========================================================= */

.form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 16px;
}


.form-group {

    display: flex;

    flex-direction: column;

    gap: 7px;
}


.form-group label {

    font-size: 12px;

    font-weight: 700;

    color: #444;
}


.form-group input {

    width: 100%;

    height: 46px;

    padding: 0 13px;

    border: 1px solid #ddd;

    border-radius: 9px;

    outline: none;

    background: #fff;

    font-size: 13px;

    transition: .2s;
}


.form-group input:focus {

    border-color: #111;

    box-shadow:
        0 0 0 3px rgba(0,0,0,.05);
}


.full {

    grid-column: 1 / -1;
}


.checkbox {

    display: flex;

    align-items: center;

    gap: 9px;

    padding-top: 5px;
}


.checkbox input {

    width: 17px;

    height: 17px;

    accent-color: #111;
}


.checkbox label {

    font-size: 12px;

    font-weight: 600;

    cursor: pointer;
}


/* =========================================================
   BUTTONS
========================================================= */

.primary-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    border: 0;

    background: #111;

    color: #fff;

    min-height: 44px;

    padding: 0 19px;

    border-radius: 9px;

    cursor: pointer;

    font-size: 12px;

    font-weight: 700;

    transition: .25s;
}


.primary-btn:hover {

    background: #2b2b2b;

    transform: translateY(-1px);
}


.secondary-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    min-height: 44px;

    padding: 0 17px;

    background: #f1f1f2;

    color: #222;

    border-radius: 9px;

    font-size: 12px;

    font-weight: 700;
}


.secondary-btn:hover {

    background: #e6e6e7;
}


/* =========================================================
   TOOLBAR
========================================================= */

.toolbar {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    margin-bottom: 15px;
}


.toolbar-info {

    color: #888;

    font-size: 11px;
}


/* =========================================================
   TABLE
========================================================= */

.table-wrapper {

    width: 100%;

    overflow-x: auto;

    border: 1px solid #ededee;

    border-radius: 11px;
}


table {

    width: 100%;

    min-width: 950px;

    border-collapse: collapse;
}


th {

    background: #fafafa;

    color: #777;

    font-size: 10px;

    font-weight: 800;

    white-space: nowrap;

    padding: 14px 12px;

    border-bottom: 1px solid #eee;
}


td {

    padding: 13px 12px;

    border-bottom: 1px solid #f0f0f0;

    font-size: 11px;

    text-align: center;

    vertical-align: middle;
}


tbody tr {

    transition: .2s;
}


tbody tr:hover {

    background: #fafafa;
}


tbody tr:last-child td {

    border-bottom: 0;
}


/* =========================================================
   PRODUCT IMAGE
========================================================= */

.product-img {

    width: 48px;

    height: 55px;

    object-fit: cover;

    border-radius: 8px;

    background: #f0f0f0;
}


.no-image {

    width: 48px;

    height: 55px;

    border-radius: 8px;

    background: #f0f0f0;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    color: #aaa;
}


/* =========================================================
   ACTIONS
========================================================= */

.actions {

    display: flex;

    justify-content: center;

    gap: 5px;
}


.action-btn {

    width: 34px;

    height: 34px;

    border-radius: 8px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    background: #f1f1f2;

    color: #333;

    transition: .2s;
}


.action-btn:hover {

    background: #111;

    color: #fff;
}


/* =========================================================
   BADGES
========================================================= */

.badge {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding: 5px 9px;

    border-radius: 20px;

    font-size: 9px;

    font-weight: 800;

    white-space: nowrap;
}


.badge-featured {

    background: #f0f0f0;

    color: #222;
}


.badge-low {

    background: #ededed;

    color: #222;
}


.badge-normal {

    color: #777;

    background: #f7f7f7;
}


/* =========================================================
   ORDER STATUS
========================================================= */

.order-status {

    min-width: 105px;

    height: 36px;

    padding: 0 9px;

    border: 1px solid #ddd;

    border-radius: 8px;

    background: #fff;

    font-size: 10px;

    outline: none;

    cursor: pointer;
}


.order-status:focus {

    border-color: #111;
}


/* =========================================================
   EMPTY
========================================================= */

.empty {

    padding: 55px 20px !important;

    text-align: center;

    color: #aaa;
}


.empty i {

    display: block;

    font-size: 30px;

    margin-bottom: 10px;

    color: #ddd;
}


/* =========================================================
   EDIT HEADER
========================================================= */

.edit-card {

    border-top: 3px solid #111;
}


/* =========================================================
   QUICK OVERVIEW
========================================================= */

.overview-grid {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 14px;
}


.overview-item {

    background: #fafafa;

    border: 1px solid #eee;

    border-radius: 11px;

    padding: 15px;
}


.overview-item span {

    display: block;

    color: #999;

    font-size: 10px;

    margin-bottom: 5px;
}


.overview-item strong {

    font-size: 18px;

    font-weight: 800;
}


/* =========================================================
   MOBILE
========================================================= */

.mobile-menu {

    display: none;
}


@media (max-width: 1250px) {

    .stats-grid {

        grid-template-columns:
            repeat(3, 1fr);
    }

    .overview-grid {

        grid-template-columns:
            repeat(2, 1fr);
    }
}


@media (max-width: 900px) {

    .sidebar {

        width: 220px;
    }

    .main {

        width: calc(100% - 220px);

        margin-right: 220px;

        padding: 25px 20px;
    }

    .stats-grid {

        grid-template-columns:
            repeat(2, 1fr);
    }
}


@media (max-width: 700px) {

    .dashboard {

        display: block;
    }

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;

        padding: 15px;

        overflow: visible;
    }

    .brand {

        padding-bottom: 15px;

        margin-bottom: 10px;
    }

    .menu-title {

        display: none;
    }

    .sidebar-link {

        display: inline-flex;

        width: auto;

        margin-left: 4px;

        margin-bottom: 4px;
    }

    .sidebar-footer {

        display: none;
    }

    .main {

        width: 100%;

        margin-right: 0;

        padding: 20px 14px 40px;
    }

    .topbar {

        align-items: flex-start;

        flex-direction: column;
    }

    .page-title h1 {

        font-size: 23px;
    }

    .stats-grid {

        grid-template-columns: 1fr 1fr;

        gap: 10px;
    }

    .stat-card {

        padding: 15px;
    }

    .stat-value {

        font-size: 22px;
    }

    .form-grid {

        grid-template-columns: 1fr;
    }

    .full {

        grid-column: auto;
    }

    .card {

        padding: 17px;

        border-radius: 12px;
    }

    .card-header {

        align-items: flex-start;

        flex-direction: column;
    }

    .overview-grid {

        grid-template-columns: 1fr 1fr;
    }
}


@media (max-width: 450px) {

    .stats-grid {

        grid-template-columns: 1fr;
    }

    .overview-grid {

        grid-template-columns: 1fr;
    }

    .sidebar-link {

        padding: 9px 10px;

        font-size: 11px;
    }

    .sidebar-link i {

        width: auto;
    }
}

</style>

</head>


<body>


<div class="dashboard">


<!-- =======================================================
     SIDEBAR
======================================================= -->

<aside class="sidebar">


    <div class="brand">

        <div class="brand-mark">
            V
        </div>

        <div class="brand-text">

            <strong>
                Velora
            </strong>

            <span>
                ADMIN PANEL
            </span>

        </div>

    </div>


    <div class="menu-title">
        الرئيسية
    </div>


    <a
        href="admin.php"
        class="sidebar-link active"
    >

        <i class="fa-solid fa-grid-2"></i>

        لوحة التحكم

    </a>


    <a
        href="#products"
        class="sidebar-link"
    >

        <i class="fa-solid fa-box"></i>

        المنتجات

    </a>


    <a
        href="#orders"
        class="sidebar-link"
    >

        <i class="fa-solid fa-bag-shopping"></i>

        الطلبات

        <?php if ($pendingOrders > 0): ?>

            <span class="badge">

                <?= $pendingOrders ?>

            </span>

        <?php endif; ?>

    </a>


    <div class="menu-title">
        الإدارة
    </div>


    <a
        href="#customers"
        class="sidebar-link"
    >

        <i class="fa-solid fa-users"></i>

        العملاء

    </a>


    <a
        href="#statistics"
        class="sidebar-link"
    >

        <i class="fa-solid fa-chart-simple"></i>

        الإحصائيات

    </a>


    <div class="menu-title">
        المتجر
    </div>


    <a
        href="index.php"
        target="_blank"
        class="sidebar-link"
    >

        <i class="fa-solid fa-store"></i>

        عرض المتجر

    </a>


    <div class="sidebar-footer">

        Velora Admin

        <br>

        Control Center

    </div>


</aside>


<!-- =======================================================
     MAIN
======================================================= -->

<main class="main">


<!-- =======================================================
     TOPBAR
======================================================= -->

<header class="topbar">


    <div class="page-title">

        <small>
            مرحبًا بك في لوحة التحكم
        </small>

        <h1>
            لوحة التحكم
        </h1>

    </div>


    <div class="topbar-actions">

        <a
            href="index.php"
            target="_blank"
            class="store-btn"
        >

            <i class="fa-solid fa-arrow-up-right-from-square"></i>

            عرض المتجر

        </a>

    </div>


</header>


<!-- =======================================================
     STATS
======================================================= -->

<section class="stats-grid">


    <div class="stat-card">

        <div class="stat-top">

            <span class="stat-label">
                المنتجات
            </span>

            <div class="stat-icon">
                <i class="fa-solid fa-box"></i>
            </div>

        </div>

        <div class="stat-value">

            <?= $totalProducts ?>

        </div>

        <div class="stat-note">
            إجمالي المنتجات في المتجر
        </div>

    </div>


    <div class="stat-card">

        <div class="stat-top">

            <span class="stat-label">
                المخزون
            </span>

            <div class="stat-icon">
                <i class="fa-solid fa-cubes-stacked"></i>
            </div>

        </div>

        <div class="stat-value">

            <?= $totalStock ?>

        </div>

        <div class="stat-note">
            إجمالي الوحدات المتوفرة
        </div>

    </div>


    <div class="stat-card">

        <div class="stat-top">

            <span class="stat-label">
                الطلبات
            </span>

            <div class="stat-icon">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>

        </div>

        <div class="stat-value">

            <?= $totalOrders ?>

        </div>

        <div class="stat-note">
            إجمالي الطلبات المسجلة
        </div>

    </div>


    <div class="stat-card">

        <div class="stat-top">

            <span class="stat-label">
                طلبات جديدة
            </span>

            <div class="stat-icon">
                <i class="fa-solid fa-clock"></i>
            </div>

        </div>

        <div class="stat-value">

            <?= $pendingOrders ?>

        </div>

        <div class="stat-note">
            تحتاج إلى مراجعة
        </div>

    </div>


    <div class="stat-card">

        <div class="stat-top">

            <span class="stat-label">
                المبيعات
            </span>

            <div class="stat-icon">
                <i class="fa-solid fa-chart-line"></i>
            </div>

        </div>

        <div class="stat-value">

            <?= number_format(
                $totalSales,
                0
            ) ?>

        </div>

        <div class="stat-note">
            إجمالي الطلبات المكتملة EGP
        </div>

    </div>


</section>


<!-- =======================================================
     OVERVIEW
======================================================= -->

<section class="card">


    <div class="card-header">

        <div class="card-title">

            <div class="card-title-icon">

                <i class="fa-solid fa-chart-pie"></i>

            </div>

            <div>

                <h2>
                    نظرة سريعة
                </h2>

                <p>
                    ملخص حالة المتجر
                </p>

            </div>

        </div>

    </div>


    <div class="overview-grid">


        <div class="overview-item">

            <span>
                المنتجات المميزة
            </span>

            <strong>
                <?= $featuredCount ?>
            </strong>

        </div>


        <div class="overview-item">

            <span>
                مخزون منخفض
            </span>

            <strong>
                <?= $lowStockCount ?>
            </strong>

        </div>


        <div class="overview-item">

            <span>
                قيد المعالجة
            </span>

            <strong>
                <?= $processingOrders ?>
            </strong>

        </div>


        <div class="overview-item">

            <span>
                تم التسليم
            </span>

            <strong>
                <?= $deliveredOrders ?>
            </strong>

        </div>


    </div>


</section>


<!-- =======================================================
     EDIT PRODUCT
======================================================= -->

<?php if ($editProduct): ?>


<section class="card edit-card">


    <div class="card-header">

        <div class="card-title">

            <div class="card-title-icon">

                <i class="fa-solid fa-pen"></i>

            </div>

            <div>

                <h2>
                    تعديل المنتج
                </h2>

                <p>
                    تعديل بيانات المنتج الحالي
                </p>

            </div>

        </div>


        <a
            href="admin.php#products"
            class="secondary-btn"
        >

            <i class="fa-solid fa-xmark"></i>

            إلغاء

        </a>

    </div>


    <form
        method="POST"
    >


        <input
            type="hidden"
            name="id"
            value="<?= (int)$editProduct["id"] ?>"
        >


        <div class="form-grid">


            <div class="form-group">

                <label>
                    اسم المنتج
                </label>

                <input
                    type="text"
                    name="name"
                    value="<?= e(
                        $editProduct["name"] ?? ""
                    ) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    السعر
                </label>

                <input
                    type="number"
                    name="price"
                    step="0.01"
                    min="0"
                    value="<?= e(
                        $editProduct["price"] ?? ""
                    ) ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    القسم
                </label>

                <input
                    type="text"
                    name="category"
                    value="<?= e(
                        $editProduct["category"] ?? ""
                    ) ?>"
                >

            </div>


            <div class="form-group">

                <label>
                    المخزون
                </label>

                <input
                    type="number"
                    name="stock"
                    min="0"
                    value="<?= (int)(
                        $editProduct["stock"] ?? 0
                    ) ?>"
                >

            </div>


            <div class="form-group">

                <label>
                    سعر الخصم
                </label>

                <input
                    type="number"
                    name="sale_price"
                    step="0.01"
                    min="0"
                    value="<?= e(
                        $editProduct["sale_price"] ?? ""
                    ) ?>"
                >

            </div>


            <div class="form-group">

                <label>
                    رابط الصورة
                </label>

                <input
                    type="text"
                    name="image"
                    value="<?= e(
                        $editProduct["image"] ?? ""
                    ) ?>"
                >

            </div>


            <div class="checkbox full">

                <input
                    type="checkbox"
                    name="featured"
                    id="edit-featured"

                    <?= (
                        (int)(
                            $editProduct["featured"] ?? 0
                        ) === 1
                    )
                        ? "checked"
                        : ""
                    ?>
                >

                <label
                    for="edit-featured"
                >
                    عرض كمنتج مميز
                </label>

            </div>


        </div>


        <button
            type="submit"
            name="edit_product"
            class="primary-btn"
        >

            <i class="fa-solid fa-floppy-disk"></i>

            حفظ التعديلات

        </button>


    </form>


</section>


<?php endif; ?>


<!-- =======================================================
     ADD PRODUCT
======================================================= -->

<section
    class="card"
    id="products"
>


    <div class="card-header">

        <div class="card-title">

            <div class="card-title-icon">

                <i class="fa-solid fa-plus"></i>

            </div>

            <div>

                <h2>
                    إضافة منتج جديد
                </h2>

                <p>
                    أضف منتجًا جديدًا إلى المتجر
                </p>

            </div>

        </div>

    </div>


    <form
        method="POST"
    >


        <div class="form-grid">


            <div class="form-group">

                <label>
                    اسم المنتج
                </label>

                <input
                    type="text"
                    name="name"
                    placeholder="مثال: Classic T-Shirt"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    السعر
                </label>

                <input
                    type="number"
                    name="price"
                    step="0.01"
                    min="0"
                    placeholder="0.00"
                    required
                >

            </div>


            <div class="form-group">

                <label>
                    القسم
                </label>

                <input
                    type="text"
                    name="category"
                    placeholder="مثال: Fashion"
                >

            </div>


            <div class="form-group">

                <label>
                    المخزون
                </label>

                <input
                    type="number"
                    name="stock"
                    min="0"
                    value="0"
                >

            </div>


            <div class="form-group">

                <label>
                    سعر الخصم
                </label>

                <input
                    type="number"
                    name="sale_price"
                    step="0.01"
                    min="0"
                    placeholder="اختياري"
                >

            </div>


            <div class="form-group">

                <label>
                    رابط الصورة
                </label>

                <input
                    type="text"
                    name="image"
                    placeholder="image.jpg أو رابط الصورة"
                >

            </div>


            <div class="checkbox full">

                <input
                    type="checkbox"
                    name="featured"
                    id="featured"
                >

                <label
                    for="featured"
                >
                    جعل المنتج مميزًا
                </label>

            </div>


        </div>


        <button
            type="submit"
            name="add_product"
            class="primary-btn"
        >

            <i class="fa-solid fa-plus"></i>

            إضافة المنتج

        </button>


    </form>


</section>


<!-- =======================================================
     PRODUCTS TABLE
======================================================= -->

<section
    class="card"
>


    <div class="card-header">

        <div class="card-title">

            <div class="card-title-icon">

                <i class="fa-solid fa-boxes-stacked"></i>

            </div>

            <div>

                <h2>
                    جميع المنتجات
                </h2>

                <p>
                    إدارة المنتجات والمخزون
                </p>

            </div>

        </div>


        <span class="toolbar-info">

            <?= $totalProducts ?>
            منتج

        </span>

    </div>


    <div class="table-wrapper">

        <table>


            <thead>

                <tr>

                    <th>
                        ID
                    </th>

                    <th>
                        الصورة
                    </th>

                    <th>
                        المنتج
                    </th>

                    <th>
                        القسم
                    </th>

                    <th>
                        السعر
                    </th>

                    <th>
                        الخصم
                    </th>

                    <th>
                        المخزون
                    </th>

                    <th>
                        مميز
                    </th>

                    <th>
                        الإجراءات
                    </th>

                </tr>

            </thead>


            <tbody>


            <?php if (!empty($products)): ?>


                <?php foreach ($products as $product): ?>


                    <tr>


                        <td>

                            #<?= (int)$product["id"] ?>

                        </td>


                        <td>

                            <?php if (!empty(
                                $product["image"]
                            )): ?>

                                <img
                                    src="<?= e(
                                        $product["image"]
                                    ) ?>"
                                    class="product-img"
                                    alt="<?= e(
                                        $product["name"]
                                    ) ?>"
                                >

                            <?php else: ?>

                                <span class="no-image">

                                    <i class="fa-regular fa-image"></i>

                                </span>

                            <?php endif; ?>

                        </td>


                        <td>

                            <strong>

                                <?= e(
                                    $product["name"]
                                ) ?>

                            </strong>

                        </td>


                        <td>

                            <?= e(
                                $product["category"] ?? "—"
                            ) ?>

                        </td>


                        <td>

                            <?= number_format(
                                (float)$product["price"],
                                2
                            ) ?>

                            EGP

                        </td>


                        <td>

                            <?php if (
                                $product["sale_price"]
                                !== null
                                &&
                                $product["sale_price"]
                                !== ""
                                &&
                                (float)$product["sale_price"] > 0
                            ): ?>

                                <strong>

                                    <?= number_format(
                                        (float)$product["sale_price"],
                                        2
                                    ) ?>

                                    EGP

                                </strong>

                            <?php else: ?>

                                —

                            <?php endif; ?>

                        </td>


                        <td>

                            <?php

                            $productStock =
                                (int)(
                                    $product["stock"] ?? 0
                                );

                            ?>


                            <?php if (
                                $productStock <= 5
                            ): ?>

                                <span class="badge badge-low">

                                    <?= $productStock ?>

                                </span>

                            <?php else: ?>

                                <span class="badge badge-normal">

                                    <?= $productStock ?>

                                </span>

                            <?php endif; ?>

                        </td>


                        <td>

                            <?php if (
                                (int)(
                                    $product["featured"] ?? 0
                                ) === 1
                            ): ?>

                                <span
                                    class="badge badge-featured"
                                >

                                    مميز

                                </span>

                            <?php else: ?>

                                —

                            <?php endif; ?>

                        </td>


                        <td>

                            <div class="actions">


                                <a
                                    href="admin.php?edit=<?= (int)$product["id"] ?>#products"
                                    class="action-btn"
                                    title="تعديل"
                                >

                                    <i class="fa-solid fa-pen"></i>

                                </a>


                                <a
                                    href="admin.php?delete=<?= (int)$product["id"] ?>"
                                    class="action-btn"
                                    title="حذف"

                                    onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟');"
                                >

                                    <i class="fa-solid fa-trash"></i>

                                </a>


                            </div>

                        </td>


                    </tr>


                <?php endforeach; ?>


            <?php else: ?>


                <tr>

                    <td
                        colspan="9"
                        class="empty"
                    >

                        <i class="fa-solid fa-box-open"></i>

                        لا توجد منتجات حاليًا.

                    </td>

                </tr>


            <?php endif; ?>


            </tbody>


        </table>

    </div>


</section>


<!-- =======================================================
     ORDERS
======================================================= -->

<section
    class="card"
    id="orders"
>


    <div class="card-header">

        <div class="card-title">

            <div class="card-title-icon">

                <i class="fa-solid fa-bag-shopping"></i>

            </div>

            <div>

                <h2>
                    الطلبات
                </h2>

                <p>
                    متابعة الطلبات وتحديث حالتها
                </p>

            </div>

        </div>


        <span class="toolbar-info">

            <?= $totalOrders ?>
            طلب

        </span>

    </div>


    <div class="table-wrapper">

        <table>


            <thead>

                <tr>

                    <th>
                        الطلب
                    </th>

                    <th>
                        العميل
                    </th>

                    <th>
                        الهاتف
                    </th>

                    <th>
                        المحافظة
                    </th>

                    <th>
                        المدينة
                    </th>

                    <th>
                        العنوان
                    </th>

                    <th>
                        ZIP
                    </th>

                    <th>
                        المنتجات
                    </th>

                    <th>
                        الشحن
                    </th>

                    <th>
                        الإجمالي
                    </th>

                    <th>
                        الدفع
                    </th>

                    <th>
                        الحالة
                    </th>

                    <th>
                        التاريخ
                    </th>

                </tr>

            </thead>


            <tbody>


            <?php if (!empty($orders)): ?>


                <?php foreach ($orders as $order): ?>


                    <tr>


                        <td>

                            <strong>

                                #<?= (int)$order["id"] ?>

                            </strong>

                        </td>


                        <td>

                            <?= e(
                                $order["full_name"] ?? ""
                            ) ?>

                        </td>


                        <td>

                            <?= e(
                                $order["phone"] ?? ""
                            ) ?>

                        </td>


                        <td>

                            <?= e(
                                $order["governorate"] ?? ""
                            ) ?>

                        </td>


                        <td>

                            <?= e(
                                $order["city"] ?? ""
                            ) ?>

                        </td>


                        <td>

                            <?= e(
                                $order["address"] ?? ""
                            ) ?>

                        </td>


                        <td>

                            <?= e(
                                $order["zip_code"] ?? ""
                            ) ?>

                        </td>


                        <td>

                            <?= number_format(
                                (float)(
                                    $order["subtotal"] ?? 0
                                ),
                                2
                            ) ?>

                            EGP

                        </td>


                        <td>

                            <?= number_format(
                                (float)(
                                    $order["shipping"] ?? 0
                                ),
                                2
                            ) ?>

                            EGP

                        </td>


                        <td>

                            <strong>

                                <?= number_format(
                                    (float)(
                                        $order["total"] ?? 0
                                    ),
                                    2
                                ) ?>

                                EGP

                            </strong>

                        </td>


                        <td>

                            <?php

                            $payment =
                                $order["payment_method"]
                                ?? "";

                            ?>


                            <?php

                            if ($payment === "cod") {

                                echo "استلام";

                            }

                            elseif ($payment === "bank") {

                                echo "بطاقة";

                            }

                            elseif ($payment === "wallet") {

                                echo "محفظة";

                            }

                            else {

                                echo e($payment);

                            }

                            ?>

                        </td>


                        <td>


                            <form
                                method="POST"
                            >


                                <input
                                    type="hidden"
                                    name="order_id"
                                    value="<?= (int)$order["id"] ?>"
                                >


                                <input
                                    type="hidden"
                                    name="update_order_status"
                                    value="1"
                                >


                                <select
                                    name="status"
                                    class="order-status"
                                    onchange="this.form.submit()"
                                >


                                    <option
                                        value="Pending"

                                        <?= (
                                            ($order["status"] ?? "")
                                            === "Pending"
                                        )
                                            ? "selected"
                                            : ""
                                        ?>
                                    >

                                        جديد

                                    </option>


                                    <option
                                        value="Processing"

                                        <?= (
                                            ($order["status"] ?? "")
                                            === "Processing"
                                        )
                                            ? "selected"
                                            : ""
                                        ?>
                                    >

                                        قيد التجهيز

                                    </option>


                                    <option
                                        value="Shipped"

                                        <?= (
                                            ($order["status"] ?? "")
                                            === "Shipped"
                                        )
                                            ? "selected"
                                            : ""
                                        ?>
                                    >

                                        تم الشحن

                                    </option>


                                    <option
                                        value="Delivered"

                                        <?= (
                                            ($order["status"] ?? "")
                                            === "Delivered"
                                        )
                                            ? "selected"
                                            : ""
                                        ?>
                                    >

                                        تم التسليم

                                    </option>


                                    <option
                                        value="Cancelled"

                                        <?= (
                                            ($order["status"] ?? "")
                                            === "Cancelled"
                                        )
                                            ? "selected"
                                            : ""
                                        ?>
                                    >

                                        ملغي

                                    </option>


                                </select>


                            </form>


                        </td>


                        <td>

                            <?= e(
                                $order["created_at"] ?? ""
                            ) ?>

                        </td>


                    </tr>


                <?php endforeach; ?>


            <?php else: ?>


                <tr>

                    <td
                        colspan="13"
                        class="empty"
                    >

                        <i class="fa-solid fa-bag-shopping"></i>

                        لا توجد طلبات حاليًا.

                    </td>

                </tr>


            <?php endif; ?>


            </tbody>


        </table>

    </div>


</section>


<!-- =======================================================
     CUSTOMERS
======================================================= -->

<section
    class="card"
    id="customers"
>


    <div class="card-header">

        <div class="card-title">

            <div class="card-title-icon">

                <i class="fa-solid fa-users"></i>

            </div>

            <div>

                <h2>
                    العملاء
                </h2>

                <p>
                    إدارة العملاء سيتم تطويرها لاحقًا
                </p>

            </div>

        </div>

    </div>


    <div class="empty">

        <i class="fa-solid fa-users"></i>

        قسم العملاء جاهز للتطوير.

    </div>


</section>


<!-- =======================================================
     STATISTICS
======================================================= -->

<section
    class="card"
    id="statistics"
>


    <div class="card-header">

        <div class="card-title">

            <div class="card-title-icon">

                <i class="fa-solid fa-chart-line"></i>

            </div>

            <div>

                <h2>
                    الإحصائيات
                </h2>

                <p>
                    ملخص أداء المتجر
                </p>

            </div>

        </div>

    </div>


    <div class="overview-grid">


        <div class="overview-item">

            <span>
                إجمالي الطلبات
            </span>

            <strong>
                <?= $totalOrders ?>
            </strong>

        </div>


        <div class="overview-item">

            <span>
                الطلبات الجديدة
            </span>

            <strong>
                <?= $pendingOrders ?>
            </strong>

        </div>


        <div class="overview-item">

            <span>
                الطلبات المكتملة
            </span>

            <strong>
                <?= $deliveredOrders ?>
            </strong>

        </div>


        <div class="overview-item">

            <span>
                الطلبات الملغاة
            </span>

            <strong>
                <?= $cancelledOrders ?>
            </strong>

        </div>


    </div>


</section>


</main>


</div>


<script>

/* =========================================================
   ACTIVE SIDEBAR LINK
========================================================= */

const sidebarLinks =
    document.querySelectorAll(
        ".sidebar-link"
    );


sidebarLinks.forEach(
    function(link) {

        link.addEventListener(
            "click",
            function() {

                sidebarLinks.forEach(
                    function(item) {

                        item.classList.remove(
                            "active"
                        );

                    }
                );


                if (
                    !this.getAttribute(
                        "target"
                    )
                ) {

                    this.classList.add(
                        "active"
                    );

                }

            }
        );

    }
);


/* =========================================================
   CONFIRM PRODUCT DELETE
========================================================= */

document.querySelectorAll(
    'a[href*="delete="]'
).forEach(
    function(button) {

        button.addEventListener(
            "click",
            function(event) {

                const confirmed =
                    confirm(
                        "هل أنت متأكد من حذف هذا المنتج؟"
                    );


                if (!confirmed) {

                    event.preventDefault();

                }

            }
        );

    }
);

</script>


</body>

</html>