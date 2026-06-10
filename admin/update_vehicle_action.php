<?php
// update_vehicle_action.php
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
$registration_no = trim($_POST['registration_no'] ?? '');
$make_model = trim($_POST['make_model'] ?? '');
$capacity = $_POST['capacity'] ?? 30;
$status = $_POST['status'] ?? 'active';
$driver_name = trim($_POST['driver_name'] ?? '');
$driver_phone = trim($_POST['driver_phone'] ?? '');

if (empty($id) || empty($registration_no) || empty($make_model)) {
    echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE vehicles SET registration_no = ?, make_model = ?, capacity = ?, status = ?, driver_name = ?, driver_phone = ? WHERE id = ?");
    $stmt->execute([$registration_no, $make_model, $capacity, $status, $driver_name ?: null, $driver_phone ?: null, $id]);
    echo json_encode(['status' => 'success', 'message' => 'Vehicle updated successfully!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}