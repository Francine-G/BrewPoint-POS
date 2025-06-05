// Debug JavaScript for BrewPoint POS
// Add this file to your page to help debug payment issues

document.addEventListener('DOMContentLoaded', function() {
    // Add a debug button to the page
    addDebugControls();
    
    // Log the cart contents
    logCartContents();
    
    // Observe DOM changes for troubleshooting
    setupObservers();
});

function addDebugControls() {
    const container = document.createElement('div');
    container.style.position = 'fixed';
    container.style.bottom = '10px';
    container.style.right = '10px';
    container.style.zIndex = '9999';
    container.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
    container.style.padding = '10px';
    container.style.borderRadius = '5px';
    container.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.2)';
    
    const debugButton = document.createElement('button');
    debugButton.textContent = 'Debug Payment';
    debugButton.style.marginRight = '10px';
    debugButton.style.padding = '5px 10px';
    debugButton.addEventListener('click', debugPayment);
    
    const resetCartButton = document.createElement('button');
    resetCartButton.textContent = 'Reset Cart';
    resetCartButton.style.padding = '5px 10px';
    resetCartButton.addEventListener('click', function() {
        localStorage.removeItem('cartItems');
        alert('Cart has been reset!');
        location.reload();
    });
    
    container.appendChild(debugButton);
    container.appendChild(resetCartButton);
    document.body.appendChild(container);
}

function debugPayment() {
    // Create an output div for showing debug information
    let debugOutput = document.getElementById('debugOutput');
    
    if (!debugOutput) {
        debugOutput = document.createElement('div');
        debugOutput.id = 'debugOutput';
        debugOutput.style.position = 'fixed';
        debugOutput.style.top = '50%';
        debugOutput.style.left = '50%';
        debugOutput.style.transform = 'translate(-50%, -50%)';
        debugOutput.style.width = '80%';
        debugOutput.style.maxHeight = '80%';
        debugOutput.style.overflowY = 'auto';
        debugOutput.style.backgroundColor = 'white';
        debugOutput.style.padding = '20px';
        debugOutput.style.border = '1px solid #ccc';
        debugOutput.style.borderRadius = '5px';
        debugOutput.style.zIndex = '10000';
        debugOutput.style.boxShadow = '0 0 10px rgba(0, 0, 0, 0.5)';
        
        const closeBtn = document.createElement('button');
        closeBtn.textContent = 'Close';
        closeBtn.style.position = 'absolute';
        closeBtn.style.top = '10px';
        closeBtn.style.right = '10px';
        closeBtn.addEventListener('click', function() {
            debugOutput.style.display = 'none';
        });
        
        debugOutput.appendChild(closeBtn);
        document.body.appendChild(debugOutput);
    } else {
        debugOutput.style.display = 'block';
    }
    
    // Clear previous content except the close button
    const closeBtn = debugOutput.querySelector('button');
    debugOutput.innerHTML = '';
    debugOutput.appendChild(closeBtn);
    
    // Add debug information
    const heading = document.createElement('h2');
    heading.textContent = 'Payment Debug Information';
    debugOutput.appendChild(heading);
    
    // Cart Items
    const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
    
    const cartSection = document.createElement('div');
    cartSection.innerHTML = `<h3>Cart Items (${cartItems.length})</h3>`;
    
    if (cartItems.length === 0) {
        cartSection.innerHTML += '<p style="color: red;">No items in cart!</p>';
    } else {
        const cartTable = document.createElement('table');
        cartTable.style.width = '100%';
        cartTable.style.borderCollapse = 'collapse';
        cartTable.style.marginBottom = '20px';
        
        // Create table header
        let headerRow = document.createElement('tr');
        ['Name', 'Size', 'Type', 'Qty', 'Base Price', 'Item Price', 'Add-ons'].forEach(text => {
            const th = document.createElement('th');
            th.textContent = text;
            th.style.border = '1px solid #ddd';
            th.style.padding = '8px';
            th.style.textAlign = 'left';
            headerRow.appendChild(th);
        });
        cartTable.appendChild(headerRow);
        
        // Add items to table
        cartItems.forEach(item => {
            const row = document.createElement('tr');
            
            const addCell = (text) => {
                const td = document.createElement('td');
                td.textContent = text;
                td.style.border = '1px solid #ddd';
                td.style.padding = '8px';
                row.appendChild(td);
            };
            
            addCell(item.name || 'Unknown');
            addCell(item.size || 'N/A');
            addCell(item.drinkType || 'N/A');
            addCell(item.quantity || '0');
            addCell(item.basePrice ? '₱' + parseFloat(item.basePrice).toFixed(2) : 'N/A');
            addCell(item.itemPrice ? '₱' + parseFloat(item.itemPrice).toFixed(2) : 'N/A');
            
            const addOns = item.addOns || item.addons || [];
            addCell(Array.isArray(addOns) ? addOns.join(', ') : addOns);
            
            cartTable.appendChild(row);
        });
        
        cartSection.appendChild(cartTable);
    }
    debugOutput.appendChild(cartSection);
    
    // DOM Elements Status
    const elementsSection = document.createElement('div');
    elementsSection.innerHTML = '<h3>DOM Elements Status</h3>';
    
    const elementsToCheck = [
        'orderItems', 'totalAmount', 'calcTotal', 
        'amountReceived', 'changeAmount', 'payBtn', 
        'resetBtn', 'orderDetailsForm', 'total_amount',
        'amount_received', 'change_amount'
    ];
    
    const elemTable = document.createElement('table');
    elemTable.style.width = '100%';
    elemTable.style.borderCollapse = 'collapse';
    
    // Create table header
    let elemHeaderRow = document.createElement('tr');
    ['Element ID', 'Exists', 'Content/Value'].forEach(text => {
        const th = document.createElement('th');
        th.textContent = text;
        th.style.border = '1px solid #ddd';
        th.style.padding = '8px';
        th.style.textAlign = 'left';
        elemHeaderRow.appendChild(th);
    });
    elemTable.appendChild(elemHeaderRow);
    
    // Check each element
    elementsToCheck.forEach(id => {
        const elem = document.getElementById(id);
        const row = document.createElement('tr');
        
        const idCell = document.createElement('td');
        idCell.textContent = id;
        idCell.style.border = '1px solid #ddd';
        idCell.style.padding = '8px';
        row.appendChild(idCell);
        
        const existsCell = document.createElement('td');
        existsCell.textContent = elem ? '✅ Yes' : '❌ No';
        existsCell.style.border = '1px solid #ddd';
        existsCell.style.padding = '8px';
        row.appendChild(existsCell);
        
        const contentCell = document.createElement('td');
        if (elem) {
            if (elem.tagName === 'INPUT') {
                contentCell.textContent = elem.value;
            } else {
                contentCell.textContent = elem.innerText || elem.textContent || 'Empty';
            }
        } else {
            contentCell.textContent = 'N/A';
        }
        contentCell.style.border = '1px solid #ddd';
        contentCell.style.padding = '8px';
        row.appendChild(contentCell);
        
        elemTable.appendChild(row);
    });
    
    elementsSection.appendChild(elemTable);
    debugOutput.appendChild(elementsSection);
    
    // Add a section for window variables
    const varsSection = document.createElement('div');
    varsSection.innerHTML = '<h3>Global Variables</h3>';
    
    const varInfo = document.createElement('p');
    varInfo.innerHTML = `amountReceived: ${window.amountReceived || 'undefined'}<br>`;
    varInfo.innerHTML += `data-order-total: ${document.body.getAttribute('data-order-total') || 'not set'}<br>`;
    
    varsSection.appendChild(varInfo);
    debugOutput.appendChild(varsSection);
    
    // Add fix buttons
    const fixSection = document.createElement('div');
    fixSection.innerHTML = '<h3>Quick Fixes</h3>';
    
    const fixCartBtn = document.createElement('button');
    fixCartBtn.textContent = 'Fix Sample Cart Data';
    fixCartBtn.style.marginRight = '10px';
    fixCartBtn.style.padding = '5px 10px';
    fixCartBtn.addEventListener('click', createSampleCart);
    
    fixSection.appendChild(fixCartBtn);
    debugOutput.appendChild(fixSection);
}

function createSampleCart() {
    const sampleCart = [
        {
            name: "Coffee Latte",
            drinkType: "Hot",
            size: "Medium",
            quantity: 1,
            basePrice: 150.00,
            itemPrice: 150.00,
            addOns: []
        },
        {
            name: "Espresso",
            drinkType: "Hot",
            size: "Small",
            quantity: 2,
            basePrice: 100.00,
            itemPrice: 200.00,
            addOns: ["Extra Shot", "Caramel"]
        }
    ];
    
    localStorage.setItem('cartItems', JSON.stringify(sampleCart));
    alert('Sample cart data created! Reloading page...');
    location.reload();
}

function logCartContents() {
    const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
    console.log('Cart Contents:', cartItems);
    
    // Also log key DOM elements
    console.log('orderItems element:', document.getElementById('orderItems'));
    console.log('totalAmount element:', document.getElementById('totalAmount'));
}

function setupObservers() {
    // Observe changes to key elements
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            console.log('DOM Change detected:', mutation);
        });
    });
    
    const config = { attributes: true, childList: true, characterData: true, subtree: true };
    
    // Observe the order items container
    const orderItems = document.getElementById('orderItems');
    if (orderItems) {
        observer.observe(orderItems, config);
    }
    
    // Observe amount and change displays
    const amountReceived = document.getElementById('amountReceived');
    if (amountReceived) {
        observer.observe(amountReceived, config);
    }
    
    const changeAmount = document.getElementById('changeAmount');
    if (changeAmount) {
        observer.observe(changeAmount, config);
    }
}