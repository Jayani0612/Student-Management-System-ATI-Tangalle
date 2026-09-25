<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$pageTitle = 'Students';
$pageSubTitle = 'Manage student records, admissions and status';

// Get departments for dropdown
$departments = mysqli_query($conn, "SELECT * FROM departments ORDER BY name");
$deptRows = [];
while ($d = mysqli_fetch_assoc($departments)) { 
    $deptRows[] = $d; 
}

// Get user info for header
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
<title>ATI Tangalle · Students</title>
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

.data-table .status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.data-table .status-badge.Active {
    background: var(--green-light);
    color: var(--green);
}

.data-table .status-badge.Inactive {
    background: var(--red-light);
    color: var(--red);
}

.data-table .status-badge.Graduated {
    background: var(--indigo-light);
    color: var(--primary);
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
    max-width: 600px;
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
    
    .modal {
        max-width: 100%;
        margin: 10px;
        border-radius: 12px;
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
                        <h3 class="panel-title"><i class="fas fa-user-graduate" style="color:var(--primary-light);margin-right:8px;"></i>Student Records</h3>
                        <div class="panel-sub">View and manage all student information</div>
                    </div>
                    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                        <div class="table-search">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="searchInput" placeholder="Search name, ID, email...">
                        </div>
                        <button class="btn btn-primary" onclick="openAddModal()"><i class="fa-solid fa-plus"></i> Add Student</button>
                    </div>
                </div>

                <div style="overflow-x:auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Student No.</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Year</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Admitted</th>
                                <th style="text-align:center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="studentsBody">
                            <tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:40px;">
                                <i class="fas fa-spinner fa-spin" style="font-size:24px;display:block;margin-bottom:10px;"></i>
                                Loading students...
                            </td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <span id="resultCount">0 students</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal-overlay" id="studentModal">
    <div class="modal">
        <div class="modal-head">
            <h3 id="modalTitle"><i class="fas fa-user-plus" style="color:var(--primary-light);margin-right:8px;"></i>Add Student</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        
            <div class="modal-body">
               v class="form-row">
                     <form id="studentForm">
    <div class="modal-body">
        <input type="hidden" id="studentId" name="id">

        <div class="form-row">
            <div class="form-group">
                <label>Student No. *</label>
                <input type="text" id="student_no" name="student_no" class="form-control" placeholder="e.g., STU-1007" required>
            </div>
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Full name" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="student@example.com">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" id="phone" name="phone" class="form-control" placeholder="07XXXXXXXX">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Address</label>
                <input type="text" id="address" name="address" class="form-control" placeholder="Student address">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Department</label>
                <select id="department_id" name="department_id" class="form-control">
                    <option value="">-- Select Department --</option>
                    <?php foreach ($deptRows as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Year Level</label>
                <select id="year_level" name="year_level" class="form-control">
                    <option value="1">Year 1</option>
                    <option value="2">Year 2</option>
                    <option value="3">Year 3</option>
                    <option value="4">Year 4</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                    <option value="Graduated">Graduated</option>
                </select>
            </div>
            <div class="form-group">
                <label>Admission Date</label>
                <input type="date" id="admission_date" name="admission_date" class="form-control">
            </div>
        </div>
    </div>
    <div class="modal-foot">
        <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Student</button>
    </div>
</form>
    </div>
</div>

<script>
// ===== SIDEBAR TOGGLE =====
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
}

// Close sidebar on outside click (mobile)
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    const hamburger = document.querySelector('.hamburger');
    if (window.innerWidth <= 992) {
        if (!sidebar.contains(e.target) && !hamburger.contains(e.target)) {
            sidebar.classList.remove('open');
        }
    }
});

// ===== LOGOUT CONFIRMATION =====
function confirmLogout() {
    return confirm('Are you sure you want to logout?');
}

// ===== MODAL FUNCTIONS =====
function openAddModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus" style="color:var(--primary-light);margin-right:8px;"></i>Add Student';
    document.getElementById('studentForm').reset();
    document.getElementById('studentId').value = '';
    document.getElementById('studentModal').classList.add('open');
}

function openEditModal(id) {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-edit" style="color:var(--primary-light);margin-right:8px;"></i>Edit Student';
    document.getElementById('studentId').value = id;
    
    fetch(`api/students_api.php?action=get&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const student = data.data;
                document.getElementById('student_no').value = student.student_no || '';
                document.getElementById('full_name').value = student.full_name || '';
                document.getElementById('email').value = student.email || '';
                document.getElementById('phone').value = student.phone ||'';
                document.getElementById('address').value = student.address || ''; 
                document.getElementById('department_id').value = student.department_id || '';
                document.getElementById('year_level').value = student.year_level || 1;
                document.getElementById('status').value = student.status || 'Active';
                document.getElementById('admission_date').value = student.admission_date || '';
                document.getElementById('studentModal').classList.add('open');
            } else {
                showToast(data.message || 'Error loading student data', 'error');
            }
        })
        .catch(err => {
            showToast('Error loading student data', 'error');
            console.error('Error:', err);
        });
}

function closeModal() {
    document.getElementById('studentModal').classList.remove('open');
}

// Close modal on outside click
document.getElementById('studentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// ===== STUDENT FORM SUBMIT =====
document.getElementById('studentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = document.getElementById('studentId').value;
    const action = id ? 'update' : 'create';
    formData.append('action', action);
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    submitBtn.disabled = true;
    
    fetch('api/students_api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        
        if (data.success) {
            closeModal();
            loadStudents();
            showToast(data.message || 'Student saved successfully!', 'success');
        } else {
            showToast(data.message || 'Error saving student.', 'error');
        }
    })
    .catch(err => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        showToast('An error occurred. Please try again.', 'error');
        console.error('Error:', err);
    });
});

// ===== DELETE STUDENT =====
function deleteStudent(id) {
    if (!confirm('Are you sure you want to delete this student? This action cannot be undone.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);
    
    fetch('api/students_api.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadStudents();
            showToast('Student deleted successfully!', 'success');
        } else {
            showToast(data.message || 'Error deleting student.', 'error');
        }
    })
    .catch(err => {
        showToast('Error deleting student. Please try again.', 'error');
        console.error('Error:', err);
    });
}

// ===== LOAD STUDENTS =====
function loadStudents() {
    const tbody = document.getElementById('studentsBody');
    const search = document.getElementById('searchInput').value;
    
    fetch(`api/students_api.php?action=list&search=${encodeURIComponent(search)}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:var(--red);padding:40px;">
                    <i class="fas fa-exclamation-circle" style="font-size:24px;display:block;margin-bottom:10px;"></i>
                    ${data.message || 'Error loading students'}
                </td></tr>`;
                document.getElementById('resultCount').textContent = '0 students';
                return;
            }
            
            const students = data.data || [];
            if (students.length === 0) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:var(--text-muted);padding:40px;">
                    <i class="fas fa-user-graduate" style="font-size:32px;display:block;margin-bottom:10px;opacity:0.3;"></i>
                    No students found
                </td></tr>`;
                document.getElementById('resultCount').textContent = '0 students';
                return;
            }
            
            let html = '';
            students.forEach(student => {
                const statusClass = student.status || 'Active';
                html += `
                    <tr>
                        <td><strong>${student.student_no || '—'}</strong></td>
                        <td>${student.full_name || '—'}</td>
                        <td>${student.department_name || '—'}</td>
                        <td>Year ${student.year_level || 1}</td>
                        <td>${student.email || '—'}</td>
                        <td>${student.address || '—'}</td> 
                        <td><span class="status-badge ${statusClass}">${statusClass}</span></td>
                        <td>${student.admission_date || '—'}</td>
                        <td>
                            <div class="actions" style="justify-content:center;">
                                <button class="btn btn-warning btn-sm" onclick="openEditModal(${student.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteStudent(${student.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
            document.getElementById('resultCount').textContent = `${students.length} students`;
        })
        .catch(err => {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:var(--red);padding:40px;">
                <i class="fas fa-exclamation-circle" style="font-size:24px;display:block;margin-bottom:10px;"></i>
                Error loading students. Please refresh the page.
            </td></tr>`;
            console.error('Error:', err);
        });
}

// ===== SEARCH WITH DEBOUNCE =====
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadStudents();
    }, 300);
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
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}

// ===== LOAD STUDENTS ON PAGE LOAD =====
document.addEventListener('DOMContentLoaded', loadStudents);
</script>
</body>
</html>