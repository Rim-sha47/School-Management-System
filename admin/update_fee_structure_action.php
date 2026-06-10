<?php
// update_fee_structure_action.php
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
$class_id = $_POST['class_id'] ?: null;
$amount = $_POST['amount'] ?? 0;
$late_fee = $_POST['late_fee'] ?? 0;

if (empty($id) || empty($name) || empty($amount)) {
    echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE fee_structures SET name = ?, class_id = ?, amount = ?, late_fee = ? WHERE id = ?");
    $stmt->execute([$name, $class_id, $amount, $late_fee, $id]);
    echo json_encode(['status' => 'success', 'message' => 'Fee structure updated successfully!']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}