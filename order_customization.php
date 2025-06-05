<?php
    include("database/db.php");
    
    // Get the product name from URL parameter
    $productName = isset($_GET['product']) ? $_GET['product'] : '';
    
    // Query to get product details
    $sql = "SELECT productName, productCategory, productImg FROM products WHERE productName = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $productName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Default values
    $productCategory = "";
    $productImg = "";
    
    // Fetch product details
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $productName = $row["productName"];
        $productCategory = $row["productCategory"];
        $productImg = $row["productImg"];
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <script src="https://kit.fontawesome.com/f4e628f07c.js" crossorigin="anonymous"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
        <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-straight/css/uicons-solid-straight.css'>
        <link rel="stylesheet" href="assets/css/Order-custom.css">
        <title>BrewPoint POS - Customization</title>
    </head>

    <body>
        <div class="container"> 
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

            <div class="customization">
                <div class="customization-header">
                    <a href = "POSsystem.php"><i class="fi fi-sr-arrow-small-left"></i></a>
                    <h2>Customization</h2>
                </div>

                <div class="customization-menu">
                    <div class="customization-card">

                        <div class="top-row">
                            <div class='product-img'>
                                <?php
                                    $imgSrc = $productImg ? "assets/img/uploads/" . $productImg : "assets/img/placeholder.png";
                                    echo "<img src='" . htmlspecialchars($imgSrc) . "' class='product-image' alt='" . htmlspecialchars($productName) . "'>";
                                ?>
                            </div>

                            <div class="product-titles">
                                <h2><?php echo htmlspecialchars($productName); ?></h2>
                                <span><p> | <?php echo htmlspecialchars($productCategory); ?></p></span>

                                <div class='product-details'>
                                    <div class="types">
                                        <div class="option-row">
                                            <label for="drink-type">Drink Type</label>
                                            <div class="button-group">
                                                <button class="option-btn">Hot</button>
                                                <button class="option-btn">Iced</button>
                                            </div>
                                        </div>

                                        <div class="option-row">
                                            <label for="size">Size</label>
                                            <div class="button-group">
                                                <button class="option-btn">Medio</button>
                                                <button class="option-btn">Grande</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        

                        <div class="bottom-row">
                            <div class="option-row">
                                <label for="addons">Add Ons</label>
                                <div class="button-group addon-buttons">
                                    <button class="option-btn">
                                        <img src="assets/img/addon_img/pearl.PNG" alt="Boba Pearl" class="addon-icon">
                                       <span>Boba Pearl</span> 
                                    </button>
                                    <button class="option-btn">
                                        <img src="assets/img/addon_img/Crystal.PNG" alt="Crystal Pearl" class="addon-icon">
                                       <span>Crystal Pearl</span>
                                    </button>
                                    <button class="option-btn">
                                        <img src="assets/img/addon_img/CreamCheese.PNG" alt="Cream Cheese" class="addon-icon">
                                      <span>Cream Cheese</span>
                                    </button>
                                    <button class="option-btn"> <img src="assets/img/addon_img/CoffeeJelly.PNG" alt="Cofee Jelly" class="addon-icon">
                                      <span>Coffee Jelly</span>
                                    </button>
                                    <button class="option-btn"> <img src="assets/img/addon_img/CrushedOreo.PNG" alt="Crushed Oreos" class="addon-icon">
                                      <span>Crushed Oreos</span>
                                    </button>
                                    <button class="option-btn"> <img src="assets/img/addon_img/CreamPuffs.PNG" alt="Cream Puff" class="addon-icon">
                                        <span>Cream Puff</span>
                                    </button>
                                    <button class="option-btn"> <img src="assets/img/addon_img/Cheesecake.PNG" alt="Cheesecake" class="addon-icon">
                                      <span>Cheesecake</span>
                                    </button>
                                    <button class="option-btn"> <img src="assets/img/addon_img/ChiaSeeds.PNG" alt="Chia Seeds" class="addon-icon">
                                      <span>Chia Seeds</span>
                                    </button>
                                    <button class="option-btn"> <img src="assets/img/addon_img/BrownSugarJelly.PNG" alt="Brown Sugar Jelly" class="addon-icon">
                                       <span>Brown Sugar Jelly</span>
                                    </button>
                                    <button class="option-btn"> <img src="assets/img/addon_img/GrassJelly.PNG" alt="Grass Jelly" class="addon-icon">
                                      <span>Grass Jelly</span>
                                    </button>
                                </div>
                            </div>

                            <div class="option-row-quantity">
                                <label for="quantity">Quantity</label>
                                <div class="button-group">
                                    <button class="option-btn">-</button>
                                    <span class="quantity">1</span>
                                    <button class="option-btn">+</button>
                                </div>

                                <div class="price-display">
                                    <h3>Total Price: <span id="totalPrice">₱0.00</span></h3>
                                </div>

                                <div class="add-btn">
                                    <button class="add-bill-btn">Add to Bill </button>
                                </div> 
                            </div>

                          
                        </div>

                        
                    </div>
                </div>
            </div>
        </div>
        <script src="assets/js/sidebar.js"></script>
        <script src="assets/js/Order_customization.js"></script>
    </body>
</html>