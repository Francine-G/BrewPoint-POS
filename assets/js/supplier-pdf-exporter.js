function openExportModal() {
    const modal = document.getElementById('exportModal');
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeExportModal() {
    const modal = document.getElementById('exportModal');
    modal.classList.remove('show');
    document.body.style.overflow = 'auto';
}

function generatePDF() {
    const btn = document.getElementById('generatePdfBtn');
    const spinner = document.getElementById('loadingSpinner');
    const icon = document.getElementById('downloadIcon');
    const btnText = document.getElementById('btnText');
    const filter = document.getElementById('productFilter').value;

    console.log('Starting PDF generation with filter:', filter);

    // Show loading state
    btn.disabled = true;
    spinner.style.display = 'block';
    icon.style.display = 'none';
    btnText.textContent = 'Generating...';

    // Check if jQuery is loaded
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded');
        Swal.fire({
            title: 'Error',
            text: 'jQuery library is required but not loaded.',
            icon: 'error',
            confirmButtonColor: '#863a3a'
        });
        resetButtonState();
        return;
    }

    // Fetch filtered supplier data
    $.ajax({
        url: 'actions/get-filtered-supplier.php',
        type: 'POST',
        data: { 
            filter: filter 
        },
        dataType: 'json',
        timeout: 10000, // 10 second timeout
        beforeSend: function() {
            console.log('Sending AJAX request to get-filtered-suppliers.php');
        },
        success: function(response, textStatus, xhr) {
            console.log('AJAX Success:', response);
            console.log('Status:', textStatus);
            console.log('XHR Status:', xhr.status);
            
            if (response && response.success) {
                console.log('Found', response.count, 'suppliers');
                createPDFFromData(response.suppliers, filter);
            } else {
                console.error('Response indicates failure:', response);
                const errorMsg = response.error || 'Unknown error occurred';
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to fetch supplier data: ' + errorMsg,
                    icon: 'error',
                    confirmButtonColor: '#863a3a'
                });
                resetButtonState();
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error Details:');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response Text:', xhr.responseText);
            console.error('Status Code:', xhr.status);
            
            let errorMessage = 'Error connecting to server.';
            
            if (xhr.status === 404) {
                errorMessage = 'The export script was not found. Please check if actions/get-filtered-suppliers.php exists.';
            } else if (xhr.status === 500) {
                errorMessage = 'Server error occurred. Please check the server logs.';
            } else if (status === 'timeout') {
                errorMessage = 'Request timed out. Please try again.';
            } else if (status === 'parsererror') {
                errorMessage = 'Error parsing server response.';
            }
            
            Swal.fire({
                title: 'Connection Error',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#863a3a',
                footer: 'Status: ' + xhr.status + ' | ' + status
            });
            resetButtonState();
        }
    });
}

function createPDFFromData(suppliers, filter) {
    console.log('Creating PDF with', suppliers.length, 'suppliers');
    
    try {
        // Check if jsPDF is loaded
        if (typeof window.jspdf === 'undefined') {
            throw new Error('jsPDF library is not loaded');
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Add logo/header
        doc.setFontSize(20);
        doc.setTextColor(139, 69, 19);
        doc.text('BREWPOINT POS', 20, 25);

        // Add title
        doc.setFontSize(16);
        doc.setTextColor(0, 0, 0);
        doc.text('Suppliers Details', 20, 40);

        // Add business info
        doc.setFontSize(10);
        doc.text('Business Name: BigBrew', 20, 55);
        doc.text(`Date: ${new Date().toLocaleDateString()}`, 20, 65);
        doc.text('Prepared By: Username', 20, 75);

        // Add filter info if applicable
        let startY = 85;
        if (filter !== 'all') {
            doc.text(`Filter: ${filter.charAt(0).toUpperCase() + filter.slice(1)}`, 20, 85);
            startY = 95;
        }

        // Check if we have suppliers
        if (suppliers.length === 0) {
            doc.setFontSize(12);
            doc.text('No suppliers found for the selected filter.', 20, startY + 20);
        } else {
            // Table headers
            doc.setFontSize(9);
            doc.setFont(undefined, 'bold');
            
            // Draw table header background
            doc.setFillColor(139, 69, 19);
            doc.rect(20, startY, 170, 10, 'F');
            
            // Header text in white
            doc.setTextColor(255, 255, 255);
            doc.text('ID', 25, startY + 7);
            doc.text('Supplier Name', 40, startY + 7);
            doc.text('Address', 85, startY + 7);
            doc.text('Product', 130, startY + 7);
            doc.text('Contact', 165, startY + 7);

            // Table rows
            doc.setFont(undefined, 'normal');
            doc.setTextColor(0, 0, 0);
            let currentY = startY + 10;

            suppliers.forEach((supplier, index) => {
                if (currentY > 270) { // Start new page if needed
                    doc.addPage();
                    currentY = 20;
                }

                // Alternate row colors
                if (index % 2 === 0) {
                    doc.setFillColor(248, 249, 250);
                    doc.rect(20, currentY, 170, 10, 'F');
                }

                // Draw row border
                doc.setDrawColor(200, 200, 200);
                doc.rect(20, currentY, 170, 10);

                // Add text with proper truncation
                doc.text(supplier.id.toString(), 25, currentY + 7);
                doc.text(truncateText(supplier.name, 25), 40, currentY + 7);
                doc.text(truncateText(supplier.address, 30), 85, currentY + 7);
                doc.text(truncateText(supplier.product, 20), 130, currentY + 7);
                doc.text(truncateText(supplier.contact, 15), 165, currentY + 7);
                
                currentY += 10;
            });
        }

        // Generate filename
        const filterText = filter !== 'all' ? `_${filter}` : '';
        const timestamp = new Date().toISOString().split('T')[0];
        const filename = `suppliers_report${filterText}_${timestamp}.pdf`;
        
        console.log('Saving PDF as:', filename);
        
        // Save PDF
        doc.save(filename);

        // Show success message
        setTimeout(() => {
            Swal.fire({
                title: 'Success!',
                text: `PDF generated successfully! ${suppliers.length} suppliers included.`,
                icon: 'success',
                confirmButtonColor: '#4e944f'
            }).then(() => {
                closeExportModal();
            });
        }, 500);

    } catch (error) {
        console.error('Error generating PDF:', error);
        Swal.fire({
            title: 'PDF Generation Error',
            text: 'Error generating PDF: ' + error.message,
            icon: 'error',
            confirmButtonColor: '#863a3a'
        });
    } finally {
        resetButtonState();
    }
}

function truncateText(text, maxLength) {
    if (!text) return '';
    return text.length > maxLength ? text.substring(0, maxLength - 3) + '...' : text;
}

function resetButtonState() {
    const btn = document.getElementById('generatePdfBtn');
    const spinner = document.getElementById('loadingSpinner');
    const icon = document.getElementById('downloadIcon');
    const btnText = document.getElementById('btnText');
    
    if (btn) btn.disabled = false;
    if (spinner) spinner.style.display = 'none';
    if (icon) icon.style.display = 'block';
    if (btnText) btnText.textContent = 'Generate PDF';
}

// Test function to check if everything is loaded properly
function testExportSystem() {
    console.log('Testing export system...');
    console.log('jQuery loaded:', typeof $ !== 'undefined');
    console.log('jsPDF loaded:', typeof window.jspdf !== 'undefined');
    console.log('SweetAlert loaded:', typeof Swal !== 'undefined');
    
    // Test AJAX endpoint
    if (typeof $ !== 'undefined') {
        $.ajax({
            url: 'actions/get-filtered-supplier.php',
            type: 'POST',
            data: { filter: 'all' },
            success: function(response) {
                console.log('Test AJAX successful:', response);
            },
            error: function(xhr, status, error) {
                console.log('Test AJAX failed:', status, error);
                console.log('Response:', xhr.responseText);
            }
        });
    }
}

// Initialize when document is ready
$(document).ready(function() {
    console.log('Document ready, initializing export system...');
    
    // Test the system
    testExportSystem();
    
    // Set up event handlers
    $('#exportModal').on('click', function(e) {
        if (e.target === this) {
            closeExportModal();
        }
    });

    // Close modal with Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#exportModal').hasClass('show')) {
            closeExportModal();
        }
    });

    // Update the export button if it exists
    const exportBtn = document.getElementById('exportBtn');
    if (exportBtn) {
        exportBtn.onclick = openExportModal;
        console.log('Export button updated');
    } else {
        console.warn('Export button not found');
    }
});