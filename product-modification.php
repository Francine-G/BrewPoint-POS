<html> 
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <script src="https://kit.fontawesome.com/f4e628f07c.js" crossorigin="anonymous"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
        <link rel="stylesheet" href="assets/css/productmodification_Style.css">

        <title>BrewPoint POS</title>

    </head>

    <body>
        
        <div class="container">

            <main class="content">
                <div class = "header">
                    <h2>Product Information</h2>

                    <div class = "user-icon">
                        <i class="fa-solid fa-circle-user"></i>
                    </div>
                </div>


                <div class = "main-contents">
                     <div class="top-content">
                        <div class="back-btn">
                            <button type = "button" class = "back-button"><a href = "POSsystem.php"><i class="fi fi-sr-left"></i></a></button>
                        </div>
                        <div class="add-button">
                            <span class="add-text"><a href = "create_product_form.php"><i class="fa-solid fa-pen-to-square"></i> Add New Product</a></span>
                        </div>
                     </div>
                     
                    <div class="table-content">
                        <div class="table-wrapper">
                            <?php 

                                include("database/db.php");
                                $sql = "SELECT productID, productName, productCategory, productImg FROM products ORDER BY productName ASC";
                                $result = $conn->query($sql);

                                if ($result === FALSE) {
                                    die("Error: " . $sql . "<br>" . $conn->error);
                                }elseif ($result->num_rows > 0){
                                    echo "<table class='add-supplier-table'>";
                                    echo "<thead>
                                            <tr>
                                                <th>Product ID</th>
                                                <th>Product Image</th>
                                                <th>Product Name</th>
                                                <th>Product Category</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead><tbody>";
                                    
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['productID']) . "</td>";
                                        
                                        // Product Image Column
                                        echo "<td class='image-column'>";
                                        if (!empty($row['productImg'])) {
                                            $imgSrc = "assets/img/uploads/" . htmlspecialchars($row['productImg']);
                                            // Check if file exists
                                            if (file_exists($imgSrc)) {
                                                echo "<div class='product-image-container'>";
                                                echo "<img src='$imgSrc' alt='" . htmlspecialchars($row['productName']) . "' class='product-thumbnail' onclick='showImageModal(\"$imgSrc\", \"" . htmlspecialchars($row['productName']) . "\")'>";
                                                echo "</div>";
                                            } else {
                                                echo "<div class='no-image'>";
                                                echo "<i class='fa-solid fa-image' style='color: #ccc; font-size: 24px;'></i>";
                                                echo "<span>Image not found</span>";
                                                echo "</div>";
                                            }
                                        } else {
                                            echo "<div class='no-image'>";
                                            echo "<i class='fa-solid fa-image' style='color: #ccc; font-size: 24px;'></i>";
                                            echo "<span>No image</span>";
                                            echo "</div>";
                                        }
                                        echo "</td>";
                                        
                                        echo "<td>" . htmlspecialchars($row['productName']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['productCategory']) . "</td>";
                                        echo "<td class='action-column'>
                                                    <a href='edit_product_form.php?id=" . $row['productID'] . "' class='edit-btn' title='Edit'>
                                                        <i class='fas fa-edit'></i>
                                                    </a>
                                                    <button onclick='confirmDelete(" . $row['productID'] . ")' class='delete-btn' title='Delete'>
                                                        <i class='fas fa-trash-alt'></i>
                                                    </button>
                                                </td>";
                                        echo "</tr>";
                                    }
                                    echo "</tbody></table>";
                                }else {
                                    echo "<p>No products found.</p>"; 
                                }
                            ?>

                        </div>
                     </div>

                </div>
            
        </div>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <?php
            $msg = $_GET['msg'] ?? null;
            if ($msg):
            ?>
            <script>
            document.addEventListener("DOMContentLoaded", () => {
                <?php if ($msg === 'added'): ?>
                    Swal.fire("Success", "Product added successfully!", "success");
                <?php elseif ($msg === 'add_error'): ?>
                    Swal.fire("Error", "Failed to add product.", "error");
                <?php elseif ($msg === 'edited'): ?>
                    Swal.fire("Success", "Product updated successfully!", "success");
                <?php elseif ($msg === 'edit_error'): ?>
                    Swal.fire("Error", "Failed to update product.", "error");
                <?php elseif ($msg === 'deleted'): ?>
                    Swal.fire("Success", "Product deleted successfully!", "success");
                <?php elseif ($msg === 'delete_error'): ?>
                    Swal.fire("Error", "Failed to delete product.", "error");
                <?php endif; ?>
            });
            </script>
        <?php endif; ?>


        <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will permanently delete the product.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4e944f',
                cancelButtonColor: '#863a3a',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirect to delete PHP file
                    window.location.href = 'actions/delete_product.php?id=' + id;
                }
            });
        }
        </script>

        <script src="assets/js/sidebar.js"></script>
    </body>
</html>