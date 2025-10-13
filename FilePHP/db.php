<?php
$servername = "localhost";      // ชื่อเซิร์ฟเวอร์ฐานข้อมูล
$username = "Admin00";           // ชื่อผู้ใช้ฐานข้อมูล
$password = "123456";            // รหัสผ่านฐานข้อมูล (ถ้าไม่มีให้เว้นว่าง)
$dbname = "foodie_moodie";      // ชื่อฐานข้อมูลที่ใช้

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("เชื่อมต่อฐานข้อมูลไม่สำเร็จ: " . $conn->connect_error);
}

?>
<?php
class Database {
    private $host = "localhost";
    private $db_name = "foodie_moodie";
    private $username = "Admin00";
    private $password = "123456";
    public $conn;

    public function getConnection(){
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception){
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>