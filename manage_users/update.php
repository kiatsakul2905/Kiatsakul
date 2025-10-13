<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include '../FilePHP/db.php';
include 'User.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // รับข้อมูลจาก form-data
    $input = $_POST;
    $profile_image = null;

    if (empty($input['id'])) {
        throw new Exception("ไม่พบ ID ผู้ใช้");
    }

    $required_fields = ['first_name', 'last_name', 'email', 'phone', 'role'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("กรุณากรอก $field");
        }
    }

    // ตรวจสอบว่าอีเมลซ้ำหรือไม่ (ยกเว้นผู้ใช้คนนี้)
    $check_query = "SELECT id FROM users WHERE email = :email AND id != :id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':email', $input['email']);
    $check_stmt->bindParam(':id', $input['id']);
    $check_stmt->execute();

    if ($check_stmt->rowCount() > 0) {
        throw new Exception("อีเมลนี้มีอยู่ในระบบแล้ว");
    }

    // อัปโหลดรูปถ้ามี
    if (!empty($_FILES['profile_image']['name'])) {
        $target_dir = "../uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid("profile_", true) . "." . $ext;
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            $profile_image = $filename;
        } else {
            throw new Exception("อัปโหลดรูปไม่สำเร็จ");
        }
    }

    // สร้าง query สำหรับอัพเดท
    $fields = [
        "first_name = :first_name",
        "last_name = :last_name",
        "email = :email",
        "phone = :phone",
        "role = :role"
    ];
    if (!empty($input['password'])) {
        $fields[] = "password = :password";
    }
    if ($profile_image) {
        $fields[] = "profile_image = :profile_image";
    }
    $query = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = :id";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $input['id']);
    $stmt->bindParam(':first_name', $input['first_name']);
    $stmt->bindParam(':last_name', $input['last_name']);
    $stmt->bindParam(':email', $input['email']);
    $stmt->bindParam(':phone', $input['phone']);
    $stmt->bindParam(':role', $input['role']);

    if (!empty($input['password'])) {
        $hashed_password = password_hash($input['password'], PASSWORD_DEFAULT);
        $stmt->bindParam(':password', $hashed_password);
    }
    if ($profile_image) {
        $stmt->bindParam(':profile_image', $profile_image);
    }

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'แก้ไขข้อมูลผู้ใช้เรียบร้อยแล้ว'
        ]);
    } else {
        throw new Exception("ไม่สามารถแก้ไขข้อมูลผู้ใช้ได้");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>