<?php
session_start();
include ("../database/db.php");

$message = "";
$toastClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Input validation and sanitization
    $uname = trim($_POST['uname']);
    $pw = $_POST['pw'];
    
    // Basic validation
    if (empty($uname) || empty($pw)) {
        $message = "Username and password are required.";
        $toastClass = "error";
    } elseif (strlen($uname) < 3 || strlen($uname) > 50) {
        $message = "Username must be between 3 and 50 characters.";
        $toastClass = "error";
    } elseif (strlen($pw) < 6) {
        $message = "Password must be at least 6 characters long.";
        $toastClass = "error";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $uname)) {
        $message = "Username can only contain letters, numbers, and underscores.";
        $toastClass = "error";
    } else {
        // Check if username already exists
        $checkUnameStmt = $conn->prepare("SELECT userID FROM users WHERE uname = ?");
        $checkUnameStmt->bind_param("s", $uname);
        $checkUnameStmt->execute();
        $checkUnameStmt->store_result();

        if ($checkUnameStmt->num_rows > 0) {
            $message = "Username already exists. Please choose a different one.";
            $toastClass = "error";
        } else {
            // Hash the password for security
            $hashedPassword = password_hash($pw, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (uname, pw, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $uname, $hashedPassword);
            
            if ($stmt->execute()) {
                $message = "Account created successfully! You can now log in.";
                $toastClass = "success";
                
                // Set session variable to show toast on redirect
                $_SESSION['toast_message'] = $message;
                $_SESSION['toast_class'] = $toastClass;
                
                // Redirect after 2 seconds to show toast
                echo "<script>
                    setTimeout(function() {
                        window.location.href = '../index.php';
                    }, 2000);
                </script>";
            } else {
                $message = "Registration failed. Please try again.";
                $toastClass = "error";
                error_log("Signup error: " . $stmt->error);
            }

            $stmt->close();
        }
          
        $checkUnameStmt->close();
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - BrewPoint</title>
    <style>
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            z-index: 9999;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        }
        
        .toast.success {
            background-color: #28a745;
        }
        
        .toast.error {
            background-color: #dc3545;
        }
        
        .toast.show {
            opacity: 1;
            transform: translateX(0);
        }
    </style>
</head>
<body>
    <?php if (!empty($message)): ?>
        <div class="toast <?php echo $toastClass; ?>" id="toast">
            <?php echo htmlspecialchars($message); ?>
        </div>
        
        <script>
            // Show toast notification
            document.addEventListener('DOMContentLoaded', function() {
                const toast = document.getElementById('toast');
                if (toast) {
                    toast.classList.add('show');
                    
                    // Hide toast after 4 seconds
                    setTimeout(function() {
                        toast.classList.remove('show');
                    }, 4000);
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>