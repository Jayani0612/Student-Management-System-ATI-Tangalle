<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

// Get action from request
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'list':
        handleList($conn);
        break;
    case 'create':
        handleCreate($conn);
        break;
    case 'update':
        handleUpdate($conn);
        break;
    case 'delete':
        handleDelete($conn);
        break;
    case 'get':
        handleGet($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function handleList($conn) {
    $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
    
    $query = "SELECT e.*, c.course_code, c.course_name 
              FROM exams e 
              LEFT JOIN courses c ON e.course_id = c.id";
    
    if (!empty($search)) {
        $query .= " WHERE e.exam_name LIKE '%$search%' 
                   OR c.course_code LIKE '%$search%'
                   OR c.course_name LIKE '%$search%'
                   OR e.room LIKE '%$search%'";
    }
    
    $query .= " ORDER BY e.exam_date ASC";
    
    $result = mysqli_query($conn, $query);
    $records = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $records[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $records]);
}

function handleGet($conn) {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        return;
    }
    
    $query = "SELECT * FROM exams WHERE id = $id";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
    }
}

function handleCreate($conn) {
    $exam_name = mysqli_real_escape_string($conn, $_POST['exam_name'] ?? '');
    $course_id = (int)($_POST['course_id'] ?? 0);
    $exam_date = mysqli_real_escape_string($conn, $_POST['exam_date'] ?? '');
    $time_label = mysqli_real_escape_string($conn, $_POST['time_label'] ?? '');
    $room = mysqli_real_escape_string($conn, $_POST['room'] ?? '');
    $status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Scheduled');
    
    if (empty($exam_name) || $course_id <= 0 || empty($exam_date)) {
        echo json_encode(['success' => false, 'message' => 'Exam name, course, and date are required']);
        return;
    }
    
    $query = "INSERT INTO exams (exam_name, course_id, exam_date, time_label, room, status) 
              VALUES ('$exam_name', $course_id, '$exam_date', '$time_label', '$room', '$status')";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Exam created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
}

function handleUpdate($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $exam_name = mysqli_real_escape_string($conn, $_POST['exam_name'] ?? '');
    $course_id = (int)($_POST['course_id'] ?? 0);
    $exam_date = mysqli_real_escape_string($conn, $_POST['exam_date'] ?? '');
    $time_label = mysqli_real_escape_string($conn, $_POST['time_label'] ?? '');
    $room = mysqli_real_escape_string($conn, $_POST['room'] ?? '');
    $status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Scheduled');
    
    if ($id <= 0 || empty($exam_name) || $course_id <= 0 || empty($exam_date)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
        return;
    }
    
    $query = "UPDATE exams SET 
              exam_name = '$exam_name',
              course_id = $course_id,
              exam_date = '$exam_date',
              time_label = '$time_label',
              room = '$room',
              status = '$status'
              WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Exam updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
}

function handleDelete($conn) {
    $id = (int)($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        return;
    }
    
    $query = "DELETE FROM exams WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Exam deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
}
?>