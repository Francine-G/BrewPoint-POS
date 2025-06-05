<?php
// export-sales-data.php - Fixed version
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "brewpos";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception('Connection failed: ' . $conn->connect_error);
    }
    $conn->set_charset("utf8");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database connection error: ' . $e->getMessage()
    ]);
    exit;
}

// Check if this is a PDF download request
if (isset($_GET['action']) && $_GET['action'] === 'download_pdf') {
    // Direct PDF download (if you want to generate PDF server-side)
    try {
        // For now, we'll redirect to the client-side generation
        // You can implement server-side PDF generation here if needed
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Direct PDF download not implemented. Use client-side generation.'
        ]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'PDF generation error: ' . $e->getMessage()
        ]);
        exit;
    }
}

// Handle POST request for data export
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get POST data
    $input = json_decode(file_get_contents('php://input'), true);
    
    // If JSON decode failed, try to get from POST array
    if ($input === null) {
        $input = $_POST;
    }
    
    // Extract parameters with defaults
    $startDate = $input['startDate'] ?? $input['start_date'] ?? '';
    $endDate = $input['endDate'] ?? $input['end_date'] ?? '';
    $category = $input['category'] ?? $input['categoryFilter'] ?? 'All';
    $dateRange = $input['date_range'] ?? '';
    
    // If date_range is 'all', get all records
    if ($dateRange === 'all') {
        $startDate = '2020-01-01'; // Far back date
        $endDate = date('Y-m-d', strtotime('+1 day')); // Tomorrow
    }
    
    // If no dates provided, default to last 30 days
    if (empty($startDate) && empty($endDate)) {
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-30 days'));
    }
    
    // Validate date format if provided
    if (!empty($startDate) && !DateTime::createFromFormat('Y-m-d', $startDate)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid start date format. Use YYYY-MM-DD'
        ]);
        exit;
    }
    
    if (!empty($endDate) && !DateTime::createFromFormat('Y-m-d', $endDate)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid end date format. Use YYYY-MM-DD'
        ]);
        exit;
    }
    
    // Check if start date is before end date
    if (!empty($startDate) && !empty($endDate) && $startDate > $endDate) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Start date must be before end date'
        ]);
        exit;
    }
    
    try {
        // Build WHERE clause for filtering
        $where_conditions = [];
        $params = [];
        $param_types = "";
        
        // Date range filter
        if (!empty($startDate) && !empty($endDate)) {
            $where_conditions[] = "DATE(o.order_date) BETWEEN ? AND ?";
            $params[] = $startDate;
            $params[] = $endDate;
            $param_types .= "ss";
        }
        
        // Category filter
        if (!empty($category) && $category !== "All") {
            $where_conditions[] = "oi.drink_type = ?";
            $params[] = $category;
            $param_types .= "s";
        }
        
        $where_clause = count($where_conditions) > 0 ? "WHERE " . implode(" AND ", $where_conditions) : "";
        
        // Get sales data with better error handling
        $sql = "SELECT 
                    oi.order_id,
                    oi.product_name,
                    oi.quantity,
                    COALESCE(oi.add_ons, 'None') as add_ons,
                    oi.item_price,
                    o.order_date,
                    COALESCE(oi.drink_type, 'Unknown') as drink_type,
                    (oi.item_price * oi.quantity) as total_amount
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.order_id
                $where_clause
                ORDER BY o.order_date DESC
                LIMIT 1000"; // Limit to prevent memory issues
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        if (count($params) > 0) {
            $stmt->bind_param($param_types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        
        $salesData = [];
        $totalRevenue = 0;
        $totalQuantity = 0;
        $orderIds = [];
        
        while ($row = $result->fetch_assoc()) {
            // Ensure numeric values are properly formatted
            $quantity = (int)$row['quantity'];
            $itemPrice = (float)$row['item_price'];
            $totalAmount = (float)$row['total_amount'];
            
            $salesData[] = [
                'order_id' => $row['order_id'],
                'product_name' => $row['product_name'],
                'quantity' => $quantity,
                'add_ons' => $row['add_ons'],
                'item_price' => $itemPrice,
                'total_amount' => $totalAmount,
                'order_date' => $row['order_date'],
                'drink_type' => $row['drink_type']
            ];
            
            $totalRevenue += $totalAmount;
            $totalQuantity += $quantity;
            $orderIds[] = $row['order_id'];
        }
        
        // Calculate unique orders
        $uniqueOrders = count(array_unique($orderIds));
        $avgOrderValue = $uniqueOrders > 0 ? $totalRevenue / $uniqueOrders : 0;
        
        // Get top selling products
        $topProducts = [];
        if (count($salesData) > 0) {
            $top_products_sql = "SELECT 
                                    oi.product_name,
                                    COALESCE(oi.drink_type, 'Unknown') as drink_type,
                                    SUM(oi.quantity) as total_quantity,
                                    SUM(oi.item_price * oi.quantity) as total_revenue,
                                    COUNT(DISTINCT oi.order_id) as order_count
                                 FROM order_items oi
                                 INNER JOIN orders o ON oi.order_id = o.order_id
                                 $where_clause
                                 GROUP BY oi.product_name, oi.drink_type
                                 ORDER BY total_quantity DESC
                                 LIMIT 5";
            
            $top_stmt = $conn->prepare($top_products_sql);
            if ($top_stmt) {
                if (count($params) > 0) {
                    $top_stmt->bind_param($param_types, ...$params);
                }
                
                if ($top_stmt->execute()) {
                    $top_result = $top_stmt->get_result();
                    
                    while ($row = $top_result->fetch_assoc()) {
                        $topProducts[] = [
                            'product_name' => $row['product_name'],
                            'drink_type' => $row['drink_type'],
                            'total_quantity' => (int)$row['total_quantity'],
                            'total_revenue' => (float)$row['total_revenue'],
                            'order_count' => (int)$row['order_count']
                        ];
                    }
                }
                $top_stmt->close();
            }
        }
        
        // Prepare response
        $response = [
            'success' => true,
            'data' => $salesData,
            'summary' => [
                'total_orders' => $uniqueOrders,
                'total_items' => count($salesData),
                'total_quantity' => $totalQuantity,
                'total_revenue' => $totalRevenue,
                'avg_order_value' => $avgOrderValue,
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate
                ],
                'category_filter' => $category,
                'report_generated' => date('Y-m-d H:i:s')
            ],
            'top_products' => $topProducts,
            'metadata' => [
                'record_count' => count($salesData),
                'query_time' => date('Y-m-d H:i:s'),
                'parameters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'category' => $category
                ]
            ]
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Query failed: ' . $e->getMessage(),
            'debug_info' => [
                'sql_error' => $conn->error,
                'parameters' => $params ?? [],
                'where_clause' => $where_clause ?? ''
            ]
        ]);
    } finally {
        if (isset($stmt)) {
            $stmt->close();
        }
        $conn->close();
    }
    
} else {
    // Handle GET requests or invalid methods
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Use POST request.'
    ]);
}
?>