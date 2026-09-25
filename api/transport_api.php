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
        $sql = "SELECT * FROM transport_routes";
        $params = [];
        $types = '';

        if ($search !== '') {
            $sql .= " WHERE route_name LIKE ? OR vehicle_no LIKE ? OR driver_name LIKE ?";
            $like = "%$search%";
            $params = [$like, $like, $like];
            $types = 'sss';
        }
        $sql .= " ORDER BY id DESC";

        $stmt = mysqli_prepare($conn, $sql);
        if ($params) mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) $rows[] = $row;
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        $stmt = mysqli_prepare($conn, "SELECT * FROM transport_routes WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);

        if ($row) {
            echo json_encode(['success' => true, 'data' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Route not found']);
        }
        break;

    case 'create':
        $routeName  = trim($_POST['route_name'] ?? '');
        $vehicleNo  = trim($_POST['vehicle_no'] ?? '');
        $driverName = trim($_POST['driver_name'] ?? '');
        $capacity   = (int)($_POST['capacity'] ?? 40);
        $stops      = trim($_POST['stops'] ?? '');
        $status     = $_POST['status'] ?? 'Active';

        if ($routeName === '') {
            echo json_encode(['success' => false, 'message' => 'Route name is required.']);
            exit();
        }

        $stmt = mysqli_prepare($conn, "INSERT INTO transport_routes (route_name, vehicle_no, driver_name, capacity, stops, status) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssiss", $routeName, $vehicleNo, $driverName, $capacity, $stops, $status);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Route added successfully.', 'id' => mysqli_insert_id($conn)]);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    case 'update':
        $id         = (int)($_POST['id'] ?? 0);
        $routeName  = trim($_POST['route_name'] ?? '');
        $vehicleNo  = trim($_POST['vehicle_no'] ?? '');
        $driverName = trim($_POST['driver_name'] ?? '');
        $capacity   = (int)($_POST['capacity'] ?? 40);
        $stops      = trim($_POST['stops'] ?? '');
        $status     = $_POST['status'] ?? 'Active';

        if (!$id || $routeName === '') {
            echo json_encode(['success' => false, 'message' => 'Route name is required.']);
            exit();
        }

        $stmt = mysqli_prepare($conn, "UPDATE transport_routes SET route_name=?, vehicle_no=?, driver_name=?, capacity=?, stops=?, status=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssissi", $routeName, $vehicleNo, $driverName, $capacity, $stops, $status, $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Route updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'Invalid route ID.']);
            exit();
        }
        $stmt = mysqli_prepare($conn, "DELETE FROM transport_routes WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);

        if (mysqli_stmt_execute($stmt)) {
            echo json_encode(['success' => true, 'message' => 'Route deleted.']);
        } else {
            echo json_encode(['success' => false, 'message' => mysqli_error($conn)]);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}
