<?php
// functions/get-item-batches.php
header('Content-Type: application/json');

// Database connection
include('../database/db.php');

// Check if itemId is provided
if (!isset($_GET['itemId']) || empty($_GET['itemId'])) {
    echo json_encode(['success' => false, 'message' => 'Item ID is required']);
    exit;
}

$itemId = intval($_GET['itemId']);

try {
    // Query to get all batches for the item
    $sql = "SELECT 
                sb.batchId,
                sb.quantity,
                sb.expiryDate,
                sb.dateAdded,
                i.itemName,
                i.itemUnit
            FROM stock_batches sb
            INNER JOIN inventory i ON sb.itemID = i.itemID
            WHERE sb.itemID = ? AND sb.quantity > 0
            ORDER BY sb.expiryDate ASC";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    
    $stmt->bind_param("i", $itemId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $batches = array();
    while ($row = $result->fetch_assoc()) {
        $batches[] = array(
            'batchId' => $row['batchId'],
            'quantity' => $row['quantity'],
            'expiryDate' => $row['expiryDate'],
            'dateAdded' => $row['dateAdded'],
            'itemName' => $row['itemName'],
            'itemUnit' => $row['itemUnit']
        );
    }
    
    $stmt->close();
    
    echo json_encode([
        'success' => true, 
        'batches' => $batches,
        'totalBatches' => count($batches)
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Close database connection
$conn->close();
?>