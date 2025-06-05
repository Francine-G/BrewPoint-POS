<?php
// delete-items.php
// Include database connection
require_once("../database/db_connection.php");

// Start session to get user information
if (!session_id()) {
    session_start();
}

// Function to get item details with batch information
function getItemDetailsWithBatches($pdo, $itemID) {
    try {
        // Get item details
        $itemStmt = $pdo->prepare("
            SELECT i.*, 
                   COUNT(sb.batchID) as total_batches,
                   SUM(sb.quantity) as total_batch_qty
            FROM inventory i 
            LEFT JOIN stock_batches sb ON i.itemID = sb.itemID AND sb.quantity > 0
            WHERE i.itemID = ?
            GROUP BY i.itemID
        ");
        $itemStmt->execute([$itemID]);
        return $itemStmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting item details: " . $e->getMessage());
        return false;
    }
}

// Get current user
$currentUser = $_SESSION['username'] ?? 'Unknown User';

// Check if we have an item ID from GET request (from redirect) or POST request (from form)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $itemID = $_GET['id'];
} elseif (isset($_POST['delete-item']) && isset($_POST['itemID'])) {
    $itemID = $_POST['itemID'];
} else {
    // No valid item ID provided
    header("Location: ../inventory-details.php");
    exit();
}

try {
    // First, get item details for confirmation
    $itemDetails = getItemDetailsWithBatches($pdo, $itemID);
    
    if ($itemDetails) {
        $itemName = $itemDetails['itemName'];
        $itemCategory = $itemDetails['itemCategory'];
        
        // Delete related stock batches first (to maintain referential integrity)
        $deleteBatchesStmt = $pdo->prepare("DELETE FROM stock_batches WHERE itemID = ?");
        $deleteBatchesStmt->execute([$itemID]);
        
        // Delete the item from inventory
        $deleteItemStmt = $pdo->prepare("DELETE FROM inventory WHERE itemID = ?");
        $deleteItemStmt->execute([$itemID]);
        
        // Redirect with success message
        echo "<script>
            alert('Item \"{$itemName}\" and all associated stock batches deleted successfully!');
            window.location.href = '../inventory-details.php';
        </script>";
        
    } else {
        // Item not found
        echo "<script>
            alert('Item not found.');
            window.location.href = '../inventory-details.php';
        </script>";
    }
    
} catch (PDOException $e) {
    // Handle database errors
    echo "<script>
        alert('Error deleting item: " . addslashes($e->getMessage()) . "');
        window.location.href = '../inventory-details.php';
    </script>";
}
?>