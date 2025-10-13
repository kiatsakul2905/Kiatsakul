<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
ob_start();
ob_clean();

header('Content-Type: application/json');

// เก็บ log การทำงาน
$debug_log = [];

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

include '../FilePHP/db.php';

$user_id = $_SESSION['user_id'];
$debug_log[] = "User ID: " . $user_id;

$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$debug_log[] = "Form data received: " . json_encode($_POST);
$debug_log[] = "Files data: " . json_encode($_FILES);

// ตรวจสอบรหัสผ่าน
if ($new_password !== '' && $new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'รหัสผ่านใหม่ไม่ตรงกัน', 'debug' => $debug_log]);
    exit;
}

// ตรวจสอบข้อมูลจำเป็น
if ($first_name === '' || $last_name === '' || $phone === '') {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบ', 'debug' => $debug_log]);
    exit;
}

$profile_image_path = '';
$file_uploaded = false;

// จัดการอัพโหลดรูป
if (isset($_FILES['profile_image'])) {
    $debug_log[] = "File upload detected";
    $debug_log[] = "Upload error code: " . $_FILES['profile_image']['error'];
    
    if ($_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $debug_log[] = "File upload OK";
        
        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $fileName = $_FILES['profile_image']['name'];
        $fileSize = $_FILES['profile_image']['size'];
        
        $debug_log[] = "File name: " . $fileName;
        $debug_log[] = "File size: " . $fileSize;
        $debug_log[] = "Temp path: " . $fileTmpPath;
        
        // ตรวจสอบขนาดไฟล์ (5MB)
        if ($fileSize > 5 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'ไฟล์ใหญ่เกิน 5MB', 'debug' => $debug_log]);
            exit;
        }
        
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $debug_log[] = "File extension: " . $fileExtension;

        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($fileExtension, $allowedfileExtensions)) {
            echo json_encode(['success' => false, 'message' => 'ไฟล์รูปต้องเป็น JPG, PNG หรือ GIF เท่านั้น', 'debug' => $debug_log]);
            exit;
        }

        // ตรวจสอบว่าเป็นไฟล์รูปจริง
        $imageInfo = getimagesize($fileTmpPath);
        if ($imageInfo === false) {
            echo json_encode(['success' => false, 'message' => 'ไฟล์ที่อัพโหลดไม่ใช่รูปภาพ', 'debug' => $debug_log]);
            exit;
        }
        $debug_log[] = "Image validation passed";

        $newFileName = 'profile_' . $user_id . '_' . time() . '.' . $fileExtension;
        $uploadFileDir = __DIR__ . '/../uploads/';
        $debug_log[] = "New filename: " . $newFileName;
        $debug_log[] = "Upload directory: " . $uploadFileDir;
        
        // ตรวจสอบโฟลเดอร์
        if (!is_dir($uploadFileDir)) {
            if (!mkdir($uploadFileDir, 0755, true)) {
                echo json_encode(['success' => false, 'message' => 'ไม่สามารถสร้างโฟลเดอร์ uploads ได้', 'debug' => $debug_log]);
                exit;
            }
            $debug_log[] = "Upload directory created";
        }
        
        if (!is_writable($uploadFileDir)) {
            echo json_encode(['success' => false, 'message' => 'ไม่มีสิทธิ์เขียนไฟล์ในโฟลเดอร์ uploads', 'debug' => $debug_log]);
            exit;
        }
        $debug_log[] = "Upload directory is writable";
        
        $dest_path = $uploadFileDir . $newFileName;
        $debug_log[] = "Destination path: " . $dest_path;

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $debug_log[] = "File moved successfully";
            $file_uploaded = true;
            
            // ลบรูปเดิม
            $sql_img = "SELECT profile_image FROM users WHERE id=?";
            $stmt_img = $conn->prepare($sql_img);
            $stmt_img->bind_param("i", $user_id);
            $stmt_img->execute();
            $result_img = $stmt_img->get_result();
            if ($result_img->num_rows > 0) {
                $row_img = $result_img->fetch_assoc();
                $old_img = $row_img['profile_image'];
                $debug_log[] = "Old image in DB: " . $old_img;
                
                if ($old_img && $old_img !== 'default.jpg' && strpos($old_img, 'profile_') !== false) {
                    $old_file_path = $uploadFileDir . basename($old_img);
                    if (file_exists($old_file_path)) {
                        unlink($old_file_path);
                        $debug_log[] = "Old file deleted: " . $old_file_path;
                    }
                }
            }
            $stmt_img->close();

            // เก็บชื่อไฟล์ในรูปแบบที่ database คาดหวัง
            $profile_image_path = $newFileName;
            $debug_log[] = "Profile image path set to: " . $profile_image_path;
        } else {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการย้ายไฟล์', 'debug' => $debug_log]);
            exit;
        }
    } else {
        $debug_log[] = "No file uploaded or upload error: " . $_FILES['profile_image']['error'];
    }
} else {
    $debug_log[] = "No file in request";
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
    $debug_log[] = "Password will be updated";
}

if ($profile_image_path !== '') {
    $sql .= ", profile_image=?";
    $params[] = $profile_image_path;
    $types .= "s";
    $debug_log[] = "Profile image will be updated to: " . $profile_image_path;
} else {
    $debug_log[] = "No profile image to update";
}

$sql .= " WHERE id=?";
$params[] = $user_id;
$types .= "i";

$debug_log[] = "Final SQL: " . $sql;
$debug_log[] = "Parameters: " . json_encode($params);
$debug_log[] = "Types: " . $types;

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error, 'debug' => $debug_log]);
    exit;
}

$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $debug_log[] = "SQL executed successfully";
    $debug_log[] = "Affected rows: " . $stmt->affected_rows;
    
    // ตรวจสอบข้อมูลในฐานข้อมูลหลังอัพเดต
    $check_sql = "SELECT profile_image FROM users WHERE id=?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result->num_rows > 0) {
        $check_row = $check_result->fetch_assoc();
        $debug_log[] = "Current profile_image in DB: " . $check_row['profile_image'];
    }
    $check_stmt->close();
    
    $response = ['success' => true, 'message' => 'อัปเดตข้อมูลเรียบร้อย', 'debug' => $debug_log];
    if ($file_uploaded && $profile_image_path !== '') {
        $response['new_image'] = '/NLPTEST/uploads/' . $profile_image_path;
        $response['file_uploaded'] = true;
    }
    echo json_encode($response);
} else {
    $debug_log[] = "SQL execution failed: " . $stmt->error;
    echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล: ' . $stmt->error, 'debug' => $debug_log]);
}

$stmt->close();
$conn->close();
exit;
?>