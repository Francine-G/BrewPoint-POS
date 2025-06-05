<?php
session_start();
echo '<!-- DEBUG: userID=' . (isset($_SESSION['userID']) ? $_SESSION['userID'] : 'NOT SET') . ' -->';
include("../database/db.php"); // Adjust path as needed

if (!isset($_SESSION['userID'])) {
    header("Location: index.php");
    exit();
}

$userID = $_SESSION['userID'];
$sql = "SELECT * FROM users WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();

if (!$user_data) {
    // User not found, force logout
    session_destroy();
    header("Location: index.php");
    exit();
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
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
        <link rel="stylesheet" href="assets/css/user-settings_style.css">

        <title>BrewPoint POS - Settings</title>
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

                        <li class="active">
                            <a href="user-settings.php">
                                <span class="icon"><i class='bx bx-cog'></i></span>
                                <span class="title">Settings</span>
                            </a>
                        </li>

                    </ul>
                </div>

                    
                <div class="logout">
                    <a href="index.php" onclick="return confirm('Are you sure you want to logout?')">
                        <span><i class="fa-solid fa-right-from-bracket"></i></span>
                        <span class="title">Logout</span>
                    </a>
                </div>
            </div>

            <main class="content">
                <div class="header">
                    <h2>Settings</h2>
                    
                    <div class="user-icon">
                        <?php
                            $profilePic = !empty($_SESSION['profile_picture']) && file_exists("assets/img/profiles/" . $_SESSION['profile_picture'])
                                ? "assets/img/profiles/" . htmlspecialchars($_SESSION['profile_picture'])
                                : null;
                            ?>
                            <?php if ($profilePic): ?>
                                <img src="<?php echo $profilePic; ?>" alt="Profile" class="user-profile-img" id="topBarProfileImg">
                            <?php else: ?>
                                <i class="fi fi-rr-circle-user" id="topBarProfileIcon"></i>
                            <?php endif; ?>
                    </div>
                </div>
                
                <div class="user-name">
                    <span>
                        <h3>Welcome, <?php echo htmlspecialchars($user_data['uname']); ?>!</h3>
                    </span> 
                    <span><h3>Account Settings</h3></span>
                </div>
                
                <div class="main-content-body">
                    <div class="bod">
                        <div class="main-content-body-title">
                            <h2>Your Profile</h2>
                            <p>Update your profile information</p>
                        </div>
                        
                        <div class="main-content-body-content">
                            <div class="profile-info">
                                <form action="actions/update-profile.php" method="POST" enctype="multipart/form-data">
                                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="display: none;" onchange="previewImage(this)">
                                    
                                    <div class="profile-picture">
                                        <img src="assets/img/profile-picture.png" alt="Default Profile Picture" id="profilePreview">
                                        <button type="button" class="change-photo-btn" onclick="document.getElementById('profile_picture').click()">
                                            <i class="bx bx-camera"></i>
                                            Change Photo
                                        </button>
                                    </div>
                                    
                                    <div class="form-sections">
                                        <div class="input-form-1">
                                            <div class="form-group">
                                                <label for="username">Username</label>
                                                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user_data['uname']); ?>" required>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="password">New Password (Optional)</label>
                                                <input type="password" name="password" id="password" placeholder="Leave blank to keep current password">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="save-btn">
                                            <i class="bx bx-save"></i>
                                            Save Changes
                                        </button>
                                        <button type="reset" class="cancel-btn">
                                            <i class="bx bx-x"></i>
                                            Cancel
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- Success/Error Messages -->
        <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="bx bx-check-circle"></i>
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="bx bx-error-circle"></i>
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
        <?php endif; ?>
    
        <script src="assets/js/Sidebar.js"></script>
        <script>
            function previewImage(input) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        // Update profile preview in the form
                        document.getElementById('profilePreview').src = e.target.result;
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.style.display = 'none', 300);
                });
            }, 5000);

            // Form validation
            document.querySelector('form').addEventListener('submit', function(e) {
                const username = document.getElementById('username').value.trim();
                if (username === '') {
                    e.preventDefault();
                    alert('Username is required!');
                    return false;
                }
            });

            // Cancel button functionality
            document.querySelector('.cancel-btn').addEventListener('click', function() {
                if (confirm('Are you sure you want to cancel? All unsaved changes will be lost.')) {
                    location.reload();
                }
            });
        </script>
    </body>
</html>