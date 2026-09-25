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
    
    $query = "SELECT * FROM events";
    
    if (!empty($search)) {
        $query .= " WHERE title LIKE '%$search%' 
                   OR location LIKE '%$search%' 
                   OR time_label LIKE '%$search%'";
    }
    
    $query .= " ORDER BY event_date ASC";
    
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
    
    $query = "SELECT * FROM events WHERE id = $id";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
    }
}

function handleCreate($conn) {
    $title = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date'] ?? '');
    $time_label = mysqli_real_escape_string($conn, $_POST['time_label'] ?? '');
    $location = mysqli_real_escape_string($conn, $_POST['location'] ?? '');
    
    if (empty($title) || empty($event_date)) {
        echo json_encode(['success' => false, 'message' => 'Title and event date are required']);
        return;
    }
    
    $query = "INSERT INTO events (title, event_date, time_label, location) 
              VALUES ('$title', '$event_date', '$time_label', '$location')";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Event created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
}

function handleUpdate($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $title = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $event_date = mysqli_real_escape_string($conn, $_POST['event_date'] ?? '');
    $time_label = mysqli_real_escape_string($conn, $_POST['time_label'] ?? '');
    $location = mysqli_real_escape_string($conn, $_POST['location'] ?? '');
    
    if ($id <= 0 || empty($title) || empty($event_date)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
        return;
    }
    
    $query = "UPDATE events SET 
              title = '$title',
              event_date = '$event_date',
              time_label = '$time_label',
              location = '$location'
              WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Event updated successfully']);
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
    
    $query = "DELETE FROM events WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
}
?>