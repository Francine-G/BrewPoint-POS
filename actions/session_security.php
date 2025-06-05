<?php
// session_security.php - Include this at the top of protected pages

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function checkUserSession() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['uname'])) {
        return false;
    }
    
    // Check session timeout (30 minutes)
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
        session_destroy();
        return false;
    }
    
    // Update last activity time
    $_SESSION['login_time'] = time();
    return true;
}

// Function to require login
function requireLogin() {
    if (!checkUserSession()) {
        $_SESSION['toast_message'] = "Please log in to access this page.";
        $_SESSION['toast_class'] = "error";
        header("Location: index.php");
        exit();
    }
}

// Function to get toast notification
function getToastNotification() {
    $toast = '';
    if (isset($_SESSION['toast_message'])) {
        $message = htmlspecialchars($_SESSION['toast_message']);
        $class = $_SESSION['toast_class'];
        
        $icon = ($class === 'success') ? 'bx-check-circle' : 'bx-error-circle';
        
        $toast = "
        <div class='toast {$class}' id='toast'>
            <i class='bx {$icon}'></i>
            <span>{$message}</span>
            <button class='toast-close' onclick='hideToast(this.parentElement)'>&times;</button>
        </div>
        
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toast = document.getElementById('toast');
                if (toast) {
                    setTimeout(() => toast.classList.add('show'), 100);
                    setTimeout(() => hideToast(toast), 5000);
                }
            });
            
            function hideToast(toast) {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }
        </script>";
        
        // Clear the session message
        unset($_SESSION['toast_message']);
        unset($_SESSION['toast_class']);
    }
    
    return $toast;
}

// Logout handler
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// CSRF Token generation
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Token validation
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>