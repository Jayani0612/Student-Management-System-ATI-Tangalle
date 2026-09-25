<?php
session_start();
require_once 'config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, full_name, username, password, role FROM users WHERE username = ?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['username']  = $user['username'];
            $_SESSION['role']      = $user['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>ATI Tangalle · Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
/* ===== RESET & BASE ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #e8f0fe 0%, #d4e4f7 40%, #b8d4f0 80%, #9cc4eb 100%);
    padding: 20px;
    position: relative;
    overflow: hidden;
}

/* ===== ANIMATED BACKGROUND ===== */
body::before {
    content: '';
    position: absolute;
    width: 500px;
    height: 500px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    top: -150px;
    right: -150px;
    animation: floatBubble 25s infinite ease-in-out;
}

body::after {
    content: '';
    position: absolute;
    width: 350px;
    height: 350px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    bottom: -100px;
    left: -100px;
    animation: floatBubble 30s infinite ease-in-out reverse;
}

@keyframes floatBubble {
    0%, 100% { transform: translate(0, 0) scale(1); }
    50% { transform: translate(60px, -40px) scale(1.1); }
}

/* Floating particles */
.particles {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
    pointer-events: none;
}

.particle {
    position: absolute;
    width: 8px;
    height: 8px;
    background: rgba(255, 255, 255, 0.4);
    border-radius: 50%;
    animation: particleFloat 18s infinite linear;
}

.particle:nth-child(1) { left: 8%; animation-duration: 20s; animation-delay: 0s; }
.particle:nth-child(2) { left: 18%; animation-duration: 24s; animation-delay: 2s; width: 12px; height: 12px; }
.particle:nth-child(3) { left: 30%; animation-duration: 17s; animation-delay: 4s; }
.particle:nth-child(4) { left: 45%; animation-duration: 22s; animation-delay: 1s; width: 14px; height: 14px; }
.particle:nth-child(5) { left: 58%; animation-duration: 19s; animation-delay: 3s; }
.particle:nth-child(6) { left: 70%; animation-duration: 25s; animation-delay: 5s; width: 10px; height: 10px; }
.particle:nth-child(7) { left: 82%; animation-duration: 18s; animation-delay: 2s; }
.particle:nth-child(8) { left: 92%; animation-duration: 23s; animation-delay: 4s; width: 13px; height: 13px; }

@keyframes particleFloat {
    0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
    10% { opacity: 1; }
    90% { opacity: 1; }
    100% { transform: translateY(-10vh) rotate(720deg); opacity: 0; }
}

/* ===== LOGIN CARD ===== */
.login-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 32px;
    padding: 50px 45px 40px;
    max-width: 440px;
    width: 100%;
    box-shadow: 
        0 30px 80px rgba(44, 106, 168, 0.15),
        0 0 0 1px rgba(255, 255, 255, 0.8);
    position: relative;
    z-index: 2;
    animation: slideUp 0.7s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* ===== INSTITUTE LOGO ===== */
.institute-logo {
    text-align: center;
    margin-bottom: 25px;
}

.institute-logo .logo-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #4a90d9, #2c6aa8);
    border-radius: 50%;
    font-size: 36px;
    color: white;
    margin-bottom: 14px;
    box-shadow: 0 8px 30px rgba(74, 144, 217, 0.25);
}

.institute-logo h1 {
    font-size: 28px;
    font-weight: 800;
    color: #1a3a5c;
    letter-spacing: -0.5px;
}

.institute-logo h1 span {
    background: linear-gradient(135deg, #4a90d9, #2c6aa8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.institute-logo .sub-title {
    display: block;
    font-size: 13px;
    font-weight: 500;
    color: #6a8aaa;
    margin-top: 4px;
    letter-spacing: 1px;
}

.login-sub {
    text-align: center;
    color: #6a8aaa;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 30px;
    padding-bottom: 22px;
    border-bottom: 2px solid #e8f0fe;
    letter-spacing: 0.3px;
}

.login-sub i {
    margin-right: 8px;
    color: #4a90d9;
}

/* ===== ERROR MESSAGE ===== */
.login-error {
    background: linear-gradient(135deg, #fff5f5, #ffe8e8);
    color: #c0392b;
    padding: 14px 20px;
    border-radius: 14px;
    margin-bottom: 22px;
    font-size: 14px;
    border-left: 4px solid #c0392b;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: shake 0.5s ease;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20% { transform: translateX(-10px); }
    40% { transform: translateX(10px); }
    60% { transform: translateX(-5px); }
    80% { transform: translateX(5px); }
}

.login-error i {
    font-size: 20px;
}

/* ===== FORM ===== */
.form-group {
    margin-bottom: 22px;
}

.form-group label {
    display: block;
    font-weight: 600;
    font-size: 13px;
    color: #1a3a5c;
    margin-bottom: 8px;
    letter-spacing: 0.3px;
}

.form-group label i {
    margin-right: 8px;
    color: #4a90d9;
    width: 16px;
}

.form-control {
    width: 100%;
    padding: 14px 18px;
    border: 2px solid #d4e0ed;
    border-radius: 14px;
    font-size: 15px;
    color: #1a3a5c;
    background: #f8fafc;
    transition: all 0.3s ease;
    font-family: inherit;
}

.form-control:focus {
    border-color: #4a90d9;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(74, 144, 217, 0.1);
    outline: none;
}

.form-control::placeholder {
    color: #a0b8d0;
    font-size: 14px;
}

/* Input group with icon */
.input-group {
    position: relative;
}

.input-group .form-control {
    padding-right: 50px;
}

.input-group .input-icon {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #a0b8d0;
    font-size: 20px;
    pointer-events: none;
    transition: color 0.3s;
}

.form-control:focus + .input-icon {
    color: #4a90d9;
}

/* ===== PASSWORD TOGGLE ===== */
.toggle-password {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #a0b8d0;
    font-size: 20px;
    transition: color 0.3s;
    background: none;
    border: none;
    padding: 0;
}

.toggle-password:hover {
    color: #4a90d9;
}

/* ===== OPTIONS ROW ===== */
.login-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 22px 0 28px;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 14px;
    color: #6a8aaa;
}

.remember-me input[type="checkbox"] {
    width: 20px;
    height: 20px;
    accent-color: #4a90d9;
    cursor: pointer;
    border-radius: 5px;
}

.remember-me .checkmark {
    display: flex;
    align-items: center;
    gap: 8px;
}

.forgot-link {
    color: #4a90d9;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: color 0.3s;
}

.forgot-link:hover {
    color: #2c6aa8;
    text-decoration: underline;
}

/* ===== BUTTON ===== */
.btn-primary {
    width: 100%;
    padding: 16px;
    background: linear-gradient(135deg, #4a90d9 0%, #357abd 50%, #2c6aa8 100%);
    border: none;
    border-radius: 14px;
    color: white;
    font-weight: 700;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
}

.btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
    transition: left 0.6s;
}

.btn-primary:hover::before {
    left: 100%;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(74, 144, 217, 0.35);
}

.btn-primary:active {
    transform: translateY(0);
    box-shadow: 0 5px 15px rgba(74, 144, 217, 0.2);
}

.btn-primary i {
    font-size: 20px;
}

/* ===== FOOTER ===== */
.login-footer {
    text-align: center;
    margin-top: 28px;
    font-size: 13px;
    color: #8aaac0;
    padding-top: 20px;
    border-top: 1px solid #e8f0fe;
}

.login-footer i {
    margin-right: 4px;
}

.login-footer a {
    color: #4a90d9;
    text-decoration: none;
    font-weight: 500;
}

.login-footer a:hover {
    text-decoration: underline;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 520px) {
    .login-card {
        padding: 35px 25px 30px;
        border-radius: 24px;
    }
    
    .institute-logo .logo-icon {
        width: 65px;
        height: 65px;
        font-size: 30px;
    }
    
    .institute-logo h1 {
        font-size: 22px;
    }
    
    .login-options {
        flex-direction: column;
        gap: 14px;
        align-items: flex-start;
    }
    
    .form-control {
        padding: 12px 15px;
        font-size: 14px;
    }
    
    .btn-primary {
        padding: 14px;
        font-size: 15px;
    }
}

@media (max-width: 380px) {
    .login-card {
        padding: 25px 18px 22px;
        border-radius: 20px;
    }
    
    .institute-logo h1 {
        font-size: 19px;
    }
    
    .institute-logo .logo-icon {
        width: 55px;
        height: 55px;
        font-size: 26px;
    }
}
</style>
</head>
<body>

<!-- Animated Particles -->
<div class="particles">
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
    <div class="particle"></div>
</div>

<div class="login-card">
    <div class="institute-logo">
        <div class="logo-icon">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <h1>ATI <span>Tangalle</span></h1>
        <span class="sub-title">Advanced Technological Institute</span>
    </div>
    <div class="login-sub">
        <i class="fas fa-shield-alt"></i> Secure Admin Portal
    </div>

    <?php if ($error): ?>
        <div class="login-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="username">
                <i class="fas fa-user"></i> Username
            </label>
            <div class="input-group">
                <input 
                    type="text" 
                    name="username" 
                    id="username" 
                    class="form-control" 
                    placeholder="Enter your username" 
                    required 
                    autofocus
                    value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                >
                <span class="input-icon">
                    <i class="fas fa-user-circle"></i>
                </span>
            </div>
        </div>

        <div class="form-group">
            <label for="password">
                <i class="fas fa-lock"></i> Password
            </label>
            <div class="input-group">
                <input 
                    type="password" 
                    name="password" 
                    id="password" 
                    class="form-control" 
                    placeholder="Enter your password" 
                    required
                >
                <button type="button" class="toggle-password" onclick="togglePassword()" aria-label="Toggle password visibility">
                    <i class="fas fa-eye" id="eye-icon"></i>
                </button>
            </div>
        </div>

        <div class="login-options">
            <label class="remember-me">
                <input type="checkbox" name="remember" id="remember">
                <span class="checkmark">
                    <i class="fas fa-check-circle" style="color: #4a90d9;"></i>
                    Remember me
                </span>
            </label>
            <a href="#" class="forgot-link">
                <i class="fas fa-key"></i> Forgot Password?
            </a>
        </div>

        <button type="submit" class="btn-primary">
            <i class="fas fa-sign-in-alt"></i>
            Sign In
        </button>
    </form>

    <div class="login-footer">
        <i class="fas fa-copyright"></i> 2026 ATI Tangalle · All rights reserved
    </div>
</div>

<script>
// Toggle password visibility
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.className = 'fas fa-eye-slash';
        eyeIcon.parentElement.style.color = '#4a90d9';
    } else {
        passwordInput.type = 'password';
        eyeIcon.className = 'fas fa-eye';
        eyeIcon.parentElement.style.color = '#a0b8d0';
    }
}

// Auto dismiss error message after 6 seconds
document.addEventListener('DOMContentLoaded', function() {
    const errorMsg = document.querySelector('.login-error');
    if (errorMsg) {
        setTimeout(() => {
            errorMsg.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            errorMsg.style.opacity = '0';
            errorMsg.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                errorMsg.style.display = 'none';
            }, 500);
        }, 6000);
    }
});

// Prevent multiple form submissions
document.querySelector('form').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('.btn-primary');
    if (submitBtn.disabled) {
        e.preventDefault();
        return;
    }
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing in...';
    
    setTimeout(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
    }, 5000);
});
</script>

</body>
</html>