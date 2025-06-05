<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <script src="https://kit.fontawesome.com/f4e628f07c.js" crossorigin="anonymous"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
         <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-rounded/css/uicons-solid-rounded.css'>
        <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-straight/css/uicons-solid-straight.css'>
        <link rel="stylesheet" href="assets/css/Payment-style.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>


        <title>BrewPoint POS</title>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <a href = "POSsystem.php"><i class="fi fi-sr-arrow-small-left"></i></a>
                <h1>Payment</h1>
            </div>

            <div class="content">
                <div class="sides">
                    <div class="left-side">
                        <div class="left-box">
                            <div class="transaction">
                                <div class="transaction-header">
                                    <h2>Payment Details</h2>
                                </div>

                                <div class="transaction-details">
                                    <div class="transaction-items" id="orderItems">
                                    <!-- Order items will be dynamically added here -->
                                    </div>
                                    <div class="transaction-total">
                                        <h2>Total</h2>
                                        <h2 id="totalAmount">₱0.00</h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="right-side">
                        <div class="right-box">
                            <div class="payment-content">
                                <h2>Payment Method</h2>

                                <div class="payment-calculation">
                                    <div class="calculation-row">
                                        <span>Total Amount:</span>
                                        <span id="calcTotal">₱0.00</span>
                                    </div>
                                    <div class="calculation-row">
                                        <span>Amount Received:</span>
                                        <span id="amountReceived">₱0.00</span>
                                    </div>
                                    <div class="calculation-row">
                                        <span>Change:</span>
                                        <span id="changeAmount">₱0.00</span>
                                    </div>
                                </div>

                                <div class="payment-method">
                                    <div class="payment-buttons">
                                        <button class="payment-btn" data-value="1">₱1</button>
                                        <button class="payment-btn" data-value="5">₱5</button>
                                        <button class="payment-btn" data-value="10">₱10</button>
                                        <button class="payment-btn" data-value="20">₱20</button>
                                        <button class="payment-btn" data-value="50">₱50</button>
                                        <button class="payment-btn" data-value="100">₱100</button>
                                        <button class="payment-btn" data-value="200">₱200</button>
                                        <button class="payment-btn" data-value="500">₱500</button>
                                        <button class="payment-btn" data-value="1000">₱1000</button>
                                    </div>
                                </div>

                                <div class="action-buttons">
                                    <button id="resetBtn" class="reset-btn">Reset</button>
                                    <button id="payBtn" class="pay-btn" disabled>Pay & Print Invoice</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hidden form to submit order data to database -->
        <form id="orderDetailsForm" action="actions/payment-transaction.php" method="POST" style="display: none;">
            <input type="hidden" id="total_amount" name="total_amount" value="0.00">
            <input type="hidden" id="amount_received" name="amount_received" value="0.00">
            <input type="hidden" id="change_amount" name="change_amount" value="0.00">
            <!-- Additional cart items will be added dynamically via JavaScript -->
        </form>

        <div id="successModal" class="modal">
            <div class="modal-content">
                <span class="close-modal">&times;</span>
                <div class="success-message">
                    <i class="bx bx-check-circle"></i>
                    <h2>Order Successful!</h2>
                    <p>Order ID: <span id="orderIdDisplay"></span></p>
                    <p>Your invoice is being printed...</p>
                    <button id="doneBtn" class="done-btn">Done</button>
                </div>
            </div>
        </div>

        <script src = "assets/js/Payment-proccess.js"></script>

    </body>
</html>