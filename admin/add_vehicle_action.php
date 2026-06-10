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

$registration_no = trim($_POST['registration_no'] ?? '');
$make_model = trim($_POST['make_model'] ?? '');
$capacity = trim($_POST['capacity'] ?? '30');
$status = trim($_POST['status'] ?? 'active');
$driver_name = trim($_POST['driver_name'] ?? '');
$driver_phone = trim($_POST['driver_phone'] ?? '');

if (empty($registration_no) || empty($make_model)) {
    echo json_encode(['status' => 'error', 'message' => 'Registration number and Make/Model are required.']);
    exit;
}

try {
    // Check uniqueness
    $stmt = $pdo->prepare("SELECT id FROM vehicles WHERE registration_no = ?");
    $stmt->execute([$registration_no]);
    if ($stmt->fetch()) {
        throw new Exception("Registration number already exists in database.");
    }

    $stmt = $pdo->prepare("INSERT INTO vehicles (registration_no, make_model, capacity, driver_name, driver_phone, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$registration_no, $make_model, $capacity, $driver_name, $driver_phone, $status]);

    echo json_encode(['status' => 'success', 'message' => 'Vehicle added to fleet!']);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
