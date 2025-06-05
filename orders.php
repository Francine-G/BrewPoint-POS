<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "brewpos";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Function to get active orders with items
function getActiveOrders($conn) {
    $sql = "SELECT o.order_id, o.total_amount, o.status, o.order_date,
                   GROUP_CONCAT(
                       CONCAT(oi.product_name, ' (', oi.size, ') x', oi.quantity) 
                       SEPARATOR ', '
                   ) as items
            FROM orders o
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            WHERE o.status = 'in_progress'
            GROUP BY o.order_id, o.total_amount, o.status, o.order_date
            ORDER BY o.order_date DESC";
    
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

// Get active orders
$activeOrders = getActiveOrders($conn);
?>

<html> 
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <script src="https://kit.fontawesome.com/f4e628f07c.js" crossorigin="anonymous"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-straight/css/uicons-solid-straight.css'>
        <link rel="stylesheet" href="assets/css/Order-Details_Style.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <title>BrewPoint POS</title>
    </head>

    <body>
        <div class="container">

            <div class="sidebar">
                <div class="logo-content">
                    <img src="assets/img/logo.png" class="logo">
                </div>

                <div class="nav-bar">
                    <ul class="menu-content">
                        <li>
                            <a href="dashboard.php">
                                <span class="icon"><i class='bx bxs-dashboard'></i></span>
                                <span class="title"> Dashboard</span>
                            </a>
                        </li>

                        <li>
                            <a href="POSsystem.php">
                                <span class="icon"><i class='bx bxs-cart-add'></i></span>
                                <span class="title">POS System</span>
                             </a>
                        </li>

                        <li>
                            <a href="orders.php">
                                <span class="icon"><i class='bx bxs-shopping-bag-alt'></i></span>
                                <span class="title">Orders</span>
                            </a>
                        </li>
    
                        <li>
                            <a href="inventory.php">
                                <span class="icon"><i class='bx bxs-package'></i></span>
                                <span class="title">Inventory</span>
                            </a>
                        </li>

                        <li>
                            <a href="sales.php">
                                <span class="icon"><i class='bx bxs-report'></i></span>
                                <span class="title">Sales Reports</span>
                            </a>
                        </li>

                        <li>
                            <a href="supplier_details.php">
                                <span class="icon"><i class='bx bxs-user-account'></i></span>
                                <span class="title">Suppliers</span>
                            </a>
                        </li>

                    </ul>
                </div>
                 
                <div class="logout">
                    <a href="index.php">
                        <span><i class="fa-solid fa-right-from-bracket"></i></span>
                        <span class="title">Logout</span>
                    </a>
                </div>
            </div>

            <main class="content">
                <div class="header">
                    <h2>Orders Overview</h2>
                    
                    <div class="user-icon">
                        <i class="fa-solid fa-circle-user"></i>
                    </div>
                </div>
                
                <div class="main-body-content">
                    <div class="top-content">
                        <div class="topheader">
                            <h3>Active Orders</h3>
                        </div>
                        
                        <div class="box-card-content">
                            <?php if (empty($activeOrders)): ?>
                                <div class="no-orders">No active orders at the moment</div>
                            <?php else: ?>
                                <?php foreach ($activeOrders as $order): ?>
                                    <div class="boxes">
                                        <div class="row order-header">
                                            <span class="order-id"><strong><?php echo htmlspecialchars($order['order_id']); ?></strong></span>
                                            <span class="status-badge in-progress">In Progress</span>
                                        </div>
                                        <div class="row order-details"> 
                                            <div class="items-list">
                                                <p><?php echo htmlspecialchars($order['items']); ?></p>
                                            </div>
                                        </div>
                                        <div class="row order-footer">
                                            <span class="total-amount">₱<?php echo number_format($order['total_amount'], 2); ?></span>
                                            <button class="btn-complete" onclick="completeOrder('<?php echo $order['order_id']; ?>')">
                                                Complete
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="bottom-content">
                        <div class="bottom-top-content">
                            <div class="bottom-header">
                                <h2>Order History</h2>
                            </div>
                            <div class="bottom-functions">
                                <input type="date" id="dateFilter" onchange="filterByDate()">
                            </div>
                        </div>
                        <div class="bottom-body-content">
                           <table id="ordersTable">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                               <tbody id="ordersTableBody">
                                   <!-- Data will be loaded via AJAX -->
                               </tbody>
                           </table>
                           <div class="pagination" id="paginationContainer">
                               <!-- Pagination will be loaded via AJAX -->
                           </div>
                        </div>
                    </div>
                </div>
                
            </main>
        </div>

        <script src="assets/js/Sidebar-nav.js"></script>
        <script>
            let currentPage = 1;
            let selectedDate = '';

            // Load orders on page load
            $(document).ready(function() {
                loadOrders(1);
            });

            // Function to load orders via AJAX
            function loadOrders(page = 1, date = '') {
                currentPage = page;
                selectedDate = date;
                
                console.log('Loading orders with:', {page: page, date: date}); // Debug line
                
                $.ajax({
                    url: 'actions/load-order-history.php',
                    type: 'POST',
                    data: {
                        page: page,
                        date: date
                    },
                    success: function(response) {
                        console.log('Raw response:', response); // Debug line
                        try {
                            const data = JSON.parse(response);
                            console.log('Parsed data:', data); // Debug line
                            $('#ordersTableBody').html(data.table_rows);
                            $('#paginationContainer').html(data.pagination);
                        } catch (e) {
                            console.error('Error parsing JSON:', e, response);
                            $('#ordersTableBody').html('<tr><td colspan="5">Errorrrrr loaaaaaading data</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', xhr.responseText); // Debug line
                        $('#ordersTableBody').html('<tr><td colspan="5">Error loading data</td></tr>');
                    }
                });
            }

            // Function to filter by date
            function filterByDate() {
                const dateValue = $('#dateFilter').val();
                loadOrders(1, dateValue);
            }

            // Function to complete an order
            function completeOrder(orderId) {
                if (confirm('Mark this order as completed?')) {
                    $.ajax({
                        url: 'actions/complete-order.php',
                        type: 'POST',
                        data: { order_id: orderId },
                        success: function(response) {
                            try {
                                const result = JSON.parse(response);
                                if (result.success) {
                                    location.reload(); // Refresh page to update active orders
                                } else {
                                    alert('Error: ' + result.message);
                                }
                            } catch (e) {
                                alert('Error processing request');
                            }
                        },
                        error: function() {
                            alert('Error completing order');
                        }
                    });
                }
            }

            // Function to change page
            function changePage(page) {
                loadOrders(page, selectedDate);
            }

            // Function to export to PDF (placeholder)
            function exportToPDF() {
                // You can implement PDF export functionality here
                alert('PDF export functionality to be implemented');
            }
        </script>

    </body>
</html>