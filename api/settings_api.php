<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'update_profile':
        updateProfile($conn);
        break;
    case 'get_user':
        getUser($conn);
        break;
    case 'create_user':
        createUser($conn);
        break;
    case 'update_user':
        updateUser($conn);
        break;
    case 'delete_user':
        deleteUser($conn);
        break;
    case 'save_general':
        saveGeneralSettings($conn);
        break;
    case 'save_security':
        saveSecuritySettings($conn);
        break;
    case 'save_notifications':
        saveNotificationSettings($conn);
        break;
    case 'backup':
        createBackup($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function updateProfile($conn) {
    $user_id = $_SESSION['user_id'];
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    
    if (empty($full_name) || empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Name and username are required']);
        return;
    }
    
    // Check if username is taken by another user
    $check_query = "SELECT id FROM users WHERE username = '$username' AND id != $user_id";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already taken']);
        return;
    }
    
    // Update basic info
    $query = "UPDATE users SET full_name = '$full_name', username = '$username' WHERE id = $user_id";
    
    // If changing password
    if (!empty($new_password)) {
        // Verify current password
        $user_query = mysqli_query($conn, "SELECT password FROM users WHERE id = $user_id");
        $user = mysqli_fetch_assoc($user_query);
        
        if (!password_verify($current_password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
            return;
        }
        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET full_name = '$full_name', username = '$username', password = '$hashed_password' WHERE id = $user_id";
    }
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['full_name'] = $full_name;
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
}

function getUser($conn) {
    $id = (int)($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        return;
    }
    
    $query = "SELECT id, full_name, username, role FROM users WHERE id = $id";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
}

function createUser($conn) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = mysqli_real_escape_string($conn, $_POST['role'] ?? 'Staff');
    
    if (empty($full_name) || empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    // Check if username exists
    $check_query = "SELECT id FROM users WHERE username = '$username'";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already exists']);
        return;
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO users (full_name, username, password, role) VALUES ('$full_name', '$username', '$hashed_password', '$role')";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'User created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
}

function updateUser($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name'] ?? '');
    $username = mysqli_real_escape_string($conn, $_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = mysqli_real_escape_string($conn, $_POST['role'] ?? 'Staff');
    
    if ($id <= 0 || empty($full_name) || empty($username)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
        return;
    }
    
    // Check if username is taken by another user
    $check_query = "SELECT id FROM users WHERE username = '$username' AND id != $id";
    $check_result = mysqli_query($conn, $check_query);
    if (mysqli_num_rows($check_result) > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already taken']);
        return;
    }
    
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $query = "UPDATE users SET full_name = '$full_name', username = '$username', password = '$hashed_password', role = '$role' WHERE id = $id";
    } else {
        $query = "UPDATE users SET full_name = '$full_name', username = '$username', role = '$role' WHERE id = $id";
    }
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
}

function deleteUser($conn) {
    $id = (int)($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        return;
    }
    
    // Prevent deleting self
    if ($id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
        return;
    }
    
    $query = "DELETE FROM users WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
}

function saveGeneralSettings($conn) {
    // Create settings table if it doesn't exist
    createSettingsTable($conn);
    
    $settings = [
        'system_name' => mysqli_real_escape_string($conn, $_POST['system_name'] ?? 'ATI Tangalle - EduSphere'),
        'system_email' => mysqli_real_escape_string($conn, $_POST['system_email'] ?? 'admin@ati.edu.lk'),
        'default_language' => mysqli_real_escape_string($conn, $_POST['default_language'] ?? 'en'),
        'timezone' => mysqli_real_escape_string($conn, $_POST['timezone'] ?? 'Asia/Colombo')
    ];
    
    foreach ($settings as $key => $value) {
        $query = "INSERT INTO system_settings (setting_key, setting_value) 
                  VALUES ('$key', '$value') 
                  ON DUPLICATE KEY UPDATE setting_value = '$value'";
        mysqli_query($conn, $query);
    }
    
    echo json_encode(['success' => true, 'message' => 'Settings saved successfully']);
}

function saveSecuritySettings($conn) {
    createSettingsTable($conn);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $settings = [
        'two_factor_auth' => $data['two_factor_auth'] ?? 'off',
        'session_timeout' => (int)($data['session_timeout'] ?? 30),
        'max_attempts' => (int)($data['max_attempts'] ?? 5)
    ];
    
    foreach ($settings as $key => $value) {
        $query = "INSERT INTO system_settings (setting_key, setting_value) 
                  VALUES ('$key', '$value') 
                  ON DUPLICATE KEY UPDATE setting_value = '$value'";
        mysqli_query($conn, $query);
    }
    
    echo json_encode(['success' => true, 'message' => 'Security settings saved successfully']);
}

function saveNotificationSettings($conn) {
    createSettingsTable($conn);
    
    $data = json_decode(file_get_contents('php://input'), true);
    $settings = [
        'email_notifications' => $data['email_notifications'] ?? 'on',
        'payment_reminders' => $data['payment_reminders'] ?? 'on',
        'event_updates' => $data['event_updates'] ?? 'on',
        'attendance_alerts' => $data['attendance_alerts'] ?? 'off'
    ];
    
    foreach ($settings as $key => $value) {
        $query = "INSERT INTO system_settings (setting_key, setting_value) 
                  VALUES ('$key', '$value') 
                  ON DUPLICATE KEY UPDATE setting_value = '$value'";
        mysqli_query($conn, $query);
    }
    
    echo json_encode(['success' => true, 'message' => 'Notification settings saved successfully']);
}

function createBackup($conn) {
    $backup_file = '../backups/backup_' . date('Y-m-d_H-i-s') . '.sql';
    
    // Create backup directory if not exists
    if (!is_dir('../backups')) {
        mkdir('../backups', 0777, true);
    }
    
    // Get database name
    $db_config = require_once '../config/db.php';
    
    // Create backup using mysqldump
    $command = "mysqldump --host=" . DB_HOST . " --user=" . DB_USER . " --password=" . DB_PASS . " " . DB_NAME . " > " . $backup_file;
    
    exec($command, $output, $return_var);
    
    if ($return_var === 0 && file_exists($backup_file)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Backup created successfully',
            'download_url' => '/backups/' . basename($backup_file)
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error creating backup']);
    }
}

function createSettingsTable($conn) {
    $query = "CREATE TABLE IF NOT EXISTS system_settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    mysqli_query($conn, $query);
}
?>