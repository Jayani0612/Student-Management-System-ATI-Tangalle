<?php
session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {

    case 'list':
        $search = trim($_GET['search'] ?? '');
        $sql = "SELECT t.*, d.name AS department_name
                FROM teachers t
                LEFT JOIN departments d ON t.department_id = d.id";
        $params = [];
        $types = '';

        if ($search !== '') {
            $sql .= " WHERE t.full_name LIKE ? OR t.subject LIKE ? OR t.email LIKE ?";
            $like = "%$search%";
            $params = [$like, $like, $like];
            $types = 'sss';
        }
        $sql .= " ORDER BY t.id DESC";

        $stmt = mysqli_prepare($conn, $sql);
        if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $teachers = [];
        while ($row = mysqli_fetch_assoc($result)) $teachers[] = $row;
        echo json_encode(['success' => true, 'data' => $teachers]);
        break;

    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = mysqli_prepare($conn, "SELECT * FROM teachers WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $teacher = mysqli_fetch_assoc($result);

        if ($teacher) {
            echo json_encode(['success' => true, 'data' => $teacher]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Teacher not found']);
        }
        break;

    case 'create':
        $fullName    = trim($_POST['full_name'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $departmentId = (int)($_POST['department_id'] ?? 0) ?: null;
        $subject     = trim($_POST['subject'] ?? '');
        $joinedDate  = $_POST['joined_date'] ?? date('Y-m-d');

        if ($fullName === '') {
            echo json_encode(['success' => false, 'message' => 'Full Name is required.']);
            exit();
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO teachers (full_name, email, phone, department_id, subject, joined_date) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssiss", $fullName, $email, $phone, $departmentId, $subject, $joinedDate);

        if (mysqli_stmt_execute($stmt)) {
            $newId = mysqli_insert_id($conn);
            mysqli_query($conn, "INSERT INTO activity_log (activity_type, description) VALUES ('teacher', 'New Teacher added: " . mysqli_real_escape_string($conn, $fullName) . "')");
            echo json_encode(['success' => true, 'message' => 'Teacher added successfully.', 'id' => $newId]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    case 'update':
        $id          = (int)($_POST['id'] ?? 0);
        $fullName    = trim($_POST['full_name'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $departmentId = (int)($_POST['department_id'] ?? 0) ?: null;
        $subject     = trim($_POST['subject'] ?? '');
        $joinedDate  = $_POST['joined_date'] ?? date('Y-m-d');

        if (!$id || $fullName === '') {
            echo json_encode(['success' => false, 'message' => 'Full Name is required.']);
            exit();
        }

        $stmt = mysqli_prepare($conn, "UPDATE teachers SET full_name=?, email=?, phone=?, department_id=?, subject=?, joined_date=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssissi", $fullName, $email, $phone, $departmentId, $subject, $joinedDate, $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Teacher updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid teacher ID.']);
            exit();
        }
        $stmt = mysqli_prepare($conn, "DELETE FROM teachers WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Teacher deleted.']);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
