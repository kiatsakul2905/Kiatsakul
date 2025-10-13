<?php
require '../FilePHP/db.php';

if (!isset($_GET['token'], $_GET['email'])) {
    die('ลิงก์ไม่ถูกต้อง');
}

$token = $_GET['token'];
$email = $_GET['email'];

// ตรวจสอบ token ว่ายังใช้ได้ไหม
$stmt = $conn->prepare("SELECT expires_at FROM password_resets WHERE email = ? AND token = ?");
$stmt->bind_param('ss', $email, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('ลิงก์รีเซ็ตรหัสผ่านไม่ถูกต้องหรือหมดอายุ');
}

$row = $result->fetch_assoc();
if (strtotime($row['expires_at']) < time()) {
    die('ลิงก์รีเซ็ตรหัสผ่านหมดอายุ');
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>รีเซ็ตรหัสผ่าน - MoodFood</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #fff7ed;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }
    .card {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      max-width: 400px;
      width: 100%;
    }
    input, button {
      width: 100%;
      padding: 10px;
      margin: 0.5rem 0;
      font-size: 1rem;
      border-radius: 6px;
      border: 1px solid #ccc;
    }
    button {
      background-color: #dc2626;
      color: white;
      border: none;
      cursor: pointer;
    }
    button:hover {
      background-color: #b91c1c;
    }
  </style>
</head>
<body>

<div class="card">
  <h2>ตั้งรหัสผ่านใหม่</h2>
  <form action="reset_password_process.php" method="POST">
    <input type="hidden" name="token" value="<?=htmlspecialchars($token)?>" />
    <input type="hidden" name="email" value="<?=htmlspecialchars($email)?>" />
    <label for="password">รหัสผ่านใหม่</label>
    <input type="password" name="password" id="password" required minlength="6" placeholder="รหัสผ่านอย่างน้อย 6 ตัว" />
    <label for="password_confirm">ยืนยันรหัสผ่านใหม่</label>
    <input type="password" name="password_confirm" id="password_confirm" required minlength="6" placeholder="ยืนยันรหัสผ่าน" />
    <button type="submit">รีเซ็ตรหัสผ่าน</button>
  </form>
</div>

<script>
  const form = document.querySelector('form');
  form.addEventListener('submit', e => {
    const pw = form.password.value;
    const pwc = form.password_confirm.value;
    if (pw !== pwc) {
      alert('รหัสผ่านใหม่กับยืนยันรหัสผ่านไม่ตรงกัน');
      e.preventDefault();
    }
  });
</script>

</body>
</html>
