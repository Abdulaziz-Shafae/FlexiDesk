<?php
include('db.php');
$data = json_decode(file_get_contents("php://input"), true);

$type = $data['type'] ?? '';
$value = trim($data['value']);

if (in_array($type, ['Department', 'JobTitle']) && $value !== '') {
    $stmt = $conn->prepare("INSERT IGNORE INTO MetaValues (type, value) VALUES (?, ?)");
    $stmt->bind_param("ss", $type, $value);
    $stmt->execute();
}
?>
