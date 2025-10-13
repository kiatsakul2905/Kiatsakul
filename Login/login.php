<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');
session_start();

include '../FilePHP/db.php';

// ฟังก์ชันส่ง JSON response
function sendResponse($success, $message, $data = []) {
    $response = array_merge(['success' => $success, 'message' => $message], $data);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// ตรวจสอบ method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Method not allowed');
}

// ตรวจสอบข้อมูลที่ส่งมา
if (!isset($_POST['email'], $_POST['password'])) {
    sendResponse(false, 'กรุณากรอกอีเมลและรหัสผ่าน');
}

$email = trim($_POST['email']);
$password = $_POST['password'];
$admin_mode = isset($_POST['admin_mode']) && $_POST['admin_mode'] === '1';

// ตรวจสอบความถูกต้องของอีเมล
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, 'รูปแบบอีเมลไม่ถูกต้อง');
}

// ตรวจสอบความยาวรหัสผ่าน
if (strlen($password) < 1) {
    sendResponse(false, 'กรุณากรอกรหัสผ่าน');
}

try {
    // ถ้าเป็น admin mode ให้ตรวจสอบเฉพาะ admin
    if ($admin_mode) {
        $sql = "SELECT id, password, first_name, last_name, role FROM users WHERE email = ? AND role = 'admin'";
    } else {
        // ปกติให้ตรวจสอบทุก role
        $sql = "SELECT id, password, first_name, last_name, role FROM users WHERE email = ? AND role IS NOT NULL";
    }
    
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        sendResponse(false, 'เกิดข้อผิดพลาดในการเตรียมคำสั่ง: ' . $conn->error);
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        if ($admin_mode) {
            sendResponse(false, 'ไม่พบบัญชีผู้ดูแลระบบ หรืออีเมล/รหัสผ่านไม่ถูกต้อง');
        } else {
            sendResponse(false, 'ไม่พบผู้ใช้ในระบบหรืออีเมล/รหัสผ่านไม่ถูกต้อง');
        }
    }

    $user = $result->fetch_assoc();

    // ตรวจสอบรหัสผ่าน
    if (!password_verify($password, $user['password'])) {
        sendResponse(false, 'อีเมลหรือรหัสผ่านไม่ถูกต้อง');
    }

    // ถ้าเป็น admin mode แต่ user ไม่ใช่ admin
    if ($admin_mode && $user['role'] !== 'admin') {
        sendResponse(false, 'บัญชีนี้ไม่มีสิทธิ์ผู้ดูแลระบบ');
    }

    // ตรวจสอบ role
    if (empty($user['role'])) {
        sendResponse(false, 'บัญชีผู้ใช้ยังไม่ได้รับการอนุมัติ');
    }

    // เก็บข้อมูลใน session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['login_time'] = time();
    
    // เพิ่ม flag สำหรับ admin
    if ($user['role'] === 'admin') {
        $_SESSION['is_admin'] = true;
        // บันทึก log การเข้าสู่ระบบ admin
        error_log("Admin login successful: " . $email . " at " . date('Y-m-d H:i:s'));
    }

    // กำหนด redirect URL ตาม role
    $redirect_url = '../Home/index.html'; // default สำหรับ user
    $role_display = 'ผู้ใช้';

    switch ($user['role']) {
        case 'admin':
            $redirect_url = '../Admin/admin_dashboard.html';
            $role_display = 'ผู้ดูแลระบบ';
            break;
        case 'user':
            $redirect_url = '../Home/index.html';
            $role_display = 'ผู้ใช้';
            break;
        default:
            $redirect_url = '../Home/index.html';
            $role_display = 'ผู้ใช้';
    }

    // ส่งข้อมูลกลับ
    sendResponse(true, 'เข้าสู่ระบบสำเร็จ', [
        'role' => $user['role'],
        'role_display' => $role_display,
        'redirect_url' => $redirect_url,
        'is_admin' => ($user['role'] === 'admin'),
        'admin_mode' => $admin_mode,
        'user' => [
            'id' => $user['id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'role' => $user['role']
        ]
    ]);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    sendResponse(false, 'เกิดข้อผิดพลาดในระบบ กรุณาลองใหม่อีกครั้ง');
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>
