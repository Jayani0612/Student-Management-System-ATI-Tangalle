```php
<?php
session_start();
require_once 'config/db.php';

// Already logged in users redirect
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'student') {
        header("Location: student_dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $student_id = trim($_POST['student_id'] ?? '');

    // Validation
    if (empty($full_name) || empty($email) || empty($username) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Check if username or email exists
        $check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username' OR email = '$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = 'Username or email already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'student';
            
            $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, email, username, password, phone, role, student_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssssss", $full_name, $email, $username, $hashed_password, $phone, $role, $student_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration · EduSphere</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #e8f0fe 0%, #d4e0ed 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-card {
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(44, 106, 168, 0.15);
        }
        .register-logo {
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            color: #1a3a5c;
            margin-bottom: 5px;
        }
        .register-logo span { color: #4a90d9; }
        .register-sub {
            text-align: center;
            color: #6a8aaa;
            margin-bottom: 25px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            color: #1a3a5c;
            margin-bottom: 5px;
        }
        .form-group label .required {
            color: #ef4444;
        }
        .form-control {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #d4e0ed;
            border-radius: 10px;
            font-size: 14px;
            color: #1a3a5c;
            transition: all 0.3s;
            font-family: inherit;
            background: #f8fafc;
}
        .form-control:focus {
            border-color: #4a90d9;
            outline: none;
            box-shadow: 0 0 0 4px rgba(74, 144, 217, 0.08);
            background: #fff;
        }
        .form-control::placeholder {
            color: #a0b8cc;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #4a90d9, #2c6aa8);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 5px;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(74, 144, 217, 0.35);
        }
        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        .register-error {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .register-success {
            background: #d1fae5;
            color: #10b981;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .register-hint {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #6a8aaa;
        }
        .register-hint a {
            color: #4a90d9;
            text-decoration: none;
            font-weight: 600;
        }
        .register-hint a:hover {
            text-decoration: underline;
        }
        .login-link {
            margin-top: 15px;
            text-align: center;
        }
        @media (max-width: 480px) {
            .register-card {
                padding: 25px 20px;
            }
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<div class="register-card">
    <div class="register-logo">Edu<span>Sphere</span></div>
    <div class="register-sub">Student Registration</div>

    <?php if ($error): ?>
        <div class="register-error">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="register-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success) ?>
        </div>
        <div style="text-align:center;margin-top:15px;">
            <a href="student_login.php" class="btn-primary" style="display:inline-block;padding:12px 30px;text-decoration:none;width:auto;">
                <i class="fas fa-sign-in-alt"></i> Login Now
            </a>
        </div>
    <?php else: ?>
        <form method="POST" id="registerForm">
            <div class="form-group">
                <label>Full Name <span class="required">*</span></label>
                <input type="text" name="full_name" class="form-control" placeholder="e.g., Jayani Perera" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Student ID</label>
                    <input type="text" name="student_id" class="form-control" placeholder="e.g., STU-2026-001">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" placeholder="07XXXXXXXX">
                </div>
            </div>

            <div class="form-group">
                <label>Email <span class="required">*</span></label>
<input type="email" name="email" class="form-control" placeholder="student@example.com" required>
            </div>

            <div class="form-group">
                <label>Username <span class="required">*</span></label>
                <input type="text" name="username" class="form-control" placeholder="Choose a username" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password <span class="required">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Min 6 characters" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm Password <span class="required">*</span></label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm password" required>
                </div>
            </div>

            <button type="submit" class="btn-primary" id="submitBtn">
                <i class="fas fa-user-plus"></i> Register
            </button>
        </form>

        <div class="register-hint">
            Already have an account? <a href="student_login.php">Login here</a>
        </div>
        <div class="register-hint" style="margin-top:5px;">
            <a href="login.php">Admin Login</a>
        </div>
    <?php endif; ?>
</div>

<script>
    // Password match validation
    document.getElementById('registerForm')?.addEventListener('submit', function(e) {
        const password = document.querySelector('input[name="password"]').value;
        const confirm = document.querySelector('input[name="confirm_password"]').value;
        
        if (password !== confirm) {
            e.preventDefault();
            alert('Passwords do not match!');
        }
    });

    // Show password strength indicator (optional)
    document.querySelector('input[name="password"]')?.addEventListener('input', function() {
        const val = this.value;
        const indicator = document.getElementById('passwordStrength');
        if (val.length < 6) {
            // Weak
        } else if (val.length < 10) {
            // Medium
        } else {
            // Strong
        }
    });
</script>
</body>
</html>