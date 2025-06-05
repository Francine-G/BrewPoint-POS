<?php
// Include database connection
include("../database/db.php");

// Start session to get user information
session_start();

// Get current user (adjust based on your session structure)
$currentUser = $_SESSION['username'] ?? $_SESSION['user'] ?? 'Unknown User';

// Check if form is submitted
if (isset($_POST['add-stock'])) {
    $itemName = trim($_POST['itemName']);
    $itemCategory = $_POST['itemCategory'];
    $itemUnit = $_POST['itemUnit'];
    $minStock = (int)$_POST['minStock'];
    
    // Validate inputs
    if (empty($itemName) || empty($itemCategory) || empty($itemUnit) || $minStock < 0) {
        header("Location: ../add-item.php?error=empty_fields");
        exit();
    }
    
    // Check if item already exists
    $checkStmt = $conn->prepare("SELECT itemID FROM inventory WHERE itemName = ? AND itemCategory = ?");
    $checkStmt->bind_param("ss", $itemName, $itemCategory);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $checkStmt->close();
        header("Location: ../add-item.php?error=item_exists");
        exit();
    }
    $checkStmt->close();
    
    // Insert new item into inventory
    $insertStmt = $conn->prepare("INSERT INTO inventory (itemName, itemCategory, itemUnit, currentQty, minStockLevel, stockLevel, dateAdded) VALUES (?, ?, ?, 0, ?, 'No Stock', NOW())");
    $insertStmt->bind_param("sssi", $itemName, $itemCategory, $itemUnit, $minStock);
    
    if ($insertStmt->execute()) {
        $insertStmt->close();
        header("Location: ../inventory-details.php?success=item_added");
    } else {
        $insertStmt->close();
        header("Location: ../add-item.php?error=database_error");
    }
} else {
    header("Location: ../add-item.php");
}

$conn->close();
?>