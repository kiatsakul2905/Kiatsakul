<?php
session_start();
include '../FilePHP/db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบก่อน']);
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT first_name, last_name, phone, profile_image FROM users WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลผู้ใช้']);
    exit;
}

$user = $result->fetch_assoc();
$img_filename = $user['profile_image'];

// ตรวจสอบรูปภาพ
$img_path = '/NLPTEST/uploads/default.jpg'; // default

if ($img_filename && $img_filename !== 'default.jpg') {
    $full_path = __DIR__ . '/../uploads/' . $img_filename;
    if (file_exists($full_path)) {
        $img_path = '/NLPTEST/uploads/' . $img_filename;
    }
}

echo json_encode([
    'success' => true,
    'data' => [
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'phone' => $user['phone'],
        'profile_image' => $img_path
    ]
]);

$stmt->close();
$conn->close();
?>