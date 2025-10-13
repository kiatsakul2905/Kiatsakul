<?php
require '../FilePHP/db.php';
header('Content-Type: application/json');

if (!isset($_GET['mood']) || empty(trim($_GET['mood']))) {
    echo json_encode([]);
    exit;
}

$mood = trim($_GET['mood']); // ส่งเป็น "happy" หรือ "sad"

$sql = "
SELECT 
    f.id AS food_id,
    f.name,
    f.food_img AS image,
    f.calories,
    GROUP_CONCAT(DISTINCT h.name) AS herbs
FROM foods f
JOIN food_emotions fe ON f.id = fe.food_id
JOIN emotions e ON fe.emotion_id = e.id
LEFT JOIN food_herbs fh ON f.id = fh.food_id
LEFT JOIN herbs h ON fh.herb_id = h.id
WHERE e.name = ?
GROUP BY f.id
LIMIT 3
";

$stmt = $conn->prepare($sql);
if (!$stmt) { echo json_encode([]); exit; }
$stmt->bind_param("s", $mood);
$stmt->execute();
$result = $stmt->get_result();

$foods = [];
while ($row = $result->fetch_assoc()) {
    $foods[] = [
        'id' => $row['food_id'],
        'name' => $row['name'],
        'image' => '/NLPTEST/uploads/foods/' . $row['image'], 
        'calories' => $row['calories'],
        'herbs' => $row['herbs'] ? explode(',', $row['herbs']) : []
    ];
}
echo json_encode($foods);
?>
