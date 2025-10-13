<?php
require '../FilePHP/db.php';

if (!isset($_POST['email'], $_POST['token'], $_POST['password'], $_POST['password_confirm'])) {
    die('ข้อมูลไม่ครบ');
}

$email = $_POST['email'];
$token = $_POST['token'];
$password = $_POST['password'];
$password_confirm = $_POST['password_confirm'];

if ($password !== $password_confirm) {
    die('รหัสผ่านใหม่กับยืนยันรหัสผ่านไม่ตรงกัน');
}

// ตรวจสอบ token และ email
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

// อัปเดตรหัสผ่านใน users
$password_hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
$stmt->bind_param('ss', $password_hashed, $email);
$stmt->execute();

// ลบ token ออก
$stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ? AND token = ?");
$stmt->bind_param('ss', $email, $token);
$stmt->execute();

// แสดงข้อความสำเร็จ
echo "รีเซ็ตรหัสผ่านเรียบร้อยแล้ว คุณสามารถ <a href='../Login/Login.html'>เข้าสู่ระบบ</a> ได้เลย";
?>
