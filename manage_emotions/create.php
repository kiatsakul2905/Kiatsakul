<?php
header('Content-Type: application/json; charset=utf-8');
include '../FilePHP/db.php';

try {
    $keyword = trim($_POST['keyword'] ?? '');
    $name = trim($_POST['name'] ?? '');
    
    if (!$keyword) throw new Exception("กรุณากรอก Keyword");
    if (!$name) $name = $keyword; // Use keyword as name if name is not provided

    $stmt = $conn->prepare("INSERT INTO emotions (keyword, name) VALUES (?, ?)");
    $stmt->bind_param("ss", $keyword, $name);
    $stmt->execute();

    echo json_encode(['success' => true, 'message' => 'เพิ่ม Keyword สำเร็จ']);
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
