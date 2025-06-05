
<?php

include('actions/session_security.php');

// Require user to be logged in
requireLogin();

$userInfo = isset($_SESSION['uname']) && !empty($_SESSION['uname']) ? $_SESSION['uname'] : 'Guest';
// Include database connection
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

// Query for inventory status counts
$inventory_status_sql = "SELECT 
    SUM(CASE WHEN stockLevel = 'Full Stock' THEN 1 ELSE 0 END) as full_stock,
    SUM(CASE WHEN stockLevel = 'Moderate Stock' THEN 1 ELSE 0 END) as moderate_stock,
    SUM(CASE WHEN stockLevel = 'Low Stock' THEN 1 ELSE 0 END) as low_stock,
    SUM(CASE WHEN stockLevel = 'No Stock' THEN 1 ELSE 0 END) as out_of_stock
    FROM inventory";

$inventory_status_result = $conn->query($inventory_status_sql);
$inventory_status = $inventory_status_result->fetch_assoc();

// Query for expiring soon items (items expiring within 30 days)
$expiring_soon_sql = "SELECT COUNT(DISTINCT i.itemID) as expiring_soon
    FROM inventory i
    LEFT JOIN stock_batches sb ON i.itemID = sb.itemID AND sb.quantity > 0
    WHERE sb.expiryDate <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
    AND sb.expiryDate >= CURDATE()
    AND sb.expiryDate IS NOT NULL";

$expiring_soon_result = $conn->query($expiring_soon_sql);
$expiring_soon = $expiring_soon_result->fetch_assoc();

// Set default values if no data found
$full_stock_count = $inventory_status['full_stock'] ?? 0;
$moderate_stock_count = $inventory_status['moderate_stock'] ?? 0;
$low_stock_count = $inventory_status['low_stock'] ?? 0;
$out_of_stock_count = $inventory_status['out_of_stock'] ?? 0;
$expiring_soon_count = $expiring_soon['expiring_soon'] ?? 0;

// Query for sales chart data (daily sales for last 7 days)
$chart_sql = "SELECT 
    DATE(o.order_date) as sale_date,
    SUM(o.total_amount) as daily_total,
    COUNT(DISTINCT o.order_id) as order_count
    FROM orders o
    WHERE o.order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(o.order_date)
    ORDER BY sale_date ASC";

$chart_result = $conn->query($chart_sql);
$chart_data = [];
while ($row = $chart_result->fetch_assoc()) {
    $chart_data[] = $row;
}

// Query for recent orders (last 10 orders)
$recent_orders_sql = "SELECT o.order_id, o.total_amount, o.status, o.order_date,
    GROUP_CONCAT(
    CONCAT(COALESCE(oi.product_name, 'Unknown'), ' (', COALESCE(oi.size, 'N/A'), ') x', COALESCE(oi.quantity, 0)) 
    SEPARATOR ', '
    ) as items
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.status = 'completed'
    GROUP BY o.order_id, o.total_amount, o.status, o.order_date
    ORDER BY o.order_date DESC
    LIMIT 10";

$recent_orders_result = $conn->query($recent_orders_sql);
$recent_orders = [];
if ($recent_orders_result && $recent_orders_result->num_rows > 0) {
    while ($row = $recent_orders_result->fetch_assoc()) {
        $recent_orders[] = $row;
    }
}

// Query for top 3 popular drinks of the day
$popular_drinks_sql = "SELECT 
    oi.product_name,
    SUM(oi.quantity) as total_quantity,
    SUM(oi.item_price * oi.quantity) as total_revenue,
    AVG(oi.item_price) as avg_price
    FROM order_items oi
    INNER JOIN orders o ON oi.order_id = o.order_id
    WHERE DATE(o.order_date) = CURDATE()
    GROUP BY oi.product_name
    ORDER BY total_quantity DESC, total_revenue DESC
    LIMIT 3";

$popular_drinks_result = $conn->query($popular_drinks_sql);
$popular_drinks = [];
if ($popular_drinks_result && $popular_drinks_result->num_rows > 0) {
    while ($row = $popular_drinks_result->fetch_assoc()) {
        $popular_drinks[] = $row;
    }
}
?>

<html> 
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <script src="https://kit.fontawesome.com/f4e628f07c.js" crossorigin="anonymous"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://kit.fontawesome.com/f4e628f07c.js" crossorigin="anonymous"></script>
        <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
        <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-straight/css/uicons-solid-straight.css'>
        <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-straight/css/uicons-regular-straight.css'>
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
        <link href='https://cdn.boxicons.com/fonts/brands/boxicons-brands.min.css' rel='stylesheet'>
        <link rel="stylesheet" href="assets/css/Dashboard-Style.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <title>BrewPoint Dashboard</title>

    </head>

    <body>
        
        <div class="container">

            <div class="sidebar">
                <div class="logo-content">
                    <img src = "assets/img/logo.png" class = "logo">
                </div>

                <div class="nav-bar">
                    <ul class = "menu-content">
                        <li>
                            <a href="dashboard.php">
                                <span class="icon"><i class='bx bxs-dashboard'></i></span>
                                <span class="title"> Dashboard</span>
                            </a>
                        </li>

                        <li>
                            <a href ="POSsystem.php">
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

                    
                <div class = "logout">
                    <a href="index.php">
                        <span><i class="fa-solid fa-right-from-bracket"></i></span>
                        <span class = "title">Logout</span>
                    </a>
                </div>
            </div>

            <div class="content">
                <div class = "header">
                     <h2>WELCOME, <?php echo isset($userInfo) ? htmlspecialchars($userInfo) : 'Guest'; ?>!</h2>

                    <div class = "user-icon">
                        <i class="fa-solid fa-circle-user"></i>
                    </div>
                </div>

                <div class = "main-contents">
                    <div class="status-section">
                        <div class="status-1">
                            <div class="row">
                                <i class="fi fi-rs-check-circle" style="font-size: 24px; margin-bottom: 5px;"></i>
                                <h2 style="font-size: 32px; font-weight: 700;"><?php echo $full_stock_count; ?></h2>
                            </div>
                            <p>Full Stock Items</p>
                        </div>

                        <div class="status-2">
                            <div class="row">
                                <i class="fi fi-rs-clock" style="font-size: 24px; margin-bottom: 5px;"></i>
                                <h2 style="font-size: 32px; font-weight: 700;"><?php echo $moderate_stock_count; ?></h2>
                            </div>
                            <p>Moderate Stock Items</p>
                        </div>

                        <div class="status-3">
                            <div class="row">
                                <i class='bx  bx-alert-triangle' style="font-size: 30px; margin-bottom: 5px;"></i>
                                <h2 style="font-size: 32px; font-weight: 700;"><?php echo $low_stock_count; ?></h2>
                            </div>
                            <p>Low Stock Items</p>
                        </div>

                        <div class="status-4">
                            <div class="row">
                                <i class='bx  bx-x-circle' style="font-size: 30px; margin-bottom: 5px;"></i>
                                <h2 style="font-size: 32px; font-weight: 700;"><?php echo $out_of_stock_count; ?></h2>
                            </div>
                            <p>Out of Stock Items</p>
                        </div>

                        <div class="status-5">
                            <div class="row">
                                <i class='bx  bx-timer' style="font-size: 24px; margin-bottom: 5px;"></i>
                                <h2 style="font-size: 32px; font-weight: 700;"><?php echo $expiring_soon_count; ?></h2>
                            </div>
                            <p>Expiring Soon Items</p>
                        </div>
                    </div>

                    <div class="table-section-1">
                        <div class="sales-status">
                            <div class="sales-status-header">
                                <div class="sales-status-title">Sales Summary (Last 7 Days)</div>
                            </div>
                            <div class="chart-wrapper">
                                <canvas id="salesChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="quick-actions">
                            <div class="quick-actions-header">Quick Actions</div>
                            <button class="action-button" onclick="window.location.href='POSsystem.php'">
                                <i class="fi fi-rs-plus"></i> Create Order
                            </button>
                            <button class="action-button" onclick="window.location.href='sales.php'">
                                <i class="fi fi-rs-file-chart-line"></i> View Sales
                            </button>
                            <button class="action-button" onclick="window.location.href='inventory.php'">
                                <i class="fi fi-rs-bell"></i> View Low Stock Alerts
                            </button>
                            <button class="action-button">
                                <i class="fas fa-file-pdf"></i> Download Sales Report
                            </button>
                        </div>
                    </div>

                    <div class="table-section-2">
                        <div class="recent-transac">
                            <div class="recent-transac-header">
                                <div class="recent-transac-title">Recent Orders</div>
                                <a href="orders.php" class="view-all-link">View All</a>
                            </div>
                            
                            <table>
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Items</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recent_orders)): ?>
                                        <?php foreach ($recent_orders as $order): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                                <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    <?php echo htmlspecialchars($order['items'] ? $order['items'] : 'No items'); ?>
                                                </td>
                                                <td>₱<?php echo number_format((float)$order['total_amount'], 2); ?></td>
                                                <td>
                                                    <span class="status-badge completed" style="padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; background-color: #d1fadf; color: #27AE60;">
                                                        Completed
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y H:i', strtotime($order['order_date'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center; padding: 20px; color: #666;">
                                                No recent orders found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="frequently-used">
                            <div class="frequently-used-header">Popular Drinks of the Day</div>
                            <div class="item-grid">
                                <?php if (!empty($popular_drinks)): ?>
                                    <?php foreach ($popular_drinks as $index => $drink): ?>
                                        <div class="item-card">
                                            <div class="item-name"><?php echo htmlspecialchars($drink['product_name']); ?></div>
                                            <div class="item-quantity">QTY SOLD: <?php echo $drink['total_quantity']; ?></div>
                                            <div class="item-revenue" style="font-size: 12px; color: #bd6c1f; font-weight: 600;">
                                                ₱<?php echo number_format($drink['total_revenue'], 2); ?>
                                            </div>
                                            <div class="item-tag" style="background-color: #bd6c1f; color: white;">
                                                #<?php echo $index + 1; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if (count($popular_drinks) < 3): ?>
                                        <?php for ($i = count($popular_drinks); $i < 3; $i++): ?>
                                            <div class="item-card" style="background-color: #f8f9fa; opacity: 0.6;">
                                                <div class="item-name">No Data</div>
                                                <div class="item-quantity">QTY SOLD: 0</div>
                                                <div class="item-revenue" style="font-size: 12px; color: #999;">₱0.00</div>
                                                <div class="item-tag" style="background-color: #ccc; color: white;">
                                                    #<?php echo $i + 1; ?>
                                                </div>
                                            </div>
                                        <?php endfor; ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php for ($i = 0; $i < 3; $i++): ?>
                                        <div class="item-card" style="background-color: #f8f9fa; opacity: 0.6;">
                                            <div class="item-name">No Sales Today</div>
                                            <div class="item-quantity">QTY SOLD: 0</div>
                                            <div class="item-revenue" style="font-size: 12px; color: #999;">₱0.00</div>
                                            <div class="item-tag" style="background-color: #ccc; color: white;">
                                                #<?php echo $i + 1; ?>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            
        </div>
        
        <script src="assets/js/Sidebar-Nav.js"></script>
        <script>
            // Sales Chart
            const ctx = document.getElementById('salesChart').getContext('2d');
            const chartData = <?php echo json_encode($chart_data); ?>;
            
            const labels = chartData.map(item => {
                const date = new Date(item.sale_date);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            });
            
            const data = chartData.map(item => parseFloat(item.daily_total));
            
            const salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Daily Sales (₱)',
                        data: data,
                        borderColor: '#bd6c1f',
                        backgroundColor: 'rgba(189, 108, 31, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#bd6c1f',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(189, 108, 31, 0.9)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#bd6c1f',
                            borderWidth: 1
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            },
                            grid: {
                                color: 'rgba(189, 108, 31, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(189, 108, 31, 0.1)'
                            }
                        }
                    }
                }
            });
        </script>

    </body>
</html>