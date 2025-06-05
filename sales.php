<?php
// Include database connection
include("database/db.php");

// Handle filtering
$category_filter = "";
$date_filter = "";
$selected_category = "All";
$selected_date = "All";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['category'])) {
        $selected_category = $_POST['category'];
    }
    if (isset($_POST['Date'])) {
        $selected_date = $_POST['Date'];
    }
}

// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Query for today's most popular item (based on quantity sold and total revenue)
$popular_sql = "SELECT 
                    oi.product_name,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.item_price * oi.quantity) as total_revenue,
                    AVG(oi.item_price) as avg_price,
                    p.productImg as product_img
                FROM order_items oi
                INNER JOIN orders o ON oi.order_id = o.order_id
                LEFT JOIN products p ON oi.product_name = p.productName
                WHERE DATE(o.order_date) = CURDATE()
                GROUP BY oi.product_name, p.productImg
                ORDER BY total_quantity DESC, total_revenue DESC
                LIMIT 1";

$popular_result = $conn->query($popular_sql);

// Check if query executed successfully
if ($popular_result === false) {
    // Log the error for debugging
    error_log("SQL Error in popular items query: " . $conn->error);
    $most_popular = null;
} else {
    $most_popular = $popular_result->fetch_assoc();
}

// Query for sales chart data (daily sales for last 7 days)
$chart_sql = "SELECT 
    DATE(o.order_date) as sale_date,
    SUM(o.total_amount) as daily_total,
    COUNT(DISTINCT o.order_id) as order_count
    FROM orders o
    WHERE o.order_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
    GROUP BY DATE(o.order_date)
    ORDER BY sale_date ASC";

$chart_result = $conn->query($chart_sql);
$chart_data = [];

if ($chart_result === false) {
    error_log("SQL Error in chart data query: " . $conn->error);
    // Initialize empty chart data if query fails
    $chart_data = [];
} else {
    while ($row = $chart_result->fetch_assoc()) {
        $chart_data[] = $row;
    }
}

// Get total sales for today
$today_sales_sql = "SELECT 
                        SUM(o.total_amount) as today_total,
                        COUNT(DISTINCT o.order_id) as today_orders
                    FROM orders o
                    WHERE DATE(o.order_date) = CURDATE()";

$today_result = $conn->query($today_sales_sql);

if ($today_result === false) {
    error_log("SQL Error in today's sales query: " . $conn->error);
    $today_stats = ['today_total' => 0, 'today_orders' => 0];
} else {
    $today_stats = $today_result->fetch_assoc();
    // Handle null values
    if ($today_stats['today_total'] === null) {
        $today_stats['today_total'] = 0;
    }
    if ($today_stats['today_orders'] === null) {
        $today_stats['today_orders'] = 0;
    }
}
?>

<html> 
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <script src="https://kit.fontawesome.com/f4e628f07c.js" crossorigin="anonymous"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.6.0/uicons-solid-straight/css/uicons-solid-straight.css'>
        <link rel="stylesheet" href="assets/css/Sales-style.css">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>

        <title>BrewPoint POS</title>
    </head>

    <body>
        <div class="container">

            <div class="sidebar">
                <div class="logo-content">
                    <img src="assets/img/logo.png" class="logo">
                </div>

                <div class="nav-bar">
                    <ul class="menu-content">
                        <li>
                            <a href="dashboard.php">
                                <span class="icon"><i class='bx bxs-dashboard'></i></span>
                                <span class="title"> Dashboard</span>
                            </a>
                        </li>

                        <li>
                            <a href="POSsystem.php">
                                <span class="icon"><i class='bx bxs-cart-add'></i></span>
                                <span class="title">POS System</span>
                             </a>
                        </li>

                        <li>
                            <a href="orders.php">
                                <span class="icon"><i class='bx bxs-shopping-bag-alt'></i></span>
                                <span class="title">Orders</span>
                            </a>
                        </li>
    
                        <li>
                            <a href="inventory.php">
                                <span class="icon"><i class='bx bxs-package'></i></span>
                                <span class="title">Inventory</span>
                            </a>
                        </li>

                        <li>
                            <a href="sales.php">
                                <span class="icon"><i class='bx bxs-report'></i></span>
                                <span class="title">Sales Reports</span>
                            </a>
                        </li>

                        <li>
                            <a href="supplier_details.php">
                                <span class="icon"><i class='bx bxs-user-account'></i></span>
                                <span class="title">Suppliers</span>
                            </a>
                        </li>

                    </ul>
                </div>

                    
                <div class="logout">
                    <a href="index.php">
                        <span><i class="fa-solid fa-right-from-bracket"></i></span>
                        <span class="title">Logout</span>
                    </a>
                </div>
            </div>

            <main class="content">
                <div class="header">
                    <h2>Sales Report</h2>
                    
                    <div class="user-icon">
                        <i class="fa-solid fa-circle-user"></i>
                    </div>
                </div>

                <div class="top-container">
                    <div class="sales-chart">
                        <div class="header">
                            <div class="text">
                                <h2>Sales Overview (Last 7 Days)</h2>
                            </div>
                            <div class="buttons">
                                <button class="filter-btn" onclick="updateChart('daily')">Daily</button>
                                <button class="filter-btn" onclick="updateChart('weekly')">Weekly</button>
                            </div>
                        </div>
                        <div class="chart-details">                        
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                    <div class="popular-details">
                        <h2>Today's Most Popular Item</h2>
                        <div class="item-details">
                            <div class="item-image">
                                <?php if ($most_popular && !empty($most_popular['product_img'])): ?>
                                    <img src="assets/img/uploads/<?php echo htmlspecialchars($most_popular['product_img']); ?>" 
                                    alt="<?php echo htmlspecialchars($most_popular['product_name']); ?>" 
                                    style="width: 250px; height: 350px; object-fit: cover; border-radius: 10px; border: 2px solid #bd6c1f;">
                                <?php else: ?>
                                    <i class="fa-solid fa-mug-hot" style="font-size: 60px; color: #bd6c1f;"></i>
                                <?php endif; ?>
                            </div>
                            <div class="item-column">
                                <div class="item-box" style="padding: 15px; border: none; display: flex; flex-direction: column; justify-content: center;">
                                    <h3 style="color: #bd6c1f; margin-bottom: 5px;">Product:</h3>
                                    <p style="font-weight: 600; font-size: 18px;">
                                        <?php echo $most_popular ? htmlspecialchars($most_popular['product_name']) : 'No sales today'; ?>
                                    </p>
                                </div>
                                <div class="item-box" style="padding: 15px; border: none; display: flex; flex-direction: column; justify-content: center;">
                                    <h3 style="color: #bd6c1f; margin-bottom: 5px;">Quantity Sold:</h3>
                                    <p style="font-weight: 600; font-size: 18px;">
                                        <?php echo $most_popular ? $most_popular['total_quantity'] . ' units' : '0 units'; ?>
                                    </p>
                                </div>
                                <div class="item-box" style="padding: 15px; border: none; display: flex; flex-direction: column; justify-content: center;">
                                    <h3 style="color: #bd6c1f; margin-bottom: 5px;">Total Revenue:</h3>
                                    <p style="font-weight: 600; font-size: 18px;">
                                        <?php echo $most_popular ? '₱' . number_format($most_popular['total_revenue'], 2) : '₱0.00'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bottom-container">
                    <div class="sales-details">
                        <div class="bottom-header">
                            <h2>Sales List</h2>
                            <div class="bottom-buttons">
                                <form id="filterForm">
                                    <select name="Date" id="dateSelect" onchange="applyFilters()">
                                        <option value="All" <?php echo ($selected_date == 'All') ? 'selected' : ''; ?>>All Dates</option>
                                        <option value="Today" <?php echo ($selected_date == 'Today') ? 'selected' : ''; ?>>Today</option>
                                        <option value="This Week" <?php echo ($selected_date == 'This Week') ? 'selected' : ''; ?>>This Week</option>
                                        <option value="This Month" <?php echo ($selected_date == 'This Month') ? 'selected' : ''; ?>>This Month</option>
                                        <option value="Last Month" <?php echo ($selected_date == 'Last Month') ? 'selected' : ''; ?>>Last Month</option>
                                    </select>
                                </form>
                                <button class="export-btn" onclick="exportToPDF()"><i class="fas fa-file-pdf"></i>  Export PDF</button>
                            </div>
                        </div>
                    
                        <div class="table">
                            <div class="table-details">
                                <table id="salesTable">
                                    <thead>
                                        <tr>
                                            <th onclick="sortTable(0)">Order ID <i class="fa-solid fa-sort"></i></th>
                                            <th onclick="sortTable(1)">Product Name <i class="fa-solid fa-sort"></i></th>
                                            <th onclick="sortTable(2)">Quantity <i class="fa-solid fa-sort"></i></th>
                                            <th onclick="sortTable(3)">Add-ons <i class="fa-solid fa-sort"></i></th>
                                            <th onclick="sortTable(4)">Price <i class="fa-solid fa-sort"></i></th>
                                            <th onclick="sortTable(5)">Date <i class="fa-solid fa-sort"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody id="salesTableBody">
                                        <!-- Data will be loaded via AJAX -->
                                    </tbody>
                                </table>
                                <div class="pagination" id="paginationContainer">
                                    <!-- Pagination will be loaded via AJAX -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <div class="modal-overlay" id="exportModal">
            <div class="modal">
                <div class="modal-header">
                    <h3>
                        <i class="fa-solid fa-file-export"></i>
                        Export Sales Report
                    </h3>
                    <button class="close-btn" onclick="closeExportModal()">&times;</button>
                </div>
                
                <div class="modal-body">
                    <form id="exportForm">
                        <div class="form-group">
                            <label for="dateRange">Date Range</label>
                            <div class="date-range">
                                <input type="date" id="startDate" class="form-control" required>
                                <input type="date" id="endDate" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="categoryFilter">Product Category</label>
                            <select id="categoryFilter" class="form-control">
                                <option value="All">All Categories</option>
                                <option value="iced Coffee">Iced Coffee</option>
                                <option value="Milktea">Milktea</option>
                                <option value="Fruit Tea">Fruit Tea</option>
                                <option value="Frappe">Frappe</option>
                                <option value="Hot Brew">Hot Brew</option>
                            </select>
                        </div>

                        <div class="export-preview">
                            <div class="preview-info">
                                <span>Date Range:</span>
                                <strong id="previewDateRange">Select dates</strong>
                            </div>
                            <div class="preview-info">
                                <span>Category:</span>
                                <strong id="previewCategory">All Categories</strong>
                            </div>
                            <div class="preview-info">
                                <span>Estimated Records:</span>
                                <strong id="previewRecords">Loading...</strong>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeExportModal()">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" onclick="generatePDF()" id="generateBtn">
                        <div class="loading-spinner" id="loadingSpinner"></div>
                        <i class="fa-solid fa-download" id="downloadIcon"></i>
                        Generate PDF
                    </button>
                </div>
            </div>
        </div>

        <script src="assets/js/Sidebar-Nav.js"></script>
        <script src="assets/js/sales-filter.js"></script>

        <script>
            let currentPage = 1;
            let selectedCategory = 'All';
            let selectedDate = 'All';

            // Load sales data on page load
            $(document).ready(function() {
                loadSalesData(1);
            });

            // Function to load sales data via AJAX
            function loadSalesData(page = 1, category = 'All', date = 'All') {
                currentPage = page;
                selectedCategory = category;
                selectedDate = date;
                
                console.log('Loading sales with:', {page: page, category: category, date: date});
                
                $.ajax({
                    url: 'actions/load-sales-data.php',
                    type: 'POST',
                    data: {
                        page: page,
                        category: category,
                        date: date
                    },
                    success: function(response) {
                        console.log('Raw response:', response);
                        try {
                            const data = JSON.parse(response);
                            console.log('Parsed data:', data);
                            $('#salesTableBody').html(data.table_rows);
                            $('#paginationContainer').html(data.pagination);
                        } catch (e) {
                            console.error('Error parsing JSON:', e, response);
                            $('#salesTableBody').html('<tr><td colspan="6">Error loading data</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', xhr.responseText);
                        $('#salesTableBody').html('<tr><td colspan="6">Error loading data</td></tr>');
                    }
                });
            }

            // Function to apply filters
            function applyFilters() {
                const category = $('#categorySelect').val();
                const date = $('#dateSelect').val();
                loadSalesData(1, category, date);
            }

            // Function to change page
            function changePage(page) {
                loadSalesData(page, selectedCategory, selectedDate);
            }

            // Sales Chart
            let salesChart; // Make chart global so we can update it
            let currentChartPeriod = 'daily'; // Track current period

            // Initialize chart when page loads
            $(document).ready(function() {
                loadSalesData(1);
                initializeSalesChart();
            });

            // Function to initialize the sales chart
            function initializeSalesChart() {
                loadChartData('daily');
            }

            // Function to load chart data based on period
            function loadChartData(period) {
                $.ajax({
                    url: 'actions/load-chart-data.php',
                    type: 'POST',
                    data: {
                        period: period
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            updateSalesChart(response.data, period);
                            currentChartPeriod = period;
                            
                            // Update active button state
                            updateActiveButton(period);
                        } else {
                            console.error('Error loading chart data:', response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error loading chart data:', error);
                    }
                });
            }

            // Function to update the sales chart
            function updateSalesChart(chartData, period) {
                const ctx = document.getElementById('salesChart').getContext('2d');
                
                // Prepare labels and data based on period
                let labels, data, title;
                
                if (period === 'daily') {
                    labels = chartData.map(item => {
                        const date = new Date(item.sale_date);
                        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                    });
                    data = chartData.map(item => parseFloat(item.daily_total || 0));
                    title = 'Daily Sales (Last 7 Days)';
                } else if (period === 'weekly') {
                    labels = chartData.map(item => `Week ${item.week_number}`);
                    data = chartData.map(item => parseFloat(item.weekly_total || 0));
                    title = 'Weekly Sales (Last 8 Weeks)';
                }
                
                // Destroy existing chart if it exists
                if (salesChart) {
                    salesChart.destroy();
                }
                
                // Create new chart
                salesChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: period === 'daily' ? 'Daily Sales (₱)' : 'Weekly Sales (₱)',
                            data: data,
                            borderColor: '#bd6c1f',
                            backgroundColor: 'rgba(189, 108, 31, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#bd6c1f',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(189, 108, 31, 0.9)',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                borderColor: '#bd6c1f',
                                borderWidth: 1,
                                callbacks: {
                                    title: function(context) {
                                        return context[0].label;
                                    },
                                    label: function(context) {
                                        return `Sales: ₱${context.parsed.y.toLocaleString()}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    }
                                },
                                grid: {
                                    color: 'rgba(189, 108, 31, 0.1)'
                                }
                            },
                            x: {
                                grid: {
                                    color: 'rgba(189, 108, 31, 0.1)'
                                }
                            }
                        }
                    }
                });
                
                // Update chart title
                document.querySelector('.sales-chart .text h2').textContent = title;
            }

            // Function to update chart period (called by filter buttons)
            function updateChart(period) {
                loadChartData(period);
            }

            // Function to update active button state
            function updateActiveButton(period) {
                // Remove active class from all buttons
                document.querySelectorAll('.filter-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Add active class to current button
                const buttons = document.querySelectorAll('.filter-btn');
                buttons.forEach(btn => {
                    if ((period === 'daily' && btn.textContent.trim() === 'Daily') ||
                        (period === 'weekly' && btn.textContent.trim() === 'Weekly')) {
                        btn.classList.add('active');
                    }
                });
            }

            function exportToPDF() {
                openExportModal();
            }

            // Modal functions
            function initializeModal() {
                const today = new Date();
                const lastWeek = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                
                document.getElementById('startDate').value = lastWeek.toISOString().split('T')[0];
                document.getElementById('endDate').value = today.toISOString().split('T')[0];
                
                updatePreview();
            }

            function openExportModal() {
                document.getElementById('exportModal').style.display = 'block';
                document.body.style.overflow = 'hidden';
                initializeModal();
            }

            function closeExportModal() {
                document.getElementById('exportModal').style.display = 'none';
                document.body.style.overflow = 'auto';
            }

            function updatePreview() {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                const category = document.getElementById('categoryFilter').value;

                // Update preview display
                if (startDate && endDate) {
                    const start = new Date(startDate).toLocaleDateString('en-US', { 
                        month: 'short', day: 'numeric', year: 'numeric' 
                    });
                    const end = new Date(endDate).toLocaleDateString('en-US', { 
                        month: 'short', day: 'numeric', year: 'numeric' 
                    });
                    document.getElementById('previewDateRange').textContent = `${start} - ${end}`;
                } else {
                    document.getElementById('previewDateRange').textContent = 'Select dates';
                }

                document.getElementById('previewCategory').textContent = category;

                // Get estimated record count
                if (startDate && endDate) {
                    getRecordCount(startDate, endDate, category);
                }
            }

            function getRecordCount(startDate, endDate, category) {
                document.getElementById('previewRecords').textContent = 'Loading...';
                
                // Use your existing AJAX pattern to get record count
                $.ajax({
                    url: 'actions/get-export-count.php',
                    type: 'POST',
                    data: {
                        startDate: startDate,
                        endDate: endDate,
                        category: category
                    },
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.success) {
                                document.getElementById('previewRecords').textContent = `~${data.count} records`;
                            } else {
                                document.getElementById('previewRecords').textContent = 'Error loading count';
                            }
                        } catch (e) {
                            document.getElementById('previewRecords').textContent = 'Error loading count';
                        }
                    },
                    error: function() {
                        document.getElementById('previewRecords').textContent = 'Error loading count';
                    }
                });
            }

            async function generatePDF() {
                const generateBtn = document.getElementById('generateBtn');
                const loadingSpinner = document.getElementById('loadingSpinner');
                const downloadIcon = document.getElementById('downloadIcon');

                // Show loading state
                generateBtn.disabled = true;
                loadingSpinner.style.display = 'block';
                downloadIcon.style.display = 'none';
                generateBtn.innerHTML = '<div class="loading-spinner"></div> Generating...';

                try {
                    const startDate = document.getElementById('startDate').value;
                    const endDate = document.getElementById('endDate').value;
                    const category = document.getElementById('categoryFilter').value;

                    // Validate inputs
                    if (!startDate || !endDate) {
                        alert('Please select both start and end dates');
                        return;
                    }

                    if (new Date(startDate) > new Date(endDate)) {
                        alert('Start date must be before end date');
                        return;
                    }

                    // Fetch data from your PHP backend
                    const response = await fetch('actions/export-sales-data.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            startDate: startDate,
                            endDate: endDate,
                            category: category
                        })
                    });

                    const data = await response.json();

                    if (!data.success) {
                        throw new Error(data.error || 'Failed to fetch data');
                    }

                    // Create PDF using jsPDF
                    const { jsPDF } = window.jspdf;
                    const doc = new jsPDF();

                    // Add company header
                    doc.setFillColor(189, 108, 31);
                    doc.rect(0, 0, 220, 40, 'F');
                    
                    doc.setTextColor(255, 255, 255);
                    doc.setFontSize(20);
                    doc.setFont('helvetica', 'bold');
                    doc.text('BREWPOINT POS', 20, 25);
                    
                    doc.setFontSize(14);
                    doc.setFont('helvetica', 'normal');
                    doc.text('SALES REPORT', 20, 35);

                    // Add report details
                    doc.setTextColor(0, 0, 0);
                    doc.setFontSize(10);
                    doc.text(`Business Name: BigBrew`, 20, 55);
                    doc.text(`Start Date: ${new Date(startDate).toLocaleDateString()}`, 20, 65);
                    doc.text(`End Date: ${new Date(endDate).toLocaleDateString()}`, 20, 75);
                    doc.text(`Prepared By: Username`, 20, 85);
                    doc.text(`Generated: ${new Date().toLocaleString()}`, 120, 55);
                    doc.text(`Category Filter: ${category}`, 120, 65);
                    doc.text(`Total Records: ${data.data.length}`, 120, 75);

                    // Prepare table data
                    const tableData = data.data.map(item => [
                        item.order_id,
                        item.product_name,
                        item.quantity.toString(),
                        item.add_ons,
                        `₱${item.item_price.toFixed(2)}`,
                        new Date(item.order_date).toLocaleString('en-US', {
                            month: '2-digit',
                            day: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        })
                    ]);

                    // Add table
                    doc.autoTable({
                        startY: 95,
                        head: [['Order ID', 'Product Name', 'Qty', 'Add-ons', 'Price', 'Date & Time']],
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
                            fillColor: [245, 245, 245]
                        },
                        columnStyles: {
                            0: { cellWidth: 25 },
                            1: { cellWidth: 45 },
                            2: { cellWidth: 15 },
                            3: { cellWidth: 30 },
                            4: { cellWidth: 25 },
                            5: { cellWidth: 40 }
                        }
                    });

                    // Add summary
                    const finalY = doc.lastAutoTable.finalY + 20;
                    doc.setFontSize(12);
                    doc.setFont('helvetica', 'bold');
                    doc.text('SUMMARY', 20, finalY);
                    
                    doc.setFontSize(10);
                    doc.setFont('helvetica', 'normal');
                    doc.text(`Total Orders: ${data.summary.total_orders}`, 20, finalY + 15);
                    doc.text(`Total Items Sold: ${data.summary.total_quantity}`, 20, finalY + 25);
                    doc.text(`Total Revenue: ₱${data.summary.total_revenue.toFixed(2)}`, 20, finalY + 35);
                    doc.text(`Average Order Value: ₱${data.summary.avg_order_value.toFixed(2)}`, 20, finalY + 45);

                    // Add top products if available
                    if (data.top_products && data.top_products.length > 0) {
                        doc.setFont('helvetica', 'bold');
                        doc.text('TOP SELLING PRODUCTS', 120, finalY + 15);
                        
                        doc.setFont('helvetica', 'normal');
                        data.top_products.slice(0, 5).forEach((product, index) => {
                            const yPos = finalY + 25 + (index * 8);
                            doc.text(`${index + 1}. ${product.product_name} (${product.total_quantity} sold)`, 120, yPos);
                        });
                    }

                    // Generate filename
                    const filename = `BrewPoint_Sales_Report_${startDate}_to_${endDate}.pdf`;

                    // Save PDF
                    doc.save(filename);

                    // Show success message
                    setTimeout(() => {
                        alert('PDF generated and downloaded successfully!');
                        closeExportModal();
                    }, 500);

                } catch (error) {
                    console.error('Error generating PDF:', error);
                    alert('Error generating PDF: ' + error.message);
                } finally {
                    // Reset button state
                    generateBtn.disabled = false;
                    loadingSpinner.style.display = 'none';
                    downloadIcon.style.display = 'inline';
                    generateBtn.innerHTML = '<i class="fa-solid fa-download"></i> Generate PDF';
                }
            }

            // Event listeners for the modal
            document.addEventListener('DOMContentLoaded', function() {
                const startDateInput = document.getElementById('startDate');
                const endDateInput = document.getElementById('endDate');
                const categorySelect = document.getElementById('categoryFilter');

                if (startDateInput) startDateInput.addEventListener('change', updatePreview);
                if (endDateInput) endDateInput.addEventListener('change', updatePreview);
                if (categorySelect) categorySelect.addEventListener('change', updatePreview);

                // Close modal when clicking overlay
                const exportModal = document.getElementById('exportModal');
                if (exportModal) {
                    exportModal.addEventListener('click', function(e) {
                        if (e.target === this) {
                            closeExportModal();
                        }
                    });
                }

                // Keyboard shortcuts
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        closeExportModal();
                    }
                });
            });
        </script>

    </body>
</html>