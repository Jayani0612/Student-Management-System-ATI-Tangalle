<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$pageTitle = 'Dashboard';

// ---- Live counts from the database ----
$totalStudents = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) c FROM students");
if ($result) {
    $totalStudents = mysqli_fetch_assoc($result)['c'] ?? 0;
}

$totalTeachers = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) c FROM teachers");
if ($result) {
    $totalTeachers = mysqli_fetch_assoc($result)['c'] ?? 0;
}

$activeCourses = 0;
$result = mysqli_query($conn, "SELECT COUNT(*) c FROM courses WHERE status='Active'");
if ($result) {
    $activeCourses = mysqli_fetch_assoc($result)['c'] ?? 0;
}

// Check if staff table exists
$totalStaff = 0;
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'staff'");
if (mysqli_num_rows($check_table) > 0) {
    $result = mysqli_query($conn, "SELECT COUNT(*) c FROM staff");
    if ($result) {
        $totalStaff = mysqli_fetch_assoc($result)['c'] ?? 0;
    }
}

// ---- Upcoming events ----
$events = [];
$check_events = mysqli_query($conn, "SHOW TABLES LIKE 'events'");
if (mysqli_num_rows($check_events) > 0) {
    $events_query = mysqli_query($conn, "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3");
    if ($events_query && mysqli_num_rows($events_query) > 0) {
        $events = $events_query;
    } else {
        $events_query = mysqli_query($conn, "SELECT * FROM events ORDER BY event_date ASC LIMIT 3");
        if ($events_query) {
            $events = $events_query;
        }
    }
}

// ---- Recent activity ----
$activities = [];
$check_activity = mysqli_query($conn, "SHOW TABLES LIKE 'activity_log'");
if (mysqli_num_rows($check_activity) > 0) {
    $activities_query = mysqli_query($conn, "SELECT * FROM activity_log ORDER BY created_at DESC LIMIT 5");
    if ($activities_query) {
        $activities = $activities_query;
    }
}

// ---- Get user info ----
$user_id = $_SESSION['user_id'];
$user_query = mysqli_query($conn, "SELECT full_name, role FROM users WHERE id = $user_id");
$user = mysqli_fetch_assoc($user_query);
$full_name = $user['full_name'] ?? 'User';
$role = $user['role'] ?? 'Staff';
$initials = implode('', array_map(function($word) { return strtoupper($word[0]); }, explode(' ', $full_name)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ATI Tangalle · Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
/* ===== RESET & VARIABLES ===== */
:root {
    --primary: #2563eb;
    --primary-light: #3b82f6;
    --primary-dark: #1d4ed8;
    --primary-bg: #eff6ff;
    --secondary: #60a5fa;
    --bg: #f8fafc;
    --card-bg: #ffffff;
    --text: #0f172a;
    --text-muted: #64748b;
    --border: #e2e8f0;
    --shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    --shadow-hover: 0 4px 20px rgba(37, 99, 235, 0.10);
    --radius: 12px;
    --transition: all 0.2s ease;
    
    --indigo: #3b82f6;
    --indigo-light: #eff6ff;
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
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
    background: var(--bg);
    color: var(--text);
    line-height: 1.6;
}

/* ===== APP LAYOUT ===== */
.app { display: flex; min-height: 100vh; }

/* ========================================
   SIDEBAR STYLES
   ======================================== */
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
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #fff;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    flex-shrink: 0;
}

.sidebar-brand .brand-text {
    font-size: 20px;
    font-weight: 700;
    line-height: 1.2;
    color: #fff;
}

.sidebar-brand .brand-text span {
    color: #60a5fa;
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

/* Custom Scrollbar */
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
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #fff;
    font-size: 9px;
    padding: 2px 10px;
    border-radius: 20px;
    font-weight: 600;
    letter-spacing: 0.5px;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
}

.sidebar-menu a:hover {
    background: rgba(255,255,255,0.07);
    color: #fff;
    transform: translateX(4px);
}

.sidebar-menu a:hover i {
    color: #60a5fa;
}

.sidebar-menu a.active {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(37, 99, 235, 0.1));
    color: #fff;
    box-shadow: inset 3px 0 0 #3b82f6;
}

.sidebar-menu a.active i {
    color: #60a5fa;
}

/* Sidebar Footer - Only Logout Button */
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
    background: rgba(255,255,255,0.95);
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
    transition: all 0.3s ease;
}

.hamburger:hover {
    color: var(--primary);
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

/* ===== CONTENT ===== */
.content {
    padding: 30px 35px;
}

/* ===== WELCOME BANNER ===== */
.welcome-banner {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border-radius: var(--radius);
    padding: 28px 32px;
    margin-bottom: 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid rgba(255,255,255,0.8);
}

.welcome-text h2 {
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 4px;
    color: #0f172a;
}

.welcome-text h2 i {
    color: #3b82f6;
    margin-right: 10px;
}

.welcome-text p {
    color: #64748b;
    font-size: 14px;
}

.welcome-stats {
    display: flex;
    gap: 35px;
}

.welcome-stats .stat-item {
    text-align: center;
}

.welcome-stats .stat-item .num {
    font-size: 26px;
    font-weight: 700;
    color: #2563eb;
}

.welcome-stats .stat-item .label {
    font-size: 11px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ===== STAT CARDS ===== */
.stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 28px;
}

.card {
    background: var(--card-bg);
    border-radius: var(--radius);
    padding: 22px 24px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    border: 1px solid var(--border);
}

.card:hover {
    box-shadow: var(--shadow-hover);
    transform: translateY(-2px);
}

.stat-card {
    position: relative;
    overflow: hidden;
}

.stat-card .stat-bg-icon {
    position: absolute;
    right: -10px;
    bottom: -10px;
    font-size: 56px;
    opacity: 0.04;
    pointer-events: none;
}

.stat-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.stat-label {
    font-size: 14px;
    font-weight: 500;
    color: var(--text-muted);
}

.stat-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.stat-value {
    font-size: 30px;
    font-weight: 700;
    letter-spacing: -0.5px;
    margin-bottom: 6px;
    color: var(--primary);
}

.stat-foot {
    font-size: 13px;
    color: var(--text-muted);
}

.stat-change {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 10px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 12px;
}

.stat-change.up {
    color: var(--green);
    background: var(--green-light);
}

.stat-change.down {
    color: var(--red);
    background: var(--red-light);
}

.stat-change.flat {
    color: var(--text-muted);
    background: var(--bg);
}

/* ===== DASHBOARD GRID ===== */
.dash-grid {
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 24px;
}

/* ===== PANELS ===== */
.panel {
    padding: 0;
    overflow: hidden;
}

.panel-head {
    padding: 18px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--border);
}

.panel-title {
    font-size: 16px;
    font-weight: 700;
    color: var(--primary);
}

.panel-sub {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 2px;
}

.toggle-group {
    display: flex;
    gap: 4px;
    background: var(--bg);
    padding: 4px;
    border-radius: 8px;
}

.toggle-group button {
    padding: 5px 12px;
    border: none;
    background: transparent;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    cursor: pointer;
    transition: var(--transition);
}

.toggle-group button.active {
    background: #fff;
    color: var(--text);
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}

.toggle-group button:hover:not(.active) {
    color: var(--text);
}

.chart-wrap {
    padding: 20px 24px 24px;
    height: 250px;
    position: relative;
}

.chart-wrap.short {
    height: 190px;
}

/* ===== EVENTS ===== */
.event-item {
    display: flex;
    gap: 14px;
    padding: 14px 24px;
    border-bottom: 1px solid var(--border);
    transition: var(--transition);
    align-items: center;
}

.event-item:hover {
    background: var(--bg);
}

.event-item.highlight {
    background: linear-gradient(135deg, var(--indigo-light), #dbeafe);
    border-left: 3px solid var(--primary);
}

.event-date {
    text-align: center;
    min-width: 48px;
    background: var(--bg);
    padding: 4px 8px;
    border-radius: 8px;
}

.event-date .month {
    font-size: 9px;
    font-weight: 700;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.event-date .day {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary);
    line-height: 1.2;
}

.event-title {
    font-weight: 600;
    font-size: 14px;
}

.event-meta {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 1px;
}

.event-meta i {
    margin-right: 4px;
}

.view-all {
    display: block;
    padding: 12px 24px;
    text-align: center;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: var(--transition);
}

.view-all:hover {
    background: var(--bg);
    color: var(--primary-dark);
}

/* ===== ACTIVITY ===== */
.activity-item {
    display: flex;
    gap: 12px;
    padding: 12px 24px;
    border-bottom: 1px solid var(--border);
    transition: var(--transition);
    align-items: center;
}

.activity-item:hover {
    background: var(--bg);
}

.activity-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    flex-shrink: 0;
}

.activity-text {
    font-size: 14px;
    font-weight: 500;
}

.activity-time {
    font-size: 12px;
    color: var(--text-muted);
    margin-top: 1px;
}

/* ===== SIDEBAR OVERLAY ===== */
.sidebar-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 999;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.sidebar-overlay.active {
    opacity: 1;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1200px) {
    .dash-grid {
        grid-template-columns: 1fr;
    }
    .welcome-stats {
        gap: 20px;
    }
}

@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
        width: 280px;
        box-shadow: 4px 0 30px rgba(0,0,0,0.3);
    }
    .sidebar.open {
        transform: translateX(0);
    }
    .sidebar.open + .sidebar-overlay {
        display: block;
        opacity: 1;
    }
    .main {
        margin-left: 0;
    }
    .hamburger {
        display: block;
    }
    .stat-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .welcome-banner {
        flex-direction: column;
        text-align: center;
        gap: 16px;
        padding: 22px;
    }
    .welcome-stats {
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .header {
        padding: 12px 16px;
    }
    .content {
        padding: 16px;
    }
    .stat-grid {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .stat-value {
        font-size: 22px;
    }
    .stat-card {
        padding: 14px 16px;
    }
    .panel-head {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
        padding: 14px 16px;
    }
    .chart-wrap {
        height: 180px;
        padding: 12px 16px 16px;
    }
    .chart-wrap.short {
        height: 150px;
    }
    .welcome-stats .stat-item .num {
        font-size: 18px;
    }
    .welcome-text h2 {
        font-size: 18px;
    }
}

@media (max-width: 480px) {
    .stat-grid {
        grid-template-columns: 1fr;
    }
    .event-item {
        padding: 10px 14px;
    }
    .activity-item {
        padding: 10px 14px;
    }
    .panel-head {
        padding: 12px 14px;
    }
    .welcome-banner {
        padding: 16px;
    }
    .welcome-stats {
        flex-direction: column;
        gap: 8px;
    }
    .sidebar {
        width: 100%;
        max-width: 300px;
        padding: 20px 16px 0;
    }
}

/* ===== SCROLLBAR ===== */
::-webkit-scrollbar {
    width: 5px;
    height: 5px;
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

/* ===== DARK MODE ===== */
@media (prefers-color-scheme: dark) {
    :root {
        --bg: #0f172a;
        --card-bg: #1e293b;
        --text: #e2e8f0;
        --text-muted: #94a3b8;
        --border: #334155;
        --shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
        --shadow-hover: 0 4px 20px rgba(59, 130, 246, 0.12);
    }
    .header {
        background: rgba(30, 41, 59, 0.95);
    }
    .header-title h1 {
        color: #e2e8f0;
    }
    .toggle-group {
        background: #334155;
    }
    .toggle-group button.active {
        background: #1e293b;
        color: #fff;
    }
    .event-item.highlight {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.08));
    }
    .stat-change.up {
        background: rgba(16, 185, 129, 0.15);
    }
    .stat-change.down {
        background: rgba(239, 68, 68, 0.15);
    }
    .stat-change.flat {
        background: rgba(255,255,255,0.05);
    }
    .stat-value {
        color: #e2e8f0;
    }
    .event-date {
        background: rgba(255,255,255,0.05);
    }
    .event-date .day {
        color: #60a5fa;
    }
    .panel-title {
        color: #e2e8f0;
    }
    .welcome-banner {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-color: #334155;
    }
    .welcome-text h2 {
        color: #e2e8f0;
    }
    .welcome-text p {
        color: #94a3b8;
    }
    .welcome-stats .stat-item .num {
        color: #60a5fa;
    }
}
</style>
</head>
<body>
<div class="app">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="logo-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div>
                <div class="brand-text">ATI <span>Tangalle</span></div>
                <span class="brand-sub">Advanced Technological Institute</span>
            </div>
        </div>
        
        <div class="sidebar-menu-wrapper">
            <ul class="sidebar-menu">
                <!-- Main Modules -->
                <li>
                    <a href="dashboard.php" class="active">
                        <i class="fas fa-th-large"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <!-- Academic Section -->
                <li class="menu-section">
                    <span class="section-label">Academic</span>
                </li>
                <li>
                    <a href="students.php">
                        <i class="fas fa-users"></i>
                        <span>Students</span>
                    </a>
                </li>
                <li>
                    <a href="teachers.php">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Teachers</span>
                    </a>
                </li>
                <li>
                    <a href="courses.php">
                        <i class="fas fa-book"></i>
                        <span>Courses</span>
                    </a>
                </li>
                <li>
                    <a href="attendance.php">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                <li>
                    <a href="timetable.php">
                        <i class="fas fa-clock"></i>
                        <span>Timetable</span>
                    </a>
                </li>
                
                <!-- Administration Section -->
                <li class="menu-section">
                    <span class="section-label">Administration</span>
                </li>
                <li>
                    <a href="finance.php">
                        <i class="fas fa-coins"></i>
                        <span>Finance</span>
                    </a>
                </li>
                <li>
                    <a href="hostel.php">
                        <i class="fas fa-hotel"></i>
                        <span>Hostel</span>
                    </a>
                </li>
                <li>
                    <a href="library.php">
                        <i class="fas fa-book-open"></i>
                        <span>Library</span>
                    </a>
                </li>
                <li>
                    <a href="transport.php">
                        <i class="fas fa-bus"></i>
                        <span>Transport</span>
                    </a>
                </li>
                
                <!-- Other Section -->
                <li class="menu-section">
                    <span class="section-label">Other</span>
                </li>
                <li>
                    <a href="events.php">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Events</span>
                    </a>
                </li>
                <li>
                    <a href="exams.php">
                        <i class="fas fa-pencil-alt"></i>
                        <span>Exams</span>
                        <span class="badge">New</span>
                    </a>
                </li>
                <li>
                    <a href="reports.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li>
                    <a href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Footer with Only Logout Button -->
        <div class="sidebar-footer">
            <a href="logout.php" class="logout-btn" onclick="return confirmLogout();">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <div class="main">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <button class="hamburger" onclick="toggleSidebar()" aria-label="Toggle Sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="header-title">
                    <h1><?= $pageTitle ?></h1>
                    <p>Welcome to ATI Tangalle</p>
                </div>
            </div>
            <div class="header-right">
                <button class="header-btn" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="dot"></span>
                </button>
            </div>
        </div>

        <div class="content">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div class="welcome-text">
                    <h2><i class="fas fa-hand-peace"></i> <span id="greetingText">Good Morning</span>, <?= htmlspecialchars(explode(' ', $full_name)[0]) ?>!</h2>
                    <p>Here's your institute overview for today. You have <?= number_format($totalStudents) ?> students and <?= number_format($totalTeachers) ?> teachers.</p>
                </div>
                <div class="welcome-stats">
                    <div class="stat-item">
                        <div class="num"><?= date('d') ?></div>
                        <div class="label">Day</div>
                    </div>
                    <div class="stat-item">
                        <div class="num"><?= date('M') ?></div>
                        <div class="label">Month</div>
                    </div>
                    <div class="stat-item">
                        <div class="num"><?= date('Y') ?></div>
                        <div class="label">Year</div>
                    </div>
                </div>
            </div>

            <!-- Stat cards -->
            <div class="stat-grid">
                <div class="card stat-card">
                    <div class="stat-bg-icon"><i class="fas fa-user-graduate"></i></div>
                    <div class="stat-head">
                        <span class="stat-label">Total Students</span>
                        <div class="stat-icon" style="background:var(--indigo-light);color:var(--primary)"><i class="fa-solid fa-user-graduate"></i></div>
                    </div>
                    <div class="stat-value"><?= number_format($totalStudents) ?></div>
                    <div class="stat-foot"><span class="stat-change up"><i class="fa-solid fa-arrow-up"></i> +12%</span> vs last month</div>
                </div>

                <div class="card stat-card">
                    <div class="stat-bg-icon"><i class="fas fa-chalkboard-user"></i></div>
                    <div class="stat-head">
                        <span class="stat-label">Teachers</span>
                        <div class="stat-icon" style="background:var(--blue-light);color:var(--blue)"><i class="fa-solid fa-chalkboard-user"></i></div>
                    </div>
                    <div class="stat-value"><?= number_format($totalTeachers) ?></div>
                    <div class="stat-foot"><span class="stat-change up"><i class="fa-solid fa-arrow-up"></i> +2%</span> vs last month</div>
                </div>

                <div class="card stat-card">
                    <div class="stat-bg-icon"><i class="fas fa-book"></i></div>
                    <div class="stat-head">
                        <span class="stat-label">Active Courses</span>
                        <div class="stat-icon" style="background:var(--orange-light);color:var(--orange)"><i class="fa-solid fa-book"></i></div>
                    </div>
                    <div class="stat-value"><?= number_format($activeCourses) ?></div>
                    <div class="stat-foot"><span class="stat-change flat">Stable</span> vs last month</div>
                </div>

                <div class="card stat-card">
                    <div class="stat-bg-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-head">
                        <span class="stat-label">Staff Members</span>
                        <div class="stat-icon" style="background:var(--purple-light);color:var(--purple)"><i class="fa-solid fa-users"></i></div>
                    </div>
                    <div class="stat-value"><?= number_format($totalStaff) ?></div>
                    <div class="stat-foot"><span class="stat-change flat">Stable</span> vs last month</div>
                </div>
            </div>

            <!-- Main grid -->
            <div class="dash-grid">
                <div>
                    <div class="card panel">
                        <div class="panel-head">
                            <div>
                                <h3 class="panel-title">Student Growth</h3>
                                <div class="panel-sub">Annual registration performance</div>
                            </div>
                            <div class="toggle-group">
                                <button class="active" data-range="yearly">Yearly</button>
                                <button data-range="monthly">Monthly</button>
                            </div>
                        </div>
                        <div class="chart-wrap"><canvas id="growthChart"></canvas></div>
                    </div>

                    <div class="card panel">
                        <div class="panel-head">
                            <div>
                                <h3 class="panel-title">Attendance Analytics</h3>
                                <div class="panel-sub">Daily average across departments</div>
                            </div>
                        </div>
                        <div class="chart-wrap short"><canvas id="attendanceChart"></canvas></div>
                    </div>
                </div>

                <div>
                    <div class="card panel">
                        <div class="panel-head">
                            <h3 class="panel-title"><i class="fas fa-calendar-alt" style="color:var(--primary);margin-right:8px;"></i> Upcoming Events</h3>
                            <i class="fa-solid fa-calendar-days" style="color:var(--text-muted)"></i>
                        </div>

                        <?php if ($events && mysqli_num_rows($events) > 0): ?>
                            <?php $first = true; while ($ev = mysqli_fetch_assoc($events)): ?>
                                <div class="event-item <?= $first ? 'highlight' : '' ?>">
                                    <div class="event-date">
                                        <div class="month"><?= strtoupper(date('M', strtotime($ev['event_date']))) ?></div>
                                        <div class="day"><?= date('d', strtotime($ev['event_date'])) ?></div>
                                    </div>
                                    <div>
                                        <div class="event-title"><?= htmlspecialchars($ev['title']) ?></div>
                                        <div class="event-meta"><i class="fa-regular fa-clock"></i> <?= htmlspecialchars($ev['time_label'] ?? '10:00 AM') ?></div>
                                    </div>
                                </div>
                            <?php $first = false; endwhile; ?>
                        <?php else: ?>
                            <div style="padding:30px;text-align:center;color:var(--text-muted);">
                                <i class="fas fa-calendar-plus" style="font-size:32px;display:block;margin-bottom:10px;opacity:0.3;"></i>
                                No upcoming events
                            </div>
                        <?php endif; ?>

                        <a href="events.php" class="view-all">View All Events <i class="fas fa-arrow-right"></i></a>
                    </div>

                    <div class="card panel">
                        <div class="panel-head">
                            <h3 class="panel-title"><i class="fas fa-clock" style="color:var(--primary);margin-right:8px;"></i> Recent Activities</h3>
                            <i class="fa-solid fa-clock" style="color:var(--text-muted)"></i>
                        </div>
                        <?php if ($activities && mysqli_num_rows($activities) > 0): ?>
                            <?php while ($act = mysqli_fetch_assoc($activities)):
                                $isPayment = ($act['activity_type'] ?? '') === 'payment';
                                $icon = $isPayment ? 'fa-credit-card' : 'fa-user-plus';
                                $bg = $isPayment ? 'var(--indigo-light)' : 'var(--blue-light)';
                                $color = $isPayment ? 'var(--primary)' : 'var(--blue)';
                            ?>
                                <div class="activity-item">
                                    <div class="activity-icon" style="background:<?= $bg ?>;color:<?= $color ?>">
                                        <i class="fa-solid <?= $icon ?>"></i>
                                    </div>
                                    <div>
                                        <div class="activity-text"><?= htmlspecialchars($act['description'] ?? 'Activity logged') ?></div>
                                        <div class="activity-time"><?= date('M d, g:i A', strtotime($act['created_at'])) ?></div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="padding:30px;text-align:center;color:var(--text-muted);">
                                <i class="fas fa-inbox" style="font-size:32px;display:block;margin-bottom:10px;opacity:0.3;"></i>
                                No recent activities
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
// ===== SIDEBAR TOGGLE =====
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('open');
    if (overlay) {
        overlay.classList.toggle('active');
    }
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.remove('open');
    if (overlay) {
        overlay.classList.remove('active');
    }
}

// Close sidebar on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSidebar();
    }
});

// Close sidebar on window resize (if going from mobile to desktop)
window.addEventListener('resize', function() {
    if (window.innerWidth > 992) {
        closeSidebar();
    }
});

// ===== CONFIRM LOGOUT =====
function confirmLogout() {
    return confirm('Are you sure you want to logout?');
}

// ===== TOGGLE GROUP =====
document.querySelectorAll('.toggle-group').forEach(group => {
    group.querySelectorAll('button').forEach(btn => {
        btn.addEventListener('click', function() {
            group.querySelectorAll('button').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
});

// ===== CHARTS =====
document.addEventListener('DOMContentLoaded', function() {
    const ctx1 = document.getElementById('growthChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Students',
                data: [120, 135, 148, 162, 180, 195, 210, 228, 245, 260, 275, 290],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.06)',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { font: { size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });

    const ctx2 = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: ['Math', 'Science', 'English', 'History', 'Art'],
            datasets: [{
                label: 'Attendance %',
                data: [92, 88, 95, 85, 90],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(139, 92, 246, 0.7)',
                    'rgba(236, 72, 153, 0.7)'
                ],
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: { font: { size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                }
            }
        }
    });
});

// ===== WELCOME TIME GREETING =====
document.addEventListener('DOMContentLoaded', function() {
    const hour = new Date().getHours();
    let greeting = 'Good Morning';
    if (hour >= 12 && hour < 17) greeting = 'Good Afternoon';
    else if (hour >= 17) greeting = 'Good Evening';
    
    const greetingText = document.getElementById('greetingText');
    if (greetingText) {
        greetingText.textContent = greeting;
    }
});
</script>
</body>
</html>