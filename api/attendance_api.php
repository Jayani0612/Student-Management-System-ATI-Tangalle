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

$action = $_REQUEST['action'] ?? '';

switch ($action) {

    case 'list':
        try {
            $search = trim($_GET['search'] ?? '');
            $sql = "SELECT a.*, s.full_name AS student_name, s.student_no
                    FROM attendance a
                    JOIN students s ON a.student_id = s.id";
            $params = [];
            $types = '';

            if ($search !== '') {
                $sql .= " WHERE s.full_name LIKE ? OR s.student_no LIKE ?";
                $like = "%$search%";
                $params = [$like, $like];
                $types = 'ss';
            }
            $sql .= " ORDER BY a.attendance_date DESC, a.id DESC";

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
            $rows = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $rows]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get':
        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                exit();
            }
            
            $stmt = mysqli_prepare($conn, "SELECT * FROM attendance WHERE id = ?");
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "i", $id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Database execute error: ' . mysqli_error($conn));
            }
            
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);

            if ($row) {
                echo json_encode(['success' => true, 'data' => $row]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Attendance record not found']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create':
        try {
            $studentId = (int)($_POST['student_id'] ?? 0);
            $date = $_POST['attendance_date'] ?? date('Y-m-d');
            $status = $_POST['status'] ?? 'Present';

            if ($studentId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please select a student.']);
                exit();
            }

            // Check if student exists
            $check = mysqli_prepare($conn, "SELECT id FROM students WHERE id = ?");
            mysqli_stmt_bind_param($check, "i", $studentId);
            mysqli_stmt_execute($check);
            $result = mysqli_stmt_get_result($check);
            if (mysqli_num_rows($result) === 0) {
                echo json_encode(['success' => false, 'message' => 'Student not found.']);
                exit();
            }

            // Check for duplicate attendance
            $check = mysqli_prepare($conn, "SELECT id FROM attendance WHERE student_id = ? AND attendance_date = ?");
            mysqli_stmt_bind_param($check, "is", $studentId, $date);
            mysqli_stmt_execute($check);
            $result = mysqli_stmt_get_result($check);
            if (mysqli_num_rows($result) > 0) {
                echo json_encode(['success' => false, 'message' => 'Attendance already recorded for this student on this date.']);
                exit();
            }

            $stmt = mysqli_prepare($conn, "INSERT INTO attendance (student_id, attendance_date, status) VALUES (?, ?, ?)");
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "iss", $studentId, $date, $status);

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Attendance recorded successfully.', 'id' => mysqli_insert_id($conn)]);
            } else {
                throw new Exception('Database error: ' . mysqli_error($conn));
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'update':
        try {
            $id = (int)($_POST['id'] ?? 0);
            $studentId = (int)($_POST['student_id'] ?? 0);
            $date = $_POST['attendance_date'] ?? date('Y-m-d');
            $status = $_POST['status'] ?? 'Present';

            if ($id <= 0 || $studentId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid data.']);
                exit();
            }

            // Check if record exists
            $check = mysqli_prepare($conn, "SELECT id FROM attendance WHERE id = ?");
            mysqli_stmt_bind_param($check, "i", $id);
            mysqli_stmt_execute($check);
            $result = mysqli_stmt_get_result($check);
            if (mysqli_num_rows($result) === 0) {
                echo json_encode(['success' => false, 'message' => 'Attendance record not found.']);
                exit();
            }

            $stmt = mysqli_prepare($conn, "UPDATE attendance SET student_id=?, attendance_date=?, status=? WHERE id=?");
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "issi", $studentId, $date, $status, $id);

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Attendance updated successfully.']);
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
                echo json_encode(['success' => false, 'message' => 'Invalid record ID.']);
                exit();
            }
            
            // Check if record exists
            $check = mysqli_prepare($conn, "SELECT id FROM attendance WHERE id = ?");
            mysqli_stmt_bind_param($check, "i", $id);
            mysqli_stmt_execute($check);
            $result = mysqli_stmt_get_result($check);
            if (mysqli_num_rows($result) === 0) {
                echo json_encode(['success' => false, 'message' => 'Attendance record not found.']);
                exit();
            }
            
            $stmt = mysqli_prepare($conn, "DELETE FROM attendance WHERE id = ?");
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "i", $id);

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Attendance record deleted successfully.']);
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