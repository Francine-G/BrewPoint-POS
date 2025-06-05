<?php

$conn = new mysqli("localhost", "root", "", "brewpos"); 

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$productID = $_GET['id'] ?? null;

if (!$productID) {
    die("Missing product ID.");
}

// Fetch supplier details
$sql = "SELECT * FROM products WHERE productID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $productID);
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_assoc();

if (!$products) {
    die("Product not found.");
}
?>

<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="assets/css/poStylesheet.css" />
    <script src="https://kit.fontawesome.com/f4e628f07c.js" crossorigin="anonymous"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="assets/css/product-modification_crud.css">
    <title>BrewPoint POS</title>
</head>

<body>
    <div class="container"> 
        <div class="form-container">
            <div class="form-header">
                <span><i class='bx bxs-user-account'></i> </span>
                <span><h1>Edit Product</h1></span>
            </div>

            <div class="form-content">
                <form action="actions/update_product.php" method = "POST" enctype="multipart/form-data">

                    <input type="hidden" name="id" value="<?= $products['productID'] ?>">

                    <label for="productName">ProductName</label>
                    <input type="text" class = "create-from" id = "productName" name = "productName" value="<?= htmlspecialchars($products['productName']) ?>" required>

                    <label for="productCategory">Product Category</label>
                    <select for="productCategory" id = "productCategory" name = "productCategory" value="<?= htmlspecialchars($products['productCategory']) ?>" required>
                    <option value="Iced Coffee" <?= $products['productCategory'] == 'Iced Coffee' ? 'selected' : '' ?>>Iced Coffee</option>
                        <option value="Milktea" <?= $products['productCategory'] == 'Milktea' ? 'selected' : '' ?>>Milktea</option>
                        <option value="Fruit Tea" <?= $products['productCategory'] == 'Fruit Tea' ? 'selected' : '' ?>>Fruit Tea</option>
                        <option value="Frappe" <?= $products['productCategory'] == 'Frappe' ? 'selected' : '' ?>>Frappe</option>
                        <option value="Hot Brew" <?= $products['productCategory'] == 'Hot Brew' ? 'selected' : '' ?>>Hot Brew</option>
                    </select>
                    
                    <label for="productImg">Product Image</label>
                    <input type="file" id="productImg" name="productImg" accept="image/*" value="<?= htmlspecialchars($products['productImg']) ?>"> 

                    <div class="action-btn">
                        <button type = "button" class = "cancel-btn">Cancel</button>
                        <button type = "submit" class = "confirm-btn">Confirm</button>
                    </div>
                </form>
                <script src="assets/js/sidebar.js"></script>
                <script>
                    document.querySelector('.cancel-btn').addEventListener('click', () => {
                        window.location.href = 'product-modification.php'; 
                    });
                </script>
            </div>
        </div>
    </div>
</body>
</html>
