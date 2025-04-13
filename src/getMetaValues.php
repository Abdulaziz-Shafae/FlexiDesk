<?php
include('db.php');

$type = $_GET['type'] ?? '';
$values = [];

if (in_array($type, ['Department', 'JobTitle'])) {
    $stmt = $conn->prepare("SELECT value FROM MetaValues WHERE type = ? ORDER BY value ASC");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $values[] = $row['value'];
    }
}

echo json_encode($values);
?>
