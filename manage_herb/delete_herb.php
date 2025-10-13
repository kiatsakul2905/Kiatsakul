<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include '../FilePHP/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    if (!$conn || $conn->connect_error) {
        throw new Exception("ไม่สามารถเชื่อมต่อฐานข้อมูลได้");
    }

    // รับข้อมูลจาก JSON body
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);

    // ตรวจสอบข้อมูล
    if ($id <= 0) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'รหัสสมุนไพรไม่ถูกต้อง'
        ]);
        exit;
    }

    // ตรวจสอบว่าสมุนไพรนี้มีอยู่หรือไม่
    $checkStmt = $conn->prepare("SELECT name FROM herbs WHERE id = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $herb = $result->fetch_assoc();
    
    if (!$herb) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'ไม่พบสมุนไพรที่ต้องการลบ'
        ]);
        exit;
    }

    // ลบสมุนไพรโดยตรง
    $stmt = $conn->prepare("DELETE FROM herbs WHERE id = ?");
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();

    ob_clean();
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => "ลบสมุนไพร \"{$herb['name']}\" สำเร็จ"
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการลบสมุนไพร'
        ]);
    }

} catch (Exception $e) {
    ob_clean();
    error_log("General error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล'
    ]);
}
?>
