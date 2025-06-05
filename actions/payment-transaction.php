<?php
// Include database connection
include("../database/db.php");

// Check if we need to debug
$debug = false;
if (isset($_GET['debug']) && $_GET['debug'] == 1) {
    $debug = true;
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $total_amount = isset($_POST['total_amount']) ? floatval($_POST['total_amount']) : 0;
    $amount_received = isset($_POST['amount_received']) ? floatval($_POST['amount_received']) : 0;
    $change_amount = isset($_POST['change_amount']) ? floatval($_POST['change_amount']) : 0;
    $cart_items_json = isset($_POST['cart_items_json']) ? $_POST['cart_items_json'] : '[]';
    
    // For debugging
    if ($debug) {
        echo "<pre>";
        echo "Total Amount: " . $total_amount . "<br>";
        echo "Amount Received: " . $amount_received . "<br>";
        echo "Change Amount: " . $change_amount . "<br>";
        echo "Cart Items: " . $cart_items_json . "<br>";
        echo "</pre>";
        exit();
    }
    
    // Decode cart items JSON
    $cart_items = json_decode($cart_items_json, true);
    
    // Check if cart items were properly decoded
    if (json_last_error() !== JSON_ERROR_NONE) {
        // Log error
        error_log("JSON decode error: " . json_last_error_msg());
        header("Location: ../payment-error.php?error=json_decode");
        exit();
    }
    
    // Generate a unique order ID
    $order_id = "ORD-" . date("YmdHis") . "-" . rand(1000, 9999);
    
    // Begin transaction to ensure all or nothing is saved
    $conn->begin_transaction();
    
    try {
        // 1. Insert into orders table - SET STATUS TO 'in_progress' FOR NEW ORDERS
        $order_date = date("Y-m-d H:i:s");
        $status = 'in_progress'; // This is the key fix!
        
        $sql_order = "INSERT INTO orders (order_id, order_date, total_amount, amount_received, change_amount, status) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt_order = $conn->prepare($sql_order);
        
        // Check if statement preparation was successful
        if ($stmt_order === false) {
            throw new Exception("Error preparing order statement: " . $conn->error);
        }
        
        $stmt_order->bind_param("ssddds", $order_id, $order_date, $total_amount, $amount_received, $change_amount, $status);
        $stmt_order->execute();
        
        // 2. Insert each item into order_items table
        if (!empty($cart_items)) {
            $sql_items = "INSERT INTO order_items (order_id, product_name, drink_type, size, quantity, base_price, add_ons, item_price) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt_items = $conn->prepare($sql_items);
            
            // Check if statement preparation was successful
            if ($stmt_items === false) {
                throw new Exception("Error preparing order items statement: " . $conn->error);
            }
            
            foreach ($cart_items as $item) {
                // Get the add-ons properly - check both possible properties
                $add_ons_array = isset($item['addOns']) ? $item['addOns'] : (isset($item['addons']) ? $item['addons'] : []);
                $add_ons = is_array($add_ons_array) ? implode(", ", $add_ons_array) : "";
                
                // Make sure we have all required data
                $name = isset($item['name']) ? $item['name'] : "Unknown";
                $drink_type = isset($item['drinkType']) ? $item['drinkType'] : "Standard";
                $size = isset($item['size']) ? $item['size'] : "Regular";
                $quantity = isset($item['quantity']) ? intval($item['quantity']) : 1;
                $base_price = isset($item['basePrice']) ? floatval($item['basePrice']) : 0;
                $item_price = isset($item['itemPrice']) ? floatval($item['itemPrice']) : $base_price * $quantity;
                
                // Bind parameters
                $stmt_items->bind_param("ssssidsd", 
                    $order_id,
                    $name,
                    $drink_type,
                    $size,
                    $quantity,
                    $base_price,
                    $add_ons,
                    $item_price
                );
                
                $stmt_items->execute();
            }
        }
        
        // Commit the transaction
        $conn->commit();
        
        // Redirect to success page with order ID
        header("Location: ../payment-sucess.php?order_id=" . urlencode($order_id));
        exit();
        
    } catch (Exception $e) {
        // Roll back the transaction in case of error
        $conn->rollback();
        
        // Log error (in production, you'd want to log this to a file)
        error_log("Transaction failed: " . $e->getMessage());
        
        // Redirect to error page
        header("Location: ../payment-error.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    // If someone tries to access this page directly without submitting the form
    header("Location: ../POSsystem.php");
    exit();
}
?>