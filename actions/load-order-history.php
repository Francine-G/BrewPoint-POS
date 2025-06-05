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
    $date = isset($_POST['date']) ? $_POST['date'] : '';
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Ensure page is at least 1
    if ($page < 1) $page = 1;

    try {
        // Count total records first
        $countSql = "SELECT COUNT(DISTINCT o.order_id) as total FROM orders o WHERE o.status = 'completed'";
        $countParams = [];
        
        if (!empty($date)) {
            $countSql .= " AND DATE(o.order_date) = ?";
            $countParams[] = $date;
        }
        
        if (!empty($countParams)) {
            $countStmt = $conn->prepare($countSql);
            $countStmt->bind_param("s", ...$countParams);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
        } else {
            $countResult = $conn->query($countSql);
        }
        
        $totalRecords = $countResult->fetch_assoc()['total'];
        $totalPages = ceil($totalRecords / $limit);
        
        // Get orders with items
        $sql = "SELECT o.order_id, o.total_amount, o.status, o.order_date,
                    GROUP_CONCAT(
                        CONCAT(COALESCE(oi.product_name, 'Unknown'), ' (', COALESCE(oi.size, 'N/A'), ') x', COALESCE(oi.quantity, 0)) 
                        SEPARATOR ', '
                    ) as items
                FROM orders o
                LEFT JOIN order_items oi ON o.order_id = oi.order_id
                WHERE o.status = 'completed'";
        
        $params = [];
        $types = "";
        
        if (!empty($date)) {
            $sql .= " AND DATE(o.order_date) = ?";
            $params[] = $date;
            $types .= "s";
        }
        
        $sql .= " GROUP BY o.order_id, o.total_amount, o.status, o.order_date
                ORDER BY o.order_date DESC
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
                $formattedDate = date('M d, Y H:i', strtotime($row['order_date']));
                $items = $row['items'] ? $row['items'] : 'No items';
                
                $tableRows .= '<tr>';
                $tableRows .= '<td>' . htmlspecialchars($row['order_id']) . '</td>';
                $tableRows .= '<td>' . htmlspecialchars($items) . '</td>';
                $tableRows .= '<td>₱' . number_format((float)$row['total_amount'], 2) . '</td>';
                $tableRows .= '<td><span class="status-badge completed">Completed</span></td>';
                $tableRows .= '<td>' . $formattedDate . '</td>';
                $tableRows .= '</tr>';
            }
        } else {
            $tableRows = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: #666;">No completed orders found</td></tr>';
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