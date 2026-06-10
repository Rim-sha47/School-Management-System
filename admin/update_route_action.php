<?php
// update_route_action.php
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

$id = $_POST['id'] ?? null;
$name = trim($_POST['name'] ?? '');
$vehicle_id = $_POST['vehicle_id'] ?: null;
$fare = $_POST['fare'] ?? 0;
$stops = trim($_POST['stops'] ?? '');

if (empty($id) || empty($name) || empty($fare)) {
    echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE routes SET name = ?, vehicle_id = ?, fare = ?, stops = ? WHERE id = ?");
    $stmt->execute([$name, $vehicle_id, $fare, $stops, $id]);
    echo json_encode(['status' => 'success', 'message' => 'Route updated successfully!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}