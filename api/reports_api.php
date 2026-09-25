<?php
session_start();
require_once '../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'students':
        getStudentsReport($conn);
        break;
    case 'teachers':
        getTeachersReport($conn);
        break;
    case 'courses':
        getCoursesReport($conn);
        break;
    case 'payments':
        getPaymentsReport($conn);
        break;
    case 'hostel':
        getHostelReport($conn);
        break;
    case 'library':
        getLibraryReport($conn);
        break;
    case 'transport':
        getTransportReport($conn);
        break;
    case 'events':
        getEventsReport($conn);
        break;
    case 'exams':
        getExamsReport($conn);
        break;
    case 'attendance':
        getAttendanceReport($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid report type']);
        break;
}

function getStudentsReport($conn) {
    $query = "SELECT student_no, full_name, email, phone, 
              (SELECT name FROM departments WHERE id = students.department_id) as department,
              year_level, status, admission_date
              FROM students ORDER BY full_name";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
}

function getTeachersReport($conn) {
    $query = "SELECT teacher_no, full_name, email, phone,
              (SELECT name FROM departments WHERE id = teachers.department_id) as department,
              subject, joined_date
              FROM teachers ORDER BY full_name";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
}

function getCoursesReport($conn) {
    $query = "SELECT course_code, course_name,
              (SELECT name FROM departments WHERE id = courses.department_id) as department,
              credits,
              (SELECT full_name FROM teachers WHERE id = courses.teacher_id) as teacher,
              status
              FROM courses ORDER BY course_code";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
}

function getPaymentsReport($conn) {
    $query = "SELECT invoice_no,
              (SELECT full_name FROM students WHERE id = payments.student_id) as student,
              amount, payment_date, status, notes
              FROM payments ORDER BY payment_date DESC";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
}

function getHostelReport($conn) {
    $query = "SELECT room_no, block, room_type, capacity, occupied,
              (capacity - occupied) as available, status
              FROM hostel_rooms ORDER BY block, room_no";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
}

function getLibraryReport($conn) {
    $query = "SELECT title, author, isbn, category, total_copies,
              available_copies, (total_copies - available_copies) as borrowed
              FROM books ORDER BY title";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
}

function getTransportReport($conn) {
    $query = "SELECT route_name, vehicle_no, driver_name, capacity, stops, status
              FROM transport_routes ORDER BY route_name";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
}

function getEventsReport($conn) {
    $query = "SELECT title, event_date, time_label, location
              FROM events ORDER BY event_date";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
}

function getExamsReport($conn) {
    $query = "SELECT e.exam_name,
              (SELECT course_code FROM courses WHERE id = e.course_id) as course_code,
              (SELECT course_name FROM courses WHERE id = e.course_id) as course_name,
              e.exam_date, e.time_label, e.room, e.status
              FROM exams e ORDER BY e.exam_date";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
}

function getAttendanceReport($conn) {
    $query = "SELECT a.attendance_date,
              (SELECT full_name FROM students WHERE id = a.student_id) as student,
              a.status
              FROM attendance a ORDER BY a.attendance_date DESC LIMIT 100";
    $result = mysqli_query($conn, $query);
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $data]);
}
?>