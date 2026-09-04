<?php

session_start();

require_once "db.php";


/* =========================================
   IF USER IS ALREADY LOGGED IN
========================================= */

if (isset($_SESSION["user_id"])) {

    header("Location: index.php");

    exit;

}


/* =========================================
   VARIABLES
========================================= */

$error = "";

$success = "";

$name = "";

$email = "";


/* =========================================
   REGISTER
========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /* =====================================
       GET DATA
    ===================================== */

    $name =
        trim(
            $_POST["name"] ?? ""
        );


    $email =
        trim(
            $_POST["email"] ?? ""
        );


    $password =
        $_POST["password"] ?? "";


    $confirmPassword =
        $_POST["confirm_password"] ?? "";


    /* =====================================
       VALIDATION
    ===================================== */

    if ($name === "") {

        $error =
            "من فضلك أدخل الاسم.";

    }

    elseif ($email === "") {

        $error =
            "من فضلك أدخل البريد الإلكتروني.";

    }

    elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $error =
            "البريد الإلكتروني غير صحيح.";

    }

    elseif ($password === "") {

        $error =
            "من فضلك أدخل كلمة السر.";

    }

    elseif (strlen($password) < 6) {

        $error =
            "كلمة السر يجب أن تكون 6 أحرف على الأقل.";

    }

    elseif ($password !== $confirmPassword) {

        $error =
            "كلمتا السر غير متطابقتين.";

    }


    /* =====================================
       CHECK EMAIL
    ===================================== */

    if ($error === "") {

        $checkUser =
            $pdo->prepare(
                "
                SELECT id
                FROM users
                WHERE email = ?
                LIMIT 1
                "
            );


        $checkUser->execute([

            $email

        ]);


        if ($checkUser->fetch()) {

            $error =
                "هذا البريد الإلكتروني مستخدم بالفعل.";

        }

    }


    /* =====================================
       CREATE ACCOUNT
    ===================================== */

    if ($error === "") {

        $hashedPassword =
            password_hash(
                $password,
                PASSWORD_DEFAULT
            );


        $stmt =
            $pdo->prepare(
                "
                INSERT INTO users (

                    name,

                    email,

                    password,

                    provider

                )

                VALUES (

                    ?,

                    ?,

                    ?,

                    'local'

                )
                "
            );


        $stmt->execute([

            $name,

            $email,

            $hashedPassword

        ]);


        /* =================================
           AUTO LOGIN
        ================================= */

        $userId =
            (int)$pdo->lastInsertId();


        $_SESSION["user_id"] =
            $userId;


        $_SESSION["user_name"] =
            $name;


        $_SESSION["user_email"] =
            $email;


        header(
            "Location: index.php"
        );

        exit;

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

<title>Velora - إنشاء حساب</title>

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
    background: #f5f5f5;
    color: #111;
    min-height: 100vh;
}

a {
    text-decoration: none;
    color: inherit;
}


/* =========================================
   PAGE
========================================= */

.auth-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px 20px;
}


/* =========================================
   BOX
========================================= */

.auth-box {
    width: 100%;
    max-width: 460px;
    background: #fff;
    padding: 45px;
    border-radius: 14px;
    box-shadow: 0 10px 40px rgba(0,0,0,.08);
}

.logo {
    display: block;
    text-align: center;
    font-size: 32px;
    font-weight: bold;
    letter-spacing: 1px;
    margin-bottom: 30px;
}

.auth-header {
    text-align: center;
    margin-bottom: 30px;
}

.auth-header h1 {
    font-size: 27px;
    margin-bottom: 10px;
}

.auth-header p {
    color: #777;
    font-size: 14px;
}


/* =========================================
   ERROR
========================================= */

.error-message {
    background: #fff1f1;
    border: 1px solid #f0caca;
    color: #b00000;
    padding: 13px;
    border-radius: 7px;
    margin-bottom: 20px;
    font-size: 13px;
    text-align: center;
}


/* =========================================
   FORM
========================================= */

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: bold;
}

.input-wrapper {
    position: relative;
}

.input-wrapper i {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #888;
}

.form-group input {
    width: 100%;
    padding: 14px 45px 14px 14px;
    border: 1px solid #ddd;
    border-radius: 7px;
    outline: none;
    font-size: 14px;
    transition: .3s;
}

.form-group input:focus {
    border-color: #111;
}


/* =========================================
   BUTTON
========================================= */

.register-btn {
    width: 100%;
    border: none;
    background: #111;
    color: #fff;
    padding: 15px;
    border-radius: 7px;
    cursor: pointer;
    font-size: 14px;
    transition: .3s;
}

.register-btn:hover {
    background: #333;
}


/* =========================================
   LOGIN LINK
========================================= */

.login-link {
    text-align: center;
    margin-top: 25px;
    color: #777;
    font-size: 14px;
}

.login-link a {
    color: #111;
    font-weight: bold;
}

.login-link a:hover {
    text-decoration: underline;
}

.back-home {
    display: block;
    text-align: center;
    margin-top: 22px;
    color: #888;
    font-size: 13px;
}

.back-home:hover {
    color: #111;
}


/* =========================================
   MOBILE
========================================= */

@media (max-width: 500px) {

    .auth-box {
        padding: 35px 22px;
    }

}

</style>

</head>

<body>

<main class="auth-page">

<div class="auth-box">


    <!-- LOGO -->

    <a
        href="index.php"
        class="logo"
    >
        Velora
    </a>


    <!-- HEADER -->

    <div class="auth-header">

        <h1>
            إنشاء حساب
        </h1>

        <p>
            أنشئ حسابك وابدأ التسوق
        </p>

    </div>


    <!-- ERROR -->

    <?php if ($error !== ""): ?>

        <div class="error-message">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- FORM -->

    <form
        method="POST"
        action="register.php"
    >


        <!-- NAME -->

        <div class="form-group">

            <label>
                الاسم
            </label>

            <div class="input-wrapper">

                <i class="fa-solid fa-user"></i>

                <input
                    type="text"
                    name="name"
                    placeholder="اكتب اسمك"
                    value="<?= htmlspecialchars($name) ?>"
                    required
                >

            </div>

        </div>


        <!-- EMAIL -->

        <div class="form-group">

            <label>
                البريد الإلكتروني
            </label>

            <div class="input-wrapper">

                <i class="fa-solid fa-envelope"></i>

                <input
                    type="email"
                    name="email"
                    placeholder="example@email.com"
                    value="<?= htmlspecialchars($email) ?>"
                    required
                >

            </div>

        </div>


        <!-- PASSWORD -->

        <div class="form-group">

            <label>
                كلمة السر
            </label>

            <div class="input-wrapper">

                <i class="fa-solid fa-lock"></i>

                <input
                    type="password"
                    name="password"
                    placeholder="6 أحرف على الأقل"
                    required
                >

            </div>

        </div>


        <!-- CONFIRM PASSWORD -->

        <div class="form-group">

            <label>
                تأكيد كلمة السر
            </label>

            <div class="input-wrapper">

                <i class="fa-solid fa-lock"></i>

                <input
                    type="password"
                    name="confirm_password"
                    placeholder="أعد كتابة كلمة السر"
                    required
                >

            </div>

        </div>


        <!-- BUTTON -->

        <button
            type="submit"
            class="register-btn"
        >
            إنشاء الحساب
        </button>


    </form>


    <!-- LOGIN -->

    <div class="login-link">

        لديك حساب بالفعل؟

        <a href="login.php">
            تسجيل الدخول
        </a>

    </div>



</div>

</main>

</body>

</html>