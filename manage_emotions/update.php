<?php
header('Content-Type: application/json; charset=utf-8');
include '../FilePHP/db.php';

try {
    $id = intval($_POST['id'] ?? 0);
    $keyword = trim($_POST['keyword'] ?? '');
    $name = trim($_POST['name'] ?? '');
    
    if (!$id || !$keyword) throw new Exception("ข้อมูลไม่ครบถ้วน");
    if (!$name) $name = $keyword; // Use keyword as name if name is not provided

    $stmt = $conn->prepare("UPDATE emotions SET keyword=?, name=? WHERE id=?");
    $stmt->bind_param("ssi", $keyword, $name, $id);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'แก้ไข Keyword สำเร็จ']);
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
