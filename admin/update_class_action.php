<?php
// update_class_action.php
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
$numeric_name = $_POST['numeric_name'] ?? null;
$capacity = $_POST['capacity'] ?? 40;

if (empty($id) || empty($name) || empty($numeric_name)) {
    echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Check if class name exists for another record
    $stmt = $pdo->prepare("SELECT id FROM classes WHERE name = ? AND id != ?");
    $stmt->execute([$name, $id]);
    if ($stmt->fetch()) {
        throw new Exception("Class name already exists.");
    }
    
    // Update class
    $stmt = $pdo->prepare("UPDATE classes SET name = ?, numeric_name = ?, capacity = ? WHERE id = ?");
    $stmt->execute([$name, $numeric_name, $capacity, $id]);
    
    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Class updated successfully!']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}