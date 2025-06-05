<?php
session_start();
include ("../database/db.php");

$message = "";
$toastClass = "";

// Rate limiting - prevent brute force attacks
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = time();
}

// Reset attempts after 15 minutes
if (time() - $_SESSION['last_attempt'] > 900) {
    $_SESSION['login_attempts'] = 0;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check rate limiting
    if ($_SESSION['login_attempts'] >= 5) {
        $message = "Too many login attempts. Please try again in 15 minutes.";
        $toastClass = "error";
    } else {
        $uname = trim($_POST['uname']);
        $pw = $_POST['pw'];
        
        // Input validation
        if (empty($uname) || empty($pw)) {
            $message = "Username and password are required.";
            $toastClass = "error";
            $_SESSION['login_attempts']++;
            $_SESSION['last_attempt'] = time();
        } else {
            // Fetch user with hashed password
            $stmt = $conn->prepare("SELECT userID, uname, pw, last_login FROM users WHERE uname = ?");
            $stmt->bind_param("s", $uname);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($pw, $user['pw'])) {
                    // Login success
                    $_SESSION['user_id'] = $user['userID'];
                    $_SESSION['uname'] = $user['uname'];
                    $_SESSION['login_time'] = time();
                    
                    // Reset login attempts
                    $_SESSION['login_attempts'] = 0;
                    
                    // Update last login time
                    $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE userID = ?");
                    $updateStmt->bind_param("i", $user['id']);
                    $updateStmt->execute();
                    $updateStmt->close();
                    
                    $message = "Login successful! Welcome back, " . htmlspecialchars($user['uname']) . "!";
                    $toastClass = "success";
                    
                    // Set session for dashboard toast
                    $_SESSION['toast_message'] = $message;
                    $_SESSION['toast_class'] = $toastClass;
                    
                    // Redirect after showing success message
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = '../dashboard.php';
                        }, 2000);
                    </script>";
                } else {
                    // Invalid password
                    $message = "Invalid username or password.";
                    $toastClass = "error";
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt'] = time();
                    
                    // Log failed login attempt
                    error_log("Failed login attempt for username: " . $uname . " from IP: " . $_SERVER['REMOTE_ADDR']);
                }
            } else {
                // User not found
                $message = "Invalid username or password.";
                $toastClass = "error";
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt'] = time();
            }

            $stmt->close();
        }
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BrewPoint</title>
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
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
        
        .attempts-warning {
            color: #dc3545;
            font-size: 12px;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php if (!empty($message)): ?>
        <div class="toast <?php echo $toastClass; ?>" id="toast">
            <?php echo htmlspecialchars($message); ?>
        </div>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toast = document.getElementById('toast');
                if (toast) {
                    toast.classList.add('show');
                    
                    setTimeout(function() {
                        toast.classList.remove('show');
                    }, 4000);
                }
            });
        </script>
    <?php endif; ?>
    
    <?php if ($_SESSION['login_attempts'] >= 3 && $_SESSION['login_attempts'] < 5): ?>
        <div class="attempts-warning">
            Warning: <?php echo $_SESSION['login_attempts']; ?>/5 login attempts used. Account will be temporarily locked after 5 failed attempts.
        </div>
    <?php endif; ?>
</body>
</html>