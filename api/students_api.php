<?php
session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Get action from request
$action = $_REQUEST['action'] ?? '';

// Handle different actions
switch ($action) {

    case 'list':
        try {
            $search = trim($_GET['search'] ?? '');
            $sql = "SELECT s.*, d.name AS department_name
                    FROM students s
                    LEFT JOIN departments d ON s.department_id = d.id";
            $params = [];
            $types = '';

            if ($search !== '') {
$sql .= " WHERE s.full_name LIKE ? OR s.student_no LIKE ? OR s.email LIKE ? OR s.address LIKE ?";                $like = "%$search%";
                $params = [$like, $like, $like, $like];
$types = 'ssss';
            }
            $sql .= " ORDER BY s.id DESC";

            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . mysqli_error($conn));
            }
            
            if ($params) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Database execute error: ' . mysqli_error($conn));
            }
            
            $result = mysqli_stmt_get_result($stmt);
            $students = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $students[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $students]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get':
        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
                exit();
            }
            
            $stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "i", $id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Database execute error: ' . mysqli_error($conn));
            }
            
            $result = mysqli_stmt_get_result($stmt);
            $student = mysqli_fetch_assoc($result);

            if ($student) {
                echo json_encode(['success' => true, 'data' => $student]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Student not found']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create':
        try {
            $studentNo   = trim($_POST['student_no'] ?? '');
            $fullName    = trim($_POST['full_name'] ?? '');
            $email       = trim($_POST['email'] ?? '');
            $phone       = trim($_POST['phone'] ?? '');
            $address     = trim($_POST['address'] ?? '');
            $departmentId = isset($_POST['department_id']) && $_POST['department_id'] !== '' ? (int)$_POST['department_id'] : null;
            $yearLevel   = (int)($_POST['year_level'] ?? 1);
            $status      = $_POST['status'] ?? 'Active';
            $admissionDate = $_POST['admission_date'] ?? date('Y-m-d');

            // Validation
            if ($studentNo === '' || $fullName === '') {
                echo json_encode(['success' => false, 'message' => 'Student No. and Full Name are required.']);
                exit();
            }

            // Check for duplicate student_no
            $check = mysqli_prepare($conn, "SELECT id FROM students WHERE student_no = ?");
            mysqli_stmt_bind_param($check, "s", $studentNo);
            mysqli_stmt_execute($check);
            $result = mysqli_stmt_get_result($check);
            if (mysqli_num_rows($result) > 0) {
                echo json_encode(['success' => false, 'message' => 'Student No. already exists.']);
                exit();
            }

            // Insert student
$stmt = mysqli_prepare($conn, "INSERT INTO students (student_no, full_name, email, phone, address, department_id, year_level, status, admission_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");            if (!$stmt) {
                throw new Exception('Database prepare error: ' . mysqli_error($conn));
            }
            
mysqli_stmt_bind_param($stmt, "sssssiiss", $studentNo, $fullName, $email, $phone, $address, $departmentId, $yearLevel, $status, $admissionDate);

            if (mysqli_stmt_execute($stmt)) {
                $newId = mysqli_insert_id($conn);
                
                // Log activity
                $desc = "New Student registered: " . mysqli_real_escape_string($conn, $fullName);
                mysqli_query($conn, "INSERT INTO activity_log (activity_type, description) VALUES ('student', '$desc')");
                
                echo json_encode(['success' => true, 'message' => 'Student added successfully.', 'id' => $newId]);
            } else {
                throw new Exception('Database error: ' . mysqli_error($conn));
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'update':
        try {
            $id          = (int)($_POST['id'] ?? 0);
            $studentNo   = trim($_POST['student_no'] ?? '');
            $fullName    = trim($_POST['full_name'] ?? '');
            $email       = trim($_POST['email'] ?? '');
            $phone       = trim($_POST['phone'] ?? '');
            $departmentId = isset($_POST['department_id']) && $_POST['department_id'] !== '' ? (int)$_POST['department_id'] : null;
            $yearLevel   = (int)($_POST['year_level'] ?? 1);
            $status      = $_POST['status'] ?? 'Active';
            $admissionDate = $_POST['admission_date'] ?? date('Y-m-d');

            // Validation
            if ($id <= 0 || $studentNo === '' || $fullName === '') {
                echo json_encode(['success' => false, 'message' => 'Student No. and Full Name are required.']);
                exit();
            }

            // Check if student exists
            $check = mysqli_prepare($conn, "SELECT id FROM students WHERE id = ?");
            mysqli_stmt_bind_param($check, "i", $id);
            mysqli_stmt_execute($check);
            $result = mysqli_stmt_get_result($check);
            if (mysqli_num_rows($result) === 0) {
                echo json_encode(['success' => false, 'message' => 'Student not found.']);
                exit();
            }

            // Check for duplicate student_no (excluding current student)
            $check = mysqli_prepare($conn, "SELECT id FROM students WHERE student_no = ? AND id != ?");
            mysqli_stmt_bind_param($check, "si", $studentNo, $id);
            mysqli_stmt_execute($check);
            $result = mysqli_stmt_get_result($check);
            if (mysqli_num_rows($result) > 0) {
                echo json_encode(['success' => false, 'message' => 'Student No. already exists for another student.']);
                exit();
            }

            // Update student
            $stmt = mysqli_prepare($conn, "UPDATE students SET student_no=?, full_name=?, email=?, phone=?, department_id=?, year_level=?, status=?, admission_date=? WHERE id=?");
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "ssssiissi", $studentNo, $fullName, $email, $phone, $departmentId, $yearLevel, $status, $admissionDate, $id);

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Student updated successfully.']);
            } else {
                throw new Exception('Database error: ' . mysqli_error($conn));
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete':
        try {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid student ID.']);
                exit();
            }
            
            // Check if student exists
            $check = mysqli_prepare($conn, "SELECT full_name FROM students WHERE id = ?");
            mysqli_stmt_bind_param($check, "i", $id);
            mysqli_stmt_execute($check);
            $result = mysqli_stmt_get_result($check);
            $student = mysqli_fetch_assoc($result);
            
            if (!$student) {
                echo json_encode(['success' => false, 'message' => 'Student not found.']);
                exit();
            }
            
            // Delete student
            $stmt = mysqli_prepare($conn, "DELETE FROM students WHERE id = ?");
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "i", $id);

            if (mysqli_stmt_execute($stmt)) {
                // Log activity
                $desc = "Student deleted: " . mysqli_real_escape_string($conn, $student['full_name']);
                mysqli_query($conn, "INSERT INTO activity_log (activity_type, description) VALUES ('student', '$desc')");
                
                echo json_encode(['success' => true, 'message' => 'Student deleted successfully.']);
            } else {
                throw new Exception('Database error: ' . mysqli_error($conn));
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action. Available actions: list, get, create, update, delete']);
}
?>