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

$name = trim($_POST['name'] ?? '');
$type = trim($_POST['type'] ?? 'other');
$class_id = trim($_POST['class_id'] ?? '');
$session_id = trim($_POST['session_id'] ?? '1');
$start_date = trim($_POST['start_date'] ?? '');
$end_date = trim($_POST['end_date'] ?? '');
$total_marks = trim($_POST['total_marks'] ?? '100');
$passing_marks = trim($_POST['passing_marks'] ?? '40');

if (empty($name) || empty($class_id) || empty($start_date) || empty($end_date)) {
    echo json_encode(['status' => 'error', 'message' => 'Exam name, class, start date, and end date are required.']);
    exit;
}

try {
    // Determine status based on dates
    $today = date('Y-m-d');
    if ($today < $start_date) {
        $status = 'upcoming';
    } elseif ($today > $end_date) {
        $status = 'completed';
    } else {
        $status = 'ongoing';
    }

    $stmt = $pdo->prepare("INSERT INTO exams (name, type, class_id, session_id, start_date, end_date, total_marks, passing_marks, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $type, $class_id, $session_id, $start_date, $end_date, $total_marks, $passing_marks, $status]);

    echo json_encode(['status' => 'success', 'message' => 'Exam schedule added successfully!']);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
