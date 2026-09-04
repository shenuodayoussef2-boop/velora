<?php

session_start();

require_once "db.php";


/* =========================================
   INITIALIZE CART
========================================= */

if (!isset($_SESSION["cart"]) || !is_array($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}


/* =========================================
   ADD PRODUCT
========================================= */

if (isset($_GET["add"])) {

    $productId = (int) $_GET["add"];

    if ($productId > 0) {

        $stmt = $pdo->prepare("
            SELECT *
            FROM products
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->execute([$productId]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {

            $stock = (int)($product["stock"] ?? 0);

            if ($stock > 0) {

                $currentQuantity =
                    (int)($_SESSION["cart"][$productId] ?? 0);

                if ($currentQuantity < $stock) {

                    $_SESSION["cart"][$productId] =
                        $currentQuantity + 1;

                }
            }
        }
    }

    header("Location: cart.php");
    exit;
}


/* =========================================
   REMOVE PRODUCT
========================================= */

if (isset($_GET["remove"])) {

    $productId = (int)$_GET["remove"];

    if ($productId > 0) {

        unset($_SESSION["cart"][$productId]);

    }

    header("Location: cart.php");
    exit;
}


/* =========================================
   CLEAR CART
========================================= */

if (isset($_GET["clear"])) {

    $_SESSION["cart"] = [];

    header("Location: cart.php");
    exit;
}


/* =========================================
   UPDATE CART
========================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["update_cart"])
) {

    $quantities = $_POST["quantity"] ?? [];

    if (is_array($quantities)) {

        foreach ($quantities as $productId => $quantity) {

            $productId = (int)$productId;
            $quantity = (int)$quantity;

            if ($productId <= 0) {
                continue;
            }


            $stmt = $pdo->prepare("
                SELECT stock
                FROM products
                WHERE id = ?
                LIMIT 1
            ");

            $stmt->execute([$productId]);

            $product = $stmt->fetch(PDO::FETCH_ASSOC);


            if (!$product) {

                unset($_SESSION["cart"][$productId]);

                continue;
            }


            $stock = (int)$product["stock"];


            if ($quantity <= 0 || $stock <= 0) {

                unset($_SESSION["cart"][$productId]);

            } else {

                if ($quantity > $stock) {
                    $quantity = $stock;
                }

                $_SESSION["cart"][$productId] = $quantity;
            }

        }

    }

    header("Location: cart.php");
    exit;
}


/* =========================================
   GET CART PRODUCTS
========================================= */

$cartProducts = [];

$totalQuantity = 0;

$subtotal = 0;


if (!empty($_SESSION["cart"])) {

    $productIds = array_keys($_SESSION["cart"]);

    $productIds = array_map("intval", $productIds);

    $productIds = array_filter($productIds);


    if (!empty($productIds)) {

        $placeholders =
            implode(
                ",",
                array_fill(
                    0,
                    count($productIds),
                    "?"
                )
            );


        $stmt = $pdo->prepare("
            SELECT *
            FROM products
            WHERE id IN ($placeholders)
        ");

        $stmt->execute($productIds);

        $products =
            $stmt->fetchAll(PDO::FETCH_ASSOC);


        foreach ($products as $product) {

            $id = (int)$product["id"];


            $quantity =
                (int)(
                    $_SESSION["cart"][$id] ?? 0
                );


            if ($quantity <= 0) {
                continue;
            }


            $stock =
                (int)(
                    $product["stock"] ?? 0
                );


            if ($quantity > $stock) {

                $quantity = $stock;

                if ($quantity > 0) {

                    $_SESSION["cart"][$id] =
                        $quantity;

                } else {

                    unset($_SESSION["cart"][$id]);

                    continue;
                }
            }


            /* =====================================
               UNIT PRICE
            ===================================== */

            if (
                isset($product["sale_price"])
                &&
                $product["sale_price"] !== null
                &&
                $product["sale_price"] !== ""
                &&
                (float)$product["sale_price"] > 0
            ) {

                $unitPrice =
                    (float)$product["sale_price"];

            } else {

                $unitPrice =
                    (float)$product["price"];
            }


            /* =====================================
               ITEM TOTAL
            ===================================== */

            $itemTotal =
                $unitPrice * $quantity;


            $product["cart_quantity"] =
                $quantity;

            $product["unit_price"] =
                $unitPrice;

            $product["item_total"] =
                $itemTotal;


            $cartProducts[] =
                $product;


            $totalQuantity += $quantity;

            $subtotal += $itemTotal;
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

?>

<!DOCTYPE html>

<html lang="ar" dir="rtl">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Velora - Cart</title>

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

html {
    scroll-behavior: smooth;
}

body {
    font-family: Arial, sans-serif;
    background: #f7f7f7;
    color: #111;
}

a {
    text-decoration: none;
    color: inherit;
}

button,
input {
    font-family: inherit;
}


/* =========================================
   HEADER
========================================= */

.navbar {
    width: 100%;
    background: #fff;
    border-bottom: 1px solid #eee;
    padding: 22px 5%;
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

    gap: 30px;
}

.logo {
    font-size: 28px;
    font-weight: bold;
    letter-spacing: 1px;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 30px;
    list-style: none;
}

.nav-links a {
    font-size: 14px;
    transition: .3s;
}

.nav-links a:hover {
    color: #777;
}

.nav-actions {
    display: flex;
    align-items: center;
    gap: 20px;
}

.nav-actions a {
    font-size: 18px;
    position: relative;
}

.cart-badge {
    position: absolute;

    top: -10px;
    right: -10px;

    width: 19px;
    height: 19px;

    border-radius: 50%;

    background: #111;
    color: #fff;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 10px;
    font-weight: bold;
}


/* =========================================
   PAGE HEADER
========================================= */

.cart-header {
    background: #111;
    color: #fff;

    padding: 70px 20px;

    text-align: center;
}

.cart-header p {
    color: #aaa;
    font-size: 12px;
    letter-spacing: 3px;
    margin-bottom: 12px;
}

.cart-header h1 {
    font-size: 42px;
}


/* =========================================
   CART SECTION
========================================= */

.cart-section {
    max-width: 1200px;
    margin: 60px auto;
    padding: 0 20px;
}

.cart-layout {
    display: grid;

    grid-template-columns:
        2fr 1fr;

    gap: 30px;

    align-items: start;
}


/* =========================================
   CART ITEMS
========================================= */

.cart-items {
    background: #fff;

    border-radius: 12px;

    overflow: hidden;

    box-shadow:
        0 5px 25px rgba(0,0,0,.05);
}

.cart-items-header {
    padding: 22px;

    border-bottom: 1px solid #eee;

    display: flex;

    justify-content: space-between;

    align-items: center;
}

.cart-items-header h2 {
    font-size: 20px;
}

.clear-cart {
    color: #888;
    font-size: 13px;
}

.clear-cart:hover {
    color: #111;
}


/* =========================================
   CART ITEM
========================================= */

.cart-item {
    display: grid;

    grid-template-columns:
        100px 1fr auto;

    gap: 20px;

    padding: 22px;

    border-bottom: 1px solid #eee;

    align-items: center;
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image {
    width: 100px;
    height: 120px;

    background: #f4f4f4;

    overflow: hidden;

    border-radius: 8px;
}

.cart-item-image img {
    width: 100%;
    height: 100%;

    object-fit: cover;
}

.cart-item-info h3 {
    font-size: 17px;
    margin-bottom: 8px;
}

.cart-category {
    color: #999;
    font-size: 11px;

    margin-bottom: 10px;

    text-transform: uppercase;
}

.cart-price {
    font-size: 15px;
    font-weight: bold;
}

.old-price {
    color: #999;

    text-decoration: line-through;

    font-size: 12px;

    margin-right: 8px;

    font-weight: normal;
}


/* =========================================
   QUANTITY
========================================= */

.quantity-box {
    display: flex;

    align-items: center;

    margin-top: 15px;

    width: fit-content;

    border: 1px solid #ddd;

    border-radius: 6px;

    overflow: hidden;
}

.quantity-box button {
    width: 35px;
    height: 35px;

    border: none;

    background: #f7f7f7;

    cursor: pointer;

    font-size: 16px;
}

.quantity-box button:hover {
    background: #eee;
}

.quantity-box input {
    width: 45px;
    height: 35px;

    border: none;

    border-left: 1px solid #ddd;
    border-right: 1px solid #ddd;

    text-align: center;

    outline: none;
}

.quantity-box input::-webkit-inner-spin-button,
.quantity-box input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}


/* =========================================
   ITEM RIGHT
========================================= */

.cart-item-right {
    text-align: left;
    min-width: 120px;
}

.item-total {
    font-size: 17px;
    font-weight: bold;
    margin-bottom: 15px;
}

.remove-btn {
    color: #999;
    font-size: 13px;
}

.remove-btn:hover {
    color: #111;
}


/* =========================================
   UPDATE
========================================= */

.update-cart-wrapper {
    padding: 20px;

    border-top: 1px solid #eee;

    text-align: left;
}

.update-btn {
    background: #111;

    color: #fff;

    border: none;

    padding: 12px 22px;

    cursor: pointer;

    border-radius: 6px;

    font-size: 13px;
}

.update-btn:hover {
    background: #333;
}


/* =========================================
   SUMMARY
========================================= */

.cart-summary {
    background: #fff;

    padding: 25px;

    border-radius: 12px;

    box-shadow:
        0 5px 25px rgba(0,0,0,.05);

    position: sticky;

    top: 110px;
}

.cart-summary h2 {
    font-size: 21px;

    margin-bottom: 25px;

    padding-bottom: 18px;

    border-bottom: 1px solid #eee;
}

.summary-row {
    display: flex;

    justify-content: space-between;

    align-items: center;

    padding: 12px 0;

    color: #666;

    font-size: 14px;
}

.summary-row.total {
    border-top: 1px solid #eee;

    margin-top: 10px;

    padding-top: 20px;

    color: #111;

    font-size: 18px;

    font-weight: bold;
}

.checkout-btn {
    display: block;

    width: 100%;

    background: #111;

    color: #fff;

    padding: 15px;

    text-align: center;

    border-radius: 6px;

    margin-top: 20px;

    font-size: 14px;
}

.checkout-btn:hover {
    background: #333;
}

.continue-shopping {
    display: block;

    text-align: center;

    margin-top: 15px;

    color: #777;

    font-size: 13px;
}

.continue-shopping:hover {
    color: #111;
}


/* =========================================
   EMPTY CART
========================================= */

.empty-cart {
    max-width: 700px;

    margin: 80px auto;

    padding: 70px 30px;

    background: #fff;

    text-align: center;

    border-radius: 12px;

    box-shadow:
        0 5px 25px rgba(0,0,0,.05);
}

.empty-cart-icon {
    width: 80px;
    height: 80px;

    margin: 0 auto 25px;

    border-radius: 50%;

    background: #f4f4f4;

    display: flex;

    align-items: center;

    justify-content: center;
}

.empty-cart-icon i {
    font-size: 30px;
    color: #555;
}

.empty-cart h2 {
    font-size: 25px;
    margin-bottom: 12px;
}

.empty-cart p {
    color: #888;
    margin-bottom: 25px;
}

.shop-btn {
    display: inline-block;

    background: #111;

    color: #fff;

    padding: 14px 25px;

    border-radius: 6px;

    font-size: 13px;
}


/* =========================================
   FOOTER
========================================= */

.footer {
    background: #0b0b0b;

    color: #fff;

    margin-top: 80px;

    padding: 50px 20px;

    text-align: center;
}

.footer p {
    color: #777;
    font-size: 12px;
}


/* =========================================
   RESPONSIVE
========================================= */

@media (max-width: 850px) {

    .cart-layout {
        grid-template-columns: 1fr;
    }

    .cart-summary {
        position: static;
    }
}

@media (max-width: 650px) {

    .navbar-container {
        flex-direction: column;
        gap: 20px;
    }

    .nav-links {
        gap: 15px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .cart-item {
        grid-template-columns:
            80px 1fr;

        gap: 15px;
    }

    .cart-item-image {
        width: 80px;
        height: 100px;
    }

    .cart-item-right {
        grid-column: 2;

        text-align: right;

        display: flex;

        align-items: center;

        justify-content: space-between;

        min-width: 0;
    }

    .cart-header h1 {
        font-size: 32px;
    }
}

</style>

</head>

<body>


<!-- =========================================
     HEADER
========================================= -->

<nav class="navbar">

    <div class="navbar-container">

        <a href="index.php" class="logo">
            Velora
        </a>


        <ul class="nav-links">

            <li>
                <a href="index.php">
                    الرئيسية
                </a>
            </li>

            <li>
                <a href="index.php#products">
                    المنتجات
                </a>
            </li>

            <li>
                <a href="index.php#about">
                    من نحن
                </a>
            </li>

            <li>
                <a href="index.php#contact">
                    تواصل معنا
                </a>
            </li>

        </ul>


        <div class="nav-actions">

            <a href="login.php" title="الحساب">
                <i class="fa-solid fa-circle-user"></i>
            </a>


            <a href="cart.php" title="السلة">

                <i class="fa-solid fa-cart-shopping"></i>

                <?php if ($totalQuantity > 0): ?>

                    <span class="cart-badge">
                        <?= $totalQuantity ?>
                    </span>

                <?php endif; ?>

            </a>

        </div>

    </div>

</nav>


<!-- =========================================
     PAGE HEADER
========================================= -->

<header class="cart-header">

    <p>
        YOUR SHOPPING CART
    </p>

    <h1>
        سلة المشتريات
    </h1>

</header>


<?php if (empty($cartProducts)): ?>


<!-- =========================================
     EMPTY CART
========================================= -->

<section class="cart-section">

    <div class="empty-cart">

        <div class="empty-cart-icon">

            <i class="fa-solid fa-cart-shopping"></i>

        </div>

        <h2>
            السلة فارغة
        </h2>

        <p>
            لم تقم بإضافة أي منتجات إلى السلة بعد.
        </p>

        <a
            href="index.php#products"
            class="shop-btn">

            ابدأ التسوق

        </a>

    </div>

</section>


<?php else: ?>


<!-- =========================================
     CART
========================================= -->

<section class="cart-section">

    <div class="cart-layout">


        <!-- =================================
             ITEMS
        ================================== -->

        <div class="cart-items">


            <div class="cart-items-header">

                <h2>

                    سلة المشتريات

                    <span
                        style="
                            color:#888;
                            font-size:13px;
                            font-weight:normal;
                        "
                    >

                        (<span id="cart-count">
                            <?= $totalQuantity ?>
                        </span>)

                    </span>

                </h2>


                <a
                    href="cart.php?clear=1"
                    class="clear-cart"

                    onclick="
                        return confirm(
                            'هل أنت متأكد من تفريغ السلة؟'
                        );
                    "
                >

                    <i class="fa-solid fa-trash"></i>

                    تفريغ السلة

                </a>

            </div>


            <!-- =================================
                 UPDATE FORM
            ================================== -->

            <form
                method="POST"
                action="cart.php"
                id="cart-form"
            >


                <?php foreach ($cartProducts as $product): ?>

                    <?php

                    $id =
                        (int)$product["id"];

                    $unitPrice =
                        (float)$product["unit_price"];

                    $quantity =
                        (int)$product["cart_quantity"];

                    $itemTotal =
                        (float)$product["item_total"];

                    ?>


                    <div
                        class="cart-item"
                        data-price="<?= $unitPrice ?>"
                        data-id="<?= $id ?>"
                    >


                        <!-- IMAGE -->

                        <div class="cart-item-image">

                            <?php if (!empty($product["image"])): ?>

                                <img
                                    src="<?= htmlspecialchars(
                                        $product["image"]
                                    ) ?>"

                                    alt="<?= htmlspecialchars(
                                        $product["name"]
                                    ) ?>"
                                >

                            <?php else: ?>

                                <div
                                    style="
                                        width:100%;
                                        height:100%;
                                        display:flex;
                                        align-items:center;
                                        justify-content:center;
                                        color:#aaa;
                                    "
                                >

                                    <i
                                        class="fa-solid fa-image"
                                        style="font-size:30px;"
                                    ></i>

                                </div>

                            <?php endif; ?>

                        </div>


                        <!-- INFO -->

                        <div class="cart-item-info">

                            <p class="cart-category">

                                <?= htmlspecialchars(
                                    $product["category"] ?? ""
                                ) ?>

                            </p>


                            <h3>

                                <?= htmlspecialchars(
                                    $product["name"]
                                ) ?>

                            </h3>


                            <div class="cart-price">

                                <?= number_format(
                                    $unitPrice,
                                    2
                                ) ?>

                                EGP


                                <?php if (
                                    !empty($product["sale_price"])
                                    &&
                                    (float)$product["sale_price"] > 0
                                ): ?>

                                    <span class="old-price">

                                        <?= number_format(
                                            (float)$product["price"],
                                            2
                                        ) ?>

                                        EGP

                                    </span>

                                <?php endif; ?>

                            </div>


                            <!-- QUANTITY -->

                            <div class="quantity-box">

                                <button
                                    type="button"
                                    onclick="
                                        changeQuantity(
                                            <?= $id ?>,
                                            -1
                                        )
                                    "
                                >
                                    −
                                </button>


                                <input
                                    type="number"

                                    id="quantity-<?= $id ?>"

                                    name="quantity[<?= $id ?>]"

                                    value="<?= $quantity ?>"

                                    min="0"

                                    max="<?= (int)$product["stock"] ?>"

                                    oninput="
                                        quantityInputChanged(
                                            <?= $id ?>
                                        )
                                    "
                                >


                                <button
                                    type="button"
                                    onclick="
                                        changeQuantity(
                                            <?= $id ?>,
                                            1
                                        )
                                    "
                                >
                                    +
                                </button>

                            </div>

                        </div>


                        <!-- RIGHT -->

                        <div class="cart-item-right">

                            <div
                                class="item-total"
                                id="item-total-<?= $id ?>"
                                data-original="<?= $itemTotal ?>"
                            >

                                <?= number_format(
                                    $itemTotal,
                                    2
                                ) ?>

                                EGP

                            </div>


                            <a
                                href="cart.php?remove=<?= $id ?>"
                                class="remove-btn"
                            >

                                <i class="fa-solid fa-xmark"></i>

                                إزالة

                            </a>

                        </div>


                    </div>


                <?php endforeach; ?>


                <!-- UPDATE -->

                <div class="update-cart-wrapper">

                    <button
                        type="submit"
                        name="update_cart"
                        class="update-btn"
                    >

                        <i class="fa-solid fa-arrows-rotate"></i>

                        تحديث السلة

                    </button>

                </div>

            </form>

        </div>


        <!-- =================================
             SUMMARY
        ================================== -->

        <aside class="cart-summary">

            <h2>
                ملخص الطلب
            </h2>


            <div class="summary-row">

                <span>
                    المنتجات
                </span>

                <span id="subtotal">

                    <?= number_format(
                        $subtotal,
                        2
                    ) ?>

                    EGP

                </span>

            </div>


            <div class="summary-row">

                <span>
                    الشحن
                </span>

                <span>

                    <?php if ($shipping > 0): ?>

                        <?= number_format(
                            $shipping,
                            2
                        ) ?>

                        EGP

                    <?php else: ?>

                        مجاني

                    <?php endif; ?>

                </span>

            </div>


            <div class="summary-row total">

                <span>
                    الإجمالي
                </span>

                <span id="grand-total">

                    <?= number_format(
                        $grandTotal,
                        2
                    ) ?>

                    EGP

                </span>

            </div>


            <a
                href="checkout.php"
                class="checkout-btn"
            >

                إتمام الطلب

                <i class="fa-solid fa-arrow-left"></i>

            </a>


            <a
                href="index.php#products"
                class="continue-shopping"
            >

                <i class="fa-solid fa-arrow-right"></i>

                متابعة التسوق

            </a>

        </aside>

    </div>

</section>


<?php endif; ?>


<!-- =========================================
     FOOTER
========================================= -->

<footer class="footer">

    <p>
        © 2026 Velora. All Rights Reserved.
    </p>

</footer>


<script>

/* =========================================
   FORMAT PRICE
========================================= */

function formatPrice(price) {

    return Number(price).toLocaleString(
        "en-US",
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }
    ) + " EGP";

}


/* =========================================
   UPDATE CART TOTALS
========================================= */

function updateCartTotals() {

    const cartItems =
        document.querySelectorAll(".cart-item");

    let subtotal = 0;

    let totalQuantity = 0;


    cartItems.forEach(function(item) {

        const price =
            parseFloat(
                item.dataset.price
            ) || 0;


        const id =
            item.dataset.id;


        const input =
            document.getElementById(
                "quantity-" + id
            );


        if (!input) {
            return;
        }


        let quantity =
            parseInt(input.value) || 0;


        const max =
            parseInt(input.max) || 999;


        if (quantity < 0) {
            quantity = 0;
        }


        if (quantity > max) {
            quantity = max;
        }


        input.value = quantity;


        const itemTotal =
            price * quantity;


        subtotal += itemTotal;

        totalQuantity += quantity;


        const itemTotalElement =
            document.getElementById(
                "item-total-" + id
            );


        if (itemTotalElement) {

            itemTotalElement.textContent =
                formatPrice(itemTotal);

        }

    });


    const shipping =
        <?= (float)$shipping ?>;


    const grandTotal =
        subtotal + shipping;


    const subtotalElement =
        document.getElementById(
            "subtotal"
        );


    const grandTotalElement =
        document.getElementById(
            "grand-total"
        );


    const cartCountElement =
        document.getElementById(
            "cart-count"
        );


    if (subtotalElement) {

        subtotalElement.textContent =
            formatPrice(subtotal);

    }


    if (grandTotalElement) {

        grandTotalElement.textContent =
            formatPrice(grandTotal);

    }


    if (cartCountElement) {

        cartCountElement.textContent =
            totalQuantity;

    }

}


/* =========================================
   PLUS / MINUS
========================================= */

function changeQuantity(productId, change) {

    const input =
        document.getElementById(
            "quantity-" + productId
        );


    if (!input) {
        return;
    }


    let quantity =
        parseInt(input.value) || 0;


    const max =
        parseInt(input.max) || 999;


    quantity += change;


    if (quantity < 0) {
        quantity = 0;
    }


    if (quantity > max) {
        quantity = max;
    }


    input.value = quantity;


    updateCartTotals();

}


/* =========================================
   MANUAL INPUT
========================================= */

function quantityInputChanged(productId) {

    const input =
        document.getElementById(
            "quantity-" + productId
        );


    if (!input) {
        return;
    }


    let quantity =
        parseInt(input.value) || 0;


    const max =
        parseInt(input.max) || 999;


    if (quantity < 0) {
        quantity = 0;
    }


    if (quantity > max) {
        quantity = max;
    }


    input.value = quantity;


    updateCartTotals();

}


/* =========================================
   INITIAL CALCULATION
========================================= */

document.addEventListener(
    "DOMContentLoaded",
    function() {

        updateCartTotals();

    }
);

</script>


</body>

</html>