<?php

include('actions/session_security.php');

// Require user to be logged in
requireLogin();

$userInfo = $_SESSION['uname'];
// Database connection
include('database/db.php');

// Check if connection exists
if (!isset($conn) || $conn->connect_error) {
    die("Connection failed: " . (isset($conn) ? $conn->connect_error : "Database connection not established"));
}

// Define categories with their corresponding images
$categories = [
    'Milk' => ['image' => 'assets/img/addon_img/Milk.PNG', 'alt' => 'Milk'],
    'Coffee Beans' => ['image' => 'assets/img/addon_img/Coffee.PNG', 'alt' => 'Coffee Beans'],
    'Syrups' => ['image' => 'assets/img/addon_img/Syrups.PNG', 'alt' => 'Syrups'],
    'Creams & Toppings' => ['image' => 'assets/img/addon_img/Creams.png', 'alt' => 'Creams & Toppings'],
    'Packaging Supplies' => ['image' => 'assets/img/addon_img/Packaging.png', 'alt' => 'Packaging Supplies'],
    'Condiments & Addons' => ['image' => 'assets/img/addon_img/Addons.png', 'alt' => 'Condiments & Addons'],
    'Others' => ['image' => 'assets/img/addon_img/IMG_3202.png', 'alt' => 'Others']
];

// Function to get category data
function getCategoryData($conn, $category) {
    $sql = "SELECT 
        i.itemName,
        i.currentQty,
        i.itemUnit,
        i.stockLevel,
        MIN(sb.expiryDate) as earliest_expiry,
        CASE 
            WHEN MIN(sb.expiryDate) IS NULL THEN 'No expiry data'
            WHEN MIN(sb.expiryDate) <= CURDATE() THEN 'Expired'
            WHEN MIN(sb.expiryDate) <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 'Critical'
            WHEN MIN(sb.expiryDate) <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Near'
            ELSE 'Fresh'
        END as expiry_status
    FROM inventory i
    LEFT JOIN stock_batches sb ON i.itemID = sb.itemID AND sb.quantity > 0
    WHERE i.itemCategory = ?
    GROUP BY i.itemID, i.itemName, i.currentQty, i.itemUnit, i.stockLevel
    ORDER BY i.itemName ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    
    $stmt->close();
    return $items;
}

// Function to get category summary
function getCategorySummary($conn, $category) {
    $sql = "SELECT 
        COUNT(*) as total_items,
        SUM(CASE WHEN i.stockLevel = 'No Stock' THEN 1 ELSE 0 END) as no_stock_items,
        SUM(CASE WHEN i.stockLevel = 'Low Stock' THEN 1 ELSE 0 END) as low_stock_items,
        SUM(CASE WHEN sb.expiryDate <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND sb.expiryDate IS NOT NULL THEN 1 ELSE 0 END) as expiring_soon
    FROM inventory i
    LEFT JOIN stock_batches sb ON i.itemID = sb.itemID AND sb.quantity > 0
    WHERE i.itemCategory = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    $summary = $result->fetch_assoc();
    
    $stmt->close();
    return $summary;
}

// Handle search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filtered_categories = $categories;

if (!empty($search)) {
    $filtered_categories = [];
    foreach ($categories as $category => $data) {
        if (stripos($category, $search) !== false) {
            $filtered_categories[$category] = $data;
        }
    }
}
?>

<html> 
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <script src="https://kit.fontawesome.com/f4e628f07c.js" crossorigin="anonymous"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel="stylesheet" href="assets/css/Inventory.css">

        <title>BrewPoint - Inventory</title>
      
    </head>
    <body>
        <div id="inventoryModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeModalBtn">&times;</span>
                <h2 id="modalTitle"></h2>
                <div id="modal-table"></div>
            </div>
        </div>
        
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

            <div class="content">
                <div class="content-wrapper">
                    <div class="header2">
                        <h1>Inventory Overview</h1>
                        <div class="search">
                            <form action="" method="GET">
                                <input type="text" name="search" placeholder="Search categories..." value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="main-content">
                    <div class="ViewAll">
                        <div class="top-box">
                            <button class="VA-btn">View All</button>
                        </div>
                    </div>
                    <div class="mid-box">
                        <ul class="Inventory-list">
                            <?php foreach ($filtered_categories as $category => $categoryData): 
                                $items = getCategoryData($conn, $category);
                                $summary = getCategorySummary($conn, $category);
                                    
                                // Build table HTML for modal
                                $tableHtml = "<table class='status'>";
                                $tableHtml .= "<tr><th>Product</th><th>Quantity</th><th>Stock Status</th><th>Expiry Status</th></tr>";
                                    
                                if (empty($items)) {
                                    $tableHtml .= "<tr><td colspan='4' style='text-align: center; color: #999;'>No items in this category</td></tr>";
                                } else {
                                    foreach ($items as $item) {
                                        $tableHtml .= "<tr>";
                                        $tableHtml .= "<td>" . htmlspecialchars($item['itemName']) . "</td>";
                                        $tableHtml .= "<td>" . number_format($item['currentQty']) . " " . htmlspecialchars($item['itemUnit']) . "</td>";
                                        $tableHtml .= "<td><span class='stock-indicator " . getStockLevelClass($item['stockLevel']) . "'>" . htmlspecialchars($item['stockLevel']) . "</span></td>";
                                        $tableHtml .= "<td><span class='stock-indicator " . getExpiryClass($item['expiry_status']) . "'>" . htmlspecialchars($item['expiry_status']) . "</span></td>";
                                        $tableHtml .= "</tr>";
                                    }
                                }
                                $tableHtml .= "</table>";
                                    
                                // Get sample items for display
                                $sampleItems = array_slice($items, 0, 4);
                            ?>
                            <li>
                                <div class="sales-summary">
                                    <div class="content-wrapper">
                                        <img src="<?php echo $categoryData['image']; ?>" alt="<?php echo $categoryData['alt']; ?>" class="Inventory-img">
                                        <div class="Inventory-content">
                                        <h2><?php echo strtoupper($category); ?></h2>
                                            <?php if (!empty($sampleItems)): ?>
                                                <?php foreach ($sampleItems as $item): ?>
                                                    <p><?php echo htmlspecialchars($item['itemName']); ?></p>
                                                <?php endforeach; ?>
                                                <?php if (count($items) > 4): ?>
                                                    <p><i>... and <?php echo count($items) - 4; ?> more items</i></p>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                  <p><i>No items available</i></p>
                                           <?php endif; ?>
                                                
                                            <div class="category-summary">
                                                <span class="summary-badge">Total: <?php echo $summary['total_items']; ?></span>
                                                <?php if ($summary['no_stock_items'] > 0): ?>
                                                    <span class="summary-badge alert-badge">No Stock: <?php echo $summary['no_stock_items']; ?></span>
                                                <?php endif; ?>
                                                <?php if ($summary['low_stock_items'] > 0): ?>
                                                    <span class="summary-badge alert-badge">Low: <?php echo $summary['low_stock_items']; ?></span>
                                                <?php endif; ?>
                                                <?php if ($summary['expiring_soon'] > 0): ?>
                                                      <span class="summary-badge alert-badge">Expiring: <?php echo $summary['expiring_soon']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="eye">
                                        <button data-title="<?php echo strtoupper($category); ?>"
                                            data-table="<?php echo htmlspecialchars($tableHtml); ?>">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </li>
                            <?php endforeach; ?>
                               
                            <?php if (empty($filtered_categories)): ?>
                                <li style="width: 100%; text-align: center; padding: 40px;">
                                       <p style="color: #999; font-size: 18px;">No categories found matching your search.</p>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                
            </div>
        </div>
    </body>
    <script src="assets/js/Sidebar-Nav.js"></script>
    <script src = "assets/js/Inventory-Navi.js" ></script>
    <script src="assets/js/inventory.js"></script>
</html>

<?php
// Helper functions for CSS classes
function getStockLevelClass($stockLevel) {
    switch($stockLevel) {
        case 'No Stock': return 'no-stock';
        case 'Low Stock': return 'low-stock';
        case 'Moderate Stock': return 'moderate-stock';
        case 'Full Stock': return 'full-stock';
        default: return '';
    }
}

function getExpiryClass($expiryStatus) {
    switch($expiryStatus) {
        case 'Expired': return 'expired';
        case 'Critical': return 'expiry-critical';
        case 'Near': return 'expiry-warning';
        case 'Fresh': return 'expiry-normal';
        case 'No expiry data': return 'no-expiry';
        default: return '';
    }
}
?>