<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$vehicle_id = trim($_POST['vehicle_id'] ?? '');
$stops = trim($_POST['stops'] ?? '');
$fare = trim($_POST['fare'] ?? '0');
$description = trim($_POST['description'] ?? '');

if (empty($name) || empty($stops)) {
    echo json_encode(['status' => 'error', 'message' => 'Route name and stops are required.']);
    exit;
}

try {
    $v_id = empty($vehicle_id) ? null : $vehicle_id;

    $stmt = $pdo->prepare("INSERT INTO routes (name, vehicle_id, stops, fare, description, status) VALUES (?, ?, ?, ?, ?, 'active')");
    $stmt->execute([$name, $v_id, $stops, $fare, $description]);

    echo json_encode(['status' => 'success', 'message' => 'Route added successfully!']);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
