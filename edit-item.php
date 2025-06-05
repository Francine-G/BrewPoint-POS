<?php
// Include database connection
include("database/db.php");

$item = null;

// Check if we're editing an existing item
if (isset($_GET['id'])) {
    $itemID = $_GET['id'];
    
    // Fetch item details from database
    $stmt = $conn->prepare("SELECT * FROM inventory WHERE itemID = ?");
    $stmt->bind_param("i", $itemID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
    } else {
        // Item not found, redirect to inventory page
        header("Location: inventory.php");
        exit();
    }
    
    $stmt->close();
}

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
                        <h1>Edit Item</h1>
                        <p>Fill up the form to edit item.</p>
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
                        <span><h1>Edit Stock</h1></span>
                    </div>
                    
                   <div class="edit-form-content">
                        <form action="actions/edit-items.php" method="POST">
                            <input type="hidden" name="itemID" value="<?= $item ? $item['itemID'] : '' ?>">
                            
                            <div class="edit-input-form-1">
                                <div class="edit-form-row">
                                    <label for="itemCategory">Category</label>
                                    <select name="itemCategory" id="category" required onchange="loadItems(this.value)">
                                        <option value="" disabled>Select Category</option>
                                        <?php 
                                        $categories->data_seek(0); // Reset pointer
                                        while($row = $categories->fetch_assoc()): 
                                        ?>
                                            <option value="<?= htmlspecialchars($row['itemCategory']) ?>" 
                                                <?= ($item && $item['itemCategory'] == $row['itemCategory']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($row['itemCategory']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="edit-form-row">
                                    <label for="itemName">Item Name</label>
                                    <input type="text" class="edit-create-from" id="itemName" name="itemName" 
                                           value="<?= $item ? htmlspecialchars($item['itemName']) : '' ?>" required>
                                </div>
                            </div>
                            
                            <div class="edit-input-form-2">
                                <div class="edit-form-row">
                                    <label for="itemUnit">Unit</label>
                                    <select name="itemUnit" id="itemUnit" required>
                                        <option value="" disabled>Select Unit</option>
                                        <option value="Liters" <?= ($item && $item['itemUnit'] == 'Liters') ? 'selected' : '' ?>>Liters</option>
                                        <option value="Kilograms" <?= ($item && $item['itemUnit'] == 'Kilograms') ? 'selected' : '' ?>>Kilograms</option>
                                        <option value="Grams" <?= ($item && $item['itemUnit'] == 'Grams') ? 'selected' : '' ?>>Grams</option>
                                        <option value="Milliters" <?= ($item && $item['itemUnit'] == 'Milliters') ? 'selected' : '' ?>>Milliters</option>
                                        <option value="Milligrams" <?= ($item && $item['itemUnit'] == 'Milligrams') ? 'selected' : '' ?>>Milligrams</option>
                                        <option value="Pounds" <?= ($item && $item['itemUnit'] == 'Pounds') ? 'selected' : '' ?>>Pounds</option>
                                        <option value="Ounces" <?= ($item && $item['itemUnit'] == 'Ounces') ? 'selected' : '' ?>>Ounces</option>
                                        <option value="Pieces" <?= ($item && $item['itemUnit'] == 'Pieces') ? 'selected' : '' ?>>Pieces</option>
                                        <option value="Packs" <?= ($item && $item['itemUnit'] == 'Packs') ? 'selected' : '' ?>>Packs</option>
                                        <option value="Others" <?= ($item && $item['itemUnit'] == 'Others') ? 'selected' : '' ?>>Others</option>
                                    </select>
                                </div>
                                
                                <div class="edit-form-row">
                                    <label for="minStock">Minimum Stock Level</label>
                                    <input type="number" class="edit-create-form" id="minStock" name="minStock" 
                                           value="<?= $item ? $item['minStockLevel'] : '' ?>" min="0" required>
                                </div>
                            </div>

                            <div class="edit-action-button">
                                <button type="button" class="cancel-item-btn" name="cancel-stock" onclick="window.location.href='inventory.php'">Cancel</button>
                                <button type="submit" class="save-item-btn" name="edit-item">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
        <script src="assets/js/sidebar.js"></script>
        <script src = "assets/js/Inventory-Navi.js" ></script>
        <script>
        function loadItems(category) {
            // For edit form, we don't need to load items dynamically since we're editing a specific item
            // The item name field is already populated and editable
            console.log('Category changed to: ' + category);
        }
        </script>
    </body>
</html>