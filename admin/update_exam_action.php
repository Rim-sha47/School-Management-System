<?php
// update_exam_action.php
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
$type = $_POST['type'] ?? 'mid_term';
$class_id = $_POST['class_id'] ?: null;
$session_id = $_POST['session_id'] ?: null;
$start_date = $_POST['start_date'] ?? null;
$end_date = $_POST['end_date'] ?? null;
$total_marks = $_POST['total_marks'] ?? 100;
$passing_marks = $_POST['passing_marks'] ?? 40;

if (empty($id) || empty($name)) {
    echo json_encode(['status' => 'error', 'message' => 'Exam name is required.']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Update exam
    $stmt = $pdo->prepare("UPDATE exams SET name = ?, type = ?, class_id = ?, session_id = ?, start_date = ?, end_date = ?, total_marks = ?, passing_marks = ? WHERE id = ?");
    $stmt->execute([$name, $type, $class_id, $session_id, $start_date, $end_date, $total_marks, $passing_marks, $id]);
    
    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Exam updated successfully!']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}