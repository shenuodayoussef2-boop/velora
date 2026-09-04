<?php
session_start();
require_once "db.php";

$error = "";
$success = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");

    if ($email === "") {
        $error = "من فضلك أدخل البريد الإلكتروني.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "من فضلك أدخل بريدًا إلكترونيًا صحيحًا.";
    } else {
        // التحقق مما إذا كان البريد مسجلاً في قاعدة البيانات
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // في المشاريع الحقيقية يتم إرسال رابط إعادة تعيين عبر البريد، 
            // لكن هنا سنقوم بتوجيهه لصفحة إعادة التعيين المباشرة أو عرض رسالة نجاح مؤقتة
            $success = "تم العثور على الحساب! يمكنك الآن تعيين كلمة مرور جديدة.";
            // تخزين البريد في الجلسة لنقله لصفحة إعادة التعيين
            $_SESSION['reset_email'] = $email;
            
            // يمكنك تحويله لصفحة إعادة التعيين مباشرة بعد ثوانٍ أو وضع زر
            header("refresh:2;url=reset-password.php");
        } else {
            $error = "هذا البريد الإلكتروني غير مسجل لدينا.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velora - استعادة كلمة السر</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; color: #111; min-height: 100vh; }
        a { text-decoration: none; color: inherit; }
        .auth-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 30px 20px; }
        .auth-box { width: 100%; max-width: 460px; background: #fff; padding: 45px; border-radius: 14px; box-shadow: 0 10px 40px rgba(0,0,0,.08); }
        .logo { display: block; text-align: center; font-size: 32px; font-weight: bold; letter-spacing: 1px; margin-bottom: 35px; }
        .auth-header { text-align: center; margin-bottom: 30px; }
        .auth-header h1 { font-size: 27px; margin-bottom: 10px; }
        .auth-header p { color: #777; font-size: 14px; line-height: 1.7; }
        .error-message { background: #fff1f1; border: 1px solid #f0caca; color: #b00000; padding: 13px; border-radius: 7px; margin-bottom: 20px; font-size: 13px; text-align: center; }
        .success-message { background: #f1fdf3; border: 1px solid #caf0cc; color: #00802b; padding: 13px; border-radius: 7px; margin-bottom: 20px; font-size: 13px; text-align: center; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: bold; }
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); color: #888; }
        .form-group input { width: 100%; padding: 14px 45px 14px 14px; border: 1px solid #ddd; border-radius: 7px; outline: none; font-size: 14px; transition: .3s; }
        .form-group input:focus { border-color: #111; }
        .login-btn { width: 100%; border: none; background: #111; color: #fff; padding: 15px; border-radius: 7px; cursor: pointer; font-size: 14px; transition: .3s; }
        .login-btn:hover { background: #333; }
        .back-home { display: block; text-align: center; margin-top: 25px; color: #888; font-size: 13px; }
        .back-home:hover { color: #111; }
    </style>
</head>
<body>

<main class="auth-page">
    <div class="auth-box">
        <a href="index.php" class="logo">Velora</a>

        <div class="auth-header">
            <h1>استعادة كلمة السر</h1>
            <p>أدخل بريدك الإلكتروني للبحث عن حسابك</p>
        </div>

        <?php if ($error !== ""): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success !== ""): ?>
            <div class="success-message"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="forgot-password.php">
            <div class="form-group">
                <label>البريد الإلكتروني</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="example@email.com" value="<?= htmlspecialchars($email) ?>" required>
                </div>
            </div>

            <button type="submit" class="login-btn">تحقق من البريد</button>
        </form>

        <a href="login.php" class="back-home">
            <i class="fa-solid fa-arrow-right"></i> العودة لتسجيل الدخول
        </a>
    </div>
</main>

</body>
</html>