<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

include '../FilePHP/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
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

    // รับพารามิเตอร์
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = 10; // จำนวนรายการต่อหน้า
    $offset = ($page - 1) * $limit;
    $search = trim($_GET['search'] ?? '');

    // สร้าง WHERE clause สำหรับการค้นหา
    $whereClause = '';
    $params = [];
    if (!empty($search)) {
        $whereClause = "WHERE h.name LIKE ?";
        $params[] = "%$search%";
    }

    // นับจำนวนรายการทั้งหมด
    $countSql = "SELECT COUNT(*) as total FROM herbs h $whereClause";
    if (!empty($params)) {
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("s", $params[0]);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
    } else {
        $countResult = $conn->query($countSql);
    }
    
    $totalRecords = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalRecords / $limit);

    // ดึงข้อมูลสมุนไพร พร้อมจำนวนอาหารที่ใช้แต่ละสมุนไพร และสรรพคุณ
    $sql = "SELECT h.id, h.name, h.properties,
                   (SELECT COUNT(*) FROM food_herbs fh WHERE fh.herb_id = h.id) AS food_count
            FROM herbs h 
            $whereClause 
            ORDER BY h.name ASC 
            LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param("sii", $params[0], $limit, $offset);
    } else {
        $stmt->bind_param("ii", $limit, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $herbs = [];

    while ($row = $result->fetch_assoc()) {
        $herbs[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'properties' => $row['properties'],
            'food_count' => intval($row['food_count'])
        ];
    }

    ob_clean();
    echo json_encode([
        'success' => true,
        'herbs' => $herbs,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_records' => $totalRecords,
            'per_page' => $limit
        ]
    ]);

    $conn->close();

} catch (Exception $e) {
    ob_clean();
    error_log("Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล'
    ]);
}
?>