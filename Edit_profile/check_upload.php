<?php
echo "<h2>การตั้งค่า PHP Upload</h2>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "<br>";
echo "max_execution_time: " . ini_get('max_execution_time') . "<br>";
echo "memory_limit: " . ini_get('memory_limit') . "<br>";

$upload_dir = __DIR__ . '/uploads/';
echo "<br><h3>ตรวจสอบโฟลเดอร์ uploads:</h3>";
echo "Path: " . $upload_dir . "<br>";
echo "Exists: " . (is_dir($upload_dir) ? 'Yes' : 'No') . "<br>";
echo "Writable: " . (is_writable($upload_dir) ? 'Yes' : 'No') . "<br>";

if (!is_dir($upload_dir)) {
    if (mkdir($upload_dir, 0755, true)) {
        echo "สร้างโฟลเดอร์สำเร็จ<br>";
    } else {
        echo "ไม่สามารถสร้างโฟลเดอร์ได้<br>";
    }
}
?>