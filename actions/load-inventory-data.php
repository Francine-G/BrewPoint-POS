<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "brewpos";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        ob_clean();
        echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
        exit;
    }
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['error' => 'Database connection error: ' . $e->getMessage()]);
    exit;
}

// Get parameters
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$category = isset($_POST['category']) ? $_POST['category'] : 'All';
$stock_level = isset($_POST['stock_level']) ? $_POST['stock_level'] : 'All';
$limit = 5;
$offset = ($page - 1) * $limit;

// Ensure page is at least 1
if ($page < 1) $page = 1;

// Function to determine stock level color
function getStockLevelClass($stockLevel, $expiryDate = null) {
    if ($expiryDate && strtotime($expiryDate) <= strtotime('+7 days')) {
        return 'expiry-warning';
    }
    
    switch($stockLevel) {
        case 'No Stock': return 'no-stock';
        case 'Low Stock': return 'low-stock';
        case 'Moderate Stock': return 'moderate-stock';
        case 'Full Stock': return 'full-stock';
        case 'Expiring Soon': return 'expiry-warning';
        default: return '';
    }
}

// Function to format expiry date with warning
function formatExpiryDate($expiryDate) {
    if (!$expiryDate) {
        return '<span class="no-expiry">No batches</span>';
    }
    
    $days_until_expiry = floor((strtotime($expiryDate) - time()) / (60 * 60 * 24));
    
    if ($days_until_expiry < 0) {
        return '<span class="expired">Expired (' . date('M d, Y', strtotime($expiryDate)) . ')</span>';
    } elseif ($days_until_expiry <= 7) {
        return '<span class="expiry-critical">' . date('M d, Y', strtotime($expiryDate)) . ' (' . $days_until_expiry . ' days)</span>';
    } elseif ($days_until_expiry <= 14) {
        return '<span class="expiry-warning">' . date('M d, Y', strtotime($expiryDate)) . ' (' . $days_until_expiry . ' days)</span>';
    } else {
        return '<span class="expiry-normal">' . date('M d, Y', strtotime($expiryDate)) . '</span>';
    }
}

try {
    // Count total records first
    $count_sql = "SELECT COUNT(DISTINCT i.itemID) as total FROM inventory i";
    
    // For expiry filter, we need to join with stock_batches
    if ($stock_level == 'Expiry') {
        $count_sql = "SELECT COUNT(DISTINCT i.itemID) as total 
                      FROM inventory i 
                      LEFT JOIN stock_batches sb ON i.itemID = sb.itemID AND sb.quantity > 0";
    }
    
    $count_sql .= " WHERE 1=1";
    $count_params = [];
    $count_types = "";
    
    // Add search conditions
    if (!empty($search)) {
        $count_sql .= " AND i.itemName LIKE ?";
        $count_params[] = '%' . $search . '%';
        $count_types .= "s";
    }
    
    if ($category != 'All') {
        $count_sql .= " AND i.itemCategory = ?";
        $count_params[] = $category;
        $count_types .= "s";
    }
    
    if ($stock_level != 'All') {
        if ($stock_level == 'Expiry') {
            $count_sql .= " AND sb.expiryDate <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND sb.expiryDate IS NOT NULL";
        } else {
            $count_sql .= " AND i.stockLevel = ?";
            $count_params[] = $stock_level;
            $count_types .= "s";
        }
    }
    
    // Execute count query
    if (!empty($count_params)) {
        $count_stmt = $conn->prepare($count_sql);
        if (!$count_stmt) {
            throw new Exception("Count prepare failed: " . $conn->error);
        }
        $count_stmt->bind_param($count_types, ...$count_params);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
    } else {
        $count_result = $conn->query($count_sql);
    }
    
    $totalRecords = $count_result->fetch_assoc()['total'];
    $totalPages = ceil($totalRecords / $limit);
    
    // Get inventory items with batches
    $sql = "SELECT 
        i.itemID,
        i.itemName,
        i.currentQty,
        i.itemUnit,
        i.itemCategory,
        i.stockLevel,
        MIN(sb.expiryDate) as earliest_expiry,
        GROUP_CONCAT(
            CONCAT(COALESCE(sb.quantity, 0), ' (', DATE_FORMAT(sb.expiryDate, '%Y-%m-%d'), ')') 
            ORDER BY sb.expiryDate 
            SEPARATOR ', '
        ) as batch_details
    FROM inventory i
    LEFT JOIN stock_batches sb ON i.itemID = sb.itemID AND sb.quantity > 0
    WHERE 1=1";
    
    $params = [];
    $types = "";
    
    // Add the same search conditions
    if (!empty($search)) {
        $sql .= " AND i.itemName LIKE ?";
        $params[] = '%' . $search . '%';
        $types .= "s";
    }
    
    if ($category != 'All') {
        $sql .= " AND i.itemCategory = ?";
        $params[] = $category;
        $types .= "s";
    }
    
    if ($stock_level != 'All') {
        if ($stock_level == 'Expiry') {
            $sql .= " AND sb.expiryDate <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND sb.expiryDate IS NOT NULL";
        } else {
            $sql .= " AND i.stockLevel = ?";
            $params[] = $stock_level;
            $types .= "s";
        }
    }
    
    $sql .= " GROUP BY i.itemID, i.itemName, i.currentQty, i.itemUnit, i.itemCategory, i.stockLevel
              ORDER BY i.itemName ASC
              LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Generate table rows
    $tableRows = '';
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $batch_details = $row['batch_details'] ? $row['batch_details'] : '';
            
            $tableRows .= '<tr>';
            $tableRows .= '<td>';
            $tableRows .= '<strong>' . htmlspecialchars($row['itemName']) . '</strong>';
            if ($batch_details) {
                $tableRows .= '<div class="batch-details" title="Quantity (Expiry Date)">';
                $tableRows .= 'Batches: ' . htmlspecialchars($batch_details);
                $tableRows .= '</div>';
            }
            $tableRows .= '</td>';
            $tableRows .= '<td>' . number_format($row['currentQty']) . '</td>';
            $tableRows .= '<td>' . htmlspecialchars($row['itemUnit']) . '</td>';
            $tableRows .= '<td>' . htmlspecialchars($row['itemCategory']) . '</td>';
            $tableRows .= '<td>' . formatExpiryDate($row['earliest_expiry']) . '</td>';
            $tableRows .= '<td>';
            $tableRows .= '<span class="stock-indicator ' . getStockLevelClass($row['stockLevel'], $row['earliest_expiry']) . '">';
            $tableRows .= htmlspecialchars($row['stockLevel']);
            $tableRows .= '</span>';
            $tableRows .= '</td>';
            $tableRows .= '<td>';
            $tableRows .= '<div class="action-buttons">';
            $tableRows .= '<button class="edit-item-btn" type="button" data-item-id="' . $row['itemID'] . '" name="edit-btn"><i class="bx bx-edit-alt"></i></button>';
            $tableRows .= ' | ';
            $tableRows .= '<button class="batch-item-btn" type="button" data-item-id="' . $row['itemID'] . '" name="batch-btn"><i class="bx bx-list-ul"></i></button>';
            $tableRows .= ' | ';
            $tableRows .= '<button class="delete-item-btn" type="button" data-item-id="' . $row['itemID'] . '" name="delete-btn"><i class="bx bx-trash"></i></button>';
            $tableRows .= '</div>';
            $tableRows .= '</td>';
            $tableRows .= '</tr>';
        }
    } else {
        $tableRows = '<tr><td colspan="7" style="text-align: center; padding: 20px; color: #666;">No inventory items found</td></tr>';
    }
    
    // Generate pagination
    $pagination = '';
    if ($totalPages > 1) {
        $pagination .= '<div class="pagination-info">Page ' . $page . ' of ' . $totalPages . ' (' . $totalRecords . ' total records)</div>';
        
        // Previous button
        if ($page > 1) {
            $pagination .= '<button class="pagination-btn" onclick="changePage(' . ($page - 1) . ')">Previous</button>';
        } else {
            $pagination .= '<button class="pagination-btn" disabled>Previous</button>';
        }
        
        // Page numbers (show max 5 pages)
        $startPage = max(1, $page - 2);
        $endPage = min($totalPages, $page + 2);
        
        for ($i = $startPage; $i <= $endPage; $i++) {
            $activeClass = ($i == $page) ? ' active' : '';
            $pagination .= '<button class="pagination-btn' . $activeClass . '" onclick="changePage(' . $i . ')">' . $i . '</button>';
        }
        
        // Next button
        if ($page < $totalPages) {
            $pagination .= '<button class="pagination-btn" onclick="changePage(' . ($page + 1) . ')">Next</button>';
        } else {
            $pagination .= '<button class="pagination-btn" disabled>Next</button>';
        }
    }
    
    // Clean any output buffer before sending JSON
    ob_clean();
    
    // Return JSON response
    $response = [
        'success' => true,
        'table_rows' => $tableRows,
        'pagination' => $pagination,
        'total_records' => (int)$totalRecords,
        'current_page' => (int)$page,
        'total_pages' => (int)$totalPages
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
}

$conn->close();
?>