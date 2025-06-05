<?php

$conn = new mysqli("localhost", "root", "", "brewpos"); 

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$supplierID = $_GET['id'] ?? null;

if (!$supplierID) {
    die("Missing supplier ID.");
}

// Fetch supplier details
$sql = "SELECT * FROM suppliers WHERE supplierID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $supplierID);
$stmt->execute();
$result = $stmt->get_result();
$suppliers = $result->fetch_assoc();

if (!$suppliers) {
    die("Supplier not found.");
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
    <link rel="stylesheet" href="assets/css/supplier_crud.css">
    <title>BrewPoint Supplier</title>
</head>

<body>
    <div class="container"> 
        <div class="form-container">
            <div class="form-header">
                <span><i class='bx bxs-user-account'></i> </span>
                <span><h1>Edit Supplier</h1></span>
            </div>

            <div class="form-content">
                <form action="actions/update_supplier.php" method="POST">
                    <input type="hidden" name="id" value="<?= $suppliers['supplierID'] ?>">

                    <label for="Name">Name</label>
                    <input type="text" class="create-from" id="Name" name="supplierName" value="<?= htmlspecialchars($suppliers['supplierName']) ?>" required>

                    <label for="Address">Address</label>
                    <input type="text" class="create-from" id="Address" name="supplierAddress" value="<?= htmlspecialchars($suppliers['supplierAddress']) ?>" required>
                    
                    <label for="Product-supply">Product Supply</label>
                    <input type="text" class="create-from" id="Product-supply" name="supplierProduct" value="<?= htmlspecialchars($suppliers['supplierProduct']) ?>" required>

                    <label for="Contact">Contact</label>
                    <input type="text" class="create-from" id="Contact" name="supplierContact" value="<?= htmlspecialchars($suppliers['supplierContact']) ?>" required>

                    <div class="action-btn">
                        <button type="button" class="cancel-btn">Cancel</button>
                        <button type="submit" class="confirm-btn">Confirm</button>
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
