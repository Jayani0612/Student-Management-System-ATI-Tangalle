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

switch ($action) {

    case 'list':
        try {
            $search = trim($_GET['search'] ?? '');
            $sql = "SELECT c.*, d.name AS department_name, t.full_name AS teacher_name
                    FROM courses c
                    LEFT JOIN departments d ON c.department_id = d.id
                    LEFT JOIN teachers t ON c.teacher_id = t.id";
            $params = [];
            $types = '';

            if ($search !== '') {
                $sql .= " WHERE c.course_name LIKE ? OR c.course_code LIKE ?";
                $like = "%$search%";
                $params = [$like, $like];
                $types = 'ss';
            }
            $sql .= " ORDER BY c.id DESC";

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
            $courses = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $courses[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $courses]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'get':
        try {
            $id = (int)($_GET['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
                exit();
            }
            
            $stmt = mysqli_prepare($conn, "SELECT * FROM courses WHERE id = ?");
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "i", $id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('Database execute error: ' . mysqli_error($conn));
            }
            
            $result = mysqli_stmt_get_result($stmt);
            $course = mysqli_fetch_assoc($result);

            if ($course) {
                echo json_encode(['success' => true, 'data' => $course]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Course not found']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create':
        try {
            $courseCode = trim($_POST['course_code'] ?? '');
            $courseName = trim($_POST['course_name'] ?? '');
            $departmentId = isset($_POST['department_id']) && $_POST['department_id'] !== '' ? (int)$_POST['department_id'] : null;
            $credits = (int)($_POST['credits'] ?? 3);
            $teacherId = isset($_POST['teacher_id']) && $_POST['teacher_id'] !== '' ? (int)$_POST['teacher_id'] : null;
            $status = $_POST['status'] ?? 'Active';

            if ($courseCode === '' || $courseName === '') {
                echo json_encode(['success' => false, 'message' => 'Course Code and Course Name are required.']);
                exit();
            }

            // Check for duplicate course_code
            $check = mysqli_prepare($conn, "SELECT id FROM courses WHERE course_code = ?");
            mysqli_stmt_bind_param($check, "s", $courseCode);
            mysqli_stmt_execute($check);
            $result = mysqli_stmt_get_result($check);
            if (mysqli_num_rows($result) > 0) {
                echo json_encode(['success' => false, 'message' => 'Course Code already exists.']);
                exit();
            }

            $stmt = mysqli_prepare($conn, "INSERT INTO courses (course_code, course_name, department_id, credits, teacher_id, status) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "ssiiis", $courseCode, $courseName, $departmentId, $credits, $teacherId, $status);

            if (mysqli_stmt_execute($stmt)) {
                $newId = mysqli_insert_id($conn);
                echo json_encode(['success' => true, 'message' => 'Course added successfully.', 'id' => $newId]);
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
            $courseCode = trim($_POST['course_code'] ?? '');
            $courseName = trim($_POST['course_name'] ?? '');
            $departmentId = isset($_POST['department_id']) && $_POST['department_id'] !== '' ? (int)$_POST['department_id'] : null;
            $credits = (int)($_POST['credits'] ?? 3);
            $teacherId = isset($_POST['teacher_id']) && $_POST['teacher_id'] !== '' ? (int)$_POST['teacher_id'] : null;
            $status = $_POST['status'] ?? 'Active';

            if ($id <= 0 || $courseCode === '' || $courseName === '') {
                echo json_encode(['success' => false, 'message' => 'Course Code and Course Name are required.']);
                exit();
            }

            // Check if course exists
            $check = mysqli_prepare($conn, "SELECT id FROM courses WHERE id = ?");
            mysqli_stmt_bind_param($check, "i", $id);
            mysqli_stmt_execute($check);
            $result = mysqli_stmt_get_result($check);
            if (mysqli_num_rows($result) === 0) {
                echo json_encode(['success' => false, 'message' => 'Course not found.']);
                exit();
            }

            // Check for duplicate course_code (excluding current course)
            $check = mysqli_prepare($conn, "SELECT id FROM courses WHERE course_code = ? AND id != ?");
            mysqli_stmt_bind_param($check, "si", $courseCode, $id);
            mysqli_stmt_execute($check);
            $result = mysqli_stmt_get_result($check);
            if (mysqli_num_rows($result) > 0) {
                echo json_encode(['success' => false, 'message' => 'Course Code already exists for another course.']);
                exit();
            }

            $stmt = mysqli_prepare($conn, "UPDATE courses SET course_code=?, course_name=?, department_id=?, credits=?, teacher_id=?, status=? WHERE id=?");
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "ssiiisi", $courseCode, $courseName, $departmentId, $credits, $teacherId, $status, $id);

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Course updated successfully.']);
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
                echo json_encode(['success' => false, 'message' => 'Invalid course ID.']);
                exit();
            }
            
            // Check if course exists
            $check = mysqli_prepare($conn, "SELECT course_name FROM courses WHERE id = ?");
            mysqli_stmt_bind_param($check, "i", $id);
            mysqli_stmt_execute($check);
            $result = mysqli_stmt_get_result($check);
            $course = mysqli_fetch_assoc($result);
            
            if (!$course) {
                echo json_encode(['success' => false, 'message' => 'Course not found.']);
                exit();
            }
            
            $stmt = mysqli_prepare($conn, "DELETE FROM courses WHERE id = ?");
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "i", $id);

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Course deleted successfully.']);
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