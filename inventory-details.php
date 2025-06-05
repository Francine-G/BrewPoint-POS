<?php
// Database connection
include('database/db.php');

// Check if connection exists
if (!isset($conn) || $conn->connect_error) {
    die("Connection failed: " . (isset($conn) ? $conn->connect_error : "Database connection not established"));
}

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
    } elseif ($days_until_expiry <= 30) {
        return '<span class="expiry-warning">' . date('M d, Y', strtotime($expiryDate)) . ' (' . $days_until_expiry . ' days)</span>';
    } else {
        return '<span class="expiry-normal">' . date('M d, Y', strtotime($expiryDate)) . '</span>';
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
        <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
        <link href='https://cdn.boxicons.com/fonts/brands/boxicons-brands.min.css' rel='stylesheet'>
        <link rel="stylesheet" href="assets/css/item-Details.css"> 
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <title>BrewPoint - Inventory Details</title>

        <style>
            /* Stock level indicators */
            .stock-indicator {
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 12px;
                font-weight: 500;
                text-transform: uppercase;
            }

            .no-stock { 
                background: linear-gradient(90deg, #ef6161, #dc2727);
                color: #20473f; 
                font-weight: 700;
            }

            .low-stock { 
                background:linear-gradient(90deg, #fb8f39, #ff764a);
                color: #20473f;  
                font-weight: 700;
            }

            .moderate-stock { 
                background:linear-gradient(90deg, #facc15, #ebb509); 
                color: #20473f;
                font-weight: 700;
            }

            .full-stock { 
                background: linear-gradient(90deg, #d6e8d7, #6d8783); 
                color: #20473f; 
                font-weight: 700;
            }

            .expiry-warning { 
                background:linear-gradient(90deg, #a14cf0, #9334ea);
                color: #20473f;  
                font-weight: 700;
            }

            /* Expiry date indicators */
                    
            .expired { 
                color: #ef6161; 
                font-weight: bold; 
            }
                    
            .expiry-critical { 
                color: #fb8f39; 
                font-weight: bold; 
            }
                    
            .expiry-normal { 
                color: #28a745; 
            }
                    
            .no-expiry { 
                color: #6c757d; 
                font-style: italic; 
            }
                    
            .batch-details {
                font-size: 11px;
                color: #6c757d;
                margin-top: 4px;
                display: flex;
                text-align: center;
            }
        </style>
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
            <div class="content">
                <div class="content-wrapper">
                    <div class="header2">
                        <h1>Inventory Details</h1>
                    </div>
                </div>
                <div class="top">
                    <div class="tbpg-functions">
                        <button type="button" class="add-item-btn"><i class='bx bx-plus-circle'></i> Add Item</button>
                        <button type="button" class="stock-in-btn"><i class='bx bx-archive-arrow-down'></i> Stock In</button>
                        <button type="button" class="stock-out-btn"><i class='bx bx-archive-arrow-up'></i> Stock Out</button>
                    </div>
                </div>
                <div class="main-content">
                    <div id="tbpg-header">
                        <form id="inventory-search-form">
                            <div class="tbpg-search">
                                <input type="search" class="search" id="searchInput" placeholder="Enter Inventory Name" />
                                <select id="categorySelect" class="category">
                                    <option value="All" selected>All Categories</option>
                                    <option value="Milk">Milk</option>
                                    <option value="Coffee Beans">Coffee Beans</option>
                                    <option value="Syrups">Syrups</option>
                                    <option value="Creams & Toppings" >Creams & Toppings</option>
                                    <option value="Packaging Supplies">Packaging Supplies</option>
                                    <option value="Condiments & Addons">Condiments & Addons</option>
                                    <option value="Others">Others</option>
                                </select>
                                <select id="stockLevelSelect" class="stock_level">
                                    <option value="All">All Stock Levels</option>
                                    <option value="Full Stock">Full Stock</option>
                                    <option value="Moderate Stock">Moderate Stock</option>
                                    <option value="Low Stock">Low Stock</option>
                                    <option value="No Stock">No Stock</option>
                                    <option value="Expiry">Expiry</option>
                                </select>
                                <button type="button" class="search-button" onclick="searchInventory()">Search</button>
                            </div>
                        </form>
                    </div>
                    <div class="body-content" id="table-content">
                        <table class="inventory-table">
                            <thead>
                                <tr>
                                    <th>ITEM NAME</th>
                                    <th>CURRENT QTY</th>
                                    <th>UNIT</th>
                                    <th>CATEGORY</th>
                                    <th>EXPIRATION DATE</th>
                                    <th>STOCK LEVEL</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody id="inventoryTableBody">
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                        </table>
                        <div class="pagination" id="paginationContainer">
                            <!-- Pagination will be loaded via AJAX -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="inventoryModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeModalBtn">&times;</span>
                <h2></h2>
                <div id="modal-table"></div>
            </div>
        </div>
        <!-- Batch Modal Example -->
        <div id="batchModal" class="batch-modal">
            <div class="batch-modal-content">
                <div class="batch-modal-header">
                    <h3 id="batchModalTitle">Item Batches</h3>
                    <span class="close">&times;</span>
                </div>
                <div class="batch-modal-body" id="batchModalBody">
                    <!-- Batch content will be loaded here -->
                </div>
            </div>
        </div>

        <script src="assets/js/sidebar.js"></script>
        <script src="assets/js/Inventory-Navi.js"></script>
        <script src="assets/js/inventory-messages.js"></script>
        
        <script>
            let currentPage = 1;
            let currentSearch = '';
            let currentCategory = 'All';
            let currentStockLevel = 'All';

            // Load inventory on page load
            $(document).ready(function() {
                loadInventory(1);
            });

            // Function to load inventory via AJAX
            function loadInventory(page = 1, search = '', category = 'All', stock_level = 'All') {
                currentPage = page;
                currentSearch = search;
                currentCategory = category;
                currentStockLevel = stock_level;
                
                console.log('Loading inventory with:', {page: page, search: search, category: category, stock_level: stock_level});
                
                $.ajax({
                    url: 'actions/load-inventory-data.php',
                    type: 'POST',
                    data: {
                        page: page,
                        search: search,
                        category: category,
                        stock_level: stock_level
                    },
                    success: function(response) {
                        console.log('Raw response:', response);
                        try {
                            const data = JSON.parse(response);
                            console.log('Parsed data:', data);
                            $('#inventoryTableBody').html(data.table_rows);
                            $('#paginationContainer').html(data.pagination);
                        } catch (e) {
                            console.error('Error parsing JSON:', e, response);
                            $('#inventoryTableBody').html('<tr><td colspan="7">Error loading data</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', xhr.responseText);
                        $('#inventoryTableBody').html('<tr><td colspan="7">Error loading data</td></tr>');
                    }
                });
            }

            // Function to search inventory
            function searchInventory() {
                const searchValue = $('#searchInput').val();
                const categoryValue = $('#categorySelect').val();
                const stockLevelValue = $('#stockLevelSelect').val();
                loadInventory(1, searchValue, categoryValue, stockLevelValue);
            }

            // Function to change page
            function changePage(page) {
                loadInventory(page, currentSearch, currentCategory, currentStockLevel);
            }

            // Allow Enter key to trigger search
            $('#searchInput').keypress(function(e) {
                if (e.which == 13) {
                    searchInventory();
                }
            });

            // Trigger search when dropdowns change
            $('#categorySelect, #stockLevelSelect').change(function() {
                searchInventory();
            });
        </script>
    </body>
</html>