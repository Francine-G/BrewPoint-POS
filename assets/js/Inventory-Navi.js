document.addEventListener('DOMContentLoaded', function() {
    // Handle delete item buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-item-btn')) {
            const button = e.target.closest('.delete-item-btn');
            const itemId = button.getAttribute('data-item-id');
            const itemName = button.closest('tr').querySelector('td:first-child strong').textContent;
            
            // Confirm deletion
            if (confirm(`Are you sure you want to delete "${itemName}"? This action cannot be undone.`)) {
                // Create a form to submit the delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'actions/delete-items.php';
                form.style.display = 'none';
                
                // Create hidden input for item ID
                const itemIdInput = document.createElement('input');
                itemIdInput.type = 'hidden';
                itemIdInput.name = 'itemID';
                itemIdInput.value = itemId;
                
                // Create hidden input for delete action
                const deleteInput = document.createElement('input');
                deleteInput.type = 'hidden';
                deleteInput.name = 'delete-item';
                deleteInput.value = '1';
                
                // Append inputs to form
                form.appendChild(itemIdInput);
                form.appendChild(deleteInput);
                
                // Append form to body and submit
                document.body.appendChild(form);
                form.submit();
            }
        }
    });

    // Handle edit item buttons using event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-item-btn')) {
            const button = e.target.closest('.edit-item-btn');
            const itemId = button.getAttribute('data-item-id');
            // Redirect to edit page
            window.location.href = `edit-item.php?id=${itemId}`;
        }
    });
    
    // Handle batch item buttons using event delegation
    document.addEventListener('click', function(e) {
        if (e.target.closest('.batch-item-btn')) {
            const button = e.target.closest('.batch-item-btn');
            const itemId = button.getAttribute('data-item-id');
            const itemName = button.closest('tr').querySelector('td:first-child strong').textContent;
            
            // Show batch modal
            showBatchModal(itemId, itemName);
        }
    });

    // Handle navigation to inventory details (static elements)
    const viewallBtn = document.querySelector('.ViewAll .VA-btn');
    if (viewallBtn) {
        viewallBtn.addEventListener('click', function() {
            window.location.href = 'inventory-details.php';
        });
    }
    
    // Handle Add Item button (static element)
    const addItemBtn = document.querySelector('.tbpg-functions .add-item-btn');
    if (addItemBtn) {
        addItemBtn.addEventListener('click', function() {
            window.location.href = 'add-item.php';
        });
    }
    
    // Handle Stock In button (static element)
    const stockInBtn = document.querySelector('.stock-in-btn');
    if (stockInBtn) {
        stockInBtn.addEventListener('click', function() {
            window.location.href = 'stock-in.php';
        });
    }
    
    // Handle Stock Out button (static element)
    const stockOutBtn = document.querySelector('.stock-out-btn');
    if (stockOutBtn) {
        stockOutBtn.addEventListener('click', function() {
            window.location.href = 'stock-out.php';
        });
    }

    // Handle cancel buttons (static elements)
    const cancelBtns = document.querySelectorAll('.cancel-item-btn');
    cancelBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            window.location.href = 'inventory-details.php';
        });
    });

    // Handle modal close events (static elements)
    const modal = document.getElementById('batchModal');
    const closeBtn = document.querySelector('.batch-modal .close');
    
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            closeBatchModal();
        });
    }
    
    if (modal) {
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeBatchModal();
            }
        });
    }
});

// Function to show batch modal
async function showBatchModal(itemId, itemName) {
    const modal = document.getElementById('batchModal');
    const modalTitle = document.getElementById('batchModalTitle');
    const modalBody = document.getElementById('batchModalBody');
    
    // Set modal title
    modalTitle.textContent = `Batches for: ${itemName}`;
    
    // Show loading state
    modalBody.innerHTML = '<div class="loading">Loading batches...</div>';
    
    // Show modal
    modal.style.display = 'block';
    
    try {
        // Fetch batch data
        const response = await fetch(`actions/get-item-batch.php?itemId=${itemId}`);
        const data = await response.json();
        
        if (data.success) {
            displayBatches(data.batches, itemId);
        } else {
            modalBody.innerHTML = `<div class="error">Error loading batches: ${data.message}</div>`;
        }
    } catch (error) {
        modalBody.innerHTML = '<div class="error">Error loading batches. Please try again.</div>';
        console.error('Error fetching batches:', error);
    }
}

// Function to display batches in modal
function displayBatches(batches, itemId) {
    const modalBody = document.getElementById('batchModalBody');
    
    if (!batches || batches.length === 0) {
        modalBody.innerHTML = '<div class="no-batches">No batches found for this item.</div>';
        return;
    }
    
    let html = '<div class="batch-list">';
    
    batches.forEach((batch, index) => {
        const expiryDate = new Date(batch.expiryDate);
        const today = new Date();
        const daysUntilExpiry = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));
        
        let expiryClass = 'expiry-normal';
        let expiryText = '';
        
        if (daysUntilExpiry < 0) {
            expiryClass = 'expired';
            expiryText = `Expired (${Math.abs(daysUntilExpiry)} days ago)`;
        } else if (daysUntilExpiry <= 7) {
            expiryClass = 'expiry-critical';
            expiryText = `${daysUntilExpiry} days left`;
        } else if (daysUntilExpiry <= 30) {
            expiryClass = 'expiry-warning';
            expiryText = `${daysUntilExpiry} days left`;
        } else {
            expiryText = `${daysUntilExpiry} days left`;
        }
        
        html += `
            <div class="batch-item">
                <div class="batch-header">
                    <span class="batch-number">Batch #${index + 1}</span>
                    <span class="batch-date">Added: ${formatDate(batch.dateAdded)}</span>
                </div>
                <div class="batch-details">
                    <div class="batch-info">
                        <span class="batch-quantity">Quantity: <strong>${batch.quantity}</strong></span>
                        <span class="batch-expiry ${expiryClass}">
                            Expires: ${formatDate(batch.expiryDate)} (${expiryText})
                        </span>
                    </div>
                </div>
            </div>
        `;
    });   
    
    modalBody.innerHTML = html;
}

// Function to close batch modal
function closeBatchModal() {
    const modal = document.getElementById('batchModal');
    modal.style.display = 'none';
}

// Function to format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Additional utility functions for inventory management
function refreshInventoryTable() {
    // Instead of reloading the page, reload the inventory data
    if (typeof loadInventory === 'function') {
        loadInventory(currentPage, currentSearch, currentCategory, currentStockLevel);
    } else {
        window.location.reload();
    }
}

function showSuccessMessage(message) {
    alert(message);
}

function showErrorMessage(message) {
    alert('Error: ' + message);
}