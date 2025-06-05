<?php
// get-export-count.php - Place this in your actions/ folder
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "brewpos";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Database connection error: ' . $e->getMessage()]);
    exit;
}

// Get POST data
$startDate = $_POST['startDate'] ?? '';
$endDate = $_POST['endDate'] ?? '';
$category = $_POST['category'] ?? 'All';

// Validate dates
if (empty($startDate) || empty($endDate)) {
    echo json_encode(['success' => false, 'error' => 'Start date and end date are required']);
    exit;
}

try {
    // Build WHERE clause for filtering
    $where_conditions = [];
    $params = [];
    $param_types = "";

    // Date range filter
    $where_conditions[] = "DATE(o.order_date) BETWEEN ? AND ?";
    $params[] = $startDate;
    $params[] = $endDate;
    $param_types .= "ss";

    // Category filter
    if ($category != "All") {
        $where_conditions[] = "oi.drink_type = ?";
        $params[] = $category;
        $param_types .= "s";
    }

    $where_clause = "WHERE " . implode(" AND ", $where_conditions);

    // Count total records
    $countSql = "SELECT COUNT(*) as total 
                 FROM order_items oi
                 INNER JOIN orders o ON oi.order_id = o.order_id
                 $where_clause";
    
    $stmt = $conn->prepare($countSql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['total'];

    echo json_encode([
        'success' => true,
        'count' => (int)$count,
        'date_range' => [
            'start' => $startDate,
            'end' => $endDate
        ],
        'category' => $category
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Query failed: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>