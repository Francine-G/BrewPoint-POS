<?php
// File: actions/get-filtered-suppliers.php
// Add error reporting at the top for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add CORS headers if needed
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "brewpos";

// Log the request for debugging
error_log("Export request received: " . print_r($_POST, true));

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    
    // Test database connection
    if ($conn->ping()) {
        error_log("Database connection successful");
    }
    
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database connection error: ' . $e->getMessage()]);
    exit;
}

// Get filter parameter
$filter = isset($_POST['filter']) ? trim($_POST['filter']) : 'all';
error_log("Filter received: " . $filter);

try {
    // Build SQL query based on filter
    if ($filter === 'all') {
        $sql = "SELECT supplierID, supplierName, supplierAddress, supplierProduct, supplierContact 
                FROM suppliers 
                ORDER BY supplierID DESC";
        $stmt = $conn->prepare($sql);
    } else {
        // Filter by product category - more flexible matching
        $sql = "SELECT supplierID, supplierName, supplierAddress, supplierProduct, supplierContact 
                FROM suppliers 
                WHERE LOWER(supplierProduct) LIKE LOWER(?) 
                ORDER BY supplierID DESC";
        $stmt = $conn->prepare($sql);
        $searchTerm = '%' . $filter . '%';
        $stmt->bind_param("s", $searchTerm);
        error_log("Filtering by: " . $searchTerm);
    }
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    error_log("Query executed, found " . $result->num_rows . " rows");
    
    // Fetch all suppliers
    $suppliers = [];
    while ($row = $result->fetch_assoc()) {
        $suppliers[] = [
            'id' => (int)$row['supplierID'],
            'name' => $row['supplierName'],
            'address' => $row['supplierAddress'],
            'product' => $row['supplierProduct'],
            'contact' => $row['supplierContact']
        ];
    }
    
    $response = [
        'success' => true,
        'suppliers' => $suppliers,
        'count' => count($suppliers),
        'filter_applied' => $filter
    ];
    
    error_log("Response prepared with " . count($suppliers) . " suppliers");
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Query error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
}

$conn->close();
?>