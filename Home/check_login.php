<?php
session_start();  // ✅ เรียกครั้งเดียวเท่านั้น

// สำหรับ debug (แนะนำให้ปิดใน production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// ถ้าไม่ได้ล็อกอิน
if (!isset($_SESSION['user_id'])) {
  echo json_encode(['loggedIn' => false]);
  exit;
}

// ตรวจสอบว่าไฟล์นี้อยู่ในโฟลเดอร์เดียวกับ db.php หรือไม่
include '../FilePHP/db.php'; // ✅ ตรวจให้ชัวร์ว่า path นี้ถูกต้อง

$id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone, profile_image FROM users WHERE id = ? ");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// ส่งข้อมูลผู้ใช้กลับไปในรูปแบบ JSON
echo json_encode([
  'loggedIn' => true,
  'first_name' => $user['first_name'],
  'last_name' => $user['last_name'],
  'email' => $user['email'],
  'phone' => $user['phone'],
  'profile_image' => $user['profile_image']
]);

$stmt->close();
$conn->close();
