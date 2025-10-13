<?php
session_start();
include '../FilePHP/db.php';

header('Content-Type: application/json');

// ตรวจสอบ session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo json_encode([
        'success' => false,
        'message' => 'ไม่มีสิทธิ์เข้าถึง - กรุณาเข้าสู่ระบบผู้ดูแล'
    ]);
    exit;
}

try {
    // ตรวจสอบในฐานข้อมูลอีกครั้ง
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT id, first_name, last_name, email, role FROM users WHERE id = ? AND role = 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // ไม่เป็น admin แล้ว
        session_destroy();
        echo json_encode([
            'success' => false,
            'message' => 'สิทธิ์ผู้ดูแลถูกเพิกถอน'
        ]);
        exit;
    }

    $user = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'user' => [
            'id' => $user['id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'role' => $user['role']
        ],
        'permissions' => [
            'can_manage_users' => true,
            'can_manage_foods' => true,
            'can_view_reports' => true,
            'can_access_admin' => true
        ]
    ]);

} catch (Exception $e) {
    error_log("Check admin error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการตรวจสอบสิทธิ์'
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>
