<?php

include "../database/db.php";

$productID = $_POST['id'];
$productName = $_POST['productName'];
$productCategory = $_POST['productCategory'];

$sql = "UPDATE products SET productName = ?, productCategory = ? WHERE productID = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error); 
}

$stmt->bind_param("ssi", $productName, $productCategory, $productID);

if ($stmt->execute()) {
    header("Location: ../product-modification.php?msg=edited");
} else {
    header("Location: ../product-modification.php?msg=edit_error");
}

$stmt->close();
$conn->close();
exit;
?>