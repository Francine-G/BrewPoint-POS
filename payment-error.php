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
        <title>BrewPoint POS - Payment Error</title>
        <style>
            .error-container {
                max-width: 600px;
                margin: 50px auto;
                padding: 30px;
                background-color: #fff;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                text-align: center;
            }
            
            .error-icon {
                font-size: 60px;
                color: #f44336;
                margin-bottom: 20px;
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
            
            .try-again-btn {
                background-color: #4CAF50;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
                transition: background-color 0.3s;
                margin-top: 20px;
                margin-left: 10px;
            }
            
            .try-again-btn:hover {
                background-color: #45a049;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="error-container">
                <i class="bx bx-error-circle error-icon"></i>
                <h1>Payment Processing Error</h1>
                <p>We're sorry, but there was an error processing your payment.</p>
                <p>The transaction could not be completed at this time.</p>
                
                <div>
                    <button class="return-btn" onclick="window.location.href='POSsystem.php'">Return to POS</button>
                    <button class="try-again-btn" onclick="window.location.href='payment.php'">Try Again</button>
                </div>
            </div>
        </div>
    </body>
</html>