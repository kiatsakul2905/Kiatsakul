<?php
header('Content-Type: application/json');
include '../FilePHP/db.php';

$sql = "SELECT * FROM foods ORDER BY id DESC";
$result = $conn->query($sql);
$foods = [];

while ($row = $result->fetch_assoc()) {
    // ดึง emotions ของอาหาร
    $em_result = $conn->query("SELECT e.id, e.name FROM food_emotions fe JOIN emotions e ON fe.emotion_id = e.id WHERE fe.food_id = ".$row['id']);
    $emotions_names = [];
    $emotions_ids = [];
    while($em = $em_result->fetch_assoc()){
        $emotions_names[] = $em['name'];
        $emotions_ids[] = intval($em['id']);
    }

    // ดึง herbs ของอาหาร
    $herb_result = $conn->query("SELECT h.id, h.name FROM food_herbs fh JOIN herbs h ON fh.herb_id = h.id WHERE fh.food_id = ".$row['id']);
    $herbs_names = [];
    $herbs_ids = [];
    while($h = $herb_result->fetch_assoc()){
        $herbs_names[] = $h['name'];
        $herbs_ids[] = intval($h['id']);
    }

    $foods[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'history' => $row['history'],
        'recipe' => $row['recipe'] ? explode(',', $row['recipe']) : [],
        'description' => $row['description'],
        'calories' => $row['calories'],
        'food_img' => $row['food_img'],
        'calories_img' => $row['calories_img'],
        'emotions' => $emotions_names,
        'emotions_ids' => $emotions_ids,
        'herbs' => $herbs_names,
        'herbs_ids' => $herbs_ids
    ];
}

echo json_encode($foods);
$conn->close();
