<?php
// 1. เปิด error reporting เพื่อดีบัก (แก้ตอน dev เท่านั้น)
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// 2. ล้าง output ที่เกิดขึ้นก่อนหน้านี้ทั้งหมด
ob_start();
ob_clean();

header('Content-Type: application/json');

// 3. เช็ค login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

include '../FilePHP/db.php'; // เชื่อมฐานข้อมูล

$user_id = $_SESSION['user_id'];
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// 4. ตรวจสอบรหัสผ่านใหม่
if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'รหัสผ่านใหม่ไม่ตรงกัน']);
    exit;
}

// 5. ตรวจสอบข้อมูลจำเป็น
if ($first_name === '' || $last_name === '' || $phone === '') {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบ']);
    exit;
}

$profile_image_path = '';

// 6. อัปโหลดรูปถ้ามี
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_image']['tmp_name'];
    $fileName = $_FILES['profile_image']['name'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $allowedfileExtensions = ['jpg', 'jpeg', 'png'];
    if (!in_array($fileExtension, $allowedfileExtensions)) {
        echo json_encode(['success' => false, 'message' => 'ไฟล์รูปต้องเป็น JPG หรือ PNG เท่านั้น']);
        exit;
    }

    $newFileName = 'profile_' . $user_id . '_' . time() . '.' . $fileExtension;
    $uploadFileDir = __DIR__ . '/../uploads/';
    
    // สร้างโฟลเดอร์ถ้ายังไม่มี
    if (!is_dir($uploadFileDir)) {
        mkdir($uploadFileDir, 0755, true);
    }
    
    $dest_path = $uploadFileDir . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $dest_path)) {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์']);
        exit;
    }

    // ลบรูปเดิมถ้าไม่ใช่ default.jpg
    $sql_img = "SELECT profile_image FROM users WHERE id=?";
    $stmt_img = $conn->prepare($sql_img);
    $stmt_img->bind_param("i", $user_id);
    $stmt_img->execute();
    $result_img = $stmt_img->get_result();
    if ($result_img->num_rows > 0) {
        $row_img = $result_img->fetch_assoc();
        $old_img = $row_img['profile_image'];
        if ($old_img && $old_img !== 'uploads/default.jpg' && file_exists(__DIR__ . '/../' . $old_img)) {
            unlink(__DIR__ . '/../' . $old_img);
        }
    }
    $stmt_img->close();

    $profile_image_path = 'uploads/' . $newFileName; // relative path สำหรับเก็บฐานข้อมูล
} else {
    // ถ้าไม่ได้อัปโหลดรูป ให้เช็คว่าผู้ใช้ยังไม่มีรูปในฐานข้อมูล
    $sql_img = "SELECT profile_image FROM users WHERE id=?";
    $stmt_img = $conn->prepare($sql_img);
    $stmt_img->bind_param("i", $user_id);
    $stmt_img->execute();
    $result_img = $stmt_img->get_result();
    if ($result_img->num_rows > 0) {
        $row_img = $result_img->fetch_assoc();
        $old_img = $row_img['profile_image'];
        if (!$old_img || $old_img === '' || $old_img === NULL) {
            $profile_image_path = 'uploads/default.jpg';
        }
    }
    $stmt_img->close();
}

// ใช้ $conn ที่ได้จาก include db.php
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'เชื่อมต่อฐานข้อมูลล้มเหลว']);
    exit;
}

// 7. สร้าง SQL query แบบ dynamic
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

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare statement ล้มเหลว: ' . $conn->error]);
    exit;
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลเรียบร้อย']);
} else {
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล: ' . $stmt->error]);
}

$stmt->close();
$conn->close();

exit;
exit;
