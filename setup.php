<?php
/**
 * Run this file ONCE in your browser (e.g. http://localhost/edusphere/setup.php)
 * after importing database.sql. It creates the default admin account.
 * Delete this file afterwards for security.
 */
require_once 'config/db.php';

$username = 'admin';
$password = 'admin123';
$fullName = 'Admin User';
$role     = 'Super Administrator';

$check = mysqli_query($conn, "SELECT id FROM users WHERE username = '$username'");

if (mysqli_num_rows($check) > 0) {
    echo "Admin user already exists. You can log in with username: <b>admin</b>.<br>";
    echo "If you forgot the password, delete the row in the 'users' table and re-run this script.";
} else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = mysqli_prepare($conn, "INSERT INTO users (full_name, username, password, role) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssss", $fullName, $username, $hash, $role);
    mysqli_stmt_execute($stmt);

    echo "Admin account created successfully!<br><br>";
    echo "Username: <b>admin</b><br>";
    echo "Password: <b>admin123</b><br><br>";
    echo "<a href='login.php'>Go to Login</a><br><br>";
    echo "<b style='color:red'>Important:</b> delete setup.php now for security.";
}
