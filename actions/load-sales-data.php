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

    // Get parameters - handle both POST and GET for debugging
    $page = 1;
    $category = 'All';
    $date = 'All';
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
        $category = isset($_POST['category']) ? $_POST['category'] : 'All';
        $date = isset($_POST['date']) ? $_POST['date'] : 'All';
    } else {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $category = isset($_GET['category']) ? $_GET['category'] : 'All';
        $date = isset($_GET['date']) ? $_GET['date'] : 'All';
    }
    
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Ensure page is at least 1
    if ($page < 1) $page = 1;

    try {
        // Build WHERE clause for filtering
        $where_conditions = [];
        $params = [];
        $param_types = "";

        // Category filter - make sure this matches your database column exactly
        if ($category != "All") {
            $where_conditions[] = "oi.drink_type = ?";
            $params[] = $category;
            $param_types .= "s";
        }

        // Date filter
        if ($date != "All") {
            switch ($date) {
                case "Today":
                    $where_conditions[] = "DATE(o.order_date) = CURDATE()";
                    break;
                case "This Week":
                    $where_conditions[] = "YEARWEEK(o.order_date, 1) = YEARWEEK(CURDATE(), 1)";
                    break;
                case "This Month":
                    $where_conditions[] = "YEAR(o.order_date) = YEAR(CURDATE()) AND MONTH(o.order_date) = MONTH(CURDATE())";
                    break;
                case "Last Month":
                    $where_conditions[] = "YEAR(o.order_date) = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH(o.order_date) = MONTH(CURDATE() - INTERVAL 1 MONTH)";
                    break;
            }
        }

        $where_clause = "";
        if (!empty($where_conditions)) {
            $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        }

        // Count total records first
        $countSql = "SELECT COUNT(*) as total 
                     FROM order_items oi
                     INNER JOIN orders o ON oi.order_id = o.order_id
                     $where_clause";
        
        if (!empty($params)) {
            $countStmt = $conn->prepare($countSql);
            if (!empty($param_types)) {
                $countStmt->bind_param($param_types, ...$params);
            }
            $countStmt->execute();
            $countResult = $countStmt->get_result();
        } else {
            $countResult = $conn->query($countSql);
        }
        
        $totalRecords = $countResult->fetch_assoc()['total'];
        $totalPages = ceil($totalRecords / $limit);
        
        // Get sales data with pagination
        $sql = "SELECT 
                    oi.order_id,
                    oi.product_name,
                    oi.quantity,
                    oi.add_ons,
                    oi.item_price,
                    o.order_date,
                    oi.drink_type
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.order_id
                $where_clause
                ORDER BY o.order_date DESC
                LIMIT ? OFFSET ?";

        // Prepare parameters for the main query
        $mainParams = $params;
        $mainParamTypes = $param_types;
        $mainParams[] = $limit;
        $mainParams[] = $offset;
        $mainParamTypes .= "ii";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        if (!empty($mainParams)) {
            $stmt->bind_param($mainParamTypes, ...$mainParams);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Generate table rows
        $tableRows = '';
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $formattedDate = date('M d, Y H:i', strtotime($row['order_date']));
                $addOns = $row['add_ons'] ? $row['add_ons'] : 'None';
                
                $tableRows .= '<tr>';
                $tableRows .= '<td>' . htmlspecialchars($row['order_id']) . '</td>';
                $tableRows .= '<td>' . htmlspecialchars($row['product_name']) . '</td>';
                $tableRows .= '<td>' . htmlspecialchars($row['quantity']) . '</td>';
                $tableRows .= '<td>' . htmlspecialchars($addOns) . '</td>';
                $tableRows .= '<td>₱' . number_format((float)$row['item_price'], 2) . '</td>';
                $tableRows .= '<td>' . $formattedDate . '</td>';
                $tableRows .= '</tr>';
            }
        } else {
            $tableRows = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: #666;">No sales data found for the selected filters</td></tr>';
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
            'total_pages' => (int)$totalPages,
            'debug' => [
                'category' => $category,
                'date' => $date,
                'page' => $page,
                'where_clause' => $where_clause
            ]
        ];
        
        echo json_encode($response);
        
    } catch (Exception $e) {
        ob_clean();
        echo json_encode(['error' => 'Query failed: ' . $e->getMessage()]);
    }

    $conn->close();
?>