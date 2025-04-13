<?php
include('db.php');
$sql = "SELECT name FROM Departments ORDER BY name ASC";
$result = $conn->query($sql);

$departments = [];
while ($row = $result->fetch_assoc()) {
    $departments[] = $row['name'];
}
echo json_encode($departments);
?>
