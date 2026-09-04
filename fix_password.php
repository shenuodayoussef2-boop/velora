<?php
require_once "db.php";

// ضع هنا بريدك الإلكتروني المسجل وكلمة المرور الجديدة التي تريدها
$email = "your_email@example.com"; 
$new_password = "your_password";

$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->execute([$hashed_password, $email]);

echo "تم تحديث وتشفير كلمة المرور بنجاح! جرب تسجيل الدخول الآن.";
?>