<?php
    // Get the order ID from URL parameter
    $order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';
    
    // If no order ID is provided, redirect to POS page
    if (empty($order_id)) {
        header("Location: POSsystem.php");
        exit();
    }
    
    // Include database connection
    include("database/db.php");
    
    // Get order details for display
    $sql = "SELECT * FROM orders WHERE order_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Default values
    $order_date = "";
    $total_amount = 0;
    $amount_received = 0;
    $change_amount = 0;
    
    // Fetch order details
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $order_date = $row["order_date"];
        $total_amount = $row["total_amount"];
        $amount_received = $row["amount_received"];
        $change_amount = $row["change_amount"];
    }
?>

<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <script src="https://kit.fontawesome.com/f4e628f07c.js" crossorigin="anonymous"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
        <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-straight/css/uicons-solid-straight.css'>
        <link rel="stylesheet" href="assets/css/payment-style.css">
        <title>BrewPoint POS - Payment Success</title>
        <style>
            .success-container {
                max-width: 600px;
                margin: 50px auto;
                padding: 30px;
                background-color: #fff;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                text-align: center;
            }
            
            .success-icon {
                font-size: 60px;
                color: #4CAF50;
                margin-bottom: 20px;
            }
            
            .success-details {
                margin: 30px 0;
                text-align: left;
            }
            
            .success-details div {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
                padding: 5px 0;
                border-bottom: 1px dashed #eee;
            }
            
            .return-btn {
                background-color: #bd6c1f;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                transition: background-color 0.3s;
                margin-top: 20px;
            }
            
            .return-btn:hover {
                background-color: #a75d18;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="success-container">
                <i class="bx bx-check-circle success-icon"></i>
                <h1>Payment Successful!</h1>
                <p>Your order has been processed and saved in our system.</p>
                
                <div class="success-details">
                    <div>
                        <span>Order ID:</span>
                        <span><?php echo htmlspecialchars($order_id); ?></span>
                    </div>
                    <div>
                        <span>Date:</span>
                        <span><?php echo htmlspecialchars($order_date); ?></span>
                    </div>
                    <div>
                        <span>Total Amount:</span>
                        <span>₱<?php echo number_format($total_amount, 2); ?></span>
                    </div>
                    <div>
                        <span>Amount Received:</span>
                        <span>₱<?php echo number_format($amount_received, 2); ?></span>
                    </div>
                    <div>
                        <span>Change:</span>
                        <span>₱<?php echo number_format($change_amount, 2); ?></span>
                    </div>
                </div>
                
                <p>The invoice has been printed.</p>
                
                <button class="return-btn" onclick="window.location.href='POSsystem.php'">Return to POS</button>
            </div>
        </div>
        
        <script>
            // Clear the cart on successful payment
            document.addEventListener('DOMContentLoaded', function() {
                localStorage.removeItem('cartItems');
            });
        </script>
    </body>
</html>