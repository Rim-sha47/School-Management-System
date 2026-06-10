<?php
// update_student_action.php
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
$registration_number = trim($_POST['registration_number'] ?? '');
$class_id = $_POST['class_id'] ?? null;

if (empty($id) || empty($first_name) || empty($last_name) || empty($registration_number)) {
    echo json_encode(['status' => 'error', 'message' => 'All required fields must be filled.']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Check if registration number exists for another student
    $stmt = $pdo->prepare("SELECT id FROM students WHERE registration_number = ? AND id != ?");
    $stmt->execute([$registration_number, $id]);
    if ($stmt->fetch()) {
        throw new Exception("Registration number already exists for another student.");
    }
    
    // Update student
    $stmt = $pdo->prepare("UPDATE students SET first_name = ?, last_name = ?, registration_number = ?, class_id = ? WHERE id = ?");
    $stmt->execute([$first_name, $last_name, $registration_number, $class_id, $id]);
    
    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Student updated successfully!']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}