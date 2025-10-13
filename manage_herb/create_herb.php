<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include '../FilePHP/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

    // รับข้อมูลจากฟอร์ม
    $name = trim($_POST['name'] ?? '');
    $properties = trim($_POST['properties'] ?? '');

    // ตรวจสอบข้อมูล
    if (empty($name)) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'กรุณากรอกชื่อสมุนไพร'
        ]);
        exit;
    }

    // ตรวจสอบว่าสมุนไพรนี้มีอยู่แล้วหรือไม่
    $checkStmt = $conn->prepare("SELECT id FROM herbs WHERE name = ?");
    $checkStmt->bind_param("s", $name);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->fetch_assoc()) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'สมุนไพรนี้มีอยู่ในระบบแล้ว'
        ]);
        exit;
    }

    // เพิ่มสมุนไพรใหม่
    $stmt = $conn->prepare("INSERT INTO herbs (name, properties) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $properties);
    $result = $stmt->execute();

    ob_clean();
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'เพิ่มสมุนไพรสำเร็จ',
            'id' => $conn->insert_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการเพิ่มสมุนไพร'
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
