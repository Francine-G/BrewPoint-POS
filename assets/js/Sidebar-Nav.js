// Enhanced Sidebar Navigation Script with Quick Actions
document.addEventListener('DOMContentLoaded', function() {
    // Get current page filename
    const currentPage = window.location.pathname.split('/').pop() || 'dashboard.php';
    
    // Map of pages to their corresponding navigation links
    const pageMap = {
        'dashboard.php': 'dashboard.php',
        'POSsystem.php': 'POSsystem.php', 
        'orders.php': 'orders.php',
        'inventory.php': 'inventory.php',
        'sales.php': 'sales.php',
        'supplier_details.php': 'supplier_details.php'
    };
    
    // Get all navigation links
    const navLinks = document.querySelectorAll('.menu-content li a');
    
    // Remove active class from all links first
    navLinks.forEach(link => {
        link.classList.remove('active');
        link.parentElement.classList.remove('active');
    });
    
    // Find and activate current page link
    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage || href === pageMap[currentPage]) {
            link.classList.add('active');
            link.parentElement.classList.add('active');
        }
    });
    
    // Add click event listeners for immediate feedback
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Remove active from all
            navLinks.forEach(l => {
                l.classList.remove('active');
                l.parentElement.classList.remove('active');
            });
            
            // Add active to clicked item
            this.classList.add('active');
            this.parentElement.classList.add('active');
        });
    });

    // Settings button click handler
    const settingsBtn = document.querySelector('.content .header .user-icon i');
    if (settingsBtn) {
        settingsBtn.addEventListener('click', function() {
            window.location.href = 'user-settings.php';
        });
    }

    // Alternative: You can also target the entire user-icon div if you prefer
    const userIconDiv = document.querySelector('.content .header .user-icon');
    if (userIconDiv) {
        userIconDiv.style.cursor = 'pointer';
        userIconDiv.addEventListener('click', function() {
            window.location.href = 'user-settings.php';
        });
    }

    // ========== QUICK ACTIONS FUNCTIONALITY ==========
    initializeQuickActions();
});

function initializeQuickActions() {
    // Quick Actions Event Listeners
    const quickActionButtons = document.querySelectorAll('.quick-actions .action-button');
    
    quickActionButtons.forEach(button => {
        // Get the button text or icon to determine action type
        const buttonText = button.textContent.trim();
        const hasIcon = button.querySelector('i');
        
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Add visual feedback
            addButtonClickEffect(this);
            
            // Determine action based on button content
            if (buttonText.includes('Create Order') || hasIcon && hasIcon.classList.contains('fi-rs-plus')) {
                handleCreateOrder();
            } else if (buttonText.includes('View Sales') || hasIcon && hasIcon.classList.contains('fi-rs-file-chart-line')) {
                handleViewSales();
            } else if (buttonText.includes('Low Stock') || hasIcon && hasIcon.classList.contains('fi-rs-bell')) {
                handleViewLowStock();
            } else if (buttonText.includes('Download Sales Report') || hasIcon && hasIcon.classList.contains('fa-file-pdf')) {
                handleDownloadSalesReport();
            }
        });
    });
}

// Quick Action Handlers
function handleCreateOrder() {
    showLoadingMessage('Redirecting to POS System...');
    setTimeout(() => {
        window.location.href = 'POSsystem.php';
    }, 500);
}

function handleViewSales() {
    showLoadingMessage('Loading Sales Dashboard...');
    setTimeout(() => {
        window.location.href = 'sales.php';
    }, 500);
}

function handleViewLowStock() {
    showLoadingMessage('Checking Inventory Alerts...');
    setTimeout(() => {
        window.location.href = 'inventory.php';
    }, 500);
}

function handleDownloadSalesReport() {
    const button = event.target.closest('.action-button');
    const originalText = button.innerHTML;
    
    // Show loading state
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating Report...';
    button.disabled = true;
    
    // Create and trigger download
    downloadSalesReport()
        .then(() => {
            // Success feedback
            button.innerHTML = '<i class="fas fa-check"></i> Downloaded!';
            button.style.backgroundColor = '#28a745';
            
            // Reset button after 2 seconds
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
                button.style.backgroundColor = '';
            }, 2000);
        })
        .catch(error => {
            console.error('Error downloading sales report:', error);
            
            // Error feedback
            button.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error!';
            button.style.backgroundColor = '#dc3545';
            
            // Reset button after 3 seconds
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
                button.style.backgroundColor = '';
            }, 3000);
            
            // Show error message to user
            showErrorMessage('Failed to download sales report. Please try again.');
        });
}

// Sales Report Download Function with improved error handling
async function downloadSalesReport() {
    try {
        console.log('Starting sales report download...');
        
        // Method 1: Try direct PDF download from server
        try {
            const directResponse = await fetch('actions/export-sales-data.php?action=download_pdf', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (directResponse.ok) {
                const blob = await directResponse.blob();
                if (blob.size > 0) {
                    downloadBlob(blob, `Sales_Report_${getCurrentDateString()}.pdf`);
                    console.log('Direct PDF download successful');
                    return;
                }
            }
        } catch (directError) {
            console.log('Direct download failed, trying data fetch method:', directError.message);
        }
        
        // Method 2: Fetch data and generate PDF client-side
        const response = await fetch('actions/export-sales-data.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'export_data',
                format: 'json',
                date_range: 'all'
            })
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`Server error: ${response.status} ${response.statusText}`);
        }
        
        const responseText = await response.text();
        console.log('Response received, length:', responseText.length);
        
        let salesData;
        try {
            salesData = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.log('Response text:', responseText);
            throw new Error('Invalid response format from server');
        }
        
        if (salesData.success === false) {
            throw new Error(salesData.message || 'Server returned error');
        }
        
        // Load and use PDF exporter
        await loadSalesPDFExporter();
        
        if (window.SalesPDFExporter) {
            console.log('Using PDF exporter with data:', salesData);
            await window.SalesPDFExporter.generateAndDownload(salesData.data || salesData);
        } else {
            // Fallback: Create simple PDF download
            console.log('PDF exporter not available, using fallback');
            await createFallbackPDF(salesData.data || salesData);
        }
        
    } catch (error) {
        console.error('Error in downloadSalesReport:', error);
        throw error;
    }
}

// Dynamically load the PDF exporter script with better error handling
function loadSalesPDFExporter() {
    return new Promise((resolve, reject) => {
        // Check if already loaded
        if (window.SalesPDFExporter) {
            console.log('PDF exporter already loaded');
            resolve();
            return;
        }
        
        // Check if script already exists
        const existingScript = document.querySelector('script[src="assets/js/sales-pdf-exporter.js"]');
        if (existingScript) {
            console.log('PDF exporter script exists, waiting for load...');
            // Script exists but may not be loaded yet, wait a bit
            let attempts = 0;
            const checkInterval = setInterval(() => {
                attempts++;
                if (window.SalesPDFExporter) {
                    clearInterval(checkInterval);
                    console.log('PDF exporter loaded after waiting');
                    resolve();
                } else if (attempts > 10) { // Wait max 5 seconds
                    clearInterval(checkInterval);
                    console.log('PDF exporter failed to load, using fallback');
                    resolve(); // Resolve anyway to use fallback
                }
            }, 500);
            return;
        }
        
        console.log('Loading PDF exporter script...');
        // Load the script
        const script = document.createElement('script');
        script.src = 'assets/js/sales-pdf-exporter.js';
        script.onload = () => {
            console.log('PDF exporter script loaded');
            // Give it a moment to initialize
            setTimeout(() => {
                if (window.SalesPDFExporter) {
                    console.log('PDF exporter initialized successfully');
                    resolve();
                } else {
                    console.log('PDF exporter script loaded but not initialized, using fallback');
                    resolve(); // Resolve anyway to use fallback
                }
            }, 200);
        };
        script.onerror = (error) => {
            console.log('Failed to load PDF exporter script:', error);
            resolve(); // Resolve anyway to use fallback
        };
        document.head.appendChild(script);
    });
}

// Fallback PDF creation function
async function createFallbackPDF(salesData) {
    console.log('Creating fallback PDF...');
    
    // Try to use jsPDF if available, otherwise create CSV
    if (window.jsPDF) {
        return createJsPDF(salesData);
    } else {
        // Load jsPDF from CDN as last resort
        try {
            await loadJsPDF();
            return createJsPDF(salesData);
        } catch (error) {
            console.log('jsPDF not available, creating CSV instead');
            return createCSVDownload(salesData);
        }
    }
}

// Create PDF using jsPDF
function createJsPDF(salesData) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    
    // Add title
    doc.setFontSize(20);
    doc.text('Sales Report', 20, 20);
    
    // Add date
    doc.setFontSize(12);
    doc.text(`Generated: ${new Date().toLocaleDateString()}`, 20, 35);
    
    // Add sales data (simplified)
    let yPosition = 50;
    doc.setFontSize(10);
    
    if (Array.isArray(salesData)) {
        salesData.forEach((item, index) => {
            if (yPosition > 280) {
                doc.addPage();
                yPosition = 20;
            }
            
            const text = `${index + 1}. ${JSON.stringify(item)}`;
            doc.text(text.substring(0, 80), 20, yPosition);
            yPosition += 10;
        });
    } else {
        doc.text('Sales data: ' + JSON.stringify(salesData).substring(0, 1000), 20, yPosition);
    }
    
    // Download
    doc.save(`Sales_Report_${getCurrentDateString()}.pdf`);
}

// Load jsPDF from CDN
function loadJsPDF() {
    return new Promise((resolve, reject) => {
        if (window.jsPDF) {
            resolve();
            return;
        }
        
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Failed to load jsPDF'));
        document.head.appendChild(script);
    });
}

// Create CSV download as ultimate fallback
function createCSVDownload(salesData) {
    console.log('Creating CSV download...');
    
    let csvContent = "Sales Report\n";
    csvContent += `Generated: ${new Date().toLocaleDateString()}\n\n`;
    
    if (Array.isArray(salesData)) {
        // Get headers from first object
        if (salesData.length > 0 && typeof salesData[0] === 'object') {
            const headers = Object.keys(salesData[0]);
            csvContent += headers.join(',') + '\n';
            
            salesData.forEach(row => {
                const values = headers.map(header => {
                    const value = row[header] || '';
                    return typeof value === 'string' && value.includes(',') ? `"${value}"` : value;
                });
                csvContent += values.join(',') + '\n';
            });
        } else {
            csvContent += "Data\n";
            salesData.forEach(item => {
                csvContent += `"${JSON.stringify(item)}"\n`;
            });
        }
    } else {
        csvContent += "Sales Data\n";
        csvContent += `"${JSON.stringify(salesData)}"`;
    }
    
    // Create and download CSV
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    downloadBlob(blob, `Sales_Report_${getCurrentDateString()}.csv`);
}

// Utility function to download blob
function downloadBlob(blob, filename) {
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// Get current date string for filename
function getCurrentDateString() {
    const now = new Date();
    return now.getFullYear() + 
           String(now.getMonth() + 1).padStart(2, '0') + 
           String(now.getDate()).padStart(2, '0');
}

// Utility Functions
function addButtonClickEffect(button) {
    button.style.transform = 'scale(0.95)';
    button.style.transition = 'transform 0.1s ease';
    
    setTimeout(() => {
        button.style.transform = 'scale(1)';
    }, 100);
}

function showLoadingMessage(message) {
    // Create or update loading toast
    let toast = document.getElementById('quick-action-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'quick-action-toast';
        toast.className = 'quick-action-toast';
        document.body.appendChild(toast);
    }
    
    toast.textContent = message;
    toast.className = 'quick-action-toast show';
    
    setTimeout(() => {
        toast.className = 'quick-action-toast';
    }, 2000);
}

function showErrorMessage(message) {
    let toast = document.getElementById('quick-action-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'quick-action-toast';
        toast.className = 'quick-action-toast error';
        document.body.appendChild(toast);
    }
    
    toast.textContent = message;
    toast.className = 'quick-action-toast error show';
    
    setTimeout(() => {
        toast.className = 'quick-action-toast error';
    }, 4000);
}

// CSS for active state and quick actions (add this to your Dashboard-style.css file)
const enhancedStyles = `
.menu-content li.active a,
.menu-content li a.active {
    background-color: #bd6c1f;
    color: white;
    transform: translateX(5px);
    box-shadow: 0 2px 10px rgba(189, 108, 31, 0.3);
}

.menu-content li.active a .icon,
.menu-content li a.active .icon {
    color: white;
}

.menu-content li.active a .title,
.menu-content li a.active .title {
    color: white;
}

/* Smooth transitions */
.menu-content li a {
    transition: all 0.3s ease;
    border-radius: 8px;
    margin: 2px 0;
}

/* Make user icon look clickable */
.user-icon {
    cursor: pointer;
    transition: transform 0.2s ease;
}

.user-icon:hover {
    transform: scale(1.1);
}

/* Quick Actions Enhancement */
.action-button {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    cursor: pointer;
}

.action-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.action-button:active {
    transform: translateY(0);
}

.action-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none !important;
}

/* Toast notifications */
.quick-action-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #333;
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    z-index: 10000;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s ease;
    font-size: 14px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
}

.quick-action-toast.show {
    opacity: 1;
    transform: translateX(0);
}

.quick-action-toast.error {
    background-color: #dc3545;
}

/* Loading spinner */
.fa-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
`;

// Inject CSS if not already present
if (!document.querySelector('#sidebar-enhanced-styles')) {
    const styleSheet = document.createElement('style');
    styleSheet.id = 'sidebar-enhanced-styles';
    styleSheet.textContent = enhancedStyles;
    document.head.appendChild(styleSheet);
}