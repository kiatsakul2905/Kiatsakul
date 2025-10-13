<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require '../vendor/autoload.php';
require '../FilePHP/db.php'; // เชื่อมฐานข้อมูล

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ตรวจสอบ email และ phone
if (!isset($_POST['email'], $_POST['phone'])) {
    echo json_encode(['success' => false, 'message' => 'กรุณาระบุอีเมลและเบอร์โทรศัพท์']);
    exit;
}

$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$phone = preg_replace('/\D/', '', $_POST['phone']); // ลบตัวอักษรที่ไม่ใช่ตัวเลข

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'อีเมลไม่ถูกต้อง']);
    exit;
}

if (strlen($phone) < 9 || strlen($phone) > 10) {
    echo json_encode(['success' => false, 'message' => 'เบอร์โทรศัพท์ไม่ถูกต้อง']);
    exit;
}

// ตรวจสอบ email + phone ในฐานข้อมูล
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND phone = ?");
$stmt->bind_param('ss', $email, $phone);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบอีเมลหรือเบอร์โทรศัพท์นี้ในระบบ']);
    exit;
}

// สร้าง token และวันหมดอายุ (1 ชั่วโมง)
$token = bin2hex(random_bytes(32));
$expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

// ลบ token เก่าของ email นี้ (ถ้ามี)
$stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();

// บันทึก token ใหม่
$stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
$stmt->bind_param('sss', $email, $token, $expires_at);
$stmt->execute();

// สร้างลิงก์รีเซ็ต
$reset_link = "http://localhost/NLPTEST/Forgot_password/reset_password.php?token=$token&email=" . urlencode($email);

// ส่งอีเมลด้วย PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'kiatsakul.p@ku.th'; // เปลี่ยนเป็นอีเมลของคุณ
    $mail->Password = 'ktru ezwt hynl ggxb'; // รหัสผ่านแอป 16 หลัก
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('kiatsakul.p@ku.th', 'MoodFood');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'ลิงก์รีเซ็ตรหัสผ่าน MoodFood';
    $mail->Body = "
        <p>คุณได้รับอีเมลนี้เนื่องจากมีการร้องขอรีเซ็ตรหัสผ่านสำหรับบัญชีของคุณ</p>
        <p>คลิกที่ลิงก์ด้านล่างเพื่อรีเซ็ตรหัสผ่าน:</p>
        <p><a href='$reset_link'>$reset_link</a></p>
        <p>ลิงก์นี้จะหมดอายุใน 1 ชั่วโมง</p>
        <p>ถ้าไม่ได้ร้องขอ กรุณาเพิกเฉยอีเมลนี้</p>
    ";

    $mail->send();

    echo json_encode(['success' => true, 'message' => 'ส่งลิงก์กู้คืนรหัสผ่านเรียบร้อยแล้ว']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'ไม่สามารถส่งอีเมลได้: ' . $mail->ErrorInfo]);
}
?>
