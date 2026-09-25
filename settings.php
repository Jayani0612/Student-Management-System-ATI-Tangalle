<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$pageTitle = 'Settings';
$pageSubTitle = 'System configuration and preferences';

// Get user info for header
$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT full_name, role, username FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($user_query);
$full_name = $user['full_name'] ?? 'User';
$role = $user['role'] ?? 'Staff';
$username = $user['username'] ?? '';
$initials = implode('', array_map(function($word) { return strtoupper($word[0]); }, explode(' ', $full_name)));

// Get all users for user management
$users_query = mysqli_query($conn, "SELECT id, full_name, username, role, created_at FROM users ORDER BY id");
$users = [];
while ($u = mysqli_fetch_assoc($users_query)) {
    $users[] = $u;
}

// Get system settings (if table exists)
$settings = [];
$settings_query = mysqli_query($conn, "SELECT * FROM system_settings");
if ($settings_query) {
    while ($s = mysqli_fetch_assoc($settings_query)) {
        $settings[$s['setting_key']] = $s['setting_value'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ATI Tangalle · Settings</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
/* ===== RESET & VARIABLES ===== */
:root {
    --primary: #2c6aa8;
    --primary-light: #4a90d9;
    --primary-dark: #1a4f7a;
    --secondary: #6a9fd8;
    --bg: #e8f0fe;
    --card-bg: #ffffff;
    --text: #1a3a5c;
    --text-muted: #6a8aaa;
    --border: #d4e0ed;
    --shadow: 0 2px 12px rgba(44, 106, 168, 0.08);
    --shadow-hover: 0 8px 30px rgba(44, 106, 168, 0.15);
    --radius: 16px;
    --transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    
    --indigo: #4a90d9;
    --indigo-light: #e8f0fe;
    --green: #10b981;
    --green-light: #d1fae5;
    --blue: #3b82f6;
    --blue-light: #dbeafe;
    --orange: #f59e0b;
    --orange-light: #fef3c7;
    --red: #ef4444;
    --red-light: #fee2e2;
    --purple: #8b5cf6;
    --purple-light: #ede9fe;
    --pink: #ec4899;
    --pink-light: #fce7f3;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
    background: var(--bg);
    color: var(--text);
    line-height: 1.6;
}

/* ===== APP LAYOUT ===== */
.app {
    display: flex;
    min-height: 100vh;
}

/* ===== SIDEBAR ===== */
.sidebar {
    width: 270px;
    background: linear-gradient(180deg, #0a1a2e 0%, #142a4a 100%);
    color: #fff;
    padding: 25px 20px;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    overflow-y: auto;
    z-index: 1000;
    transition: var(--transition);
}

.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 0 10px 30px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}

.sidebar-brand .logo-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #4a90d9, #2c6aa8);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #fff;
}

.sidebar-brand .brand-text {
    font-size: 20px;
    font-weight: 700;
}

.sidebar-brand .brand-text span {
    color: #6a9fd8;
}

.sidebar-brand .brand-sub {
    font-size: 10px;
    font-weight: 400;
    color: rgba(255,255,255,0.4);
    display: block;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.sidebar-menu {
    list-style: none;
    margin-top: 25px;
}

.sidebar-menu li {
    margin-bottom: 4px;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 16px;
    color: rgba(255,255,255,0.6);
    text-decoration: none;
    border-radius: 12px;
    transition: var(--transition);
    font-weight: 500;
    font-size: 14px;
}

.sidebar-menu a i {
    width: 20px;
    font-size: 16px;
    color: rgba(255,255,255,0.4);
    transition: var(--transition);
}

.sidebar-menu a:hover {
    background: rgba(255,255,255,0.08);
    color: #fff;
}

.sidebar-menu a:hover i {
    color: #6a9fd8;
}

.sidebar-menu a.active {
    background: linear-gradient(135deg, rgba(74, 144, 217, 0.25), rgba(44, 106, 168, 0.15));
    color: #fff;
    box-shadow: 0 0 30px rgba(74, 144, 217, 0.05);
}

.sidebar-menu a.active i {
    color: #6a9fd8;
}

.sidebar-menu .badge {
    margin-left: auto;
    background: #4a90d9;
    color: #fff;
    font-size: 10px;
    padding: 2px 10px;
    border-radius: 20px;
    font-weight: 600;
}

.sidebar-footer {
    position: absolute;
    bottom: 20px;
    left: 20px;
    right: 20px;
    padding-top: 20px;
    border-top: 1px solid rgba(255,255,255,0.08);
}

.sidebar-footer .logout-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    color: rgba(255,255,255,0.5);
    text-decoration: none;
    font-size: 14px;
    padding: 12px 16px;
    border-radius: 12px;
    transition: var(--transition);
    background: rgba(239, 68, 68, 0.08);
    border: 1px solid rgba(239, 68, 68, 0.12);
    font-weight: 500;
    width: 100%;
    cursor: pointer;
}

.sidebar-footer .logout-btn i {
    color: rgba(239, 68, 68, 0.6);
    transition: var(--transition);
}

.sidebar-footer .logout-btn:hover {
    background: rgba(239, 68, 68, 0.18);
    color: #fff;
    border-color: rgba(239, 68, 68, 0.3);
}

.sidebar-footer .logout-btn:hover i {
    color: #ef4444;
}

/* ===== MAIN CONTENT ===== */
.main {
    flex: 1;
    margin-left: 270px;
    min-height: 100vh;
}

/* ===== HEADER ===== */
.header {
    background: var(--card-bg);
    padding: 16px 35px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0;
    z-index: 100;
    backdrop-filter: blur(20px);
    background: rgba(255,255,255,0.92);
}

.header-left {
    display: flex;
    align-items: center;
    gap: 20px;
}

.hamburger {
    display: none;
    background: none;
    border: none;
    font-size: 24px;
    color: var(--text);
    cursor: pointer;
    padding: 5px;
}

.header-title h1 {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary);
}

.header-title p {
    font-size: 13px;
    color: var(--text-muted);
}

.header-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.header-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: var(--bg);
    border-radius: 50%;
    cursor: pointer;
    font-size: 18px;
    color: var(--text-muted);
    transition: var(--transition);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.header-btn:hover {
    background: var(--indigo-light);
    color: var(--indigo);
    transform: translateY(-2px);
}

.header-btn .dot {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 8px;
    height: 8px;
    background: var(--red);
    border-radius: 50%;
    border: 2px solid #fff;
}

.header-user {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 4px 16px 4px 4px;
    border-radius: 50px;
    background: var(--bg);
    cursor: pointer;
    transition: var(--transition);
}

.header-user:hover {
    background: var(--indigo-light);
}

.header-user .avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-light), var(--primary));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 600;
    font-size: 14px;
}

.header-user .name {
    font-weight: 600;
    font-size: 14px;
    color: var(--text);
}

.header-user .role {
    font-size: 12px;
    color: var(--text-muted);
}

/* ===== CONTENT ===== */
.content {
    padding: 30px 35px;
}

/* ===== CARD ===== */
.card {
    background: var(--card-bg);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
    overflow: hidden;
}

.card:hover {
    box-shadow: var(--shadow-hover);
}

.panel {
    padding: 0;
}

/* ===== SETTINGS TABS ===== */
.settings-tabs {
    display: flex;
    border-bottom: 1px solid var(--border);
    background: var(--bg);
    padding: 0 25px;
    gap: 4px;
    flex-wrap: wrap;
}

.settings-tabs .tab {
    padding: 14px 20px;
    font-weight: 600;
    font-size: 14px;
    color: var(--text-muted);
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 8px;
}

.settings-tabs .tab:hover {
    color: var(--text);
    background: rgba(74, 144, 217, 0.05);
}

.settings-tabs .tab.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
}

.settings-tabs .tab i {
    font-size: 16px;
}

.tab-content {
    display: none;
    padding: 25px;
}

.tab-content.active {
    display: block;
}

/* ===== TABLE TOOLBAR ===== */
.table-toolbar {
    padding: 20px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border);
    flex-wrap: wrap;
    gap: 15px;
}

.panel-title {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary);
}

.panel-sub {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 2px;
}

.table-search {
    display: flex;
    align-items: center;
    background: var(--bg);
    border-radius: 12px;
    padding: 0 16px;
    border: 2px solid transparent;
    transition: var(--transition);
}

.table-search:focus-within {
    border-color: var(--primary-light);
    background: #fff;
    box-shadow: 0 0 0 4px rgba(74, 144, 217, 0.08);
}

.table-search i {
    color: var(--text-muted);
    font-size: 14px;
    margin-right: 10px;
}

.table-search input {
    border: none;
    background: transparent;
    padding: 10px 0;
    font-size: 14px;
    color: var(--text);
    width: 220px;
    outline: none;
    font-family: inherit;
}

.table-search input::placeholder {
    color: var(--text-muted);
}

/* ===== BUTTONS ===== */
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    font-family: inherit;
}

.btn-primary {
    background: linear-gradient(135deg, #4a90d9, #2c6aa8);
    color: #fff;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(74, 144, 217, 0.35);
}

.btn-outline {
    background: transparent;
    color: var(--text-muted);
    border: 2px solid var(--border);
}

.btn-outline:hover {
    background: var(--bg);
    border-color: var(--primary-light);
    color: var(--primary);
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 8px;
}

.btn-danger {
    background: var(--red);
    color: #fff;
}

.btn-danger:hover {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(239, 68, 68, 0.3);
}

.btn-warning {
    background: var(--orange);
    color: #fff;
}

.btn-warning:hover {
    background: #d97706;
    transform: translateY(-2px);
}

.btn-success {
    background: var(--green);
    color: #fff;
}

.btn-success:hover {
    background: #059669;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
}

/* ===== DATA TABLE ===== */
.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.data-table thead {
    background: var(--bg);
}

.data-table thead th {
    padding: 14px 20px;
    text-align: left;
    font-weight: 600;
    color: var(--text-muted);
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--border);
}

.data-table tbody tr {
    border-bottom: 1px solid var(--border);
    transition: var(--transition);
}

.data-table tbody tr:hover {
    background: var(--bg);
}

.data-table tbody td {
    padding: 14px 20px;
    color: var(--text);
}

.data-table .role-badge {
    display: inline-block;
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.data-table .role-badge.Admin {
    background: var(--purple-light);
    color: var(--purple);
}

.data-table .role-badge.Super {
    background: var(--red-light);
    color: var(--red);
}

.data-table .role-badge.Staff {
    background: var(--blue-light);
    color: var(--blue);
}

.data-table .role-badge.Teacher {
    background: var(--green-light);
    color: var(--green);
}

.data-table .actions {
    display: flex;
    gap: 6px;
}

.data-table .actions .btn {
    padding: 5px 10px;
    font-size: 12px;
    border-radius: 6px;
}

/* ===== FORM ===== */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group label {
    font-weight: 600;
    font-size: 13px;
    color: var(--text);
}

.form-control {
    padding: 10px 14px;
    border: 2px solid var(--border);
    border-radius: 10px;
    font-size: 14px;
    color: var(--text);
    background: var(--bg);
    transition: var(--transition);
    font-family: inherit;
}

.form-control:focus {
    border-color: var(--primary-light);
    background: #fff;
    box-shadow: 0 0 0 4px rgba(74, 144, 217, 0.08);
    outline: none;
}

.form-control::placeholder {
    color: var(--text-muted);
}

.form-control:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.setting-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid var(--border);
}

.setting-item:last-child {
    border-bottom: none;
}

.setting-item .setting-info {
    flex: 1;
}

.setting-item .setting-info h4 {
    font-size: 15px;
    font-weight: 600;
    color: var(--text);
}

.setting-item .setting-info p {
    font-size: 13px;
    color: var(--text-muted);
}

/* Toggle Switch */
.switch {
    position: relative;
    width: 48px;
    height: 26px;
    flex-shrink: 0;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.switch .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: var(--border);
    transition: var(--transition);
    border-radius: 26px;
}

.switch .slider:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background: #fff;
    transition: var(--transition);
    border-radius: 50%;
}

.switch input:checked + .slider {
    background: var(--primary);
}

.switch input:checked + .slider:before {
    transform: translateX(22px);
}

/* ===== PAGINATION ===== */
.pagination {
    padding: 16px 25px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.pagination #resultCount {
    color: var(--text-muted);
    font-size: 14px;
}

/* ===== MODAL ===== */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);
    backdrop-filter: blur(4px);
    z-index: 2000;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-overlay.open {
    display: flex;
}

.modal {
    background: var(--card-bg);
    border-radius: var(--radius);
    max-width: 500px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    animation: modalIn 0.3s ease;
}

@keyframes modalIn {
    from {
        opacity: 0;
        transform: scale(0.95) translateY(20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.modal-head {
    padding: 20px 25px;
    border-bottom: 1px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-head h3 {
    font-size: 18px;
    font-weight: 700;
    color: var(--primary);
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    color: var(--text-muted);
    cursor: pointer;
    transition: var(--transition);
    line-height: 1;
}

.modal-close:hover {
    color: var(--red);
    transform: rotate(90deg);
}

.modal-body {
    padding: 25px;
}

.modal-foot {
    padding: 16px 25px;
    border-top: 1px solid var(--border);
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

/* ===== TOAST ===== */
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 16px 24px;
    border-radius: 12px;
    color: #fff;
    font-weight: 500;
    box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    z-index: 9999;
    transform: translateX(120%);
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    max-width: 400px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-family: 'Segoe UI', sans-serif;
    font-size: 14px;
}

.toast.show {
    transform: translateX(0);
}

.toast-success {
    background: linear-gradient(135deg, #2c6aa8, #4a90d9);
}

.toast-error {
    background: linear-gradient(135deg, #dc2626, #ef4444);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
        width: 280px;
    }
    
    .sidebar.open {
        transform: translateX(0);
    }
    
    .main {
        margin-left: 0;
    }
    
    .hamburger {
        display: block;
    }
}

@media (max-width: 768px) {
    .header {
        padding: 12px 18px;
    }
    
    .content {
        padding: 18px;
    }
    
    .table-toolbar {
        flex-direction: column;
        align-items: stretch;
        padding: 16px 18px;
    }
    
    .table-search {
        width: 100%;
    }
    
    .table-search input {
        width: 100%;
    }
    
    .data-table {
        font-size: 13px;
    }
    
    .data-table thead th,
    .data-table tbody td {
        padding: 10px 12px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .settings-tabs {
        padding: 0 15px;
    }
    
    .settings-tabs .tab {
        padding: 12px 14px;
        font-size: 13px;
    }
    
    .header-user .name,
    .header-user .role {
        display: none;
    }
    
    .header-user {
        padding: 4px;
        background: transparent;
    }
}

@media (max-width: 480px) {
    .data-table {
        font-size: 12px;
    }
    
    .data-table thead th,
    .data-table tbody td {
        padding: 8px 10px;
    }
    
    .btn {
        padding: 8px 14px;
        font-size: 12px;
    }
    
    .settings-tabs .tab {
        font-size: 12px;
        padding: 10px 12px;
    }
    
    .settings-tabs .tab span {
        display: none;
    }
}

/* ===== SCROLLBAR ===== */
::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

::-webkit-scrollbar-track {
    background: transparent;
}

::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: var(--text-muted);
}
</style>
</head>
<body>
<div class="app">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <?php include 'includes/header.php'; ?>

        <div class="content">
            <div class="card panel">
                <div class="table-toolbar">
                    <div>
                        <h3 class="panel-title"><i class="fas fa-cog" style="color:var(--primary-light);margin-right:8px;"></i>System Settings</h3>
                        <div class="panel-sub">Configure system preferences and manage users</div>
                    </div>
                </div>

                <!-- Settings Tabs -->
                <div class="settings-tabs">
                    <div class="tab active" data-tab="profile" onclick="switchTab('profile')">
                        <i class="fas fa-user"></i> <span>Profile</span>
                    </div>
                    <div class="tab" data-tab="users" onclick="switchTab('users')">
                        <i class="fas fa-users"></i> <span>Users</span>
                    </div>
                    <div class="tab" data-tab="general" onclick="switchTab('general')">
                        <i class="fas fa-globe"></i> <span>General</span>
                    </div>
                    <div class="tab" data-tab="security" onclick="switchTab('security')">
                        <i class="fas fa-shield-alt"></i> <span>Security</span>
                    </div>
                    <div class="tab" data-tab="notifications" onclick="switchTab('notifications')">
                        <i class="fas fa-bell"></i> <span>Notifications</span>
                    </div>
                    <div class="tab" data-tab="backup" onclick="switchTab('backup')">
                        <i class="fas fa-database"></i> <span>Backup</span>
                    </div>
                </div>

                <!-- Profile Tab -->
                <div class="tab-content active" id="tab-profile">
                    <form id="profileForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Full Name *</label>
                                <input type="text" id="profile_full_name" name="full_name" class="form-control" value="<?= htmlspecialchars($full_name) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Username *</label>
                                <input type="text" id="profile_username" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($role) ?>" disabled>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" placeholder="Enter current password">
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Enter new password">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm new password">
                        </div>
                        <div style="margin-top:16px;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
                        </div>
                    </form>
                </div>

                <!-- Users Tab -->
                <div class="tab-content" id="tab-users">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:12px;">
                        <div class="table-search">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="userSearch" placeholder="Search users...">
                        </div>
                        <button class="btn btn-primary" onclick="openAddUserModal()"><i class="fa-solid fa-plus"></i> Add User</button>
                    </div>
                    <div style="overflow-x:auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Username</th>
                                    <th>Role</th>
                                    <th>Created</th>
                                    <th style="text-align:center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="usersBody">
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($u['full_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($u['username']) ?></td>
                                        <td><span class="role-badge <?= htmlspecialchars($u['role']) ?>"><?= htmlspecialchars($u['role']) ?></span></td>
                                        <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                        <td>
                                            <div class="actions" style="justify-content:center;">
                                                <button class="btn btn-warning btn-sm" onclick="editUser(<?= $u['id'] ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                                    <button class="btn btn-danger btn-sm" onclick="deleteUser(<?= $u['id'] ?>)">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination">
                        <span id="userCount"><?= count($users) ?> users</span>
                    </div>
                </div>

                <!-- General Tab -->
                <div class="tab-content" id="tab-general">
                    <form id="generalForm">
                        <div class="form-group">
                            <label>System Name</label>
                            <input type="text" id="system_name" name="system_name" class="form-control" value="<?= htmlspecialchars($settings['system_name'] ?? 'ATI Tangalle - EduSphere') ?>">
                        </div>
                        <div class="form-group">
                            <label>System Email</label>
                            <input type="email" id="system_email" name="system_email" class="form-control" value="<?= htmlspecialchars($settings['system_email'] ?? 'admin@ati.edu.lk') ?>">
                        </div>
                        <div class="form-group">
                            <label>Default Language</label>
                            <select id="default_language" name="default_language" class="form-control">
                                <option value="en" <?= ($settings['default_language'] ?? 'en') == 'en' ? 'selected' : '' ?>>English</option>
                                <option value="si" <?= ($settings['default_language'] ?? 'en') == 'si' ? 'selected' : '' ?>>Sinhala</option>
                                <option value="ta" <?= ($settings['default_language'] ?? 'en') == 'ta' ? 'selected' : '' ?>>Tamil</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Time Zone</label>
                            <select id="timezone" name="timezone" class="form-control">
                                <option value="Asia/Colombo" selected>Asia/Colombo (UTC +5:30)</option>
                                <option value="UTC">UTC</option>
                                <option value="America/New_York">America/New York</option>
                                <option value="Europe/London">Europe/London</option>
                                <option value="Asia/Dubai">Asia/Dubai</option>
                                <option value="Asia/Singapore">Asia/Singapore</option>
                            </select>
                        </div>
                        <div style="margin-top:16px;">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Settings</button>
                        </div>
                    </form>
                </div>

                <!-- Security Tab -->
                <div class="tab-content" id="tab-security">
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Two-Factor Authentication</h4>
                            <p>Require 2FA for all admin users</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="two_factor_auth" <?= ($settings['two_factor_auth'] ?? 'off') == 'on' ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Session Timeout</h4>
                            <p>Auto-logout after inactivity (minutes)</p>
                        </div>
                        <input type="number" id="session_timeout" class="form-control" style="width:100px;" value="<?= $settings['session_timeout'] ?? 30 ?>">
                    </div>
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Login Attempts</h4>
                            <p>Max failed login attempts before lockout</p>
                        </div>
                        <input type="number" id="max_attempts" class="form-control" style="width:100px;" value="<?= $settings['max_attempts'] ?? 5 ?>">
                    </div>
                    <div style="margin-top:16px;">
                        <button class="btn btn-primary" onclick="saveSecuritySettings()"><i class="fas fa-save"></i> Save Security Settings</button>
                    </div>
                </div>

                <!-- Notifications Tab -->
                <div class="tab-content" id="tab-notifications">
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Email Notifications</h4>
                            <p>Receive system notifications via email</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="email_notifications" <?= ($settings['email_notifications'] ?? 'on') == 'on' ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Payment Reminders</h4>
                            <p>Send automatic payment reminders</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="payment_reminders" <?= ($settings['payment_reminders'] ?? 'on') == 'on' ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Event Updates</h4>
                            <p>Notify about upcoming events</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="event_updates" <?= ($settings['event_updates'] ?? 'on') == 'on' ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Attendance Alerts</h4>
                            <p>Send attendance notifications</p>
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="attendance_alerts" <?= ($settings['attendance_alerts'] ?? 'off') == 'on' ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                    <div style="margin-top:16px;">
                        <button class="btn btn-primary" onclick="saveNotificationSettings()"><i class="fas fa-save"></i> Save Notification Settings</button>
                    </div>
                </div>

                <!-- Backup Tab -->
                <div class="tab-content" id="tab-backup">
                    <div style="text-align:center;padding:20px 0;">
                        <i class="fas fa-database" style="font-size:48px;color:var(--primary-light);margin-bottom:16px;display:block;"></i>
                        <h3 style="margin-bottom:8px;">Database Backup</h3>
                        <p style="color:var(--text-muted);margin-bottom:20px;">Create a backup of your entire system database</p>
                        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
                            <button class="btn btn-primary" onclick="createBackup()"><i class="fas fa-download"></i> Download Backup</button>
                            <button class="btn btn-outline" onclick="scheduleBackup()"><i class="fas fa-clock"></i> Schedule Backup</button>
                        </div>
                    </div>
                    <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--border);">
                        <h4 style="margin-bottom:12px;">Recent Backups</h4>
                        <div id="backupList" style="color:var(--text-muted);">
                            <p><i class="fas fa-file-archive"></i> backup_2026-07-18_14-30-25.sql (2.4 MB)</p>
                            <p><i class="fas fa-file-archive"></i> backup_2026-07-17_10-15-42.sql (2.3 MB)</p>
                            <p><i class="fas fa-file-archive"></i> backup_2026-07-16_08-45-10.sql (2.1 MB)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div class="modal-overlay" id="userModal">
    <div class="modal">
        <div class="modal-head">
            <h3 id="userModalTitle"><i class="fas fa-user-plus" style="color:var(--primary-light);margin-right:8px;"></i>Add User</h3>
            <button class="modal-close" onclick="closeUserModal()">&times;</button>
        </div>
        <form id="userForm">
            <div class="modal-body">
                <input type="hidden" id="userId" name="id">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" id="user_full_name" name="full_name" class="form-control" placeholder="John Doe" required>
                </div>
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" id="user_username" name="username" class="form-control" placeholder="johndoe" required>
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" id="user_password" name="password" class="form-control" placeholder="Enter password">
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <select id="user_role" name="role" class="form-control">
                        <option value="Admin">Admin</option>
                        <option value="Staff">Staff</option>
                        <option value="Teacher">Teacher</option>
                        <option value="Super">Super Administrator</option>
                    </select>
                </div>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn btn-outline" onclick="closeUserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save User</button>
            </div>
        </form>
    </div>
</div>

<script>
// ===== SIDEBAR TOGGLE =====
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    const hamburger = document.querySelector('.hamburger');
    if (window.innerWidth <= 992) {
        if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    }
});

function confirmLogout() {
    return confirm('Are you sure you want to logout?');
}

// ===== TAB SWITCHING =====
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.settings-tabs .tab').forEach(el => el.classList.remove('active'));
    
    document.getElementById('tab-' + tabId).classList.add('active');
    document.querySelector(`.settings-tabs .tab[data-tab="${tabId}"]`).classList.add('active');
}

// ===== PROFILE FORM =====
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword && newPassword !== confirmPassword) {
        showToast('Passwords do not match!', 'error');
        return;
    }
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    submitBtn.disabled = true;
    
    fetch('api/settings_api.php?action=update_profile', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        
        if (data.success) {
            showToast(data.message || 'Profile updated successfully!', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast(data.message || 'Error updating profile.', 'error');
        }
    })
    .catch(err => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        showToast('An error occurred. Please try again.', 'error');
        console.error('Error:', err);
    });
});

// ===== USER MANAGEMENT =====
function openAddUserModal() {
    document.getElementById('userModalTitle').innerHTML = '<i class="fas fa-user-plus" style="color:var(--primary-light);margin-right:8px;"></i>Add User';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('user_password').required = true;
    document.getElementById('userModal').classList.add('open');
}

function editUser(id) {
    document.getElementById('userModalTitle').innerHTML = '<i class="fas fa-edit" style="color:var(--primary-light);margin-right:8px;"></i>Edit User';
    document.getElementById('userId').value = id;
    document.getElementById('user_password').required = false;
    document.getElementById('user_password').placeholder = 'Leave blank to keep current';
    
    fetch(`api/settings_api.php?action=get_user&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('user_full_name').value = data.data.full_name;
                document.getElementById('user_username').value = data.data.username;
                document.getElementById('user_role').value = data.data.role;
                document.getElementById('userModal').classList.add('open');
            } else {
                showToast(data.message || 'Error loading user', 'error');
            }
        })
        .catch(err => {
            showToast('Error loading user', 'error');
            console.error('Error:', err);
        });
}

function closeUserModal() {
    document.getElementById('userModal').classList.remove('open');
}

document.getElementById('userModal').addEventListener('click', function(e) {
    if (e.target === this) closeUserModal();
});

document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = document.getElementById('userId').value;
    const action = id ? 'update_user' : 'create_user';
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    submitBtn.disabled = true;
    
    fetch(`api/settings_api.php?action=${action}`, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        
        if (data.success) {
            closeUserModal();
            showToast(data.message || 'User saved successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Error saving user.', 'error');
        }
    })
    .catch(err => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        showToast('An error occurred. Please try again.', 'error');
        console.error('Error:', err);
    });
});

function deleteUser(id) {
    if (!confirm('Are you sure you want to delete this user?')) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    fetch('api/settings_api.php?action=delete_user', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('User deleted successfully!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(data.message || 'Error deleting user.', 'error');
        }
    })
    .catch(err => {
        showToast('Error deleting user.', 'error');
        console.error('Error:', err);
    });
}

// ===== GENERAL SETTINGS =====
document.getElementById('generalForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    submitBtn.disabled = true;
    
    fetch('api/settings_api.php?action=save_general', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        
        if (data.success) {
            showToast(data.message || 'Settings saved successfully!', 'success');
        } else {
            showToast(data.message || 'Error saving settings.', 'error');
        }
    })
    .catch(err => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        showToast('An error occurred. Please try again.', 'error');
        console.error('Error:', err);
    });
});

// ===== SECURITY SETTINGS =====
function saveSecuritySettings() {
    const data = {
        two_factor_auth: document.getElementById('two_factor_auth').checked ? 'on' : 'off',
        session_timeout: document.getElementById('session_timeout').value,
        max_attempts: document.getElementById('max_attempts').value
    };
    
    fetch('api/settings_api.php?action=save_security', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Security settings saved successfully!', 'success');
        } else {
            showToast(data.message || 'Error saving settings.', 'error');
        }
    })
    .catch(err => {
        showToast('Error saving settings.', 'error');
        console.error('Error:', err);
    });
}

// ===== NOTIFICATION SETTINGS =====
function saveNotificationSettings() {
    const data = {
        email_notifications: document.getElementById('email_notifications').checked ? 'on' : 'off',
        payment_reminders: document.getElementById('payment_reminders').checked ? 'on' : 'off',
        event_updates: document.getElementById('event_updates').checked ? 'on' : 'off',
        attendance_alerts: document.getElementById('attendance_alerts').checked ? 'on' : 'off'
    };
    
    fetch('api/settings_api.php?action=save_notifications', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('Notification settings saved successfully!', 'success');
        } else {
            showToast(data.message || 'Error saving settings.', 'error');
        }
    })
    .catch(err => {
        showToast('Error saving settings.', 'error');
        console.error('Error:', err);
    });
}

// ===== BACKUP FUNCTIONS =====
function createBackup() {
    showToast('Creating backup... Please wait.', 'info');
    
    fetch('api/settings_api.php?action=backup')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.download_url;
                showToast('Backup created successfully!', 'success');
            } else {
                showToast(data.message || 'Error creating backup.', 'error');
            }
        })
        .catch(err => {
            showToast('Error creating backup.', 'error');
            console.error('Error:', err);
        });
}

function scheduleBackup() {
    showToast('Backup scheduled for 2:00 AM daily', 'success');
}

// ===== USER SEARCH =====
document.getElementById('userSearch')?.addEventListener('input', function() {
    const search = this.value.toLowerCase();
    const rows = document.querySelectorAll('#usersBody tr');
    let visible = 0;
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(search)) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });
    
    document.getElementById('userCount').textContent = visible + ' users';
});

// ===== TOAST NOTIFICATION =====
function showToast(message, type = 'success') {
    const colors = {
        success: 'linear-gradient(135deg, #2c6aa8, #4a90d9)',
        error: 'linear-gradient(135deg, #dc2626, #ef4444)',
        info: 'linear-gradient(135deg, #6a9fd8, #4a90d9)'
    };
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        info: 'fa-info-circle'
    };
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.style.background = colors[type] || colors.success;
    toast.innerHTML = `<i class="fas ${icons[type] || icons.success}"></i> ${message}`;
    document.body.appendChild(toast);
    
    setTimeout(() => { toast.classList.add('show'); }, 100);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}
</script>
</body>
</html>