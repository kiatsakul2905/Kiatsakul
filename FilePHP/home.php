<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>หน้าแรก</title>
</head>
<body>
  <h1>ยินดีต้อนรับสู่เว็บไซต์สมุนไพรจากอารมณ์</h1>

  <?php if ($isLoggedIn): ?>
    <p>ยินดีต้อนรับคุณ <?php echo htmlspecialchars($_SESSION['first_name']); ?></p>
    <a href="profile.php">ดูโปรไฟล์</a> |
    <a href="edit_profile.php">แก้ไขข้อมูลส่วนตัว</a> |
    <a href="logout.php">ออกจากระบบ</a>

    <h2>เนื้อหาหลัก</h2>
    <p>แสดงเนื้อหาต่าง ๆ เกี่ยวกับอาหารและอารมณ์...</p>

  <?php else: ?>
    <p>คุณยังไม่ได้เข้าสู่ระบบ</p>
    <a href="login.html">เข้าสู่ระบบเพื่อดูเนื้อหา</a>
  <?php endif; ?>
</body>
</html>
