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
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $properties = trim($_POST['properties'] ?? '');

    // ตรวจสอบข้อมูล
    if ($id <= 0) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'รหัสสมุนไพรไม่ถูกต้อง'
        ]);
        exit;
    }

    if (empty($name)) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'กรุณากรอกชื่อสมุนไพร'
        ]);
        exit;
    }

    // ตรวจสอบว่าสมุนไพรนี้มีอยู่หรือไม่
    $checkStmt = $conn->prepare("SELECT id FROM herbs WHERE id = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if (!$result->fetch_assoc()) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'ไม่พบสมุนไพรที่ต้องการแก้ไข'
        ]);
        exit;
    }

    // ตรวจสอบว่าชื่อสมุนไพรซ้ำกับรายการอื่นหรือไม่
    $duplicateStmt = $conn->prepare("SELECT id FROM herbs WHERE name = ? AND id != ?");
    $duplicateStmt->bind_param("si", $name, $id);
    $duplicateStmt->execute();
    $duplicateResult = $duplicateStmt->get_result();
    
    if ($duplicateResult->fetch_assoc()) {
        ob_clean();
        echo json_encode([
            'success' => false,
            'message' => 'ชื่อสมุนไพรนี้มีอยู่ในระบบแล้ว'
        ]);
        exit;
    }

    // อัปเดตข้อมูลสมุนไพร
    $stmt = $conn->prepare("UPDATE herbs SET name = ?, properties = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $properties, $id);
    $result = $stmt->execute();

    ob_clean();
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'แก้ไขสมุนไพรสำเร็จ'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการแก้ไขสมุนไพร'
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