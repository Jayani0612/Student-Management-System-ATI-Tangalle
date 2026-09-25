<?php
require_once 'config/db.php';

// ========================================
// 🔑 RESET PASSWORD - මෙතන වෙනස් කරන්න
// ========================================

$username = 'admin';        // ← ඔබගේ username එක දාන්න
$new_password = 'admin123'; // ← ඔබට ඕනෙ password එක දාන්න

// ========================================
// SCRIPT - මෙය වෙනස් කරන්න එපා
// ========================================

echo "<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; padding: 40px; background: #f0f4f8; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { color: #1a3a5c; }
        .success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; border-left: 4px solid #10b981; }
        .error { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; border-left: 4px solid #ef4444; }
        .info { background: #e0f2fe; color: #0369a1; padding: 15px; border-radius: 8px; border-left: 4px solid #3b82f6; }
        .btn { display: inline-block; padding: 10px 20px; background: #2c6aa8; color: #fff; text-decoration: none; border-radius: 8px; margin-top: 10px; }
        .btn:hover { background: #1a4f7a; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f8fafc; font-weight: 600; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h2>🔑 Reset Password</h2>";

// 1. Check if user exists
$check = $conn->prepare("SELECT id, username, role, is_active FROM users WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
$result = $check->get_result();

if ($result->num_rows === 0) {
    echo "<div class='error'>❌ User not found: <strong>" . htmlspecialchars($username) . "</strong></div>";
    
    echo "<div class='info'>📋 Available users in database:<br>";
    $all = $conn->query("SELECT username FROM users");
    while ($row = $all->fetch_assoc()) {
        echo "• " . $row['username'] . "<br>";
    }
    echo "</div>";
    echo "</div></body></html>";
    exit();
}

$user = $result->fetch_assoc();

// 2. Create new password hash
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

// 3. Update password
$update = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
$update->bind_param("ss", $hashed_password, $username);

if ($update->execute()) {
    echo "<div class='success'>✅ Password updated successfully!</div>";
    echo "<h3>📋 New Credentials</h3>";
    echo "<table>";
    echo "<tr><td><strong>Username</strong></td><td>" . htmlspecialchars($username) . "</td></tr>";
    echo "<tr><td><strong>Password</strong></td><td><strong style='color:#2c6aa8;'>" . htmlspecialchars($new_password) . "</strong></td></tr>";
    echo "<tr><td><strong>Role</strong></td><td>" . $user['role'] . "</td></tr>";
    echo "</table>";
    
    echo "<a href='login.php' class='btn'>🔐 Go to Login</a> ";
    echo "<a href='test_login.php' class='btn' style='background:#6b7280;'>🧪 Test Login</a>";
} else {
    echo "<div class='error'>❌ Failed to update password!<br>Error: " . $conn->error . "</div>";
}

echo "</div></body></html>";
?>