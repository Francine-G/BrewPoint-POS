<html>
    <head>
    <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="assets/css/poStylesheet.css" />
        <script src="https://kit.fontawesome.com/f4e628f07c.js" crossorigin="anonymous"></script>
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="assets/css/product-modification_crud.css">

        <title>BrewPoint POS</title>
    </head>

    <body>
        <div class="container"> 
            <div class="form-container">

            <div class="form-header">
                <span><i class='bx bxs-user-account'></i> </span>
                <span><h1> Create Product</h1></span>
            </div>

            <div class="form-content">
                <form action="actions/create_product.php" method = "POST" enctype="multipart/form-data">
                    <label for="productName">ProductName</label>
                    <input type="text" class = "create-from" id = "Name" name = "productName" required>

                    <label for="productCategory">Product Category</label>
                    <select for="productType" id = "productCategory" name = "productCategory" required>
                        <option>Select Category</option>
                        <option>Iced Coffee</option>
                        <option>Milktea</option>
                        <option>Fruit Tea</option>
                        <option>Frappe</option>
                        <option>Hot Brew</option>
                    </select>
                    
                    <label for="productImg">Product Image</label>
                    <input type="file" id="productImg" name="productImg" accept="image/*">

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
