<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
ob_start();
ob_clean();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

include '../FilePHP/db.php';

$user_id = $_SESSION['user_id'];
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// ตรวจสอบรหัสผ่าน
if ($new_password !== '' && $new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'รหัสผ่านใหม่ไม่ตรงกัน']);
    exit;
}

// ตรวจสอบข้อมูลจำเป็น
if ($first_name === '' || $last_name === '' || $phone === '') {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบ']);
    exit;
}

$profile_image_path = '';

// จัดการอัพโหลดรูป
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_image']['tmp_name'];
    $fileName = $_FILES['profile_image']['name'];
    $fileSize = $_FILES['profile_image']['size'];
    
    // ตรวจสอบขนาดไฟล์ (5MB)
    if ($fileSize > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'ไฟล์ใหญ่เกิน 5MB']);
        exit;
    }
    
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($fileExtension, $allowedfileExtensions)) {
        echo json_encode(['success' => false, 'message' => 'ไฟล์รูปต้องเป็น JPG, PNG หรือ GIF เท่านั้น']);
        exit;
    }

    // ตรวจสอบว่าเป็นไฟล์รูปจริง
    $imageInfo = getimagesize($fileTmpPath);
    if ($imageInfo === false) {
        echo json_encode(['success' => false, 'message' => 'ไฟล์ที่อัพโหลดไม่ใช่รูปภาพ']);
        exit;
    }

    $newFileName = 'profile_' . $user_id . '_' . time() . '.' . $fileExtension;
    $uploadFileDir = __DIR__ . '/../uploads/'; // แก้ไขเป็น path ที่ถูกต้อง
    
    // ตรวจสอบโฟลเดอร์
    if (!is_dir($uploadFileDir)) {
        if (!mkdir($uploadFileDir, 0755, true)) {
            echo json_encode(['success' => false, 'message' => 'ไม่สามารถสร้างโฟลเดอร์ uploads ได้']);
            exit;
        }
    }
    
    if (!is_writable($uploadFileDir)) {
        echo json_encode(['success' => false, 'message' => 'ไม่มีสิทธิ์เขียนไฟล์ในโฟลเดอร์ uploads']);
        exit;
    }
    
    $dest_path = $uploadFileDir . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $dest_path)) {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการย้ายไฟล์']);
        exit;
    }

    // ลบรูปเดิม
    $sql_img = "SELECT profile_image FROM users WHERE id=?";
    $stmt_img = $conn->prepare($sql_img);
    $stmt_img->bind_param("i", $user_id);
    $stmt_img->execute();
    $result_img = $stmt_img->get_result();
    if ($result_img->num_rows > 0) {
        $row_img = $result_img->fetch_assoc();
        $old_img = $row_img['profile_image'];
        if ($old_img && $old_img !== 'default.jpg' && file_exists(__DIR__ . '/../uploads/' . basename($old_img))) {
            unlink(__DIR__ . '/../uploads/' . basename($old_img));
        }
    }
    $stmt_img->close();

    $profile_image_path = $newFileName; // เก็บแค่ชื่อไฟล์
}

// อัพเดตฐานข้อมูล
$sql = "UPDATE users SET first_name=?, last_name=?, phone=?";
$params = [$first_name, $last_name, $phone];
$types = "sss";

if ($new_password !== '') {
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $sql .= ", password=?";
    $params[] = $hashed_password;
    $types .= "s";
}

if ($profile_image_path !== '') {
    $sql .= ", profile_image=?";
    $params[] = $profile_image_path;
    $types .= "s";
}

$sql .= " WHERE id=?";
$params[] = $user_id;
$types .= "i";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $response = ['success' => true, 'message' => 'อัปเดตข้อมูลเรียบร้อย'];
    if ($profile_image_path !== '') {
        $response['new_image'] = '/NLPTEST/uploads/' . $profile_image_path;
    }
    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
exit;
?>