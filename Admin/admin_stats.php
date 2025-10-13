<?php
session_start();
include '../FilePHP/db.php';

header('Content-Type: application/json');

// ตรวจสอบสิทธิ์ admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'ไม่มีสิทธิ์เข้าถึง'
    ]);
    exit;
}

try {
    // นับจำนวนผู้ใช้
    $users_result = $conn->query("SELECT COUNT(*) as count FROM users");
    $users_count = $users_result->fetch_assoc()['count'];

    // นับจำนวนอาหาร (ถ้ามีตาราง foods)
    $foods_count = 0;
    $foods_result = $conn->query("SHOW TABLES LIKE 'foods'");
    if ($foods_result->num_rows > 0) {
        $foods_result = $conn->query("SELECT COUNT(*) as count FROM foods");
        $foods_count = $foods_result->fetch_assoc()['count'];
    }

    echo json_encode([
        'success' => true,
        'stats' => [
            'users' => $users_count,
            'foods' => $foods_count,
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลสถิติ',
        'error' => $e->getMessage()
    ]);
}

$conn->close();
?>
