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

    // รับข้อมูล JSON และไฟล์
    $input = $_POST;
    $profile_image = null;

    // ตรวจสอบข้อมูลที่จำเป็น
    $required_fields = ['first_name', 'last_name', 'email', 'phone', 'password', 'role', 'status'];
    foreach ($required_fields as $field) {
        if (empty($input[$field])) {
            throw new Exception("กรุณากรอก $field");
        }
    }

    // ตรวจสอบว่าอีเมลซ้ำหรือไม่
    $check_query = "SELECT id FROM users WHERE email = :email";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':email', $input['email']);
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

    // เข้ารหัสรหัสผ่าน
    $hashed_password = password_hash($input['password'], PASSWORD_DEFAULT);

    // เพิ่มข้อมูลผู้ใช้ใหม่
    $query = "INSERT INTO users (first_name, last_name, email, phone, password, role,  profile_image, created_at) 
              VALUES (:first_name, :last_name, :email, :phone, :password, :role, :profile_image, NOW())";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':first_name', $input['first_name']);
    $stmt->bindParam(':last_name', $input['last_name']);
    $stmt->bindParam(':email', $input['email']);
    $stmt->bindParam(':phone', $input['phone']);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':role', $input['role']);
    $stmt->bindParam(':profile_image', $profile_image);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'เพิ่มผู้ใช้เรียบร้อยแล้ว',
            'user_id' => $db->lastInsertId()
        ]);
    } else {
        throw new Exception("ไม่สามารถเพิ่มผู้ใช้ได้");
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>