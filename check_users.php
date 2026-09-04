<?php
require_once "db.php";

try {
    $stmt = $pdo->query("SELECT id, name, email, password FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h2>المستخدمون المسجلون في قاعدة البيانات:</h2>";
    if (count($users) > 0) {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; direction: rtl;'>";
        echo "<tr><th>ID</th><th>الاسم</th><th>البريد الإلكتروني</th><th>كلمة المرور المشفرة</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['id'] . "</td>";
            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['password']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>لا توجد أي حسابات مسجلة في جدول users حالياً!</p>";
    }
} catch (PDOException $e) {
    echo "خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage();
}
?>