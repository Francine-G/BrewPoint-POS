<?php

    $supplierName = $_POST['supplierName'];
    $supplierAddress = $_POST['supplierAddress'];
    $supplierProduct = $_POST['supplierProduct'];
    $supplierContact = $_POST['supplierContact'];

    include("../database/db.php");

    $sql = "INSERT INTO suppliers (supplierName, supplierAddress,supplierProduct, supplierContact) 
    VALUES ('$supplierName', '$supplierAddress', '$supplierProduct', '$supplierContact')";

    if ($conn->query($sql) === TRUE) {
        header("location: ../supplier_details.php?msg=added");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

?>