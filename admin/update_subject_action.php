<?php
// update_subject_action.php
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
$code = trim($_POST['code'] ?? '');
$type = $_POST['type'] ?? 'theory';
$class_id = $_POST['class_id'] ?: null;

if (empty($id) || empty($name) || empty($code)) {
    echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Check if subject code exists for another subject
    $stmt = $pdo->prepare("SELECT id FROM subjects WHERE code = ? AND id != ?");
    $stmt->execute([$code, $id]);
    if ($stmt->fetch()) {
        throw new Exception("Subject code already exists.");
    }
    
    // Update subject
    $stmt = $pdo->prepare("UPDATE subjects SET name = ?, code = ?, type = ?, class_id = ? WHERE id = ?");
    $stmt->execute([$name, $code, $type, $class_id, $id]);
    
    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Subject updated successfully!']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}