<?php
$conn = new mysqli("localhost", "root", "", "brewpos"); 

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['id'];
$name = $_POST['supplierName'];
$address = $_POST['supplierAddress'];
$product = $_POST['supplierProduct'];
$contact = $_POST['supplierContact'];

$sql = "UPDATE suppliers SET supplierName = ?, supplierAddress = ?, supplierProduct = ?, supplierContact = ? WHERE supplierID = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error); 
}

$stmt->bind_param("ssssi", $name, $address, $product, $contact, $id);

if ($stmt->execute()) {
    header("Location: ../supplier_details.php?msg=edited");
} else {
    header("Location: ../supplier_details.php?msg=edit_error");
}

$stmt->close();
$conn->close();
exit;
?>