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
    $limit = 10;
    $offset = ($page - 1) * $limit;

    // Ensure page is at least 1
    if ($page < 1) $page = 1;

    try {
        // Count total records first
        $countSql = "SELECT COUNT(*) as total FROM suppliers";
        $countResult = $conn->query($countSql);
        
        if (!$countResult) {
            throw new Exception("Count query failed: " . $conn->error);
        }
        
        $totalRecords = $countResult->fetch_assoc()['total'];
        $totalPages = ceil($totalRecords / $limit);
        
        // Get suppliers with pagination
        $sql = "SELECT supplierID, supplierName, supplierAddress, supplierProduct, supplierContact 
                FROM suppliers 
                ORDER BY supplierID DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Generate table rows
        $tableRows = '';
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $tableRows .= '<tr>';
                $tableRows .= '<td>' . htmlspecialchars($row['supplierID']) . '</td>';
                $tableRows .= '<td>' . htmlspecialchars($row['supplierName']) . '</td>';
                $tableRows .= '<td>' . htmlspecialchars($row['supplierAddress']) . '</td>';
                $tableRows .= '<td>' . htmlspecialchars($row['supplierProduct']) . '</td>';
                $tableRows .= '<td>' . htmlspecialchars($row['supplierContact']) . '</td>';
                $tableRows .= '<td class="action-column">
                                <a href="edit_supplier_form.php?id=' . $row['supplierID'] . '" class="edit-btn" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button onclick="confirmDelete(' . $row['supplierID'] . ')" class="delete-btn" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                              </td>';
                $tableRows .= '</tr>';
            }
        } else {
            $tableRows = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: #666;">No suppliers found</td></tr>';
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