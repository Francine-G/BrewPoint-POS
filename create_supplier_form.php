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
        <link rel="stylesheet" href="assets/css/supplier_crud.css">

        <title>BrewPoint Supplier</title>
    </head>

    <body>
        <div class="container"> 
            <div class="form-container">

            <div class="form-header">
                <span><i class='bx bxs-user-account'></i> </span>
                <span><h1> Create Supplier</h1></span>
            </div>

            <div class="form-content">
                <form action="actions/create_supplier.php" method = "POST">
                    <label for="Name">Name</label>
                    <input type="text" class = "create-from" id = "Name" name = "supplierName" required>

                    <label for="Address">Address</label>
                    <input type="text" class = "create-from" id = "Address" name = "supplierAddress" required>
                    
                    <label for="Address">Product Supply</label>
                    <input type="text" class = "create-from" id = "Product-supply" name = "supplierProduct" required>

                    <label for="Address">Contact</label>
                    <input type="text" class = "create-from" id = "Contact" name = "supplierContact" required>

                    <div class="action-btn">
                        <button type = "button" class = "cancel-btn">Cancel</button>
                        <button type = "submit" class = "confirm-btn">Confirm</button>
                    </div>
                </form>
                <script src="assets/js/sidebar.js"></script>
                <script>
                    document.querySelector('.cancel-btn').addEventListener('click', () => {
                        window.location.href = 'supplier_details.php'; 
                    });
                </script>
            </div>
               
            </div>
        </div>
    </body>
</html>
