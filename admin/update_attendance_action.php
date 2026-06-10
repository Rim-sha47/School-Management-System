<?php
// update_attendance_action.php
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
$status = $_POST['status'] ?? 'present';
$remarks = trim($_POST['remarks'] ?? '');

if (empty($id)) {
    echo json_encode(['status' => 'error', 'message' => 'Record ID required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE attendance SET status = ?, remarks = ? WHERE id = ?");
    $stmt->execute([$status, $remarks ?: null, $id]);
    echo json_encode(['status' => 'success', 'message' => 'Attendance updated successfully!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}