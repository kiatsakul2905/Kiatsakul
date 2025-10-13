<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once '../FilePHP/db.php';
include_once 'User.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $user = new User($db);
    $stmt = $user->read();
    $num = $stmt->rowCount();

    if($num > 0) {
        $users_arr = array();
        $users_arr["records"] = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user_item = array(
                "id" => $row['id'],
                "first_name" => $row['first_name'],
                "last_name" => $row['last_name'],
                "email" => $row['email'],
                "role" => $row['role'],
                "phone" => $row['phone'],
                "profile_image" => $row['profile_image'],
                "created_at" => $row['created_at']
            );

            array_push($users_arr["records"], $user_item);
        }

        http_response_code(200);
        echo json_encode($users_arr);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "No users found."));
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(array("message" => "Database error: " . $e->getMessage()));
}
?>
