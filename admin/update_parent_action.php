<?php
// update_parent_action.php
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
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$relation = $_POST['relation'] ?? 'father';
$occupation = trim($_POST['occupation'] ?? '');

if (empty($id) || empty($first_name) || empty($last_name)) {
    echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Update parent
    $stmt = $pdo->prepare("UPDATE parents SET first_name = ?, last_name = ?, email = ?, phone = ?, relation = ?, occupation = ? WHERE id = ?");
    $stmt->execute([$first_name, $last_name, $email ?: null, $phone ?: null, $relation, $occupation ?: null, $id]);
    
    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Parent updated successfully!']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}