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
    
    $query = "SELECT * FROM books";
    
    if (!empty($search)) {
        $query .= " WHERE title LIKE '%$search%' 
                   OR author LIKE '%$search%' 
                   OR category LIKE '%$search%'
                   OR isbn LIKE '%$search%'";
    }
    
    $query .= " ORDER BY title ASC";
    
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
    
    $query = "SELECT * FROM books WHERE id = $id";
    $result = mysqli_query($conn, $query);
    
    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Record not found']);
    }
}

function handleCreate($conn) {
    $title = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $author = mysqli_real_escape_string($conn, $_POST['author'] ?? '');
    $category = mysqli_real_escape_string($conn, $_POST['category'] ?? '');
    $isbn = mysqli_real_escape_string($conn, $_POST['isbn'] ?? '');
    $total_copies = (int)($_POST['total_copies'] ?? 1);
    $available_copies = (int)($_POST['available_copies'] ?? 1);
    
    if (empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Book title is required']);
        return;
    }
    
    if ($available_copies > $total_copies) {
        echo json_encode(['success' => false, 'message' => 'Available copies cannot exceed total copies']);
        return;
    }
    
    // Check if ISBN already exists (if provided)
    if (!empty($isbn)) {
        $check_query = "SELECT id FROM books WHERE isbn = '$isbn'";
        $check_result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($check_result) > 0) {
            echo json_encode(['success' => false, 'message' => 'ISBN already exists']);
            return;
        }
    }
    
    $query = "INSERT INTO books (title, author, category, isbn, total_copies, available_copies) 
              VALUES ('$title', '$author', '$category', '$isbn', $total_copies, $available_copies)";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Book created successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
}

function handleUpdate($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $title = mysqli_real_escape_string($conn, $_POST['title'] ?? '');
    $author = mysqli_real_escape_string($conn, $_POST['author'] ?? '');
    $category = mysqli_real_escape_string($conn, $_POST['category'] ?? '');
    $isbn = mysqli_real_escape_string($conn, $_POST['isbn'] ?? '');
    $total_copies = (int)($_POST['total_copies'] ?? 1);
    $available_copies = (int)($_POST['available_copies'] ?? 1);
    
    if ($id <= 0 || empty($title)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
        return;
    }
    
    if ($available_copies > $total_copies) {
        echo json_encode(['success' => false, 'message' => 'Available copies cannot exceed total copies']);
        return;
    }
    
    // Check if ISBN already exists (excluding current book)
    if (!empty($isbn)) {
        $check_query = "SELECT id FROM books WHERE isbn = '$isbn' AND id != $id";
        $check_result = mysqli_query($conn, $check_query);
        if (mysqli_num_rows($check_result) > 0) {
            echo json_encode(['success' => false, 'message' => 'ISBN already exists']);
            return;
        }
    }
    
    $query = "UPDATE books SET 
              title = '$title',
              author = '$author',
              category = '$category',
              isbn = '$isbn',
              total_copies = $total_copies,
              available_copies = $available_copies
              WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Book updated successfully']);
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
    
    $query = "DELETE FROM books WHERE id = $id";
    
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'message' => 'Book deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
    }
}
?>