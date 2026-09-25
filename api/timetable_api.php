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
            $sql = "SELECT t.*, c.course_code, c.course_name
                    FROM timetable t
                    JOIN courses c ON t.course_id = c.id";
            $params = [];
            $types = '';

            if ($search !== '') {
                $sql .= " WHERE c.course_code LIKE ? OR c.course_name LIKE ? OR t.room LIKE ?";
                $like = "%$search%";
                $params = [$like, $like, $like];
                $types = 'sss';
            }
            $sql .= " ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), t.start_time";

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
            
            $stmt = mysqli_prepare($conn, "SELECT * FROM timetable WHERE id = ?");
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
                echo json_encode(['success' => false, 'message' => 'Timetable entry not found']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'create':
        try {
            $courseId = (int)($_POST['course_id'] ?? 0);
            $dayOfWeek = $_POST['day_of_week'] ?? 'Monday';
            $startTime = $_POST['start_time'] ?? '08:00:00';
            $endTime = $_POST['end_time'] ?? '09:00:00';
            $room = $_POST['room'] ?? '';

            if ($courseId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please select a course.']);
                exit();
            }

            // Check if course exists
            $check = mysqli_prepare($conn, "SELECT id FROM courses WHERE id = ?");
            mysqli_stmt_bind_param($check, "i", $courseId);
            mysqli_stmt_execute($check);
            $result = mysqli_stmt_get_result($check);
            if (mysqli_num_rows($result) === 0) {
                echo json_encode(['success' => false, 'message' => 'Course not found.']);
                exit();
            }

            // Check for duplicate timetable entry
            $check = mysqli_prepare($conn, "SELECT id FROM timetable WHERE day_of_week = ? AND start_time = ? AND room = ?");
            mysqli_stmt_bind_param($check, "sss", $dayOfWeek, $startTime, $room);
            mysqli_stmt_execute($check);
            $result = mysqli_stmt_get_result($check);
            if (mysqli_num_rows($result) > 0) {
                echo json_encode(['success' => false, 'message' => 'A schedule already exists for this time and room.']);
                exit();
            }

            $stmt = mysqli_prepare($conn, "INSERT INTO timetable (course_id, day_of_week, start_time, end_time, room) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "issss", $courseId, $dayOfWeek, $startTime, $endTime, $room);

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Schedule added successfully.', 'id' => mysqli_insert_id($conn)]);
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
            $courseId = (int)($_POST['course_id'] ?? 0);
            $dayOfWeek = $_POST['day_of_week'] ?? 'Monday';
            $startTime = $_POST['start_time'] ?? '08:00:00';
            $endTime = $_POST['end_time'] ?? '09:00:00';
            $room = $_POST['room'] ?? '';

            if ($id <= 0 || $courseId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid data.']);
                exit();
            }

            // Check if record exists
            $check = mysqli_prepare($conn, "SELECT id FROM timetable WHERE id = ?");
            mysqli_stmt_bind_param($check, "i", $id);
            mysqli_stmt_execute($check);
            $result = mysqli_stmt_get_result($check);
            if (mysqli_num_rows($result) === 0) {
                echo json_encode(['success' => false, 'message' => 'Timetable entry not found.']);
                exit();
            }

            $stmt = mysqli_prepare($conn, "UPDATE timetable SET course_id=?, day_of_week=?, start_time=?, end_time=?, room=? WHERE id=?");
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "issssi", $courseId, $dayOfWeek, $startTime, $endTime, $room, $id);

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Schedule updated successfully.']);
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
            $check = mysqli_prepare($conn, "SELECT id FROM timetable WHERE id = ?");
            mysqli_stmt_bind_param($check, "i", $id);
            mysqli_stmt_execute($check);
            $result = mysqli_stmt_get_result($check);
            if (mysqli_num_rows($result) === 0) {
                echo json_encode(['success' => false, 'message' => 'Timetable entry not found.']);
                exit();
            }
            
            $stmt = mysqli_prepare($conn, "DELETE FROM timetable WHERE id = ?");
            if (!$stmt) {
                throw new Exception('Database prepare error: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "i", $id);

            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['success' => true, 'message' => 'Schedule deleted successfully.']);
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