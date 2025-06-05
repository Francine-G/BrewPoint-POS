<?php
$conn = new mysqli("localhost", "root", "", "brewpos");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: ../supplier_details.php?msg=delete_error");
    exit;
}

$sql = "DELETE FROM suppliers WHERE supplierID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../supplier_details.php?msg=deleted");
} else {
    header("Location: ../supplier_details.php?msg=delete_error");
}
exit;
