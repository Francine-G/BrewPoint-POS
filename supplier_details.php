<?php
// Move PHP code to top for better organization
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "brewpos";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
?>

<html> 
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <script src="https://kit.fontawesome.com/f4e628f07c.js" crossorigin="anonymous"></script>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel="stylesheet" href="assets/css/Supplier_Style.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <title>BrewPoint POS</title>

    </head>

    <body>
        
        <div class="container">

            <div class="sidebar">
                <div class="logo-content">
                    <img src = "assets/img/logo.png" class = "logo">
                </div>

                <div class="nav-bar">
                    <ul class = "menu-content">
                        <li>
                            <a href="dashboard.php">
                                <span class="icon"><i class='bx bxs-dashboard'></i></span>
                                <span class="title"> Dashboard</span>
                            </a>
                        </li>

                        <li>
                            <a href ="POSsystem.php">
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

                    
                <div class = "logout">
                    <a href="index.php">
                        <span><i class="fa-solid fa-right-from-bracket"></i></span>
                        <span class = "title">Logout</span>
                    </a>
                </div>
            </div>

            <main class="content">
                <div class = "header">
                    <h2>Supplier Information</h2>

                    <div class = "user-icon">
                        <i class="fa-solid fa-circle-user"></i>
                    </div>
                </div>

                <div class = "main-contents">
                     <div class="top-content">
                        <div class="add-button">
                            <span class="add-text"><a href = "create_supplier_form.php"><i class="fa-solid fa-pen-to-square"></i> Add New Supplier</a></span>
                        </div>
                     </div>
                     
                     <div class="table-content">
                        <div class="table-wrapper">
                            <div class="top-header">
                                <div class="filter">
                                    <select id="filterSelect">
                                        <option value="all">All</option>
                                        <option value="coffee">Coffee Beans</option>
                                        <option value="syrups">Syrups</option>
                                    </select>
                                </div>
                                <div class="export-btn">
                                    <button id="exportBtn" onclick="openExportModal()"><i class="fas fa-file-pdf"></i> Export PDF</button>
                                </div>
                            </div>
                            <table class='add-supplier-table' id="suppliersTable">
                                <thead>
                                    <tr>
                                        <th>Supplier ID</th>
                                        <th>Supplier Name</th>
                                        <th>Address</th>
                                        <th>Product Supply</th>
                                        <th>Contact</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="suppliersTableBody">
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                            <div class="pagination" id="paginationContainer">
                                <!-- Pagination will be loaded via AJAX -->
                            </div>
                        </div>
                     </div>

                </div>
            </main>
            
        </div>

        <div id="exportModal" class="modal-overlay">
            <div class="modal-content">
                <div class="modal-header">
                    <h3>
                        <i class="fas fa-file-export"></i>
                        Export Suppliers Report
                    </h3>
                    <button type="button" class="close-btn" onclick="closeExportModal()">
                        &times;
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="productFilter">Filter by Product Category:</label>
                        <select id="productFilter" class="form-control">
                            <option value="all">All Products</option>
                            <option value="coffee">Coffee Beans</option>
                            <option value="syrups">Syrups</option>
                            <option value="pastries">Pastries</option>
                            <option value="equipment">Equipment</option>
                            <option value="packaging">Packaging</option>
                        </select>
                    </div>
                    
                    <div class="filter-info">
                        <strong>Note:</strong> The PDF will include all suppliers that match your selected filter criteria. 
                        If "All Products" is selected, all suppliers will be included in the report.
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeExportModal()">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="generatePdfBtn" onclick="generatePDF()">
                        <span class="loading-spinner" id="loadingSpinner"></span>
                        <i class="fas fa-download" id="downloadIcon"></i>
                        <span id="btnText">Generate PDF</span>
                    </button>
                </div>
            </div>
        </div>
        <script src="assets/js/Sidebar-nav.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="assets/js/supplier-pdf-exporter.js"></script>

        <?php
            $msg = $_GET['msg'] ?? null;
            if ($msg):
            ?>
            <script>
            document.addEventListener("DOMContentLoaded", () => {
                <?php if ($msg === 'added'): ?>
                    Swal.fire("Success", "Supplier added successfully!", "success");
                <?php elseif ($msg === 'add_error'): ?>
                    Swal.fire("Error", "Failed to add supplier.", "error");
                <?php elseif ($msg === 'edited'): ?>
                    Swal.fire("Success", "Supplier updated successfully!", "success");
                <?php elseif ($msg === 'edit_error'): ?>
                    Swal.fire("Error", "Failed to update supplier.", "error");
                <?php elseif ($msg === 'deleted'): ?>
                    Swal.fire("Success", "Supplier deleted successfully!", "success");
                <?php elseif ($msg === 'delete_error'): ?>
                    Swal.fire("Error", "Failed to delete supplier.", "error");
                <?php endif; ?>
            });
            </script>
        <?php endif; ?>

        <script>
            let currentPage = 1;

            // Load suppliers on page load
            $(document).ready(function() {
                loadSuppliers(1);
            });

            // Function to load suppliers via AJAX
            function loadSuppliers(page = 1) {
                currentPage = page;
                
                console.log('Loading suppliers with page:', page); // Debug line
                
                $.ajax({
                    url: 'actions/load-supplier-data.php',
                    type: 'POST',
                    data: {
                        page: page
                    },
                    success: function(response) {
                        console.log('Raw response:', response); // Debug line
                        try {
                            const data = JSON.parse(response);
                            console.log('Parsed data:', data); // Debug line
                            $('#suppliersTableBody').html(data.table_rows);
                            $('#paginationContainer').html(data.pagination);
                        } catch (e) {
                            console.error('Error parsing JSON:', e, response);
                            $('#suppliersTableBody').html('<tr><td colspan="6">Error loading data</td></tr>');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', xhr.responseText); // Debug line
                        $('#suppliersTableBody').html('<tr><td colspan="6">Error loading data</td></tr>');
                    }
                });
            }

            // Function to change page
            function changePage(page) {
                loadSuppliers(page);
            }

            function confirmDelete(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will permanently delete the supplier.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#4e944f',
                    cancelButtonColor: '#863a3a',
                    confirmButtonText: 'Yes'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect to delete PHP file
                        window.location.href = 'actions/delete_supplier.php?id=' + id;
                    }
                });
            }
        </script>

    </body>
</html>