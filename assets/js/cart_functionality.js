document.addEventListener('DOMContentLoaded', function() {
    // Update cart counter on page load
    updateCartCounter();
    
    // Set up cart icon click event to show receipt panel
    const cartIcon = document.getElementById('cartIcon');
    const receiptPanel = document.getElementById('receiptPanel');
    const closeReceipt = document.getElementById('closeReceipt');
    const overlay = document.querySelector('.overlay');
    
    // Check if we're on a page with cart icon and receipt panel
    if (cartIcon && receiptPanel) {
        cartIcon.addEventListener('click', function() {
            receiptPanel.classList.add('active');
            if (overlay) overlay.classList.add('active');
            loadCartItems();
        });
    }
    
    // Close receipt panel when X is clicked
    if (closeReceipt) {
        closeReceipt.addEventListener('click', function() {
            receiptPanel.classList.remove('active');
            if (overlay) overlay.classList.remove('active');
        });
    }
    
    // Close receipt when clicking outside
    if (overlay) {
        overlay.addEventListener('click', function() {
            receiptPanel.classList.remove('active');
            overlay.classList.remove('active');
        });
    }
    
    // Setup checkout button
    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
            
            if (cartItems.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            
            // Process checkout - redirect to payment page
            window.location.href = 'payment.php';
        });
    }
});

// Update the cart counter in header
function updateCartCounter() {
    try {
        const cartCounter = document.getElementById('cartCounter');
        if (!cartCounter) return;
        
        // Get items directly from localStorage
        const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
        cartCounter.textContent = cartItems.length;
    } catch (error) {
        console.error('Error updating cart counter:', error);
        // Set a default value if there's an error
        const cartCounter = document.getElementById('cartCounter');
        if (cartCounter) cartCounter.textContent = '0';
    }
}

// Load cart items into receipt panel
function loadCartItems() {
    try {
        const receiptItems = document.getElementById('receiptItems');
        const subtotalElement = document.getElementById('subtotal');
        const totalElement = document.getElementById('total');
        
        if (!receiptItems || !subtotalElement || !totalElement) return;
        
        // Clear previous content
        receiptItems.innerHTML = '';
        
        // Get cart items directly from localStorage
        const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
        
        if (cartItems.length === 0) {
            receiptItems.innerHTML = '<p class="empty-cart">Your cart is empty</p>';
            subtotalElement.textContent = '₱0.00';
            totalElement.textContent = '₱0.00';
            return;
        }
        
        let subtotal = 0;
        
        // Add each item to receipt
        cartItems.forEach((item, index) => {
            const itemElement = document.createElement('div');
            itemElement.classList.add('receipt-item');
            
            // Generate add-ons text - Handle both addOns and addons properties
            const addOns = item.addOns || item.addons || [];
            const addonsText = addOns.length > 0 
                ? `+ ${addOns.join(', ')}` 
                : 'No add-ons';
            
            // Use itemPrice directly - consistent with payment page
            const itemTotal = parseFloat(item.itemPrice);
            subtotal += itemTotal;
            
            // Create item HTML with consistent formatting
            itemElement.innerHTML = `
                <div class="item-details">
                    <h3>${item.quantity}x ${item.name} (${item.size}, ${item.drinkType})</h3>
                    <p class="item-customization">${addonsText}</p>
                </div>
                <div class="item-price">₱${itemTotal.toFixed(2)}</div>
                <button class="remove-item" data-index="${index}">×</button>
            `;
            
            receiptItems.appendChild(itemElement);
        });
        
        // Add remove item functionality
        const removeButtons = document.querySelectorAll('.remove-item');
        removeButtons.forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                removeCartItem(index);
            });
        });
        
        // Update totals
        subtotalElement.textContent = `₱${subtotal.toFixed(2)}`;
        totalElement.textContent = `₱${subtotal.toFixed(2)}`;
    } catch (error) {
        console.error('Error loading cart items:', error);
        // Display error message in receipt panel
        const receiptItems = document.getElementById('receiptItems');
        if (receiptItems) {
            receiptItems.innerHTML = '<p class="empty-cart">Error loading cart items</p>';
        }
        
        const subtotalElement = document.getElementById('subtotal');
        const totalElement = document.getElementById('total');
        if (subtotalElement) subtotalElement.textContent = '₱0.00';
        if (totalElement) totalElement.textContent = '₱0.00';
    }
}

// Remove item from cart
function removeCartItem(index) {
    try {
        const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
        
        if (index >= 0 && index < cartItems.length) {
            cartItems.splice(index, 1);
            localStorage.setItem('cartItems', JSON.stringify(cartItems));
            
            // Update UI
            updateCartCounter();
            loadCartItems();
        }
    } catch (error) {
        console.error('Error removing cart item:', error);
    }
}