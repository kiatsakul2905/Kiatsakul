<?php
header('Content-Type: application/json');
include '../FilePHP/db.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = isset($_POST['name']) ? $_POST['name'] : '';
$history = isset($_POST['history']) ? $_POST['history'] : '';
$recipe = isset($_POST['recipe']) ? $_POST['recipe'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';
$calories = isset($_POST['calories']) ? intval($_POST['calories']) : 0;
$emotions = isset($_POST['emotions']) ? $_POST['emotions'] : [];
$herbs = isset($_POST['herbs']) ? $_POST['herbs'] : [];

// โหลดรูปเดิม
$result = $conn->query("SELECT food_img, calories_img FROM foods WHERE id=$id");
$row = $result->fetch_assoc();
$food_img = $row['food_img'];
$calories_img = $row['calories_img'];

// อัปโหลดรูปใหม่ถ้ามี
if (!empty($_FILES['food_img']['name'])) {
    $ext = pathinfo($_FILES['food_img']['name'], PATHINFO_EXTENSION);
    $food_img = uniqid('food_', true).'.'.$ext;
    $uploadDir = '../uploads/foods/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    move_uploaded_file($_FILES['food_img']['tmp_name'], $uploadDir.$food_img);
}
if (!empty($_FILES['calories_img']['name'])) {
    $ext = pathinfo($_FILES['calories_img']['name'], PATHINFO_EXTENSION);
    $calories_img = uniqid('cal_', true).'.'.$ext;
    $uploadDir = '../uploads/cal/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    move_uploaded_file($_FILES['calories_img']['tmp_name'], $uploadDir.$calories_img);
}

// Update foods
$stmt = $conn->prepare("UPDATE foods SET name=?, history=?, recipe=?, description=?, calories=?, food_img=?, calories_img=? WHERE id=?");
$stmt->bind_param("ssssissi", $name, $history, $recipe, $description, $calories, $food_img, $calories_img, $id);

if ($stmt->execute()) {
    // ลบ emotion เก่า
    $conn->query("DELETE FROM food_emotions WHERE food_id=$id");
    // insert emotion ใหม่
    if(!empty($emotions)) {
        foreach($emotions as $eid){
            $stmt2 = $conn->prepare("INSERT INTO food_emotions (food_id, emotion_id) VALUES (?, ?)");
            $stmt2->bind_param("ii", $id, $eid);
            $stmt2->execute();
            $stmt2->close();
        }
    }
    // ลบ herbs เก่า
    $conn->query("DELETE FROM food_herbs WHERE food_id=$id");
    // insert herbs ใหม่
    if(!empty($herbs)) {
        foreach($herbs as $hid){
            $stmt3 = $conn->prepare("INSERT INTO food_herbs (food_id, herb_id) VALUES (?, ?)");
            $stmt3->bind_param("ii", $id, $hid);
            $stmt3->execute();
            $stmt3->close();
        }
    }
    echo json_encode(['success'=>true,'message'=>'แก้ไขอาหารเรียบร้อย']);
} else {
    echo json_encode(['success'=>false,'message'=>'เกิดข้อผิดพลาด']);
}
$stmt->close();
$conn->close();
