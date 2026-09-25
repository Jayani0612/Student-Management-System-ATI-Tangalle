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
    
    $query = "SELECT f.*, s.full_name as student_name, s.student_no 
              FROM finance f 
              LEFT JOIN students s ON f.student_id = s.id";
    
    if (!empty($search)) {
        $query .= " WHERE f.invoice_no LIKE '%$search%' 
                   OR s.full_name LIKE '%$search%' 
                   OR s.student_no LIKE '%$search%'";
    }
    
    $query .= " ORDER BY f.id DESC";
    
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
    
    $query = "SELECT * FROM finance WHERE id = $id";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
    }
}

function handleCreate($conn) {
    $invoice_no = mysqli_real_escape_string($conn, $_POST['invoice_no'] ?? '');
    $student_id = (int)($_POST['student_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $payment_date = mysqli_real_escape_string($conn, $_POST['payment_date'] ?? date('Y-m-d'));
    $status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Pending');
    $notes = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');
    
    if (empty($invoice_no) || $student_id <= 0 || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invoice number, student, and amount are required']);
        return;
    }
    
    $query = "INSERT INTO finance (invoice_no, student_id, amount, payment_date, status, notes) 
              VALUES ('$invoice_no', $student_id, $amount, '$payment_date', '$status', '$notes')";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Payment record created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
}

function handleUpdate($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $invoice_no = mysqli_real_escape_string($conn, $_POST['invoice_no'] ?? '');
    $student_id = (int)($_POST['student_id'] ?? 0);
    $amount = (float)($_POST['amount'] ?? 0);
    $payment_date = mysqli_real_escape_string($conn, $_POST['payment_date'] ?? date('Y-m-d'));
    $status = mysqli_real_escape_string($conn, $_POST['status'] ?? 'Pending');
    $notes = mysqli_real_escape_string($conn, $_POST['notes'] ?? '');
    
    if ($id <= 0 || empty($invoice_no) || $student_id <= 0 || $amount <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
        return;
    }
    
    $query = "UPDATE finance SET 
              invoice_no = '$invoice_no',
              student_id = $student_id,
              amount = $amount,
              payment_date = '$payment_date',
              status = '$status',
              notes = '$notes'
              WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Payment record updated successfully']);
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
    
    $query = "DELETE FROM finance WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Payment record deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
}
?>