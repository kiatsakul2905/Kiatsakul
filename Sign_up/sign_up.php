<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
include '../FilePHP/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// รับข้อมูล
$first_name = $_POST['first_name'] ?? '';
$last_name = $_POST['last_name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// ตรวจสอบความถูกต้องเบื้องต้น
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'รูปแบบอีเมลไม่ถูกต้อง']);
    exit;
}

if (!preg_match('/^0[0-9]{9}$/', $phone)) {
    echo json_encode(['success' => false, 'message' => 'เบอร์โทรศัพท์ต้องขึ้นต้นด้วย 0 และมี 10 หลัก']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร']);
    exit;
}

if ($password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'รหัสผ่านไม่ตรงกัน']);
    exit;
}

// ตรวจสอบอีเมลซ้ำ
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param('s', $email);
$check->execute();
$check->store_result();
if ($check->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'อีเมลนี้มีอยู่ในระบบแล้ว']);
    exit;
}
$check->close();

// ตรวจสอบและอัปโหลดรูปภาพ
if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'กรุณาอัปโหลดรูปภาพโปรไฟล์']);
    exit;
}

$targetDir = "../uploads/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}
$imageName = basename($_FILES['profile_image']['name']);
$targetFile = $targetDir . time() . '_' . $imageName;
$imageType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

if (!in_array($imageType, ['jpg', 'jpeg', 'png'])) {
    echo json_encode(['success' => false, 'message' => 'อัปโหลดได้เฉพาะไฟล์ JPG หรือ PNG']);
    exit;
}

if (!move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFile)) {
    echo json_encode(['success' => false, 'message' => 'อัปโหลดรูปภาพไม่สำเร็จ']);
    exit;
}

// เข้ารหัสรหัสผ่าน
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// ตั้งค่า role เป็น user
$role = 'user';

// บันทึกข้อมูลผู้ใช้ลงฐานข้อมูล
$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password, profile_image, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('sssssss', $first_name, $last_name, $email, $phone, $hashedPassword, $targetFile, $role);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'สมัครสมาชิกสำเร็จ!']);
} else {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
}

$stmt->close();
$conn->close();
?>
