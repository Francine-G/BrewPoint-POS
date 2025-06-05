window.SalesPDFExporter = {
    // Main function to generate and download PDF
    async generateAndDownload(salesData) {
        try {
            console.log('Generating PDF with data:', salesData);
            
            // Ensure jsPDF is loaded
            await this.loadjsPDF();
            
            // Create PDF document
            const doc = this.createPDFDocument(salesData);
            
            // Generate filename with current date
            const filename = `Sales_Report_${this.getCurrentDateString()}.pdf`;
            
            // Save the PDF
            doc.save(filename);
            
            console.log('PDF generated successfully:', filename);
            return true;
            
        } catch (error) {
            console.error('Error generating PDF:', error);
            throw new Error('Failed to generate PDF: ' + error.message);
        }
    },

    // Load jsPDF library
    async loadjsPDF() {
        return new Promise((resolve, reject) => {
            // Check if jsPDF is already loaded
            if (window.jsPDF) {
                resolve();
                return;
            }

            // Load jsPDF from CDN
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
            
            script.onload = () => {
                // Also load autoTable plugin for better table formatting
                const autoTableScript = document.createElement('script');
                autoTableScript.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js';
                
                autoTableScript.onload = () => {
                    console.log('jsPDF and autoTable loaded successfully');
                    resolve();
                };
                
                autoTableScript.onerror = () => {
                    console.log('autoTable failed to load, continuing without it');
                    resolve(); // Continue without autoTable
                };
                
                document.head.appendChild(autoTableScript);
            };
            
            script.onerror = () => {
                reject(new Error('Failed to load jsPDF library'));
            };
            
            document.head.appendChild(script);
        });
    },

    // Create the PDF document
    createPDFDocument(salesData) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Set up the document
        let yPosition = 20;
        
        // Add header
        yPosition = this.addHeader(doc, yPosition);
        
        // Add report information
        yPosition = this.addReportInfo(doc, yPosition, salesData);
        
        // Add sales data table
        yPosition = this.addSalesTable(doc, yPosition, salesData);
        
        // Add summary
        this.addSummary(doc, yPosition, salesData);
        
        return doc;
    },

    // Add header to PDF
    addHeader(doc, yPosition) {
        // Company header background
        doc.setFillColor(189, 108, 31); // Your brand color
        doc.rect(0, 0, 210, 35, 'F');
        
        // Company name
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(24);
        doc.setFont('helvetica', 'bold');
        doc.text('BREWPOINT POS', 20, 20);
        
        // Report title
        doc.setFontSize(16);
        doc.text('Sales Report', 20, 30);
        
        return 45;
    },

    // Add report information
    addReportInfo(doc, yPosition, salesData) {
        doc.setTextColor(0, 0, 0);
        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        
        const currentDate = new Date().toLocaleDateString();
        const currentTime = new Date().toLocaleTimeString();
        
        // Left column
        doc.text(`Generated: ${currentDate} ${currentTime}`, 20, yPosition);
        doc.text(`Total Records: ${Array.isArray(salesData) ? salesData.length : 0}`, 20, yPosition + 8);
        
        // Right column
        doc.text(`Report Type: Complete Sales Data`, 120, yPosition);
        doc.text(`Business: BigBrew Coffee Shop`, 120, yPosition + 8);
        
        return yPosition + 20;
    },

    // Add sales data table
    addSalesTable(doc, yPosition, salesData) {
        if (!Array.isArray(salesData) || salesData.length === 0) {
            doc.setFontSize(12);
            doc.text('No sales data available for the selected period.', 20, yPosition);
            return yPosition + 20;
        }

        // Prepare table data
        const tableData = this.prepareSalesTableData(salesData);
        
        // Check if autoTable is available
        if (doc.autoTable) {
            // Use autoTable for better formatting
            doc.autoTable({
                startY: yPosition,
                head: [['Order ID', 'Product', 'Qty', 'Price', 'Total', 'Date']],
                body: tableData,
                theme: 'grid',
                styles: {
                    fontSize: 8,
                    cellPadding: 3,
                },
                headStyles: {
                    fillColor: [189, 108, 31],
                    textColor: [255, 255, 255],
                    fontStyle: 'bold'
                },
                alternateRowStyles: {
                    fillColor: [248, 248, 248]
                },
                columnStyles: {
                    0: { cellWidth: 25 }, // Order ID
                    1: { cellWidth: 60 }, // Product
                    2: { cellWidth: 15 }, // Qty
                    3: { cellWidth: 25 }, // Price
                    4: { cellWidth: 25 }, // Total
                    5: { cellWidth: 40 }  // Date
                },
                margin: { top: 10 }
            });
            
            return doc.lastAutoTable.finalY + 10;
        } else {
            // Fallback to manual table creation
            return this.createManualTable(doc, yPosition, tableData);
        }
    },

    // Prepare sales data for table
    prepareSalesTableData(salesData) {
        return salesData.map(item => {
            const orderId = item.order_id || 'N/A';
            const productName = this.truncateText(item.product_name || 'Unknown', 25);
            const quantity = item.quantity || 0;
            const price = item.item_price ? `₱${parseFloat(item.item_price).toFixed(2)}` : '₱0.00';
            const total = item.total_amount ? `₱${parseFloat(item.total_amount).toFixed(2)}` : 
                         (item.item_price && item.quantity) ? `₱${(parseFloat(item.item_price) * parseInt(item.quantity)).toFixed(2)}` : '₱0.00';
            const date = item.order_date ? new Date(item.order_date).toLocaleDateString() : 'N/A';
            
            return [orderId, productName, quantity.toString(), price, total, date];
        });
    },

    // Create manual table (fallback)
    createManualTable(doc, yPosition, tableData) {
        const headers = ['Order ID', 'Product', 'Qty', 'Price', 'Total', 'Date'];
        const colWidths = [25, 60, 15, 25, 25, 40];
        let xPosition = 20;
        
        // Draw header
        doc.setFillColor(189, 108, 31);
        doc.rect(xPosition, yPosition, 190, 8, 'F');
        
        doc.setTextColor(255, 255, 255);
        doc.setFontSize(8);
        doc.setFont('helvetica', 'bold');
        
        let currentX = xPosition + 2;
        headers.forEach((header, index) => {
            doc.text(header, currentX, yPosition + 6);
            currentX += colWidths[index];
        });
        
        yPosition += 10;
        
        // Draw data rows
        doc.setTextColor(0, 0, 0);
        doc.setFont('helvetica', 'normal');
        
        tableData.forEach((row, rowIndex) => {
            if (yPosition > 270) {
                doc.addPage();
                yPosition = 20;
            }
            
            // Alternate row colors
            if (rowIndex % 2 === 0) {
                doc.setFillColor(248, 248, 248);
                doc.rect(xPosition, yPosition - 2, 190, 8, 'F');
            }
            
            currentX = xPosition + 2;
            row.forEach((cell, cellIndex) => {
                doc.text(cell.toString(), currentX, yPosition + 4);
                currentX += colWidths[cellIndex];
            });
            
            yPosition += 8;
        });
        
        return yPosition + 10;
    },

    // Add summary section
    addSummary(doc, yPosition, salesData) {
        if (!Array.isArray(salesData) || salesData.length === 0) {
            return;
        }

        // Calculate summary statistics
        const summary = this.calculateSummary(salesData);
        
        // Add summary header
        doc.setFontSize(14);
        doc.setFont('helvetica', 'bold');
        doc.text('Summary', 20, yPosition);
        
        yPosition += 15;
        
        // Add summary data
        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        
        const summaryItems = [
            `Total Orders: ${summary.totalOrders}`,
            `Total Items Sold: ${summary.totalQuantity}`,
            `Total Revenue: ₱${summary.totalRevenue.toFixed(2)}`,
            `Average Order Value: ₱${summary.avgOrderValue.toFixed(2)}`
        ];
        
        summaryItems.forEach((item, index) => {
            doc.text(item, 20, yPosition + (index * 8));
        });
    },

    // Calculate summary statistics
    calculateSummary(salesData) {
        const uniqueOrderIds = new Set();
        let totalQuantity = 0;
        let totalRevenue = 0;
        
        salesData.forEach(item => {
            uniqueOrderIds.add(item.order_id);
            totalQuantity += parseInt(item.quantity) || 0;
            
            const itemTotal = item.total_amount ? parseFloat(item.total_amount) : 
                            (item.item_price && item.quantity) ? (parseFloat(item.item_price) * parseInt(item.quantity)) : 0;
            totalRevenue += itemTotal;
        });
        
        const totalOrders = uniqueOrderIds.size;
        const avgOrderValue = totalOrders > 0 ? totalRevenue / totalOrders : 0;
        
        return {
            totalOrders,
            totalQuantity,
            totalRevenue,
            avgOrderValue
        };
    },

    // Utility function to truncate text
    truncateText(text, maxLength) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength - 3) + '...' : text;
    },

    // Get current date string for filename
    getCurrentDateString() {
        const now = new Date();
        return now.getFullYear() + 
               String(now.getMonth() + 1).padStart(2, '0') + 
               String(now.getDate()).padStart(2, '0');
    }
};

// Initialize the exporter when the script loads
console.log('Sales PDF Exporter initialized successfully');