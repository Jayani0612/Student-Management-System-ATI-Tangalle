<?php
header('Content-Type: application/json');

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'edusphere';

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    echo json_encode(['error' => 'Connection failed']);
    exit;
}

$result = mysqli_query($conn, "SELECT * FROM teachers");

$teachers = [];
while ($row = mysqli_fetch_assoc($result)) {
    $teachers[] = $row;
}

echo json_encode($teachers);
?>