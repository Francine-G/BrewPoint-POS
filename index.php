<!DOCTYPE html>
<html> 
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <link rel="stylesheet" href="assets/css/Login.css">
        <script src="assets/js/login.js"></script>
        <title>BrewPoint Login</title>

    </head>

    <body>
        <div class="container">
            <div class="form-box login-form">
                <form method="POST" action="actions/login.php" id="loginForm">
                    <h1>Log In</h1>
                    
                    <div class="input-group">
                        <input type="text" placeholder="Username" name="uname" required 
                               pattern="[a-zA-Z0-9_]{3,50}" 
                               title="Username must be 3-50 characters, letters, numbers, and underscores only">
                        <i class='bx bxs-user'></i>
                    </div>
                    <div class="input-group">
                        <input type="password" placeholder="Password" name="pw" required minlength="6">
                        <i class='bx bxs-lock-alt'></i>
                    </div>

                    <button class="btn login-btn" type="submit" name="login">Login</button>
                </form>
            </div>

            <div class="form-box signup-form">
                <form method="POST" action="actions/signup.php" id="signupForm">
                    <h1>Sign Up</h1>
                    
                    <div class="input-group">
                        <input type="text" placeholder="Username" name="uname" required 
                               pattern="[a-zA-Z0-9_]{3,50}"
                               title="Username must be 3-50 characters, letters, numbers, and underscores only"
                               id="signupUsername">
                        <i class='bx bxs-user'></i>
                    </div>
                    <div class="input-group">
                        <input type="password" placeholder="Password" name="pw" required 
                               minlength="6" id="signupPassword">
                        <i class='bx bxs-lock-alt'></i>
                        <div class="password-strength" id="passwordStrength"></div>
                    </div>

                    <button class="btn signup-btn" type="submit" name="signup">Sign Up</button>
                </form>
            </div>

            <div class="toggle-box">
                <div class="toggle-panel toggle-left">
                    <h1>Hello, Welcome to BrewPOINT!</h1>
                    <p>Don't have an account?</p>
                    <button class="btn toggle-signup-btn">Sign Up</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Hello, Welcome Back!</h1>
                    <p>Already have an account?</p>
                    <button class="btn toggle-login-btn">Login</button>
                </div>
            </div>
        </div>

        <div class="security-info">
            <i class='bx bxs-shield-check'></i>
            <span>Secure Login • Password Protected</span>
        </div>

        <script>
            // Toast notification system
            function showToast(message, type) {
                // Remove existing toast
                const existingToast = document.querySelector('.toast');
                if (existingToast) {
                    existingToast.remove();
                }
                
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                
                const icon = type === 'success' ? 'bx-check-circle' : 'bx-error-circle';
                
                toast.innerHTML = `
                    <i class='bx ${icon}'></i>
                    <span>${message}</span>
                    <button class="toast-close" onclick="hideToast(this.parentElement)">&times;</button>
                `;
                
                document.body.appendChild(toast);
                
                // Show toast
                setTimeout(() => toast.classList.add('show'), 100);
                
                // Auto hide after 5 seconds
                setTimeout(() => hideToast(toast), 5000);
            }
            
            function hideToast(toast) {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }
            
            // Password strength checker
            document.getElementById('signupPassword')?.addEventListener('input', function() {
                const password = this.value;
                const strengthDiv = document.getElementById('passwordStrength');
                
                if (password.length === 0) {
                    strengthDiv.textContent = '';
                    return;
                }
                
                let strength = 0;
                if (password.length >= 6) strength++;
                if (password.match(/[a-z]/)) strength++;
                if (password.match(/[A-Z]/)) strength++;
                if (password.match(/[0-9]/)) strength++;
                if (password.match(/[^a-zA-Z0-9]/)) strength++;
                
                if (strength <= 2) {
                    strengthDiv.textContent = 'Weak password';
                    strengthDiv.className = 'password-strength strength-weak';
                } else if (strength <= 3) {
                    strengthDiv.textContent = 'Medium password';
                    strengthDiv.className = 'password-strength strength-medium';
                } else {
                    strengthDiv.textContent = 'Strong password';
                    strengthDiv.className = 'password-strength strength-strong';
                }
            });
            
            // Form validation
            document.getElementById('loginForm')?.addEventListener('submit', function(e) {
                const username = this.querySelector('input[name="uname"]').value.trim();
                const password = this.querySelector('input[name="pw"]').value;
                
                if (username.length < 3) {
                    e.preventDefault();
                    showToast('Username must be at least 3 characters long', 'error');
                    return;
                }
                
                if (password.length < 6) {
                    e.preventDefault();
                    showToast('Password must be at least 6 characters long', 'error');
                    return;
                }
            });
            
            document.getElementById('signupForm')?.addEventListener('submit', function(e) {
                const username = this.querySelector('input[name="uname"]').value.trim();
                const password = this.querySelector('input[name="pw"]').value;
                
                if (username.length < 3 || username.length > 50) {
                    e.preventDefault();
                    showToast('Username must be between 3 and 50 characters', 'error');
                    return;
                }
                
                if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                    e.preventDefault();
                    showToast('Username can only contain letters, numbers, and underscores', 'error');
                    return;
                }
                
                if (password.length < 6) {
                    e.preventDefault();
                    showToast('Password must be at least 6 characters long', 'error');
                    return;
                }
            });
            
            // Check for session messages on page load
            document.addEventListener('DOMContentLoaded', function() {
                // This would be populated by PHP session data
                // For demo purposes, you can test with:
                // showToast('Welcome to BrewPoint!', 'success');
            });
        </script>
    </body>
</html>