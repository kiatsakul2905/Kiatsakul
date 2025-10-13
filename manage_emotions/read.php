<?php
header('Content-Type: application/json; charset=utf-8');
include '../FilePHP/db.php';

$result = $conn->query("SELECT id, keyword, name FROM emotions ORDER BY id ASC");
$emotions = [];
while ($row = $result->fetch_assoc()) {
    $emotions[] = $row;
}

echo json_encode(['records' => $emotions]);
