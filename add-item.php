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

        <title>Nahum Stock.</title>
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

            <div class="edit-main-content">
                <div class="edit-main-content-header">
                    <div class="content-header">
                        <h1>Add New Item</h1>
                        <p>Fill up the form to add a new item.</p>
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

                <div class="edit-form-box">
                    <div class="edit-form-header">
                        <span><h1>Add Stock</h1></span>
                    </div>
                    
                    <div class="edit-form-content">

                        <form action="actions/add-items.php" method="POST">
                            <div class="edit-input-form-1">
                                <div class="edit-form-row">
                                    <label for="itemName">Item Name</label>
                                    <input type="text" class="edit-create-from" id="Name" name="itemName" required>
                                </div>

                                <div class="edit-form-row">
                                    <label for="itemCategory">Category</label>
                                    <select name="itemCategory" id="category" required>
                                        <option value="" disabled selected>Select Category</option>
                                        <option value="Milk">Milk</option>
                                        <option value="Coffee Beans">Coffee Beans</option>
                                        <option value="Syrups">Syrups</option>
                                        <option value="Creams & Toppings" >Creams & Toppings</option>
                                        <option value="Packaging Supplies">Packaging Supplies</option>
                                        <option value="Condiments & Addons">Condiments & Addons</option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="edit-input-form-2">
                                <div class="edit-form-row">
                                    <label for="itemUnit">Unit</label>
                                    <select name="itemUnit" id="itemUnit" required>
                                        <option value="" disabled selected>Select Unit</option>
                                        <option value="Liters">Liters</option>
                                        <option value="Kilograms">Kilograms</option>
                                        <option value="Grams">Grams</option>
                                        <option value="Milliters">Milliters</option>
                                        <option value="Milligrams">Milligrams</option>
                                        <option value="Pounds">Pounds</option>
                                        <option value="Ounces">Ounces</option>
                                        <option value="Pieces">Pieces</option>
                                        <option value="Packs">Packs</option>
                                        <option value="Others">Others</option>
                                    </select>
                                </div>
                                <div class="edit-form-row">
                                    <label for="minStock">Minimum Stock Level</label>
                                    <input type="number" class="edit-create-form" id="minStock" name="minStock" required>
                                </div>
                            </div>

                            <div class="edit-action-button">
                                <button type="button" class="cancel-item-btn" name="cancel-stock" onclick="window.location.href='inventory.php'">Cancel</button>
                                <button type="submit" class="edit-item-btn" name="add-stock">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
        <script src="assets/js/sidebar.js"></script>
        <script src = "assets/js/Inventory-Navi.js" ></script>
    </body>
</html>