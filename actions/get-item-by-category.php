<?php
// Include database connection
include("../database/db.php");

header('Content-Type: application/json');

if (isset($_POST['category']) && !empty($_POST['category'])) {
    $category = $_POST['category'];
    
    // Get items from selected category
    $stmt = $conn->prepare("SELECT itemName, currentQty, itemUnit FROM inventory WHERE itemCategory = ? ORDER BY itemName");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = array();
    while ($row = $result->fetch_assoc()) {
        $items[] = array(
            'itemName' => $row['itemName'],
            'currentQty' => $row['currentQty'],
            'itemUnit' => $row['itemUnit']
        );
    }
    
    $stmt->close();
    echo json_encode($items);
} else {
    echo json_encode(array());
}

$conn->close();
?>