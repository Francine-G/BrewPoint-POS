<html> 
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <script src="https://kit.fontawesome.com/f4e628f07c.js" crossorigin="anonymous"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-straight/css/uicons-solid-straight.css'>
        <link rel="stylesheet" href="assets/css/PoS-style.css">
        <script src="assets/js/filter_nav.js"></script>

        <title>BrewPoint POS</title>
    </head>

    <body>
        <div class="overlay"></div>
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
                    <h2>BREWPOINT POS</h2>
                    
                    <div class="user-icon">
                        <div class="cart-wrapper" id="cartIcon">
                            <i class="fi fi-ss-shopping-cart"></i>
                            <div class="cart-counter" id="cartCounter">0</div>
                        </div>
                        <i class="fa-solid fa-circle-user"></i>
                    </div>
                </div>


                <div class="main-contentS">
                    <div class="left-side">
                        <div class="top-mid">
                            <div class="product-category-filter">
                                <div class="boxes">
                                    <ul class="filter-box">
                                        <li class="filter-button active">All</li>
                                        <li class="filter-button">Iced Coffee</li>
                                        <li class="filter-button">Milktea</li>
                                        <li class="filter-button">Fruit Tea</li>
                                        <li class="filter-button">Frappe</li>
                                        <li class="filter-button">Hot Brew</li>
                                    </ul>
                                </div>     
                            </div>

                            <div class="edit-product">
                                <div class="edit-button">
                                    <span class="edit-text"><a href="product-modification.php"><i class="fa-solid fa-pen-to-square"></i> Modify Product Menu</a></span>
                                </div>
                            </div>                            
                        </div>
                        
                        <div class="mid-mid">
                            <div class="product-menu">
                                <?php
                                    include("database/db.php");
                                    
                                    $sql = "SELECT productName, productCategory, productImg FROM products";
                                    $result = $conn->query($sql);

                                    if ($result->num_rows > 0) {
                                        while($row = $result->fetch_assoc()) {
                                            $productName = $row["productName"];
                                            $productCategory = $row["productCategory"];
                                            $productImg = $row["productImg"];
                                            
                                            echo "<div class='product-card'>";

                                                $imgSrc = $productImg ? "assets/img/uploads/" . $productImg : "assets/img/placeholder.png";
                                                
                                                echo "<div class='product-img'>";
                                                    echo "<img src='" . htmlspecialchars($imgSrc) . "' class='product-image' alt='" . htmlspecialchars($productName) . "'>";
                                                echo "</div>";
                                                
                                                echo "<div class='product-details'>";
                                                    echo "<h2>" . htmlspecialchars($productName) . "</h2>";
                                                    echo "<span><p> | " . htmlspecialchars($productCategory) . "</p></span>";
                                                echo "</div>"; 
                                                
                                                echo "<div class='order-btn'>";
                                                    echo "<button class='order-button' id='" . htmlspecialchars($productName) . "'><a href='order_customization.php?product=" . urlencode($productName) . "'>Order</a></button>";
                                                echo "</div>"; 
                                            
                                            echo "</div>"; 
                                        }
                                    } else {
                                        echo "<p>No products found.</p>";
                                    }
                                    
                                    // Close database connection
                                    $conn->close();
                                ?>
                            </div>
                        </div>
                    </div>   


                </div>

            </main>

            <aside class="right-side" id="receiptPanel">
                <div class="receipt-container">
                    <div class="receipt">
                        <i class="fa-solid fa-xmark close-receipt" id="closeReceipt"></i>
                        <h2>Receipt</h2>
                        
                        <div class="receipt-items" id="receiptItems">
                            <!-- Receipt items will be dynamically added here -->
                        </div>
                        
                        <div class="receipt-total">
                            <div class="total-row">
                                <span>Subtotal:</span>
                                <span id="subtotal">₱0.00</span>
                            </div>
                            <div class="total-row grand-total">
                                <span>Total:</span>
                                <span id="total">₱0.00</span>
                            </div>
                        </div>
                        
                        <button class="checkout-btn" id="checkoutBtn">Proceed to Checkout</button>
                    </div>
                </div>
            </aside>
            
        </div>

        <script src="assets/js/cart_functionality.js"></script>
        <script src="assets/js/Sidebar-Nav.js"></script>
    </body>
</html>