<?php
// Include database connection
include("../database/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $productName = isset($_POST['productName']) ? $_POST['productName'] : '';
    $drinkType = isset($_POST['drinkType']) ? $_POST['drinkType'] : '';
    $size = isset($_POST['size']) ? $_POST['size'] : '';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $totalPrice = isset($_POST['totalPrice']) ? (float)$_POST['totalPrice'] : 0.00;
    
    // Get add-ons (if any)
    $addOns = isset($_POST['addon']) ? $_POST['addon'] : [];
    $addOnsJson = json_encode($addOns);
    
    // Set session ID if not exists
    if (!isset($_COOKIE['cart_session_id'])) {
        $cart_session_id = uniqid('cart_', true);
        setcookie('cart_session_id', $cart_session_id, time() + (86400 * 30), "/"); // 30 days
    } else {
        $cart_session_id = $_COOKIE['cart_session_id'];
    }
    
    // Insert into temporary_cart table
    $sql = "INSERT INTO temporary_cart (session_id, product_name, drink_type, size, addons, quantity, total_price, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("sssssid", $cart_session_id, $productName, $drinkType, $size, $addOnsJson, $quantity, $totalPrice);
        
        if ($stmt->execute()) {
            // Get the inserted ID
            $cart_item_id = $conn->insert_id;
            
            // Create an array to store in JavaScript
            $cartItem = [
                'id' => $cart_item_id,
                'name' => $productName,
                'drinkType' => $drinkType,
                'size' => $size,
                'addOns' => $addOns,
                'quantity' => $quantity,
                'itemPrice' => $totalPrice
            ];
            
            // Get existing items from localStorage
            echo "
            <script>
                // Get the cart item data
                const cartItem = " . json_encode($cartItem) . ";
                
                // Get existing cart items from localStorage
                let cartItems = JSON.parse(localStorage.getItem('cartItems') || '[]');
                
                // Add new item to the array
                cartItems.push(cartItem);
                
                // Save updated array back to localStorage
                localStorage.setItem('cartItems', JSON.stringify(cartItems));
                
                // Update the cart counter
                const cartCounter = document.getElementById('cartCounter');
                if (cartCounter) {
                    cartCounter.textContent = cartItems.length;
                }
                
                // Redirect back to POS system
                window.location.href = '../POSsystem.php';
            </script>
            ";
        } else {
            echo "Error: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
    }
    
    $conn->close();
} else {
    // Redirect if not POST request
    header("Location: ../POSsystem.php");
}
?>