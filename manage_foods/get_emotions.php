<?php
header('Content-Type: application/json');
include '../FilePHP/db.php';

$result = $conn->query("SELECT * FROM emotions ORDER BY id ASC");
$emotions = [];
while($row = $result->fetch_assoc()){
    $emotions[] = ['id'=>$row['id'], 'keyword'=>$row['keyword'], 'name'=>$row['name']];
}
echo json_encode($emotions);
$conn->close();
