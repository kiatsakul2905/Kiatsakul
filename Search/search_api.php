<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// เชื่อมต่อฐานข้อมูล
include '../FilePHP/db.php';

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้'
    ]);
    exit;
}

// รับ action parameter
$action = isset($_GET['action']) ? trim($_GET['action']) : 'search';

try {
    switch ($action) {
        case 'search':
            searchFood($conn);
            break;
        case 'all':
            getAllFoods($conn);
            break;
        case 'suggestions':
            getSearchSuggestions($conn);
            break;
        default:
            http_response_code(400);
            echo json_encode([
                'error' => true,
                'message' => 'Action ไม่ถูกต้อง กรุณาระบุ action: search, all, หรือ suggestions'
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} finally {
    $conn->close();
}

/**
 * ฟังก์ชันค้นหาอาหาร
 */
function searchFood($conn) {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    if (empty($search)) {
        echo json_encode([
            'error' => true,
            'message' => 'กรุณากรอกคำค้นหา',
            'data' => []
        ]);
        return;
    }
    
    $sql = "SELECT DISTINCT
                f.id AS food_id,
                f.name,
                f.food_img AS image,
                f.recipe,
                f.calories_img,
                f.calories,
                f.history,
                GROUP_CONCAT(DISTINCT e.name) AS mood,
                GROUP_CONCAT(DISTINCT h.name) AS herbs,
                (
                    CASE WHEN f.name LIKE ? THEN 10 ELSE 0 END +
                    CASE WHEN f.name LIKE ? THEN 5 ELSE 0 END +
                    CASE WHEN EXISTS(SELECT 1 FROM food_herbs fh2 
                                   JOIN herbs h2 ON fh2.herb_id = h2.id 
                                   WHERE fh2.food_id = f.id AND h2.name LIKE ?) THEN 8 ELSE 0 END +
                    CASE WHEN f.recipe LIKE ? THEN 6 ELSE 0 END +
                    CASE WHEN f.recipe LIKE ? THEN 3 ELSE 0 END +
                    CASE WHEN f.history LIKE ? THEN 2 ELSE 0 END
                ) AS relevance_score
            FROM foods f
            LEFT JOIN food_emotions fe ON f.id = fe.food_id
            LEFT JOIN emotions e ON fe.emotion_id = e.id
            LEFT JOIN food_herbs fh ON f.id = fh.food_id
            LEFT JOIN herbs h ON fh.herb_id = h.id
            WHERE (
                f.name LIKE ? OR
                f.recipe LIKE ? OR
                f.history LIKE ? OR
                EXISTS(SELECT 1 FROM food_herbs fh3 
                       JOIN herbs h3 ON fh3.herb_id = h3.id 
                       WHERE fh3.food_id = f.id AND h3.name LIKE ?)
            )
            GROUP BY f.id, f.name, f.food_img, f.recipe, f.calories_img, f.calories, f.history
            HAVING relevance_score > 0
            ORDER BY relevance_score DESC, f.name ASC
            LIMIT 50";
    
    $searchParam = "%{$search}%";
    $exactSearchParam = $search;
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("เกิดข้อผิดพลาดในการเตรียม query: " . $conn->error);
    
    $stmt->bind_param(
        "ssssssssss",
        $exactSearchParam,
        $searchParam,
        $searchParam,
        $searchParam,
        $searchParam,
        $searchParam,
        $searchParam,
        $searchParam,
        $searchParam,
        $searchParam
    );
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $foods = [];
    while ($row = $result->fetch_assoc()) {
        // วัตถุดิบจาก recipe
        $ingredients = $row['recipe'] ? array_map('trim', explode(',', $row['recipe'])) : [];
        $foods[] = [
            'id' => (int)$row['food_id'],
            'name' => $row['name'],
            'image' => '/NLPTEST/uploads/foods/' . $row['image'],
            'recipe' => $row['recipe'],
            'ingredients' => $ingredients,
            'calories_img' => $row['calories_img'],
            'calories' => isset($row['calories']) ? (int)$row['calories'] : 0,
            'history' => $row['history'],
            'mood' => $row['mood'] ? explode(',', $row['mood']) : [],
            'herbs' => $row['herbs'] ? explode(',', $row['herbs']) : [],
            'relevance_score' => (int)$row['relevance_score']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'ค้นหาสำเร็จ',
        'search_term' => $search,
        'total_results' => count($foods),
        'data' => $foods
    ], JSON_UNESCAPED_UNICODE);
    
    $stmt->close();
}

/**
 * ฟังก์ชันดึงรายการอาหารทั้งหมด
 */
function getAllFoods($conn) {
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(1, (int)$_GET['limit'])) : 20;
    $category = isset($_GET['category']) ? trim($_GET['category']) : '';
    $sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'name';
    $order = strtolower($_GET['order'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
    $offset = ($page - 1) * $limit;

    $whereClause = '';
    $params = [];
    $types = '';
    if (!empty($category)) {
        $whereClause = "WHERE e.name = ?";
        $params[] = $category;
        $types .= 's';
    }

    $validSortFields = ['name','id'];
    $sort = in_array($sort, $validSortFields) ? $sort : 'name';
    $orderClause = "ORDER BY f.{$sort} {$order}";

    $countSql = "SELECT COUNT(DISTINCT f.id) as total
                 FROM foods f
                 LEFT JOIN food_emotions fe ON f.id = fe.food_id
                 LEFT JOIN emotions e ON fe.emotion_id = e.id
                 {$whereClause}";
    $countStmt = $conn->prepare($countSql);
    if (!empty($params)) $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $totalRows = $countStmt->get_result()->fetch_assoc()['total'];

    $sql = "SELECT DISTINCT
                f.id AS food_id,
                f.name,
                f.food_img AS image,
                f.recipe,
                f.calories_img,
                f.calories,
                f.history,
                GROUP_CONCAT(DISTINCT e.name) AS mood,
                GROUP_CONCAT(DISTINCT h.name) AS herbs
            FROM foods f
            LEFT JOIN food_emotions fe ON f.id = fe.food_id
            LEFT JOIN emotions e ON fe.emotion_id = e.id
            LEFT JOIN food_herbs fh ON f.id = fh.food_id
            LEFT JOIN herbs h ON fh.herb_id = h.id
            {$whereClause}
            GROUP BY f.id, f.name, f.food_img, f.recipe, f.calories_img, f.calories, f.history
            {$orderClause}
            LIMIT ? OFFSET ?";

    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $foods = [];
    while ($row = $result->fetch_assoc()) {
        $ingredients = $row['recipe'] ? array_map('trim', explode(',', $row['recipe'])) : [];
        $foods[] = [
            'id' => (int)$row['food_id'],
            'name' => $row['name'],
            'image' => '/NLPTEST/uploads/foods/' . $row['image'],
            'recipe' => $row['recipe'],
            'ingredients' => $ingredients,
            'calories_img' => $row['calories_img'],
            'calories' => isset($row['calories']) ? (int)$row['calories'] : 0,
            'history' => $row['history'],
            'mood' => $row['mood'] ? array_map('trim', explode(',', $row['mood'])) : [],
            'herbs' => $row['herbs'] ? array_map('trim', explode(',', $row['herbs'])) : []
        ];
    }

    $totalPages = ceil($totalRows / $limit);
    echo json_encode([
        'success' => true,
        'message' => 'ดึงข้อมูลสำเร็จ',
        'data' => $foods,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => (int)$totalRows,
            'items_per_page' => $limit,
            'has_next_page' => $page < $totalPages,
            'has_prev_page' => $page > 1
        ],
        'filters' => [
            'category' => $category,
            'sort' => $sort,
            'order' => $order
        ]
    ], JSON_UNESCAPED_UNICODE);

    $stmt->close();
    $countStmt->close();
}

/**
 * ฟังก์ชันแสดงคำแนะนำการค้นหา
 */
function getSearchSuggestions($conn) {
    $query = trim($_GET['q'] ?? '');
    $limit = min(10, max(1, (int)($_GET['limit'] ?? 5)));
    if (empty($query) || strlen($query) < 2) {
        echo json_encode([
            'success' => true,
            'message' => 'คำค้นหาสั้นเกินไป',
            'suggestions' => []
        ]);
        return;
    }

    $suggestions = [];
    $searchParam = "%{$query}%";
    $exactQuery = "{$query}%";

    $tables = [
        ['foods','food','อาหาร'],
        ['herbs','herb','สมุนไพร'],
        ['ingredients','ingredient','ส่วนผสม']
    ];

    foreach ($tables as $tbl) {
        if (count($suggestions) >= $limit) break;
        $remaining = $limit - count($suggestions);
        $sql = "SELECT DISTINCT name as suggestion, ? as type, ? as type_label
                FROM {$tbl[0]} WHERE name LIKE ?
                ORDER BY CASE WHEN name LIKE ? THEN 1 ELSE 2 END, LENGTH(name), name
                LIMIT ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $tbl[1], $tbl[2], $searchParam, $exactQuery, $remaining);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) $suggestions[] = $row;
        $stmt->close();
    }

    echo json_encode([
        'success' => true,
        'message' => 'ดึงคำแนะนำสำเร็จ',
        'query' => $query,
        'suggestions' => $suggestions
    ], JSON_UNESCAPED_UNICODE);
}
?>

