document.addEventListener('DOMContentLoaded', function() {
    // Get product name from URL
    const urlParams = new URLSearchParams(window.location.search);
    const productName = urlParams.get('product');
    
    // Set default values
    let selectedDrinkType = '';
    let selectedSize = '';
    let selectedAddOns = [];
    let quantity = 1;
    
    // Price calculation variables
    const sizeBasePrice = {
        'Medio': 35,
        'Grande': 45
    };
    const addOnPrice = 9; // Each add-on costs 9 PHP
    
    // Initialize option buttons
    initializeOptionButtons();
    
    // Initialize add-on buttons
    initializeAddOnButtons();
    
    // Initialize quantity buttons
    initializeQuantityButtons();
    
    // Initialize add to bill button
    initializeAddToBillButton();
    
    // Initial price calculation
    updateTotalPrice();
    
    // Initialize option buttons
    function initializeOptionButtons() {
        // Drink type buttons
        const drinkTypeButtons = document.querySelectorAll('.types .option-row:nth-child(1) .option-btn');
        drinkTypeButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent form submission
                
                // Remove active class from all buttons in this group
                drinkTypeButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Update selected drink type
                selectedDrinkType = this.textContent;
                
                // Update price
                updateTotalPrice();
            });
        });
        
        // Size buttons
        const sizeButtons = document.querySelectorAll('.types .option-row:nth-child(2) .option-btn');
        sizeButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent form submission
                
                // Remove active class from all buttons in this group
                sizeButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Update selected size
                selectedSize = this.textContent;
                
                // Update price
                updateTotalPrice();
            });
        });
    }
    
    // Initialize add-on buttons
    function initializeAddOnButtons() {
        const addOnButtons = document.querySelectorAll('.addon-buttons .option-btn');
        addOnButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent form submission
                
                // Toggle active class on clicked button
                this.classList.toggle('active');
                
                const addOnName = this.textContent;
                
                // Update selected add-ons array
                if (this.classList.contains('active')) {
                    // Add to selected add-ons if not already included
                    if (!selectedAddOns.includes(addOnName)) {
                        selectedAddOns.push(addOnName);
                    }
                } else {
                    // Remove from selected add-ons
                    const index = selectedAddOns.indexOf(addOnName);
                    if (index !== -1) {
                        selectedAddOns.splice(index, 1);
                    }
                }
                
                // Update price
                updateTotalPrice();
            });
        });
    }
    
    // Initialize quantity buttons
    function initializeQuantityButtons() {
        const decrementBtn = document.querySelector('.option-row-quantity .button-group .option-btn:first-child');
        const incrementBtn = document.querySelector('.option-row-quantity .button-group .option-btn:last-child');
        const quantityDisplay = document.querySelector('.option-row-quantity .button-group .quantity');
        
        if (decrementBtn && incrementBtn && quantityDisplay) {
            decrementBtn.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent form submission
                
                if (quantity > 1) {
                    quantity--;
                    quantityDisplay.textContent = quantity;
                    updateTotalPrice();
                }
            });
            
            incrementBtn.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent form submission
                
                quantity++;
                quantityDisplay.textContent = quantity;
                updateTotalPrice();
            });
        }
    }
    
    // Initialize add to bill button
    function initializeAddToBillButton() {
        const addToBillBtn = document.querySelector('.add-bill-btn');
        
        if (addToBillBtn) {
            addToBillBtn.addEventListener('click', function(e) {
                e.preventDefault(); // Prevent form submission
                
                // Validate selections
                if (!selectedDrinkType) {
                    showNotification('Please select a drink type!');
                    return;
                }
                
                if (!selectedSize) {
                    showNotification('Please select a size!');
                    return;
                }
                
                // Calculate base price based on size
                const basePrice = sizeBasePrice[selectedSize] || 0;
                
                // Calculate item price
                const addOnCost = selectedAddOns.length * addOnPrice;
                const itemPrice = (basePrice + addOnCost) * quantity;
                
                // Create cart item object with all customization details
                const cartItem = {
                    name: productName,
                    drinkType: selectedDrinkType,
                    size: selectedSize,
                    addOns: [...selectedAddOns],
                    quantity: quantity,
                    basePrice: basePrice,
                    itemPrice: itemPrice,
                    addOnCost: addOnCost
                };
                
                // Save to local storage
                addToCart(cartItem);
                
                // Navigate back to POS page
                showNotification('Added to cart!');
                setTimeout(() => {
                    window.location.href = 'POSsystem.php';
                }, 1000);
            });
        }
    }
    
    // Add to cart function
    function addToCart(item) {
        // Get existing cart items or initialize empty array
        const existingCartItems = JSON.parse(localStorage.getItem('cartItems') || '[]');
        
        // Add new item to cart
        existingCartItems.push(item);
        
        // Save updated cart to localStorage
        localStorage.setItem('cartItems', JSON.stringify(existingCartItems));
        
        // Update cart counter
        updateCartCounter(existingCartItems.length);
        
        // Update cart total
        updateCartTotal(existingCartItems);
    }
    
    // Update cart counter in localStorage and UI
    function updateCartCounter(count) {
        // Save to localStorage
        localStorage.setItem('cartCounter', count);
        
        // Update UI counter if it exists
        const cartCounter = document.getElementById('cartCounter');
        if (cartCounter) {
            cartCounter.textContent = count;
        }
    }
    
    // Update cart total in localStorage
    function updateCartTotal(cartItems) {
        let total = 0;
        
        // Calculate total from all items
        cartItems.forEach(item => {
            total += item.itemPrice;
        });
        
        // Save to localStorage
        localStorage.setItem('cartTotal', total.toFixed(2));
    }
    
    // Update total price display based on selections
    function updateTotalPrice() {
        const totalPriceElement = document.getElementById('totalPrice');
        
        if (totalPriceElement) {
            // Base price depending on size
            let basePrice = 0;
            if (selectedSize === 'Medio') {
                basePrice = 35;
            } else if (selectedSize === 'Grande') {
                basePrice = 45;
            }
            
            // Add-on costs
            const addOnCost = selectedAddOns.length * addOnPrice;
            
            // Calculate total
            const totalPrice = (basePrice + addOnCost) * quantity;
            
            // Update display
            totalPriceElement.textContent = `₱${totalPrice.toFixed(2)}`;
        }
    }
    
    // Load cart counter on page load
    function loadCartCounter() {
        const cartCounter = document.getElementById('cartCounter');
        if (cartCounter) {
            // Get cart items directly and count them
            const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
            cartCounter.textContent = cartItems.length.toString();
        }
    }
    
    // Call this function on page load
    loadCartCounter();

    
    // Show notification
    function showNotification(message) {
        // Create notification element if it doesn't exist
        let notification = document.getElementById('notification');
        if (!notification) {
            notification = document.createElement('div');
            notification.id = 'notification';
            notification.style.position = 'fixed';
            notification.style.bottom = '20px';
            notification.style.right = '20px';
            notification.style.backgroundColor = '#bd6c1f';
            notification.style.color = 'white';
            notification.style.padding = '10px 20px';
            notification.style.borderRadius = '5px';
            notification.style.zIndex = '1000';
            notification.style.opacity = '0';
            notification.style.transition = 'opacity 0.3s ease';
            document.body.appendChild(notification);
        }
        
        // Update message and show notification
        notification.textContent = message;
        notification.style.opacity = '1';
        
        // Hide notification after 3 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
        }, 3000);
    }
    
    // Load cart counter on page load
    loadCartCounter();
});