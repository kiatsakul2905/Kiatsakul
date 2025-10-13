<?php
header('Content-Type: application/json; charset=utf-8');
include '../FilePHP/db.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') throw new Exception("Method not allowed");

    // Get id from query string for DELETE requests
    $id = intval($_GET['id'] ?? 0);
    if (!$id) throw new Exception("ไม่พบ ID");

    $stmt = $conn->prepare("DELETE FROM emotions WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'ลบ Keyword สำเร็จ']);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลที่ต้องการลบ']);
    }
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
