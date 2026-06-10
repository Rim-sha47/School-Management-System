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

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$event_date = trim($_POST['event_date'] ?? '');
$type = trim($_POST['type'] ?? 'other');
$start_time = trim($_POST['start_time'] ?? '');
$end_time = trim($_POST['end_time'] ?? '');
$venue = trim($_POST['venue'] ?? '');
$created_by = $_SESSION['user_id'];

if (empty($title) || empty($event_date) || empty($start_time) || empty($end_time) || empty($venue)) {
    echo json_encode(['status' => 'error', 'message' => 'Title, event date, start/end times and venue are required.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, start_time, end_time, venue, type, is_published, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)");
    $stmt->execute([$title, $description, $event_date, $start_time, $end_time, $venue, $type, $created_by]);

    echo json_encode(['status' => 'success', 'message' => 'Event scheduled successfully!']);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
