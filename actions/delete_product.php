<?php
$conn = new mysqli("localhost", "root", "", "brewpos");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$productID = $_GET['id'] ?? null;

if (!$productID) {
    header("Location: ../supplier_details.php?msg=delete_error");
    exit;
}

$sql = "DELETE FROM products WHERE productID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productID);

if ($stmt->execute()) {
    header("Location: ../product-modification.php?msg=deleted");
} else {
    header("Location: ../product-modification.php?msg=delete_error");
}
exit;
