<?php
header('Content-Type: application/json');
include '../FilePHP/db.php';

$name = isset($_POST['name']) ? $_POST['name'] : '';
$history = isset($_POST['history']) ? $_POST['history'] : '';
$recipe = isset($_POST['recipe']) ? $_POST['recipe'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';
$calories = isset($_POST['calories']) ? intval($_POST['calories']) : 0;
$emotions = isset($_POST['emotions']) ? $_POST['emotions'] : [];
$herbs = isset($_POST['herbs']) ? $_POST['herbs'] : [];

// อัปโหลดรูปอาหาร
$food_img = '';
if (!empty($_FILES['food_img']['name'])) {
    $ext = pathinfo($_FILES['food_img']['name'], PATHINFO_EXTENSION);
    $food_img = uniqid('food_', true).'.'.$ext;
    $uploadDir = '../uploads/foods/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    move_uploaded_file($_FILES['food_img']['tmp_name'], $uploadDir.$food_img);
}

// อัปโหลดรูปแคลอรี่
$calories_img = '';
if (!empty($_FILES['calories_img']['name'])) {
    $ext = pathinfo($_FILES['calories_img']['name'], PATHINFO_EXTENSION);
    $calories_img = uniqid('cal_', true).'.'.$ext;
    $uploadDir = '../uploads/cal/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    move_uploaded_file($_FILES['calories_img']['tmp_name'], $uploadDir.$calories_img);
}

// Insert foods
$stmt = $conn->prepare("INSERT INTO foods (name, history, recipe, description, calories, food_img, calories_img) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssiss", $name, $history, $recipe, $description, $calories, $food_img, $calories_img);

if ($stmt->execute()) {
    $food_id = $conn->insert_id; // id ของอาหารที่เพิ่ม
    // insert emotions
    if(!empty($emotions)) {
        foreach($emotions as $eid){
            $stmt2 = $conn->prepare("INSERT INTO food_emotions (food_id, emotion_id) VALUES (?, ?)");
            $stmt2->bind_param("ii", $food_id, $eid);
            $stmt2->execute();
            $stmt2->close();
        }
    }
    // insert herbs
    if(!empty($herbs)) {
        foreach($herbs as $hid){
            $stmt3 = $conn->prepare("INSERT INTO food_herbs (food_id, herb_id) VALUES (?, ?)");
            $stmt3->bind_param("ii", $food_id, $hid);
            $stmt3->execute();
            $stmt3->close();
        }
    }
    echo json_encode(['success'=>true,'message'=>'เพิ่มอาหารเรียบร้อย']);
} else {
    echo json_encode(['success'=>false,'message'=>'เกิดข้อผิดพลาด']);
}
$stmt->close();
$conn->close();
