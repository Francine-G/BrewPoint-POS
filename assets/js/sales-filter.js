// Advanced Sales Filtering and Table Management
class SalesManager {
    constructor() {
        this.currentSort = { column: -1, direction: 'asc' };
        this.originalData = [];
        this.filteredData = [];
        this.init();
    }

    init() {
        this.cacheOriginalData();
        this.setupEventListeners();
        this.setupRealTimeFiltering();
        this.initializeChart();
    }

    cacheOriginalData() {
        const table = document.getElementById('salesTable');
        if (table) {
            const rows = table.querySelectorAll('tbody tr');
            this.originalData = Array.from(rows).map(row => {
                const cells = Array.from(row.cells);
                return {
                    element: row.cloneNode(true),
                    data: cells.map(cell => cell.textContent.trim()),
                    orderId: parseInt(cells[0].textContent.trim()),
                    productName: cells[1].textContent.trim().toLowerCase(),
                    quantity: parseInt(cells[2].textContent.trim()),
                    addons: cells[3].textContent.trim().toLowerCase(),
                    price: parseFloat(cells[4].textContent.replace('₱', '').replace(',', '')),
                    date: new Date(cells[5].textContent.trim())
                };
            });
            this.filteredData = [...this.originalData];
        }
    }

    setupEventListeners() {
        // Filter form submission
        const filterForm = document.getElementById('filterForm');
        if (filterForm) {
            filterForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.applyServerFilters();
            });
        }

        // Real-time search
        this.setupSearchBox();

        // Export functions
        const exportBtn = document.querySelector('.export-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', () => this.exportToExcel());
        }

        // Chart filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.updateChartPeriod(e.target));
        });
    }
    
    renderTable(data) {
        const tbody = document.querySelector('#salesTable tbody');
        if (!tbody) return;

        tbody.innerHTML = '';
        
        if (data.length === 0) {
            const emptyRow = document.createElement('tr');
            emptyRow.innerHTML = '<td colspan="6" style="text-align: center; padding: 40px; color: #646464;">No results found</td>';
            tbody.appendChild(emptyRow);
            return;
        }

        data.forEach(item => {
            const row = item.element.cloneNode(true);
            row.style.animation = 'fadeIn 0.3s ease-out';
            tbody.appendChild(row);
        });
    }

    updateTableStats(showing, total) {
        let statsElement = document.getElementById('tableStats');
        if (!statsElement) {
            statsElement = document.createElement('div');
            statsElement.id = 'tableStats';
            statsElement.style.cssText = 'color: #646464; font-size: 14px; margin-top: 10px;';
            document.querySelector('.table-details').appendChild(statsElement);
        }
        
        if (showing === total) {
            statsElement.textContent = `Showing ${total} entries`;
        } else {
            statsElement.textContent = `Showing ${showing} of ${total} entries`;
        }
    }

    applyServerFilters() {
        // Show loading state
        this.showLoadingState();
        
        // Submit form (this will reload the page with filtered data)
        const form = document.getElementById('filterForm');
        form.submit();
    }

    showLoadingState() {
        const tbody = document.querySelector('#salesTable tbody');
        if (tbody) {
            tbody.innerHTML = '<tr><td colspan="6" class="loading-state"><i class="fa-solid fa-spinner fa-spin"></i> Loading filtered data...</td></tr>';
        }
    }

    // Enhanced sorting with animations
    sortTable(columnIndex) {
        const isCurrentColumn = this.currentSort.column === columnIndex;
        this.currentSort.direction = isCurrentColumn && this.currentSort.direction === 'asc' ? 'desc' : 'asc';
        this.currentSort.column = columnIndex;

        const sortedData = [...this.filteredData].sort((a, b) => {
            let comparison = 0;
            
            switch (columnIndex) {
                case 0: // Order ID
                    comparison = a.orderId - b.orderId;
                    break;
                case 1: // Product Name
                    comparison = a.productName.localeCompare(b.productName);
                    break;
                case 2: // Quantity
                    comparison = a.quantity - b.quantity;
                    break;
                case 3: // Add-ons
                    comparison = a.addons.localeCompare(b.addons);
                    break;
                case 4: // Price
                    comparison = a.price - b.price;
                    break;
                case 5: // Date
                    comparison = a.date - b.date;
                    break;
                default:
                    comparison = a.data[columnIndex].localeCompare(b.data[columnIndex]);
            }
            
            return this.currentSort.direction === 'asc' ? comparison : -comparison;
        });

        this.renderTable(sortedData);
        this.updateSortIndicators(columnIndex);
    }

    updateSortIndicators(activeColumn) {
        const headers = document.querySelectorAll('#salesTable th');
        headers.forEach((header, index) => {
            const icon = header.querySelector('i');
            if (icon) {
                if (index === activeColumn) {
                    icon.className = this.currentSort.direction === 'asc' ? 
                        'fa-solid fa-sort-up' : 'fa-solid fa-sort-down';
                    header.style.background = 'rgba(255, 255, 255, 0.2)';
                } else {
                    icon.className = 'fa-solid fa-sort';
                    header.style.background = '';
                }
            }
        });
    }

    // Enhanced export functionality
    exportToExcel() {
        const table = document.getElementById('salesTable');
        const data = [];
        
        // Get headers
        const headers = Array.from(table.querySelectorAll('thead th')).map(th => 
            th.textContent.replace(/\s*\w+\s*$/, '').trim() // Remove sort icon text
        );
        data.push(headers);
        
        // Get visible rows data
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cells = Array.from(row.cells);
            if (cells.length > 1) { // Skip empty state rows
                data.push(cells.map(cell => cell.textContent.trim()));
            }
        });
        
        // Create CSV content
        const csvContent = data.map(row => 
            row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')
        ).join('\n');
        
        // Add BOM for proper Excel encoding
        const BOM = '\uFEFF';
        const blob = new Blob([BOM + csvContent], { type: 'text/csv;charset=utf-8;' });
        
        // Create download link
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `sales_report_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Show success message
        this.showNotification('Export completed successfully!', 'success');
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        `;
        
        switch (type) {
            case 'success':
                notification.style.background = ' #28a745';
                break;
            case 'error':
                notification.style.background = ' #dc3545';
                break;
            default:
                notification.style.background = ' #bd6c1f';
        }
        
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }

    // Chart management
    updateChartPeriod(button) {
        // Remove active class from all buttons
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        
        // Here you would typically make an AJAX call to get new chart data
        // For now, we'll just show a notification
        const period = button.textContent.toLowerCase();
        this.showNotification(`Chart updated to show ${period} data`, 'info');
    }

    initializeChart() {
        // Enhanced chart initialization with better animations
        const ctx = document.getElementById('salesChart');
        if (ctx && window.Chart) {
            // Chart configuration would go here
            // This is handled in the main PHP file
        }
    }

    // Clear all filters
    clearAllFilters() {
        document.getElementById('categorySelect').value = 'All';
        document.getElementById('dateSelect').value = 'All';
        const searchBox = document.getElementById('searchBox');
        if (searchBox) searchBox.value = '';
        
        // Reset to original data
        this.filteredData = [...this.originalData];
        this.renderTable(this.filteredData);
        this.updateTableStats(this.filteredData.length, this.filteredData.length);
        
        this.showNotification('All filters cleared', 'info');
    }

    // Get sales statistics
    getSalesStats() {
        const stats = {
            totalOrders: this.filteredData.length,
            totalRevenue: this.filteredData.reduce((sum, item) => sum + item.price, 0),
            averageOrderValue: 0,
            topProducts: {}
        };
        
        stats.averageOrderValue = stats.totalOrders > 0 ? stats.totalRevenue / stats.totalOrders : 0;
        
        // Calculate top products
        this.filteredData.forEach(item => {
            const product = item.data[1]; // Product name
            stats.topProducts[product] = (stats.topProducts[product] || 0) + 1;
        });
        
        return stats;
    }
}

// Global functions for backward compatibility
function sortTable(columnIndex) {
    if (window.salesManager) {
        window.salesManager.sortTable(columnIndex);
    }
}

function applyFilters() {
    if (window.salesManager) {
        window.salesManager.applyServerFilters();
    }
}

function clearFilters() {
    if (window.salesManager) {
        window.salesManager.clearAllFilters();
    }
}

function exportToPDF() {
    if (window.salesManager) {
        window.salesManager.exportToExcel();
    }
}

function updateChart(period) {
    if (window.salesManager) {
        const button = event.target || document.querySelector('.filter-btn');
        window.salesManager.updateChartPeriod(button);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.salesManager = new SalesManager();
    
    // Set up periodic data refresh (optional)
    setInterval(() => {
        // Could implement auto-refresh of data here
        console.log('Sales data auto-refresh check...');
    }, 300000); // Every 5 minutes
    
    console.log('Advanced Sales Manager initialized successfully');
});

// Filter Functions
            function applyFilters() {
                // Auto-submit form when dropdown changes
                document.getElementById('filterForm').submit();
            }

            function clearFilters() {
                document.getElementById('categorySelect').value = 'All';
                document.getElementById('dateSelect').value = 'All';
                document.getElementById('filterForm').submit();
            }

            // Table sorting functionality
            let sortDirection = {};
            
            function sortTable(columnIndex) {
                const table = document.getElementById('salesTable');
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                
                // Toggle sort direction
                sortDirection[columnIndex] = sortDirection[columnIndex] === 'asc' ? 'desc' : 'asc';
                
                rows.sort((a, b) => {
                    const aValue = a.cells[columnIndex].textContent.trim();
                    const bValue = b.cells[columnIndex].textContent.trim();
                    
                    // Handle different data types
                    let comparison = 0;
                    if (columnIndex === 0) { // Order ID
                        comparison = parseInt(aValue) - parseInt(bValue);
                    } else if (columnIndex === 2) { // Quantity
                        comparison = parseInt(aValue) - parseInt(bValue);
                    } else if (columnIndex === 4) { // Price
                        const aPrice = parseFloat(aValue.replace('₱', '').replace(',', ''));
                        const bPrice = parseFloat(bValue.replace('₱', '').replace(',', ''));
                        comparison = aPrice - bPrice;
                    } else if (columnIndex === 5) { // Date
                        comparison = new Date(aValue) - new Date(bValue);
                    } else { // Text columns
                        comparison = aValue.localeCompare(bValue);
                    }
                    
                    return sortDirection[columnIndex] === 'asc' ? comparison : -comparison;
                });
                
                // Re-append sorted rows
                rows.forEach(row => tbody.appendChild(row));
                
                // Update sort indicators
                updateSortIndicators(columnIndex);
            }
            
            function updateSortIndicators(activeColumn) {
                const headers = document.querySelectorAll('#salesTable th');
                headers.forEach((header, index) => {
                    const icon = header.querySelector('i');
                    if (icon) {
                        if (index === activeColumn) {
                            icon.className = sortDirection[activeColumn] === 'asc' ? 
                                'fa-solid fa-sort-up' : 'fa-solid fa-sort-down';
                        } else {
                            icon.className = 'fa-solid fa-sort';
                        }
                    }
                });
            }

            // Chart update function (placeholder for future functionality)
            function updateChart(period) {
                // This can be expanded to fetch different time period data via AJAX
                console.log('Updating chart for period:', period);
                
                // Remove active class from all buttons and add to clicked one
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                event.target.classList.add('active');
            }

            // Export to PDF function
            function exportToPDF() {
                // Create a simple CSV export for now
                let csv = 'Order ID,Product Name,Quantity,Add-ons,Price,Date\n';
                
                const table = document.getElementById('salesTable');
                const rows = table.querySelectorAll('tbody tr');
                
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    const rowData = Array.from(cells).map(cell => 
                        '"' + cell.textContent.trim().replace(/"/g, '""') + '"'
                    ).join(',');
                    csv += rowData + '\n';
                });
                
                // Download CSV
                const blob = new Blob([csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'sales_report_' + new Date().toISOString().split('T')[0] + '.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            }

            // Initialize page
            document.addEventListener('DOMContentLoaded', function() {
                // Add click handlers for table headers if they don't exist
                console.log('Sales report page loaded successfully');
                
                // Highlight active filter button
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                        this.classList.add('active');
                    });
                });
            });
