<?php
require 'db_connection.php';

if (isset($_GET['type'])) {
    $type = $_GET['type'];
    $stmt = $conn->prepare("SELECT name_food, calories_food FROM db_food WHERE type_food = ?");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $result = $stmt->get_result();
    $foods = [];
    while ($row = $result->fetch_assoc()) {
        $foods[] = $row;
    }
    echo json_encode($foods);
}
?>
