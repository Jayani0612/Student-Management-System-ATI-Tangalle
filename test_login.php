<?php
require_once 'config/db.php';

// ===== ඔබගේ database එකේ තියෙන username සහ password =====
$username = 'admin';      // admin account එක
$password = 'admin123';   // admin හදපු වෙලාවේ දාපු password එක

echo "<h2>🔍 Login Test</h2>";

$stmt = mysqli_prepare($conn, "SELECT id, username, password, role, is_active FROM users WHERE username = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user) {
    echo "✅ User found!<br>";
    echo "Username: " . $user['username'] . "<br>";
    echo "Role: " . $user['role'] . "<br>";
    echo "is_active: " . $user['is_active'] . "<br><br>";
    
    echo "Password check: ";
    if (password_verify($password, $user['password'])) {
        echo "✅ CORRECT!<br><br>";
        echo "🎉 You can login with:<br>";
        echo "Username: <strong>" . $username . "</strong><br>";
        echo "Password: <strong>" . $password . "</strong>";
    } else {
        echo "❌ WRONG!<br><br>";
        echo "Password in database: " . $user['password'] . "<br>";
        echo "Your password: " . $password . "<br><br>";
        
        // Show what password works
        echo "💡 Try these passwords:<br>";
        echo "- admin123<br>";
        echo "- password123<br>";
    }
} else {
    echo "❌ User not found!<br><br>";
    echo "Available users in database:<br>";
    $all_users = mysqli_query($conn, "SELECT username FROM users");
    while ($row = mysqli_fetch_assoc($all_users)) {
        echo "- " . $row['username'] . "<br>";
    }
}
?>