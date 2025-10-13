<?php
header('Content-Type: application/json');
include '../FilePHP/db.php';

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

// ลบไฟล์รูป
$result = $conn->query("SELECT food_img, calories_img FROM foods WHERE id=$id");
$row = $result->fetch_assoc();
if($row['food_img']) @unlink('../uploads/foods/'.$row['food_img']);
if($row['calories_img']) @unlink('../uploads/cal/'.$row['calories_img']);

$stmt = $conn->prepare("DELETE FROM foods WHERE id=?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success'=>true,'message'=>'ลบอาหารเรียบร้อย']);
} else {
    echo json_encode(['success'=>false,'message'=>'เกิดข้อผิดพลาด']);
}
$stmt->close();
$conn->close();
