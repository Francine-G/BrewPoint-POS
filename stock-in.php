<?php
// Include database connection
include("database/db.php");

// Get categories for dropdown
$categoryQuery = "SELECT DISTINCT itemCategory FROM inventory WHERE currentQty >= 0 ORDER BY itemCategory";
$categories = $conn->query($categoryQuery);
?>

<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://kit.fontawesome.com/f4e628f07c.js" crossorigin="anonymous"></script>
        <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css'>
        <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-straight/css/uicons-solid-straight.css'>
        <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-straight/css/uicons-regular-straight.css'>
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel="stylesheet" href="assets/css/stockin-out.css">

        <title>BrewPoint - Stock In</title>
    </head>

    <body>
        <div class="content">
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

            <div class="in-main-content">
                <div class="in-main-content-header">
                    <div class="content-header">
                        <h1>Stock In</h1>
                        <p>Fill up the form to stock in the item.</p>
                    </div>
                    
                    <div class="user-icon">
                        <i class="fa-solid fa-circle-user"></i>
                    </div>
                    
                    <?php if (isset($_GET['error'])): ?>
                        <div class="error-message">
                            <?php
                                $error = $_GET['error'];
                                if ($error == 'empty_fields') echo "Please fill in all required fields.";
                                elseif ($error == 'expired_date') echo "Cannot add expired items.";
                                elseif ($error == 'item_not_found') echo "Item not found in inventory.";
                                elseif ($error == 'database_error') echo "Database error occurred.";
                            ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="in-form-box">
                    <div class="in-form-content">
                            <div class="in-form-header">
                                <span><h1>Add Stock</h1></span>
                            </div>
                        <form action="actions/stockin-items.php" method="POST">

                            <div class="in-input-form-2">
                                <label for="itemCategory">Category</label>
                                <select name="itemCategory" id="category" required onchange="loadItems(this.value)">
                                    <option value="" disabled selected>Select Category</option>
                                    <?php while($row = $categories->fetch_assoc()): ?>
                                        <option value="<?= htmlspecialchars($row['itemCategory']) ?>">
                                            <?= htmlspecialchars($row['itemCategory']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>

                                <label for="itemName">Item Name</label>
                                <select name="itemName" id="itemName" required disabled>
                                    <option value="" disabled selected>Select Category First</option>
                                </select>
                            </div>
                            
                            <div class="in-input-form-1">
                                <label for="itemQty">Quantity</label>
                                <input type="number" class="create-from" id="quantity" name="itemQty" min="1" required>
                            </div>

                            <div class="in-input-form-3">
                                <label for="itemDate">Expiry Date</label>
                                <input type="date" class="create-form" id="expiryDate" name="expiryDate" required min="<?= date('Y-m-d') ?>">
                            </div>

                            <div class="in-action-button">
                                <button type="button" class="cancel-item-btn" name="cancel-stock" onclick="window.location.href='inventory.php'">Cancel</button>
                                <button type="submit" class="in-item-btn" name="add-stock">Add Stock</button>
                            </div>
                        </form>
                    </div>
                </div>
                
            </div>

        </div>
        <script src="assets/js/inventory-messages.js"></script> 
        <script src="assets/js/Sidebar-nav.js"></script>
        <script src="assets/js/Inventory-Navi.js"></script>
        <script>
        function loadItems(category) {
            const itemSelect = document.getElementById('itemName');
            
            if (category === '') {
                itemSelect.innerHTML = '<option value="" disabled selected>Select Category First</option>';
                itemSelect.disabled = true;
                return;
            }
            
            // Clear current options
            itemSelect.innerHTML = '<option value="" disabled selected>Loading...</option>';
            itemSelect.disabled = true;
            
            // Fetch items for selected category
            fetch('actions/get-item-by-category.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'category=' + encodeURIComponent(category)
            })
            .then(response => response.json())
            .then(data => {
                itemSelect.innerHTML = '<option value="" disabled selected>Select Item</option>';
                
                if (data.length > 0) {
                    data.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.itemName;
                        option.textContent = item.itemName + ' (Current: ' + item.currentQty + ' ' + item.itemUnit + ')';
                        itemSelect.appendChild(option);
                    });
                    itemSelect.disabled = false;
                } else {
                    itemSelect.innerHTML = '<option value="" disabled>No items in this category</option>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                itemSelect.innerHTML = '<option value="" disabled>Error loading items</option>';
            });
        }
        </script>
    </body>
</html>