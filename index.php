<?php

session_start();

require_once "db.php";

/* =========================================
   GET PRODUCTS FROM DATABASE
========================================= */

$stmt = $pdo->query("
    SELECT *
    FROM products
    ORDER BY featured DESC, id DESC
");

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================================
   CART COUNT
========================================= */

$cartCount = 0;

if (isset($_SESSION["cart"]) && is_array($_SESSION["cart"])) {

    foreach ($_SESSION["cart"] as $quantity) {

        $cartCount += (int) $quantity;

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

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <title>Velora</title>

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
    background: #fff;
    color: #111;
}

a {
    text-decoration: none;
    color: inherit;
}

img {
    max-width: 100%;
    display: block;
}

.container {
    width: 90%;
    max-width: 1200px;
    margin: auto;
}


/* =========================================
   HEADER
========================================= */

.nav-links {

    width: 100%;

    padding: 22px 0;

    background: #fff;

    border-bottom: 1px solid #eee;

    position: sticky;

    top: 0;

    z-index: 1000;
}

.nav-links .container {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 30px;
}

.nav-links ul {

    list-style: none;

    display: flex;

    align-items: center;

    gap: 35px;
}

.nav-links ul li a {

    font-size: 14px;

    color: #111;

    transition: .3s;
}

.nav-links ul li a:hover {

    color: #777;
}

.nav-icon {

    display: flex;

    align-items: center;

    gap: 20px;
}

.nav-icon > i,
.nav-icon a {

    cursor: pointer;

    font-size: 18px;

    color: #111;
}

.cart-icon {

    position: relative;

    display: flex;

    align-items: center;
}

.cart-count {

    position: absolute;

    top: -10px;

    right: -10px;

    width: 19px;

    height: 19px;

    background: #111;

    color: #fff;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 10px;

    font-weight: bold;
}


/* =========================================
   HERO
========================================= */

#home {

    min-height: 650px;

    display: flex;

    align-items: center;

    background:
        linear-gradient(
            90deg,
            rgba(255,255,255,.97),
            rgba(255,255,255,.65),
            rgba(255,255,255,.1)
        ),
        url("https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=1800&q=80");

    background-size: cover;

    background-position: center;
}

.home-content {

    width: 90%;

    max-width: 1200px;

    margin: auto;
}

.home-subtitle {

    font-size: 13px;

    letter-spacing: 4px;

    color: #777;

    display: block;

    margin-bottom: 20px;
}

.home-content h1 {

    font-size: clamp(45px, 7vw, 85px);

    line-height: 1.05;

    font-weight: 700;

    margin-bottom: 25px;
}

.home-content h1 span {

    color: #777;
}

.home-content p {

    max-width: 520px;

    color: #666;

    font-size: 16px;

    line-height: 1.9;

    margin-bottom: 30px;
}

.home-btn {

    display: inline-flex;

    align-items: center;

    gap: 15px;

    background: #111;

    color: #fff;

    padding: 15px 25px;

    font-size: 13px;

    transition: .3s;
}

.home-btn:hover {

    background: #333;
}


/* =========================================
   SECTION TITLE
========================================= */

.section-title {

    text-align: center;

    margin-bottom: 50px;
}

.section-title p {

    font-size: 12px;

    letter-spacing: 3px;

    color: #888;

    margin-bottom: 10px;
}

.section-title h2 {

    font-size: 36px;
}


/* =========================================
   PRODUCTS
========================================= */

.featured-products {

    padding: 100px 5%;
}

.products-container {

    max-width: 1200px;

    margin: auto;

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 25px;
}

.product-card {

    background: #fff;

    overflow: hidden;

    transition: .3s;
}

.product-card:hover {

    transform: translateY(-5px);
}

.product-image {

    height: 390px;

    background: #f4f4f4;

    position: relative;

    overflow: hidden;
}

.product-image img {

    width: 100%;

    height: 100%;

    object-fit: cover;

    transition: .5s;
}

.product-card:hover
.product-image img {

    transform: scale(1.04);
}

.product-badge {

    position: absolute;

    top: 15px;

    right: 15px;

    z-index: 2;

    background: #111;

    color: #fff;

    padding: 6px 10px;

    font-size: 10px;

    letter-spacing: 1px;
}

.product-badge.sale {

    background: #c00;
}

.product-info {

    padding: 18px 5px;
}

.product-category {

    font-size: 11px;

    color: #999;

    text-transform: uppercase;

    letter-spacing: 1px;

    margin-bottom: 8px;
}

.product-info h3 {

    font-size: 17px;

    margin-bottom: 10px;

    font-weight: 500;
}

.price {

    display: block;

    font-size: 16px;

    font-weight: bold;

    margin-bottom: 15px;
}

.add-cart {

    display: block;

    width: 100%;

    padding: 12px;

    background: #111;

    color: #fff;

    border: none;

    text-align: center;

    cursor: pointer;

    font-size: 13px;

    transition: .3s;
}

.add-cart:hover {

    background: #333;
}


/* =========================================
   EMPTY PRODUCTS
========================================= */

.empty-products {

    grid-column: 1 / -1;

    text-align: center;

    padding: 60px 20px;

    color: #888;
}


/* =========================================
   CATEGORIES
========================================= */

.categories {

    padding: 100px 5%;

    background: #f7f7f7;
}

.categories-container {

    max-width: 1200px;

    margin: auto;

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 20px;
}

.category-card {

    height: 350px;

    position: relative;

    overflow: hidden;

    background: #ddd;
}

.category-card img {

    width: 100%;

    height: 100%;

    object-fit: cover;

    transition: .5s;
}

.category-card:hover img {

    transform: scale(1.06);
}

.category-overlay {

    position: absolute;

    inset: 0;

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    background:
        rgba(0,0,0,.25);

    color: #fff;

    text-align: center;
}

.category-overlay h3 {

    font-size: 28px;

    margin-bottom: 12px;
}

.category-overlay a {

    border-bottom: 1px solid #fff;

    padding-bottom: 4px;

    font-size: 13px;
}


/* =========================================
   WHY VELORA
========================================= */

.why-velora {

    padding: 100px 5%;
}

.features-container {

    max-width: 1100px;

    margin: auto;

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 25px;
}

.feature-card {

    text-align: center;

    padding: 25px;
}

.feature-icon {

    width: 65px;

    height: 65px;

    border-radius: 50%;

    background: #f4f4f4;

    display: flex;

    align-items: center;

    justify-content: center;

    margin: 0 auto 20px;
}

.feature-icon i {

    font-size: 22px;
}

.feature-card h3 {

    margin-bottom: 12px;

    font-size: 18px;
}

.feature-card p {

    color: #777;

    font-size: 13px;

    line-height: 1.8;
}


/* =========================================
   NEWSLETTER
========================================= */

.newsletter {

    background: #111;

    color: #fff;

    padding: 90px 20px;

    text-align: center;
}

.newsletter-content {

    max-width: 700px;

    margin: auto;
}

.newsletter-subtitle {

    color: #aaa;

    letter-spacing: 3px;

    font-size: 11px;

    margin-bottom: 15px;
}

.newsletter h2 {

    font-size: 38px;

    margin-bottom: 20px;
}

.newsletter-text {

    color: #aaa;

    line-height: 1.8;

    margin-bottom: 30px;
}

.newsletter-form {

    display: flex;

    max-width: 550px;

    margin: auto;
}

.newsletter-form input {

    flex: 1;

    padding: 15px;

    border: none;

    outline: none;

    font-family: inherit;
}

.newsletter-form button {

    padding: 15px 25px;

    background: #fff;

    color: #111;

    border: none;

    cursor: pointer;

    font-weight: bold;
}


/* =========================================
   FOOTER
========================================= */

.footer {

    background: #0b0b0b;

    color: #fff;

    padding: 70px 5% 0;
}

.footer-container {

    max-width: 1200px;

    margin: auto;

    display: grid;

    grid-template-columns:
        2fr 1fr 1fr 1fr;

    gap: 50px;

    padding-bottom: 50px;
}

.footer-column h2 {

    font-size: 28px;

    margin-bottom: 15px;
}

.footer-column h3 {

    font-size: 16px;

    margin-bottom: 20px;
}

.footer-column p {

    color: #aaa;

    line-height: 1.8;

    max-width: 300px;

    font-size: 13px;
}

.footer-column ul {

    list-style: none;
}

.footer-column li {

    margin-bottom: 12px;
}

.footer-column li a {

    color: #aaa;

    font-size: 13px;

    transition: .3s;
}

.footer-column li a:hover {

    color: #fff;
}

.social-links {

    display: flex;

    gap: 12px;

    margin-top: 20px;
}

.social-links a {

    width: 35px;

    height: 35px;

    border: 1px solid #444;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;
}

.footer-bottom {

    max-width: 1200px;

    margin: auto;

    padding: 20px 0;

    border-top: 1px solid #222;

    display: flex;

    justify-content: space-between;

    align-items: center;

    color: #777;

    font-size: 12px;
}

.footer-payment {

    display: flex;

    align-items: center;

    gap: 12px;
}

.footer-payment i {

    font-size: 20px;
}


/* =========================================
   RESPONSIVE
========================================= */

@media (max-width: 1000px) {

    .products-container {

        grid-template-columns:
            repeat(2, 1fr);
    }

    .features-container {

        grid-template-columns:
            repeat(2, 1fr);
    }

    .categories-container {

        grid-template-columns:
            repeat(2, 1fr);
    }

    .footer-container {

        grid-template-columns:
            repeat(2, 1fr);
    }
}

@media (max-width: 700px) {

    .nav-links .container {

        flex-direction: column;

    }

    .nav-links ul {

        gap: 15px;

        flex-wrap: wrap;

        justify-content: center;
    }

    .products-container {

        grid-template-columns: 1fr;
    }

    .categories-container {

        grid-template-columns: 1fr;
    }

    .features-container {

        grid-template-columns: 1fr;
    }

    .footer-container {

        grid-template-columns: 1fr;
    }

    .footer-bottom {

        flex-direction: column;

        gap: 15px;

        text-align: center;
    }

    .newsletter-form {

        flex-direction: column;

        gap: 10px;
    }

    .newsletter-form button {

        width: 100%;
    }

    #home {

        min-height: 550px;

        background-position: 65% center;
    }

}
.user-menu {
    display: flex;
    align-items: center;
    gap: 15px;
}

.user-menu a {
    font-size: 18px;
    color: #111;
}
    </style>

</head>

<body>


<!-- =========================================
     HEADER
========================================= -->

<nav class="nav-links">

    <div class="container">

        <ul>

            <li>
                <a href="#home">
                    الرئيسية
                </a>
            </li>

            <li>
                <a href="#products">
                    المنتجات
                </a>
            </li>

            <li>
                <a href="#about">
                    من نحن
                </a>
            </li>

            <li>
                <a href="#contact">
                    تواصل معنا
                </a>
            </li>

        </ul>


        <div class="nav-icon">

            <i
                class="fa-solid fa-magnifying-glass"
                title="بحث">
            </i>


<?php if (isset($_SESSION["user_id"])): ?>

    <div class="user-menu">

    <a
    href="account.php"
    title="حسابي"
    >
    <i class="fa-solid fa-circle-user"></i>
    </a>

        <a
            href="logout.php"
            title="تسجيل الخروج"
        >

            <i class="fa-solid fa-right-from-bracket"></i>

        </a>

    </div>

        <?php else: ?>

        <a
        href="login.php"
        title="تسجيل الدخول"
    >

        <i class="fa-solid fa-circle-user"></i>

    </a>

            <?php endif; ?>


            <a
                href="cart.php"
                class="cart-icon"
                title="السلة">

                <i class="fa-solid fa-cart-shopping"></i>

                <?php if ($cartCount > 0): ?>

                    <span class="cart-count">
                        <?= $cartCount ?>
                    </span>

                <?php endif; ?>

            </a>

        </div>

    </div>

</nav>


<!-- =========================================
     HERO
========================================= -->

<section id="home">

    <div class="home-content">

        <span class="home-subtitle">
            WELCOME TO VELORA
        </span>

        <h1>

            اكتشف أسلوبك

            <br>

            <span>
                مع Velora
            </span>

        </h1>

        <p>

            اكتشف مجموعة مميزة من المنتجات
            المصممة بعناية لتناسب ذوقك
            وتكمل أسلوبك.

        </p>

        <a
            href="#products"
            class="home-btn">

            SEE COLLECTION

            <i class="fa-solid fa-arrow-left"></i>

        </a>

    </div>

</section>


<!-- =========================================
     PRODUCTS
========================================= -->

<section
    id="products"
    class="featured-products">

    <div class="section-title">

        <p>
            OUR PRODUCTS
        </p>

        <h2>
            Featured Collection
        </h2>

    </div>


    <div
        class="products-container">


        <?php if (empty($products)): ?>

            <div class="empty-products">

                <i class="fa-solid fa-box-open"></i>

                <h3>
                    لا توجد منتجات حاليًا
                </h3>

                <p>
                    سيتم إضافة المنتجات قريبًا.
                </p>

            </div>


        <?php else: ?>


            <?php foreach ($products as $product): ?>

                <div class="product-card">


                    <div class="product-image">


                        <?php if (!empty($product["sale_price"])): ?>

                            <span class="product-badge sale">
                                SALE
                            </span>

                        <?php elseif ((int)($product["featured"] ?? 0) === 1): ?>

                            <span class="product-badge">
                                FEATURED
                            </span>

                        <?php else: ?>

                            <span class="product-badge">
                                NEW
                            </span>

                        <?php endif; ?>


                        <?php if (!empty($product["image"])): ?>

                            <img
                                src="<?= htmlspecialchars($product["image"]) ?>"
                                alt="<?= htmlspecialchars($product["name"]) ?>"
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
                                    style="font-size:45px;">
                                </i>

                            </div>

                        <?php endif; ?>


                    </div>


                    <div class="product-info">


                        <p class="product-category">

                            <?= htmlspecialchars(
                                $product["category"] ?? ""
                            ) ?>

                        </p>


                        <h3>

                            <?= htmlspecialchars(
                                $product["name"]
                            ) ?>

                        </h3>


                        <?php if (!empty($product["sale_price"])): ?>

                            <span
                                class="price"
                                style="display:flex; gap:10px; align-items:center;">

                                <span>

                                    <?= number_format(
                                        (float)$product["sale_price"],
                                        2
                                    ) ?>

                                    EGP

                                </span>

                                <del
                                    style="
                                        color:#999;
                                        font-size:13px;
                                        font-weight:normal;
                                    ">

                                    <?= number_format(
                                        (float)$product["price"],
                                        2
                                    ) ?>

                                    EGP

                                </del>

                            </span>

                        <?php else: ?>

                            <span class="price">

                                <?= number_format(
                                    (float)$product["price"],
                                    2
                                ) ?>

                                EGP

                            </span>

                        <?php endif; ?>


                        <?php if ((int)$product["stock"] > 0): ?>

                            <a
                                href="cart.php?add=<?= (int)$product["id"] ?>"
                                class="add-cart">

                                Add to Cart

                            </a>

                        <?php else: ?>

                            <button
                                class="add-cart"
                                disabled
                                style="
                                    background:#ccc;
                                    cursor:not-allowed;
                                ">

                                Out of Stock

                            </button>

                        <?php endif; ?>


                    </div>

                </div>


            <?php endforeach; ?>


        <?php endif; ?>


    </div>

</section>


<!-- =========================================
     CATEGORIES
========================================= -->

<section class="categories">

    <div class="section-title">

        <p>
            EXPLORE VELORA
        </p>

        <h2>
            Shop by Category
        </h2>

    </div>


    <div class="categories-container">


        <div class="category-card">

            <img
                src="https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=900&q=80"
                alt="Clothing"
            >

            <div class="category-overlay">

                <h3>
                    Clothing
                </h3>

                <a href="#products">
                    Shop Now
                </a>

            </div>

        </div>


        <div class="category-card">

            <img
                src="https://images.unsplash.com/photo-1492707892479-7bc8d5a4ee93?auto=format&fit=crop&w=900&q=80"
                alt="Accessories"
            >

            <div class="category-overlay">

                <h3>
                    Accessories
                </h3>

                <a href="#products">
                    Shop Now
                </a>

            </div>

        </div>


        <div class="category-card">

            <img
                src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=900&q=80"
                alt="Shoes"
            >

            <div class="category-overlay">

                <h3>
                    Shoes
                </h3>

                <a href="#products">
                    Shop Now
                </a>

            </div>

        </div>


        <div class="category-card">

            <img
                src="https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&w=900&q=80"
                alt="Perfumes"
            >

            <div class="category-overlay">

                <h3>
                    Perfumes
                </h3>

                <a href="#products">
                    Shop Now
                </a>

            </div>

        </div>


        <div class="category-card">

            <img
                src="https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=900&q=80"
                alt="Beauty"
            >

            <div class="category-overlay">

                <h3>
                    Beauty
                </h3>

                <a href="#products">
                    Shop Now
                </a>

            </div>

        </div>


        <div class="category-card">

            <img
                src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=900&q=80"
                alt="Lifestyle"
            >

            <div class="category-overlay">

                <h3>
                    Lifestyle
                </h3>

                <a href="#products">
                    Shop Now
                </a>

            </div>

        </div>


    </div>

</section>


<!-- =========================================
     WHY VELORA
========================================= -->

<section
    class="why-velora"
    id="about">

    <div class="section-title">

        <p>
            WHY VELORA
        </p>

        <h2>
            Made for Your Lifestyle
        </h2>

    </div>


    <div class="features-container">


        <div class="feature-card">

            <div class="feature-icon">

                <i class="fa-solid fa-truck"></i>

            </div>

            <h3>
                Fast Delivery
            </h3>

            <p>
                Get your favorite products delivered
                quickly and safely.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">

                <i class="fa-solid fa-lock"></i>

            </div>

            <h3>
                Secure Payment
            </h3>

            <p>
                Your payment information is protected
                with secure checkout.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">

                <i class="fa-solid fa-rotate-left"></i>

            </div>

            <h3>
                Easy Returns
            </h3>

            <p>
                Shop with confidence with our simple
                return process.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">

                <i class="fa-solid fa-headset"></i>

            </div>

            <h3>
                24/7 Support
            </h3>

            <p>
                Our support team is always here
                to help you.
            </p>

        </div>


    </div>

</section>


<!-- =========================================
     NEWSLETTER
========================================= -->

<section class="newsletter">

    <div class="newsletter-content">

        <p class="newsletter-subtitle">
            STAY IN THE LOOP
        </p>

        <h2>
            Get 10% Off Your First Order
        </h2>

        <p class="newsletter-text">

            Subscribe to our newsletter and be the first
            to discover new collections and exclusive offers.

        </p>


        <form
            class="newsletter-form"
            method="POST"
            action="#">

            <input
                type="email"
                name="email"
                placeholder="Enter your email address"
                required
            >

            <button type="submit">
                Subscribe
            </button>

        </form>

    </div>

</section>


<!-- =========================================
     FOOTER
========================================= -->

<footer
    class="footer"
    id="contact">

    <div class="footer-container">


        <div class="footer-column footer-brand">

            <h2>
                Velora
            </h2>

            <p>

                Discover products made to match your style
                and everyday lifestyle.

            </p>


            <div class="social-links">

                <a href="#">
                    <i class="fa-brands fa-facebook-f"></i>
                </a>

                <a href="#">
                    <i class="fa-brands fa-instagram"></i>
                </a>

                <a href="#">
                    <i class="fa-brands fa-x-twitter"></i>
                </a>

            </div>

        </div>


        <div class="footer-column">

            <h3>
                Shop
            </h3>

            <ul>

                <li>
                    <a href="#products">
                        All Products
                    </a>
                </li>

                <li>
                    <a href="#products">
                        New Arrivals
                    </a>
                </li>

                <li>
                    <a href="#products">
                        Best Sellers
                    </a>
                </li>

                <li>
                    <a href="#products">
                        Sale
                    </a>
                </li>

            </ul>

        </div>


        <div class="footer-column">

            <h3>
                Categories
            </h3>

            <ul>

                <li>
                    <a href="#products">
                        Clothing
                    </a>
                </li>

                <li>
                    <a href="#products">
                        Accessories
                    </a>
                </li>

                <li>
                    <a href="#products">
                        Shoes
                    </a>
                </li>

                <li>
                    <a href="#products">
                        Perfumes
                    </a>
                </li>

                <li>
                    <a href="#products">
                        Beauty
                    </a>
                </li>

            </ul>

        </div>


        <div class="footer-column">

            <h3>
                Customer Care
            </h3>

            <ul>

                <li>
                    <a href="#">
                        Contact Us
                    </a>
                </li>

                <li>
                    <a href="#">
                        Shipping & Delivery
                    </a>
                </li>

                <li>
                    <a href="#">
                        Returns & Refunds
                    </a>
                </li>

                <li>
                    <a href="#">
                        FAQ
                    </a>
                </li>

            </ul>

        </div>


    </div>


    <div class="footer-bottom">

        <p>
            © 2026 Velora. All Rights Reserved.
        </p>


        <div class="footer-payment">

            <span>
                Secure Payment
            </span>

            <i class="fa-brands fa-cc-visa"></i>

            <i class="fa-brands fa-cc-mastercard"></i>

            <i class="fa-brands fa-cc-paypal"></i>

        </div>

    </div>

</footer>


</body>

</html>