<?php
// Include database connection
include("../database/db.php");

// Start session to get user information
session_start();

// Simple logging function
function simpleLog($message) {
    error_log("[STOCKOUT DEBUG] " . $message);
    // Also log to a file if error_log doesn't work
    file_put_contents('../debug_stockout.log', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

// Get current user
$currentUser = $_SESSION['username'] ?? $_SESSION['user'] ?? 'Unknown User';

// Check if form is submitted
if (isset($_POST['out-stock'])) {
    simpleLog("Stock out request received");
    
    // Get form data
    $itemName = trim($_POST['itemName']);
    $itemQty = (int)$_POST['itemQty'];
    $itemCategory = $_POST['itemCategory'];
    $itemDeduction = $_POST['itemDeduction'];
    $notes = trim($_POST['notes']) ?? '';
    
    simpleLog("Form data - Item: $itemName, Qty: $itemQty, Category: $itemCategory");
    
    // Basic validation
    if (empty($itemName) || $itemQty <= 0 || empty($itemCategory)) {
        simpleLog("Validation failed - empty fields");
        header("Location: ../stock-out.php?error=empty_fields");
        exit();
    }
    
    // Test database connection
    if (!$conn) {
        simpleLog("Database connection failed: " . mysqli_connect_error());
        header("Location: ../stock-out.php?error=db_connection");
        exit();
    }
    
    simpleLog("Database connection OK");
    
    try {
        // Get item details
        $sql = "SELECT itemID, currentQty, minStockLevel, itemUnit FROM inventory WHERE itemName = ? AND itemCategory = ?";
        $getItemStmt = $conn->prepare($sql);
        
        if (!$getItemStmt) {
            throw new Exception("Failed to prepare item query: " . $conn->error);
        }
        
        $getItemStmt->bind_param("ss", $itemName, $itemCategory);
        
        if (!$getItemStmt->execute()) {
            throw new Exception("Failed to execute item query: " . $getItemStmt->error);
        }
        
        $result = $getItemStmt->get_result();
        
        if ($result->num_rows == 0) {
            $getItemStmt->close();
            simpleLog("Item not found in inventory");
            header("Location: ../stock-out.php?error=item_not_found");
            exit();
        }
        
        $item = $result->fetch_assoc();
        $itemID = $item['itemID'];
        $currentQty = $item['currentQty'];
        $minStockLevel = $item['minStockLevel'];
        $itemUnit = $item['itemUnit'];
        $getItemStmt->close();
        
        simpleLog("Item found - ID: $itemID, Current Qty: $currentQty");
        
        // Check sufficient stock
        if ($currentQty < $itemQty) {
            simpleLog("Insufficient stock - Available: $currentQty, Requested: $itemQty");
            header("Location: ../stock-out.php?error=insufficient_stock");
            exit();
        }
        
        // Check if stock_batches table exists and has data for this item
        $checkBatchSql = "SELECT COUNT(*) as batch_count, COALESCE(SUM(quantity), 0) as total_batch_qty FROM stock_batches WHERE itemID = ?";
        $checkBatchStmt = $conn->prepare($checkBatchSql);
        
        if (!$checkBatchStmt) {
            throw new Exception("Failed to prepare batch check query: " . $conn->error);
        }
        
        $checkBatchStmt->bind_param("i", $itemID);
        $checkBatchStmt->execute();
        $batchCheckResult = $checkBatchStmt->get_result();
        $batchInfo = $batchCheckResult->fetch_assoc();
        $checkBatchStmt->close();
        
        simpleLog("Batch info - Count: {$batchInfo['batch_count']}, Total Qty: {$batchInfo['total_batch_qty']}");
        
        // Start transaction
        $conn->begin_transaction();
        simpleLog("Transaction started");
        
        $newQty = $currentQty - $itemQty;
        
        // Determine stock level
        if ($newQty == 0) {
            $stockLevel = 'No Stock';
        } elseif ($newQty <= $minStockLevel) {
            $stockLevel = 'Low Stock';
        } elseif ($newQty <= ($minStockLevel * 2)) {
            $stockLevel = 'Moderate Stock';
        } else {
            $stockLevel = 'Full Stock';
        }
        
        // If item has batches, update them using FIFO
       if ($batchInfo['batch_count'] > 0 && $batchInfo['total_batch_qty'] > 0) {
            simpleLog("Processing batches using FIFO method");
            
            $remainingQty = $itemQty;
            $batchSql = "SELECT batchID, quantity FROM stock_batches WHERE itemID = ? AND quantity > 0 ORDER BY expiryDate ASC, dateAdded ASC";
            $batchStmt = $conn->prepare($batchSql);
            
            if (!$batchStmt) {
                throw new Exception("Failed to prepare batch selection: " . $conn->error);
            }
            
            $batchStmt->bind_param("i", $itemID);
            $batchStmt->execute();
            $batchResult = $batchStmt->get_result();
            
            // FIXED: Proper while loop condition
            while (($batch = $batchResult->fetch_assoc()) && $remainingQty > 0) {
                $batchID = $batch['batchID'];
                $batchQty = $batch['quantity'];
                
                simpleLog("Processing batch $batchID with quantity $batchQty, remaining to remove: $remainingQty");
                
                if ($batchQty <= $remainingQty) {
                    // Remove entire batch
                    $newBatchQty = 0;
                    $removedQty = $batchQty;
                } else {
                    // Partial removal
                    $newBatchQty = $batchQty - $remainingQty;
                    $removedQty = $remainingQty;
                }
                
                // Update batch quantity
                $updateBatchSql = "UPDATE stock_batches SET quantity = ? WHERE batchID = ?";
                $updateBatchStmt = $conn->prepare($updateBatchSql);
                
                if (!$updateBatchStmt) {
                    throw new Exception("Failed to prepare batch update: " . $conn->error);
                }
                
                $updateBatchStmt->bind_param("ii", $newBatchQty, $batchID);
                
                if (!$updateBatchStmt->execute()) {
                    throw new Exception("Failed to update batch $batchID: " . $updateBatchStmt->error);
                }
                
                $updateBatchStmt->close();
                $remainingQty -= $removedQty;
                
                simpleLog("Updated batch $batchID: removed $removedQty, new batch qty: $newBatchQty, remaining to remove: $remainingQty");
                
                // If we've removed all needed quantity, break out of loop
                if ($remainingQty <= 0) {
                    break;
                }
            }
            
            $batchStmt->close();
            
            // Verify that we've removed the correct amount
            if ($remainingQty > 0) {
                throw new Exception("Could not remove all requested quantity. $remainingQty units remaining.");
            }
            
            simpleLog("Batch processing completed successfully");
        } else {
            simpleLog("No batches to process - updating inventory only");
        }
        
        // Update main inventory
        $updateInventorySql = "UPDATE inventory SET currentQty = ?, stockLevel = ?, lastUpdated = NOW() WHERE itemID = ?";
        $updateInventoryStmt = $conn->prepare($updateInventorySql);
        
        if (!$updateInventoryStmt) {
            throw new Exception("Failed to prepare inventory update: " . $conn->error);
        }
        
        $updateInventoryStmt->bind_param("isi", $newQty, $stockLevel, $itemID);
        
        if (!$updateInventoryStmt->execute()) {
            throw new Exception("Failed to update inventory: " . $updateInventoryStmt->error);
        }
        
        $updateInventoryStmt->close();
        simpleLog("Inventory updated - New qty: $newQty, Stock level: $stockLevel");
        
        // Commit transaction
        $conn->commit();
        simpleLog("Transaction committed successfully");
        
        header("Location: ../inventory-details.php?success=stock_removed");
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $errorMsg = $e->getMessage();
        simpleLog("ERROR: " . $errorMsg);
        
        header("Location: ../stock-out.php?error=database_error&debug=" . urlencode($errorMsg));
    }
    
} else {
    simpleLog("No form submission detected");
    header("Location: ../stock-out.php");
}

$conn->close();
?>