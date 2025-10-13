<?php
// สร้างโฟลเดอร์ uploads ที่ตำแหน่งที่ถูกต้อง
$correct_upload_dir = __DIR__ . '/../uploads/';
$wrong_upload_dir = __DIR__ . '/uploads/';

echo "<h2>แก้ไขโครงสร้างโฟลเดอร์ uploads</h2>";

// ตรวจสอบโฟลเดอร์ที่ถูกต้อง
echo "<h3>ตรวจสอบโฟลเดอร์ที่ถูกต้อง:</h3>";
echo "Path: " . $correct_upload_dir . "<br>";
echo "Exists: " . (is_dir($correct_upload_dir) ? 'Yes' : 'No') . "<br>";

// สร้างโฟลเดอร์ที่ถูกต้อง
if (!is_dir($correct_upload_dir)) {
    if (mkdir($correct_upload_dir, 0755, true)) {
        echo "<span style='color: green;'>✅ สร้างโฟลเดอร์ที่ถูกต้องสำเร็จ</span><br>";
    } else {
        echo "<span style='color: red;'>❌ ไม่สามารถสร้างโฟลเดอร์ได้</span><br>";
    }
} else {
    echo "<span style='color: blue;'>ℹ️ โฟลเดอร์มีอยู่แล้ว</span><br>";
}

// ตรวจสอบสิทธิ์การเขียน
echo "Writable: " . (is_writable($correct_upload_dir) ? 'Yes' : 'No') . "<br>";

// สร้างไฟล์ default.jpg ถ้ายังไม่มี
$default_image_path = $correct_upload_dir . 'default.jpg';
if (!file_exists($default_image_path)) {
    // สร้างรูป default แบบง่าย ๆ
    $image = imagecreate(200, 200);
    $bg_color = imagecolorallocate($image, 240, 240, 240);
    $text_color = imagecolorallocate($image, 100, 100, 100);
    
    imagestring($image, 5, 50, 90, 'Default', $text_color);
    imagestring($image, 5, 55, 110, 'User', $text_color);
    
    if (imagejpeg($image, $default_image_path)) {
        echo "<span style='color: green;'>✅ สร้างไฟล์ default.jpg สำเร็จ</span><br>";
    } else {
        echo "<span style='color: red;'>❌ ไม่สามารถสร้างไฟล์ default.jpg ได้</span><br>";
    }
    imagedestroy($image);
} else {
    echo "<span style='color: blue;'>ℹ️ ไฟล์ default.jpg มีอยู่แล้ว</span><br>";
}

// ย้ายไฟล์จากโฟลเดอร์เก่า (ถ้ามี)
if (is_dir($wrong_upload_dir)) {
    echo "<h3>ย้ายไฟล์จากโฟลเดอร์เก่า:</h3>";
    $files = glob($wrong_upload_dir . '*');
    foreach ($files as $file) {
        if (is_file($file)) {
            $filename = basename($file);
            $new_path = $correct_upload_dir . $filename;
            if (rename($file, $new_path)) {
                echo "✅ ย้าย $filename สำเร็จ<br>";
            } else {
                echo "❌ ไม่สามารถย้าย $filename ได้<br>";
            }
        }
    }
    
    // ลบโฟลเดอร์เก่า
    if (rmdir($wrong_upload_dir)) {
        echo "<span style='color: green;'>✅ ลบโฟลเดอร์เก่าสำเร็จ</span><br>";
    }
}

echo "<br><h3>สถานะปัจจุบัน:</h3>";
echo "โฟลเดอร์ที่ถูกต้อง: " . realpath($correct_upload_dir) . "<br>";
echo "ไฟล์ในโฟลเดอร์: <br>";
$files = glob($correct_upload_dir . '*');
foreach ($files as $file) {
    echo "- " . basename($file) . "<br>";
}
?>