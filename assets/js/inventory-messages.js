// Inventory Success and Error Messages System
// Add this script to your inventory.php page

document.addEventListener('DOMContentLoaded', function() {
    // Get URL parameters to check for success/error messages
    const urlParams = new URLSearchParams(window.location.search);
    const successType = urlParams.get('success');
    const errorType = urlParams.get('error');
    
    // Success Messages
    const successMessages = {
        // Stock In Success
        'stock_added': {
            title: 'Stock Added Successfully!',
            message: 'New stock has been added to inventory. Stock levels updated.',
            icon: '✅'
        },
        
        // Stock Out Success
        'stock_removed': {
            title: 'Stock Removed Successfully!',
            message: 'Stock has been removed from inventory using FIFO method. Stock levels updated.',
            icon: '📦'
        },
        
        // Edit Item Success
        'item_updated': {
            title: 'Item Updated Successfully!',
            message: 'Item details have been updated and stock levels recalculated.',
            icon: '✏️'
        },
        
        // Add Item Success (if you have this feature)
        'item_added': {
            title: 'Item Added Successfully!',
            message: 'New item has been added to the inventory system.',
            icon: '➕'
        },
        
        // Delete Item Success (handled in delete-items.php with alert, but can be added here)
        'item_deleted': {
            title: 'Item Deleted Successfully!',
            message: 'Item and all associated stock batches have been removed from inventory.',
            icon: '🗑️'
        }
    };
    
    // Error Messages
    const errorMessages = {
        // Stock In Errors
        'empty_fields': {
            title: 'Invalid Input!',
            message: 'Please fill in all required fields with valid values.',
            icon: '⚠️'
        },
        'expired_date': {
            title: 'Invalid Expiry Date!',
            message: 'Cannot add stock with an expiry date in the past.',
            icon: '📅'
        },
        'item_not_found': {
            title: 'Item Not Found!',
            message: 'The selected item does not exist in the inventory.',
            icon: '❌'
        },
        'database_error': {
            title: 'Database Error!',
            message: 'A database error occurred. Please try again or contact support.',
            icon: '💾'
        },
        
        // Stock Out Errors
        'insufficient_stock': {
            title: 'Insufficient Stock!',
            message: 'Not enough stock available to complete this operation.',
            icon: '📉'
        },
        
        // Edit Item Errors
        'duplicate_item': {
            title: 'Duplicate Item!',
            message: 'An item with this name and category already exists.',
            icon: '🔄'
        },
        
        // General Errors
        'invalid_data': {
            title: 'Invalid Data!',
            message: 'Please check your input and try again.',
            icon: '❌'
        },
        'permission_denied': {
            title: 'Permission Denied!',
            message: 'You do not have permission to perform this action.',
            icon: '🚫'
        },
        'system_error': {
            title: 'System Error!',
            message: 'An unexpected error occurred. Please try again later.',
            icon: '⚠️'
        }
    };
    
    // Create and show popup function
    function showPopup(type, config) {
        // Remove any existing popups
        const existingPopup = document.getElementById('inventory-popup');
        if (existingPopup) {
            existingPopup.remove();
        }
        
        // Create popup HTML
        const popupHTML = `
            <div id="inventory-popup" class="popup-overlay" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
                animation: fadeIn 0.3s ease-out;
            ">
                <div class="popup-content" style="
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                    max-width: 400px;
                    width: 90%;
                    text-align: center;
                    animation: slideIn 0.3s ease-out;
                    border-left: 5px solid ${type === 'success' ? '#4CAF50' : '#f44336'};
                ">
                    <div class="popup-icon" style="
                        font-size: 50px;
                        margin-bottom: 15px;
                        color: ${type === 'success' ? '#4CAF50' : '#f44336'};
                    ">
                        ${config.icon}
                    </div>
                    <h2 style="
                        color: ${type === 'success' ? '#4CAF50' : '#f44336'};
                        margin-bottom: 10px;
                        font-size: 24px;
                        font-weight: bold;
                    ">
                        ${config.title}
                    </h2>
                    <p style="
                        color: #666;
                        margin-bottom: 25px;
                        font-size: 16px;
                        line-height: 1.5;
                    ">
                        ${config.message}
                    </p>
                    <button id="popup-close-btn" style="
                        background-color: ${type === 'success' ? '#4CAF50' : '#f44336'};
                        color: white;
                        border: none;
                        padding: 12px 30px;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 16px;
                        font-weight: bold;
                        transition: background-color 0.3s;
                    ">
                        OK
                    </button>
                </div>
            </div>
            
            <style>
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                
                @keyframes slideIn {
                    from { 
                        transform: translateY(-50px);
                        opacity: 0;
                    }
                    to { 
                        transform: translateY(0);
                        opacity: 1;
                    }
                }
                
                #popup-close-btn:hover {
                    background-color: ${type === 'success' ? '#45a049' : '#da190b'} !important;
                    transform: translateY(-2px);
                }
            </style>
        `;
        
        // Add popup to page
        document.body.insertAdjacentHTML('beforeend', popupHTML);
        
        // Add event listeners
        const popup = document.getElementById('inventory-popup');
        const closeBtn = document.getElementById('popup-close-btn');
        
        // Close popup function
        function closePopup() {
            popup.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => {
                popup.remove();
                // Clean URL parameters
                const url = new URL(window.location);
                url.searchParams.delete('success');
                url.searchParams.delete('error');
                window.history.replaceState({}, document.title, url.toString());
            }, 300);
        }
        
        // Close button click
        closeBtn.addEventListener('click', closePopup);
        
        // Close on overlay click
        popup.addEventListener('click', function(e) {
            if (e.target === popup) {
                closePopup();
            }
        });
        
        // Close on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePopup();
            }
        });
        
        // Auto-close after 5 seconds for success messages
        if (type === 'success') {
            setTimeout(closePopup, 5000);
        }
    }
    
    // Check for success messages
    if (successType && successMessages[successType]) {
        showPopup('success', successMessages[successType]);
    }
    
    // Check for error messages
    if (errorType && errorMessages[errorType]) {
        showPopup('error', errorMessages[errorType]);
    }
    
    // Manual popup trigger function (can be called from other scripts)
    window.showInventoryMessage = function(type, messageKey, customMessage = null) {
        const messages = type === 'success' ? successMessages : errorMessages;
        
        if (customMessage) {
            showPopup(type, customMessage);
        } else if (messages[messageKey]) {
            showPopup(type, messages[messageKey]);
        }
    };
    
    // Add CSS for fadeOut animation
    if (!document.getElementById('popup-animations')) {
        const style = document.createElement('style');
        style.id = 'popup-animations';
        style.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
});