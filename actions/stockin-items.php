<?php
// Include database connection
include("../database/db.php");

// Start session to get user information
session_start();

// Get current user (adjust based on your session structure)
$currentUser = $_SESSION['username'] ?? $_SESSION['user'] ?? 'Unknown User';

// Check if form is submitted
if (isset($_POST['add-stock'])) {
    $itemName = $_POST['itemName'];
    $itemQty = (int)$_POST['itemQty'];
    $itemCategory = $_POST['itemCategory'];
    $expiryDate = $_POST['expiryDate'];
    
    // Validate inputs
    if (empty($itemName) || $itemQty <= 0 || empty($itemCategory) || empty($expiryDate)) {
        header("Location: ../stock-in.php?error=empty_fields");
        exit();
    }
    
    // Check if expiry date is not in the past
    $currentDate = date('Y-m-d');
    if ($expiryDate < $currentDate) {
        header("Location: ../stock-in.php?error=expired_date");
        exit();
    }
    
    // Get item ID from inventory
    $getItemStmt = $conn->prepare("SELECT itemID, currentQty, minStockLevel FROM inventory WHERE itemName = ? AND itemCategory = ?");
    $getItemStmt->bind_param("ss", $itemName, $itemCategory);
    $getItemStmt->execute();
    $result = $getItemStmt->get_result();
    
    if ($result->num_rows == 0) {
        $getItemStmt->close();
        header("Location: ../stock-in.php?error=item_not_found");
        exit();
    }
    
    $item = $result->fetch_assoc();
    $itemID = $item['itemID'];
    $currentQty = $item['currentQty'];
    $minStockLevel = $item['minStockLevel'];
    $getItemStmt->close();
    
    // Insert into stock_batches table (for FIFO tracking)
    $batchStmt = $conn->prepare("INSERT INTO stock_batches (itemID, quantity, expiryDate, dateAdded) VALUES (?, ?, ?, NOW())");
    $batchStmt->bind_param("iis", $itemID, $itemQty, $expiryDate);
    
    if ($batchStmt->execute()) {
        $batchStmt->close();
        
        // Calculate new quantity and stock level
        $newQty = $currentQty + $itemQty;
        
        // Determine stock level
        $stockLevel = '';
        if ($newQty == 0) {
            $stockLevel = 'No Stock';
        } elseif ($newQty <= $minStockLevel) {
            $stockLevel = 'Low Stock';
        } elseif ($newQty <= ($minStockLevel * 2)) {
            $stockLevel = 'Moderate Stock';
        } else {
            $stockLevel = 'Full Stock';
        }
        
        // Check for items nearing expiry (within 30 days)
        $thirtyDaysFromNow = date('Y-m-d', strtotime('+30 days'));
        if ($expiryDate <= $thirtyDaysFromNow) {
            $stockLevel = 'Expiry';
        }
        
        // Update inventory table
        $updateStmt = $conn->prepare("UPDATE inventory SET currentQty = ?, stockLevel = ?, lastUpdated = NOW() WHERE itemID = ?");
        $updateStmt->bind_param("isi", $newQty, $stockLevel, $itemID);
        
        if ($updateStmt->execute()) {
            $updateStmt->close();
            header("Location: ../inventory-details.php?success=stock_added");
        } else {
            $updateStmt->close();
            header("Location: ../stock-in.php?error=database_error");
        }
    } else {
        $batchStmt->close();
        header("Location: ../stock-in.php?error=database_error");
    }
    
} else {
    header("Location: ../stock-in.php");
}

$conn->close();
?>