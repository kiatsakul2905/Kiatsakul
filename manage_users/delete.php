<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include '../FilePHP/db.php';
include 'User.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // รับข้อมูล JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (empty($input['id'])) {
        throw new Exception("ไม่พบ ID ผู้ใช้");
    }
    
    // ตรวจสอบว่าผู้ใช้มีอยู่จริงหรือไม่
    $check_query = "SELECT id FROM users WHERE id = :id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':id', $input['id']);
    $check_stmt->execute();
    
    if ($check_stmt->rowCount() === 0) {
        throw new Exception("ไม่พบผู้ใช้ที่ต้องการลบ");
    }
    
    // ลบผู้ใช้
    $query = "DELETE FROM users WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $input['id']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'ลบผู้ใช้เรียบร้อยแล้ว'
        ]);
    } else {
        throw new Exception("ไม่สามารถลบผู้ใช้ได้");
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
