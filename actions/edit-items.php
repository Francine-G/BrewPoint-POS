<?php
// Include database connection
include("../database/db.php");

// Start session to get user information
session_start();

// Get current user (adjust based on your session structure)
$currentUser = $_SESSION['username'] ?? $_SESSION['user'] ?? 'Unknown User';

// Check if form was submitted
if (isset($_POST['edit-item'])) {
    $itemID = intval($_POST['itemID']);
    $itemName = trim($_POST['itemName']);
    $itemCategory = $_POST['itemCategory'];
    $itemUnit = $_POST['itemUnit'];
    $minStock = (int)$_POST['minStock'];
    
    try {
        // Validate inputs
        if (empty($itemName) || empty($itemCategory) || empty($itemUnit) || $minStock < 0) {
            echo "<script>
                window.location.href = '../inventory-details.php';
            </script>";
            exit();
        }
        
        // Get current item data
        $currentDataStmt = $conn->prepare("SELECT itemName, itemCategory, itemUnit, minStockLevel, currentQty, stockLevel FROM inventory WHERE itemID = ?");
        $currentDataStmt->bind_param("i", $itemID);
        $currentDataStmt->execute();
        $currentResult = $currentDataStmt->get_result();
        $currentData = $currentResult->fetch_assoc();
        
        if (!$currentData) {
            echo "<script>
                window.location.href = '../inventory-details.php';
            </script>";
            exit();
        }
        
        // Check if another item with the same name and category already exists (excluding current item)
        $duplicateCheckStmt = $conn->prepare("SELECT itemID FROM inventory WHERE itemName = ? AND itemCategory = ? AND itemID != ?");
        $duplicateCheckStmt->bind_param("ssi", $itemName, $itemCategory, $itemID);
        $duplicateCheckStmt->execute();
        $duplicateResult = $duplicateCheckStmt->get_result();
        
        if ($duplicateResult->num_rows > 0) {
            $duplicateCheckStmt->close();
            
            echo "<script>
                window.location.href = '../inventory-details.php';
            </script>";
            exit();
        }
        $duplicateCheckStmt->close();
        
        // Determine new stock level based on current quantity and new minimum stock level
        $currentQty = $currentData['currentQty'];
        $newStockLevel = '';
        
        if ($currentQty == 0) {
            $newStockLevel = 'No Stock';
        } elseif ($currentQty <= $minStock) {
            $newStockLevel = 'Low Stock';
        } elseif ($currentQty <= ($minStock * 2)) {
            $newStockLevel = 'Moderate Stock';
        } else {
            $newStockLevel = 'Full Stock';
        }
        
        // Check if any items in stock_batches are nearing expiry (within 30 days)
        $expiryCheckStmt = $conn->prepare("SELECT COUNT(*) as expiring_count FROM stock_batches WHERE itemID = ? AND quantity > 0 AND expiryDate <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
        $expiryCheckStmt->bind_param("i", $itemID);
        $expiryCheckStmt->execute();
        $expiryResult = $expiryCheckStmt->get_result();
        $expiryData = $expiryResult->fetch_assoc();
        
        if ($expiryData['expiring_count'] > 0 && $currentQty > 0) {
            $newStockLevel = 'Expiry';
        }
        $expiryCheckStmt->close();
        
        // Update item in inventory
        $updateStmt = $conn->prepare("UPDATE inventory SET itemName = ?, itemCategory = ?, itemUnit = ?, minStockLevel = ?, stockLevel = ?, lastUpdated = NOW() WHERE itemID = ?");
        $updateStmt->bind_param("sssisi", $itemName, $itemCategory, $itemUnit, $minStock, $newStockLevel, $itemID);
        
        if ($updateStmt->execute()) {
            echo "<script>
                window.location.href = '../inventory-details.php?success=item_updated';
            </script>";
        } else {
            throw new Exception("Failed to update item: " . $conn->error);
        }
        
        $updateStmt->close();
        $currentDataStmt->close();
        
    } catch (Exception $e) {
        // Handle errors
        echo "<script>
            window.location.href = '../inventory-details.php';
        </script>";
    }
    
    $conn->close();
    
} else {
    // If someone tries to access this file directly without submitting the form
    header("Location: ../inventory-details.php");
    exit();
}
?>