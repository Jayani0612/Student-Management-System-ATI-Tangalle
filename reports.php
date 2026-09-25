<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$pageTitle = 'Reports';
$pageSubTitle = 'Generate and view system reports';

// Get user info for header
$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT full_name, role FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($user_query);
$full_name = $user['full_name'] ?? 'User';
$role = $user['role'] ?? 'Staff';
$initials = implode('', array_map(function($word) { return strtoupper($word[0]); }, explode(' ', $full_name)));

// Get statistics
$total_students = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM students");
if ($result) {
    $total_students = mysqli_fetch_assoc($result)['count'] ?? 0;
}

$total_teachers = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM teachers");
if ($result) {
    $total_teachers = mysqli_fetch_assoc($result)['count'] ?? 0;
}

$total_courses = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM courses");
if ($result) {
    $total_courses = mysqli_fetch_assoc($result)['count'] ?? 0;
}

$total_books = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM books");
if ($result) {
    $total_books = mysqli_fetch_assoc($result)['count'] ?? 0;
}

$total_finance = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM finance");
if ($result) {
    $total_finance = mysqli_fetch_assoc($result)['count'] ?? 0;
}

$total_events = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) as count FROM events");
if ($result) {
    $total_events = mysqli_fetch_assoc($result)['count'] ?? 0;
}
// Total Payments
$total_payments_query = mysqli_query($conn, "SELECT SUM(amount) AS total FROM finance");
$total_payments_row = mysqli_fetch_assoc($total_payments_query);
$total_payments = $total_payments_row['total'] ?? 0;
// Payment statistics
$finance_data = [
    'total_paid' => 0,
    'total_pending' => 0,
    'total_overdue' => 0,
    'paid_count' => 0,
    'pending_count' => 0,
    'overdue_count' => 0
];
$finance_stats = mysqli_query($conn, "SELECT 
    SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END) as total_paid,
    SUM(CASE WHEN status = 'Pending' THEN amount ELSE 0 END) as total_pending,
    SUM(CASE WHEN status = 'Overdue' THEN amount ELSE 0 END) as total_overdue,
    COUNT(CASE WHEN status = 'Paid' THEN 1 END) as paid_count,
    COUNT(CASE WHEN status = 'Pending' THEN 1 END) as pending_count,
    COUNT(CASE WHEN status = 'Overdue' THEN 1 END) as overdue_count
FROM finance");
if ($finance_stats) {
    $finance_data = mysqli_fetch_assoc($finance_stats);
}



// Book stats
$book_data = [
    'total_copies' => 0,
    'total_available' => 0
];
$book_stats = mysqli_query($conn, "SELECT 
    SUM(total_copies) as total_copies,
    SUM(available_copies) as total_available
FROM books");
if ($book_stats) {
    $book_data = mysqli_fetch_assoc($book_stats);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ATI Tangalle · Reports</title>
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
    padding: 25px 20px 0;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    overflow: hidden;
    z-index: 1000;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    display: flex;
    flex-direction: column;
}

/* Sidebar Brand */
.sidebar-brand {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 0 10px 25px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    flex-shrink: 0;
}

.sidebar-brand .logo-icon {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #4a90d9, #2c6aa8);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #fff;
    box-shadow: 0 4px 15px rgba(74, 144, 217, 0.3);
    flex-shrink: 0;
}

.sidebar-brand .brand-text {
    font-size: 20px;
    font-weight: 700;
    line-height: 1.2;
    color: #fff;
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
    margin-top: 2px;
}

/* Sidebar Menu Wrapper (Scrollable) */
.sidebar-menu-wrapper {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 10px 0 10px;
    margin: 0 -8px;
    padding-right: 8px;
}

.sidebar-menu-wrapper::-webkit-scrollbar {
    width: 4px;
}
.sidebar-menu-wrapper::-webkit-scrollbar-track {
    background: transparent;
}
.sidebar-menu-wrapper::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.15);
    border-radius: 10px;
}
.sidebar-menu-wrapper::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.25);
}
.sidebar-menu-wrapper {
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.15) transparent;
}

/* Sidebar Menu */
.sidebar-menu {
    list-style: none;
    padding: 0;
    margin: 0;
}

.sidebar-menu li {
    margin-bottom: 2px;
}

.sidebar-menu .menu-section {
    padding: 16px 16px 6px;
    cursor: default;
}

.sidebar-menu .section-label {
    font-size: 10px;
    font-weight: 600;
    color: rgba(255,255,255,0.25);
    text-transform: uppercase;
    letter-spacing: 1.5px;
    display: block;
}

.sidebar-menu a {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 10px 16px;
    color: rgba(255,255,255,0.55);
    text-decoration: none;
    border-radius: 10px;
    transition: all 0.25s ease;
    font-weight: 500;
    font-size: 14px;
    position: relative;
}

.sidebar-menu a i {
    width: 20px;
    font-size: 16px;
    color: rgba(255,255,255,0.35);
    transition: all 0.25s ease;
    text-align: center;
    flex-shrink: 0;
}

.sidebar-menu a span {
    flex: 1;
}

.sidebar-menu a .badge {
    margin-left: auto;
    background: linear-gradient(135deg, #4a90d9, #2c6aa8);
    color: #fff;
    font-size: 9px;
    padding: 2px 10px;
    border-radius: 20px;
    font-weight: 600;
    letter-spacing: 0.5px;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(74, 144, 217, 0.3);
}

.sidebar-menu a:hover {
    background: rgba(255,255,255,0.07);
    color: #fff;
    transform: translateX(4px);
}

.sidebar-menu a:hover i {
    color: #6a9fd8;
}

.sidebar-menu a.active {
    background: linear-gradient(135deg, rgba(74, 144, 217, 0.2), rgba(44, 106, 168, 0.1));
    color: #fff;
    box-shadow: inset 3px 0 0 #4a90d9;
}

.sidebar-menu a.active i {
    color: #6a9fd8;
}

/* Sidebar Footer - Only Logout */
.sidebar-footer {
    flex-shrink: 0;
    padding: 16px 0 20px;
    border-top: 1px solid rgba(255,255,255,0.06);
    margin-top: auto;
}

.sidebar-footer .logout-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    color: rgba(255,255,255,0.5);
    text-decoration: none;
    font-size: 14px;
    padding: 10px 16px;
    border-radius: 10px;
    transition: all 0.25s ease;
    background: rgba(239, 68, 68, 0.06);
    border: 1px solid rgba(239, 68, 68, 0.08);
    font-weight: 500;
    width: 100%;
    cursor: pointer;
}

.sidebar-footer .logout-btn i {
    color: rgba(239, 68, 68, 0.5);
    font-size: 16px;
    transition: all 0.25s ease;
}

.sidebar-footer .logout-btn span {
    flex: 1;
}

.sidebar-footer .logout-btn:hover {
    background: rgba(239, 68, 68, 0.15);
    color: #fff;
    border-color: rgba(239, 68, 68, 0.25);
    transform: translateX(2px);
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

/* ===== STATS GRID ===== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 18px;
    margin-bottom: 30px;
}

.stat-card {
    background: var(--card-bg);
    border-radius: var(--radius);
    padding: 20px 24px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-hover);
}

.stat-card .stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin-bottom: 10px;
}

.stat-card .stat-icon.blue { background: var(--blue-light); color: var(--blue); }
.stat-card .stat-icon.green { background: var(--green-light); color: var(--green); }
.stat-card .stat-icon.orange { background: var(--orange-light); color: var(--orange); }
.stat-card .stat-icon.purple { background: var(--purple-light); color: var(--purple); }
.stat-card .stat-icon.red { background: var(--red-light); color: var(--red); }
.stat-card .stat-icon.pink { background: var(--pink-light); color: var(--pink); }

.stat-card .stat-number {
    font-size: 28px;
    font-weight: 700;
    color: var(--text);
    line-height: 1.2;
}

.stat-card .stat-label {
    font-size: 14px;
    color: var(--text-muted);
    margin-top: 2px;
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

.btn-success {
    background: var(--green);
    color: #fff;
}

.btn-success:hover {
    background: #059669;
}

.btn-danger {
    background: var(--red);
    color: #fff;
}

.btn-danger:hover {
    background: #dc2626;
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

/* ===== REPORT GRID ===== */
.report-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-top: 20px;
}

.report-card {
    background: var(--bg);
    border-radius: var(--radius);
    padding: 20px;
    border: 2px solid transparent;
    transition: var(--transition);
    cursor: pointer;
    text-align: center;
}

.report-card:hover {
    border-color: var(--primary-light);
    background: var(--card-bg);
    transform: translateY(-4px);
    box-shadow: var(--shadow-hover);
}

.report-card .report-icon {
    font-size: 32px;
    margin-bottom: 8px;
    display: block;
}

.report-card .report-name {
    font-weight: 600;
    font-size: 14px;
    color: var(--text);
}

.report-card .report-desc {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 2px;
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
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .stat-card .stat-number {
        font-size: 22px;
    }
    .report-grid {
        grid-template-columns: 1fr 1fr;
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
    .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 10px;
    }
    .stat-card {
        padding: 14px 16px;
    }
    .stat-card .stat-number {
        font-size: 18px;
    }
    .report-grid {
        grid-template-columns: 1fr;
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
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
            <div>
                <div class="brand-text">ATI <span>Tangalle</span></div>
                <span class="brand-sub">Advanced Technological Institute</span>
            </div>
        </div>
        
        <div class="sidebar-menu-wrapper">
            <ul class="sidebar-menu">
                <li>
                    <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                        <i class="fas fa-th-large"></i> <span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-section"><span class="section-label">Academic</span></li>
                <li><a href="students.php" class="<?= basename($_SERVER['PHP_SELF']) == 'students.php' ? 'active' : '' ?>"><i class="fas fa-users"></i> <span>Students</span></a></li>
                <li><a href="teachers.php" class="<?= basename($_SERVER['PHP_SELF']) == 'teachers.php' ? 'active' : '' ?>"><i class="fas fa-chalkboard-teacher"></i> <span>Teachers</span></a></li>
                <li><a href="courses.php" class="<?= basename($_SERVER['PHP_SELF']) == 'courses.php' ? 'active' : '' ?>"><i class="fas fa-book"></i> <span>Courses</span></a></li>
                <li><a href="attendance.php" class="<?= basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'active' : '' ?>"><i class="fas fa-clipboard-check"></i> <span>Attendance</span></a></li>
                <li><a href="timetable.php" class="<?= basename($_SERVER['PHP_SELF']) == 'timetable.php' ? 'active' : '' ?>"><i class="fas fa-clock"></i> <span>Timetable</span></a></li>
                
                <li class="menu-section"><span class="section-label">Administration</span></li>
                <li><a href="finance.php" class="<?= basename($_SERVER['PHP_SELF']) == 'finance.php' ? 'active' : '' ?>"><i class="fas fa-coins"></i> <span>Finance</span></a></li>
                <li><a href="hostel.php" class="<?= basename($_SERVER['PHP_SELF']) == 'hostel.php' ? 'active' : '' ?>"><i class="fas fa-hotel"></i> <span>Hostel</span></a></li>
                <li><a href="library.php" class="<?= basename($_SERVER['PHP_SELF']) == 'library.php' ? 'active' : '' ?>"><i class="fas fa-book-open"></i> <span>Library</span></a></li>
                <li><a href="transport.php" class="<?= basename($_SERVER['PHP_SELF']) == 'transport.php' ? 'active' : '' ?>"><i class="fas fa-bus"></i> <span>Transport</span></a></li>
                
                <li class="menu-section"><span class="section-label">Other</span></li>
                <li><a href="events.php" class="<?= basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : '' ?>"><i class="fas fa-calendar-alt"></i> <span>Events</span></a></li>
                <li><a href="exams.php" class="<?= basename($_SERVER['PHP_SELF']) == 'exams.php' ? 'active' : '' ?>"><i class="fas fa-pencil-alt"></i> <span>Exams</span> <span class="badge">New</span></a></li>
                <li><a href="reports.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>"><i class="fas fa-chart-bar"></i> <span>Reports</span></a></li>
                <li><a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>"><i class="fas fa-cog"></i> <span>Settings</span></a></li>
            </ul>
        </div>
        
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn" onclick="return confirmLogout();">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </div>
    </div>

    <div class="main">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="header-title">
                    <h1><?= $pageTitle ?></h1>
                    <p><?= $pageSubTitle ?></p>
                </div>
            </div>
            <div class="header-right">
                <button class="header-btn" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="dot"></span>
                </button>
                <div class="header-user">
                    <div class="avatar"><?= $initials ?: 'U' ?></div>
                    <div>
                        <div class="name"><?= htmlspecialchars($full_name) ?></div>
                        <div class="role"><?= htmlspecialchars($role) ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content">
            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                    <div class="stat-number"><?= $total_students ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-chalkboard-teacher"></i></div>
                    <div class="stat-number"><?= $total_teachers ?></div>
                    <div class="stat-label">Total Teachers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="fas fa-book"></i></div>
                    <div class="stat-number"><?= $total_courses ?></div>
                    <div class="stat-label">Total Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fas fa-book-open"></i></div>
                    <div class="stat-number"><?= $total_books ?></div>
                    <div class="stat-label">Total Books</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon pink"><i class="fas fa-calendar-alt"></i></div>
                    <div class="stat-number"><?= $total_events ?></div>
                    <div class="stat-label">Total Events</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="stat-number"><?= $total_payments ?></div>
                    <div class="stat-label">Total Payments</div>
                </div>
            </div>

            <!-- Report Types -->
            <div class="card panel" style="margin-bottom: 30px;">
                <div class="table-toolbar">
                    <div>
                        <h3 class="panel-title"><i class="fas fa-file-alt" style="color:var(--primary-light);margin-right:8px;"></i>Generate Reports</h3>
                        <div class="panel-sub">Select a report type to generate</div>
                    </div>
                </div>
                <div style="padding: 20px 25px;">
                    <div class="report-grid">
                        <div class="report-card" onclick="generateReport('students')">
                            <span class="report-icon">👨‍🎓</span>
                            <div class="report-name">Student Report</div>
                            <div class="report-desc">Complete student list with details</div>
                        </div>
                        <div class="report-card" onclick="generateReport('teachers')">
                            <span class="report-icon">👨‍🏫</span>
                            <div class="report-name">Teacher Report</div>
                            <div class="report-desc">Teacher directory with subjects</div>
                        </div>
                        <div class="report-card" onclick="generateReport('courses')">
                            <span class="report-icon">📚</span>
                            <div class="report-name">Course Report</div>
                            <div class="report-desc">Course catalog with details</div>
                        </div>
                        <div class="report-card" onclick="generateReport('payments')">
                            <span class="report-icon">💰</span>
                            <div class="report-name">Payment Report</div>
                            <div class="report-desc">Financial transaction summary</div>
                        </div>
                        
                        <div class="report-card" onclick="generateReport('library')">
                            <span class="report-icon">📖</span>
                            <div class="report-name">Library Report</div>
                            <div class="report-desc">Book catalog and availability</div>
                        </div>
                        <div class="report-card" onclick="generateReport('transport')">
                            <span class="report-icon">🚌</span>
                            <div class="report-name">Transport Report</div>
                            <div class="report-desc">Route and vehicle details</div>
                        </div>
                        <div class="report-card" onclick="generateReport('events')">
                            <span class="report-icon">📅</span>
                            <div class="report-name">Events Report</div>
                            <div class="report-desc">Upcoming and past events</div>
                        </div>
                        <div class="report-card" onclick="generateReport('exams')">
                            <span class="report-icon">✏️</span>
                            <div class="report-name">Exams Report</div>
                            <div class="report-desc">Exam schedule and status</div>
                        </div>
                        <div class="report-card" onclick="generateReport('attendance')">
                            <span class="report-icon">📊</span>
                            <div class="report-name">Attendance Report</div>
                            <div class="report-desc">Student attendance summary</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Display -->
            <div class="card panel" id="reportDisplay">
                <div class="table-toolbar">
                    <div>
                        <h3 class="panel-title"><i class="fas fa-chart-bar" style="color:var(--primary-light);margin-right:8px;"></i>Report Preview</h3>
                        <div class="panel-sub" id="reportSubTitle">Select a report type to view data</div>
                    </div>
                    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                        <button class="btn btn-success" onclick="exportReport()" id="exportBtn" style="display:none;">
                            <i class="fas fa-download"></i> Export CSV
                        </button>
                        <button class="btn btn-outline" onclick="printReport()" id="printBtn" style="display:none;">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
                <div style="overflow-x:auto;min-height:200px;" id="reportContent">
                    <div style="text-align:center;color:var(--text-muted);padding:60px 20px;">
                        <i class="fas fa-file-alt" style="font-size:48px;display:block;margin-bottom:16px;opacity:0.3;"></i>
                        <h3 style="font-weight:600;margin-bottom:8px;">No Report Selected</h3>
                        <p>Click on any report card above to generate a report</p>
                    </div>
                </div>
                <div class="pagination">
                    <span id="resultCount">0 entries</span>
                </div>
            </div>
        </div>
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

// ===== GENERATE REPORT =====
let currentReportType = '';
let currentReportData = [];

function generateReport(type) {
    currentReportType = type;
    const reportNames = {
        'students': 'Student Report',
        'teachers': 'Teacher Report',
        'courses': 'Course Report',
        'payments': 'Payment Report',        
        'library': 'Library Report',
        'transport': 'Transport Report',
        'events': 'Events Report',
        'exams': 'Exams Report',
        'attendance': 'Attendance Report'
    };
    
    document.getElementById('reportSubTitle').textContent = reportNames[type] || 'Report Preview';
    document.getElementById('exportBtn').style.display = 'flex';
    document.getElementById('printBtn').style.display = 'flex';
    
    const content = document.getElementById('reportContent');
    content.innerHTML = `
        <div style="text-align:center;color:var(--text-muted);padding:40px;">
            <i class="fas fa-spinner fa-spin" style="font-size:28px;display:block;margin-bottom:12px;"></i>
            Loading report data...
        </div>
    `;
    
    fetch(`api/reports_api.php?action=${type}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                currentReportData = data.data || [];
                displayReport(data.data);
                document.getElementById('resultCount').textContent = `${data.data.length} entries`;
                showToast(`Report generated: ${reportNames[type]}`, 'success');
            } else {
                content.innerHTML = `
                    <div style="text-align:center;color:var(--red);padding:40px;">
                        <i class="fas fa-exclamation-circle" style="font-size:32px;display:block;margin-bottom:12px;"></i>
                        ${data.message || 'Error loading report data'}
                    </div>
                `;
                showToast(data.message || 'Error loading report', 'error');
            }
        })
        .catch(err => {
            content.innerHTML = `
                <div style="text-align:center;color:var(--red);padding:40px;">
                    <i class="fas fa-exclamation-circle" style="font-size:32px;display:block;margin-bottom:12px;"></i>
                    Error loading report. Please try again.
                </div>
            `;
            console.error('Error:', err);
            showToast('Error loading report', 'error');
        });
}

// ===== DISPLAY REPORT =====
function displayReport(data) {
    const content = document.getElementById('reportContent');
    
    if (!data || data.length === 0) {
        content.innerHTML = `
            <div style="text-align:center;color:var(--text-muted);padding:40px;">
                <i class="fas fa-inbox" style="font-size:32px;display:block;margin-bottom:12px;opacity:0.3;"></i>
                No data available for this report
            </div>
        `;
        return;
    }
    
    let html = '<table class="data-table"><thead><tr>';
    
    // Get headers from first record
    const headers = Object.keys(data[0]);
    headers.forEach(header => {
        const label = header.replace(/_/g, ' ').toUpperCase();
        html += `<th>${label}</th>`;
    });
    html += '</tr></thead><tbody>';
    
    // Add data rows
    data.forEach(record => {
        html += '<tr>';
        headers.forEach(header => {
            let value = record[header] || '—';
            html += `<td>${value}</td>`;
        });
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    content.innerHTML = html;
}

// ===== EXPORT REPORT =====
function exportReport() {
    if (!currentReportType || currentReportData.length === 0) {
        showToast('No data to export. Generate a report first.', 'error');
        return;
    }
    
    // Create CSV
    const headers = Object.keys(currentReportData[0]);
    let csv = headers.join(',') + '\n';
    
    currentReportData.forEach(record => {
        const row = headers.map(header => {
            let value = record[header] || '';
            if (typeof value === 'string' && (value.includes(',') || value.includes('"'))) {
                value = '"' + value.replace(/"/g, '""') + '"';
            }
            return value;
        });
        csv += row.join(',') + '\n';
    });
    
    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${currentReportType}_report_${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    showToast('Report exported successfully!', 'success');
}

// ===== PRINT REPORT =====
function printReport() {
    if (!currentReportType || currentReportData.length === 0) {
        showToast('No data to print. Generate a report first.', 'error');
        return;
    }
    window.print();
}

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