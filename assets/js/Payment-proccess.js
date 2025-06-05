document.addEventListener('DOMContentLoaded', function() {
    // Load cart items from localStorage
    const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
    
    // Display order items and calculate total
    displayOrderItems(cartItems);
    
    // Initialize payment buttons
    initializePaymentButtons();
    
    // Initialize reset button
    initializeResetButton();
    
    // Initialize pay button
    initializePayButton();
    
    // Initialize modal close button
    initializeModalClose();
});

// Display order items in the payment page
function displayOrderItems(cartItems) {
    const orderItemsContainer = document.getElementById('orderItems');
    const totalAmountElement = document.getElementById('totalAmount');
    const calcTotalElement = document.getElementById('calcTotal');
    
    if (!orderItemsContainer || !totalAmountElement || !calcTotalElement) return;
    
    // Clear previous content
    orderItemsContainer.innerHTML = '';
    
    // Create hidden form fields for order details
    const orderDetailsForm = document.getElementById('orderDetailsForm');
    if (orderDetailsForm) {
        // Create hidden input for cart items JSON
        let cartItemsInput = document.querySelector('input[name="cart_items_json"]');
        if (!cartItemsInput) {
            cartItemsInput = document.createElement('input');
            cartItemsInput.type = 'hidden';
            cartItemsInput.name = 'cart_items_json';
            orderDetailsForm.appendChild(cartItemsInput);
        }
        cartItemsInput.value = JSON.stringify(cartItems);
    }
    
    if (cartItems.length === 0) {
        orderItemsContainer.innerHTML = '<p class="empty-order">No items in order</p>';
        totalAmountElement.textContent = '₱0.00';
        calcTotalElement.textContent = '₱0.00';
        return;
    }
    
    let total = 0;
    
    // Add each item to order display
    cartItems.forEach((item, index) => {
        const itemElement = document.createElement('div');
        itemElement.classList.add('order-item');
        
        // Handle both addOns and addons properties consistently
        const addOns = item.addOns || item.addons || [];
        const addonsText = addOns.length > 0 
            ? `+ ${addOns.join(', ')}` 
            : 'No add-ons';
        
        // Use itemPrice directly if available
        const itemPrice = parseFloat(item.itemPrice);
        total += itemPrice;
        
        // Create item HTML - ensure we're showing exactly what was selected
        itemElement.innerHTML = `
            <div class="item-details">
                <h3>${item.quantity}x ${item.name} (${item.size}, ${item.drinkType})</h3>
                <p class="item-customization">${addonsText}</p>
            </div>
            <div class="item-price">₱${itemPrice.toFixed(2)}</div>
        `;
        
        orderItemsContainer.appendChild(itemElement);
    });
    
    // Add items to the hidden form fields
    if (orderDetailsForm) {
        // Add total amount
        const totalInput = document.getElementById('total_amount');
        if (totalInput) totalInput.value = total.toFixed(2);
    }
    
    // Update totals
    totalAmountElement.textContent = `₱${total.toFixed(2)}`;
    calcTotalElement.textContent = `₱${total.toFixed(2)}`;
    
    // Store order total in a data attribute for easy access
    document.body.setAttribute('data-order-total', total.toFixed(2));
}

// Initialize payment buttons
function initializePaymentButtons() {
    const paymentButtons = document.querySelectorAll('.payment-btn');
    const amountReceivedElement = document.getElementById('amountReceived');
    const changeAmountElement = document.getElementById('changeAmount');
    const payButton = document.getElementById('payBtn');    
    const amountReceivedInput = document.getElementById('amount_received');
    const changeInput = document.getElementById('change_amount');
    
    if (!amountReceivedElement || !changeAmountElement || !payButton) return;
    
    // Initialize amount received globally so it can be reset properly
    window.amountReceived = 0; 
    
    paymentButtons.forEach(button => {
        button.addEventListener('click', function() {
            const value = parseFloat(this.getAttribute('data-value'));
            if (!isNaN(value)) {
                window.amountReceived += value;
                
                // Update displayed amount received
                amountReceivedElement.textContent = `₱${window.amountReceived.toFixed(2)}`;
                
                // Update hidden form field
                if (amountReceivedInput) {
                    amountReceivedInput.value = window.amountReceived.toFixed(2);
                }
                
                // Calculate change
                const orderTotal = parseFloat(document.body.getAttribute('data-order-total') || 0);
                const change = window.amountReceived - orderTotal;
                
                // Update displayed change amount
                changeAmountElement.textContent = change >= 0 ? `₱${change.toFixed(2)}` : '₱0.00';
                
                // Update hidden form field
                if (changeInput) {
                    changeInput.value = change >= 0 ? change.toFixed(2) : '0.00';
                }
                
                // Enable pay button if enough money received
                payButton.disabled = change < 0;
            }
        });
    });
}

// Initialize reset button
function initializeResetButton() {
    const resetButton = document.getElementById('resetBtn');
    const amountReceivedElement = document.getElementById('amountReceived');
    const changeAmountElement = document.getElementById('changeAmount');
    const payButton = document.getElementById('payBtn');
    const amountReceivedInput = document.getElementById('amount_received');
    const changeInput = document.getElementById('change_amount');
    
    if (!resetButton || !amountReceivedElement || !changeAmountElement || !payButton) return;
    
    resetButton.addEventListener('click', function() {
        // Reset the global amount received variable
        window.amountReceived = 0;
        
        // Reset displayed values
        amountReceivedElement.textContent = '₱0.00';
        changeAmountElement.textContent = '₱0.00';
        
        // Reset hidden form fields
        if (amountReceivedInput) amountReceivedInput.value = '0.00';
        if (changeInput) changeInput.value = '0.00';
        
        // Disable pay button
        payButton.disabled = true;
    });
}

// Generate PDF Receipt Function
function generatePDFReceipt() {
    try {
        // Check if jsPDF is available
        if (typeof window.jspdf === 'undefined') {
            console.error('jsPDF library not loaded');
            alert('PDF generation library not available. Please try again.');
            return false;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        
        // Get cart items and order details
        const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
        const orderTotal = parseFloat(document.body.getAttribute('data-order-total') || 0);
        const amountReceived = parseFloat(document.getElementById('amount_received').value || 0);
        const changeAmount = parseFloat(document.getElementById('change_amount').value || 0);
        
        // Generate Order ID (you might want to get this from your backend)
        const now = new Date();
        const orderId = `ORD-${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, '0')}${String(now.getDate()).padStart(2, '0')}${String(now.getHours()).padStart(2, '0')}${String(now.getMinutes()).padStart(2, '0')}-${Math.floor(Math.random() * 10000)}`;
        
        // Set font
        doc.setFont('courier');
        
        // Header
        doc.setFontSize(16);
        doc.text('BREWPOINT POS', 105, 20, { align: 'center' });
        doc.setFontSize(14);
        doc.text('RECEIPT', 105, 30, { align: 'center' });
        
        // Draw line
        doc.line(20, 35, 190, 35);
        
        // Order info
        doc.setFontSize(10);
        let y = 50;
        doc.text(`Date: ${now.toLocaleString()}`, 20, y);
        y += 8;
        doc.text(`Order ID: ${orderId}`, 20, y);
        y += 15;
        
        // Items header
        doc.line(20, y, 190, y);
        y += 8;
        doc.setFontSize(12);
        doc.text('ITEMS PURCHASED:', 20, y);
        y += 8;
        doc.line(20, y, 190, y);
        y += 10;
        
        // Items list
        doc.setFontSize(9);
        
        cartItems.forEach(item => {
            // Handle both addOns and addons properties consistently
            const addOns = item.addOns || item.addons || [];
            const addonsText = addOns.length > 0 ? ` + ${addOns.join(', ')}` : '';
            const itemPrice = parseFloat(item.itemPrice);
            
            // Item name with quantity and customization
            const itemText = `${item.quantity}x ${item.name} (${item.size}, ${item.drinkType})${addonsText}`;
            
            // Split long text if needed
            const lines = doc.splitTextToSize(itemText, 150);
            lines.forEach(line => {
                doc.text(line, 20, y);
                y += 6;
            });
            
            // Price
            doc.text(`₱${itemPrice.toFixed(2)}`, 190, y - 6, { align: 'right' });
            y += 4;
        });
        
        // Payment details section
        y += 10;
        doc.line(20, y, 190, y);
        y += 10;
        doc.setFontSize(10);
        
        doc.text(`Subtotal: ₱${orderTotal.toFixed(2)}`, 20, y);
        y += 8;
        doc.setFontSize(12);
        doc.text(`TOTAL: ₱${orderTotal.toFixed(2)}`, 20, y);
        y += 10;
        doc.setFontSize(10);
        doc.text(`Amount Received: ₱${amountReceived.toFixed(2)}`, 20, y);
        y += 8;
        doc.text(`Change: ₱${changeAmount.toFixed(2)}`, 20, y);
        y += 8;
        doc.line(20, y, 190, y);
        
        // Footer
        y += 15;
        doc.setFontSize(10);
        doc.text('Thank you for your order!', 105, y, { align: 'center' });
        y += 8;
        doc.text('Visit us again soon!', 105, y, { align: 'center' });
        
        // Generate filename
        const filename = `Receipt_${orderId}.pdf`;
        
        // Save the PDF
        doc.save(filename);
        
        return true;
    } catch (error) {
        console.error('Error generating PDF:', error);
        alert('Error generating receipt. The payment will still be processed.');
        return false;
    }
}

// Initialize pay button
function initializePayButton() {
    const payButton = document.getElementById('payBtn');
    const successModal = document.getElementById('successModal');
    const orderIdDisplay = document.getElementById('orderIdDisplay');
    const orderDetailsForm = document.getElementById('orderDetailsForm');
    
    if (!payButton || !orderDetailsForm) return;
    
    payButton.addEventListener('click', function() {
        // Check if we have a valid order
        const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
        if (cartItems.length === 0) {
            alert('No items in cart. Please add items before payment.');
            return;
        }
        
        // Disable the button to prevent double clicks
        payButton.disabled = true;
        payButton.textContent = 'Generating Receipt...';
        
        // Generate PDF receipt first
        const receiptGenerated = generatePDFReceipt();
        
        // Small delay to ensure PDF generation completes
        setTimeout(() => {
            // Submit the form to save order to database
            orderDetailsForm.submit();
            
            // The form will redirect to a success page
        }, 1000);
    });
}

// Initialize modal close button
function initializeModalClose() {
    const closeModal = document.querySelector('.close-modal');
    const successModal = document.getElementById('successModal');
    const doneButton = document.getElementById('doneBtn');
    
    if (!closeModal || !successModal || !doneButton) return;
    
    // Close modal and redirect to POS page
    const closeAndRedirect = function() {
        successModal.style.display = 'none';
        
        // Clear cart
        localStorage.removeItem('cartItems');
        
        // Redirect to POS page
        window.location.href = 'POSsystem.php';
    };
    
    // Set up event listeners
    closeModal.addEventListener('click', closeAndRedirect);
    doneButton.addEventListener('click', closeAndRedirect);
}   